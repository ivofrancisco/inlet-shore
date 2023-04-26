<?php

namespace WP_Defender\Controller;

use Calotes\Component\Request;
use Calotes\Component\Response;
use WP_Defender\Component\Config\Config_Hub_Helper;
use WP_Defender\Controller;
use WP_Defender\Model\Lockout_Ip;
use WP_Defender\Model\Setting\Blacklist_Lockout as Model_Blacklist_Lockout;
use WP_Defender\Traits\Country;
use WP_Defender\Traits\IP;
use WP_Defender\Traits\Continent;
use WP_Defender\Integrations\MaxMind_Geolocation;
use WP_Defender\Behavior\WPMUDEV;
use WP_Error;

/**
 * Class Blacklist
 *
 * @package WP_Defender\Controller
 */
class Blacklist extends Controller {
	use IP, Country, Continent;

	/**
	 * @var string
	 */
	protected $slug = 'wdf-ip-lockout';

	/**
	 * @var Model_Blacklist_Lockout
	 */
	protected $model;

	/**
	 * @var \WP_Defender\Component\Blacklist_Lockout
	 */
	protected $service;

	public function __construct() {
		$this->register_routes();
		add_action( 'defender_enqueue_assets', [ &$this, 'enqueue_assets' ] );
		$this->model = wd_di()->get( Model_Blacklist_Lockout::class );
		$this->service = wd_di()->get( \WP_Defender\Component\Blacklist_Lockout::class );
		add_action( 'wd_blacklist_this_ip', [ &$this, 'blacklist_an_ip' ] );
		// Update MaxMind's DB.
		if ( ! empty( $this->model->maxmind_license_key ) ) {
			if ( ! wp_next_scheduled( 'wpdef_update_geoip' ) ) {
				wp_schedule_event( strtotime( 'next Thursday' ), 'weekly', 'wpdef_update_geoip' );
			}
			// @since 2.8.0 Allows update or remove the database of MaxMind automatic and periodically (MaxMind's TOS).
			$bind_updater = (bool) apply_filters( 'wd_update_maxmind_database', true );
			// Bind to the scheduled updater action.
			if ( $bind_updater ) {
				add_action( 'wpdef_update_geoip', [ &$this, 'update_database' ] );
			}
		}

		if ( ! wp_next_scheduled( 'wpdef_fetch_global_ip_list' ) ) {
			wp_schedule_event( time(), 'daily', 'wpdef_fetch_global_ip_list' );
		}
		add_action( 'wpdef_fetch_global_ip_list', [ $this, 'fetch_global_ip_list' ] );
	}

	/**
	 * Add an IP into blacklist.
	 *
	 * @param string $ip
	 *
	 * @return void
	 */
	public function blacklist_an_ip( string $ip ): void {
		$this->model->add_to_list( $ip, 'blocklist' );
	}

	/**
	 * @return null|void
	 */
	public function enqueue_assets() {
		if ( ! $this->is_page_active() ) {
			return;
		}
		wp_localize_script( 'def-iplockout', 'blacklist', $this->data_frontend() );
	}

	/**
	 * All the variables that we will show on frontend, both in the main page, or dashboard widget.
	 *
	 * @return array
	 */
	public function data_frontend(): array {
		$user_ip = $this->get_user_ip();
		$arr_model = $this->model->export();
		$exist_geodb = $this->service->is_geodb_downloaded();
		// If MaxMind GeoIP DB is downloaded then display the required data.
		if ( $exist_geodb ) {
			$country_list = $this->countries_list();
			$blacklist_countries = array_merge( [ 'all' => __( 'Block all', 'defender-security' ) ], $country_list );
			$whitelist_countries = array_merge( [ 'all' => __( 'Allow all', 'defender-security' ) ], $country_list );
			$countries_with_continents_list = $this->get_countries_with_continents();
		} else {
			$blacklist_countries = [];
			$whitelist_countries = [];
			$countries_with_continents_list = [];
		}

		$wpmudev = wd_di()->get( WPMUDEV::class );

		return array_merge(
			[
				'model' => $arr_model,
				'misc' => [
					'user_ip' => $user_ip,
					'is_geodb_downloaded' => $exist_geodb,
					'blacklist_countries' => $blacklist_countries,
					'whitelist_countries' => $whitelist_countries,
					'current_country' => $this->get_current_country( $user_ip ),
					'no_ips' => '' === $arr_model['ip_blacklist'] && '' === $arr_model['ip_whitelist'],
					'is_dash_activated' => $wpmudev->is_dash_activated(),
					'is_site_connected_to_hub' => $wpmudev->is_site_connected_to_hub(),
					'home_url' => get_home_url(),
					'countries_with_continents_list' => $countries_with_continents_list,
				],
				'hub' => [
					'global_ip_list' => $this->get_global_ip_list(),
					'global_ip_setting_url' => $wpmudev->get_api_base_url() . 'hub2/ip-banning',
				],
			],
			$this->dump_routes_and_nonces()
		);
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 * @defender_route
	 */
	public function save_settings( Request $request ): Response {
		$data = $request->get_data(
			[
				'country_blacklist' => [
					'type' => 'array',
				],
				'country_whitelist' => [
					'type' => 'array',
				],
				'ip_blacklist' => [
					'type' => 'string',
					'sanitize' => 'sanitize_textarea_field',
				],
				'ip_whitelist' => [
					'type' => 'string',
					'sanitize' => 'sanitize_textarea_field',
				],
				'ip_lockout_message' => [
					'type' => 'string',
					'sanitize' => 'sanitize_textarea_field',
				],
				'global_ip_list' => [
					'type' => 'bool',
				],
				'http_ip_header' => [
					'type' => 'string',
					'sanitize' => 'sanitize_text_field',
				],
				'trusted_proxies_ip' => [
					'type' => 'string',
					'sanitize' => 'sanitize_textarea_field',
				],
			]
		);
		$this->model->import( $data );
		if ( $this->model->validate() ) {
			$this->model->save();
			Config_Hub_Helper::set_clear_active_flag();

			return new Response(
				true,
				array_merge(
					[
						'message' => __( 'Your settings have been updated.', 'defender-security' ),
						'auto_close' => true,
					],
					$this->data_frontend()
				)
			);
		}

		return new Response(
			false,
			array_merge(
				[ 'message' => $this->model->get_formatted_errors() ],
				$this->data_frontend()
			)
		);
	}

	/**
	 * Download the GEODB IP from Maxmind.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 * @defender_route
	 */
	public function download_geodb( Request $request ) {
		$data = $request->get_data( [
			'license_key' => [
				'type' => 'string',
				'sanitize' => 'sanitize_text_field'
			]
		] );
		$license_key = $data['license_key'];
		$service_geo = wd_di()->get( MaxMind_Geolocation::class );
		$tmp = $service_geo->get_downloaded_url( $license_key );
		if ( ! is_wp_error( $tmp ) ) {
			$phar = new \PharData( $tmp );
			$path = $this->get_tmp_path() . DIRECTORY_SEPARATOR . 'maxmind';
			if ( ! is_dir( $path ) ) {
				wp_mkdir_p( $path );
			}
			$phar->extractTo( $path, null, true );
			// Todo: move logic for the path to MaxMind_Geolocation class.
			$this->model->geodb_path = $path . DIRECTORY_SEPARATOR . $phar->current()->getFileName() . DIRECTORY_SEPARATOR . $service_geo->get_db_full_name();
			// Save because we'll check for a saved path.
			$this->model->save();

			if ( file_exists( $tmp ) ) {
				unlink( $tmp );
			}

			$country = $this->get_current_country( $this->get_user_ip() );
			$current_country = '';
			if ( ! empty( $country ) && ! empty( $country['iso'] ) ) {
				$current_country = $country['iso'];
				$this->model = $this->service->add_default_whitelisted_country( $this->model, $country['iso'] );
			}
			$this->model->maxmind_license_key = $license_key;
			$this->model->save();

			return new Response( true, [
				'message' => __(
					'You have successfully downloaded Geo IP Database. You can now use this feature to ban any countries to access any area of your website.',
					'defender-security'
				),
				'is_geodb_downloaded' => $this->service->is_geodb_downloaded(),
				'current_country' => $current_country,
			] );
		} else {
			$this->log( 'Error from MaxMind: ' . $tmp->get_error_message() );
			$string = sprintf(
			/* translators: ... */
				__(
					'You have entered an invalid <a target="_blank" href="%s">license key</a>. If you just created the key, please wait 5 minutes before trying to activate it.',
					'defender-security'
				),
				'https://www.maxmind.com/en/accounts/current/license-key'
			);

			if ( ( new WPMUDEV() )->show_support_links() ) {
				$string .= sprintf(
				/* translators: ... */
					__( ' Still having trouble? <a target="_blank" href="%s">Open a support ticket</a>.', 'defender-security' ),
					WP_DEFENDER_SUPPORT_LINK
				);
			}

			return new Response( false, [ 'invalid_text' => $string ] );
		}
	}

	/**
	 * @return void
	 * @defender_route
	 */
	public function export_ips(): void {
		$data = [];

		foreach ( $this->model->get_list( 'blocklist' ) as $ip ) {
			$data[] = [
				'ip' => $ip,
				'type' => 'blocklist',
			];
		}
		foreach ( $this->model->get_list( 'allowlist' ) as $ip ) {
			$data[] = [
				'ip' => $ip,
				'type' => 'allowlist',
			];
		}

		$fp = fopen( 'php://memory', 'w' );
		foreach ( $data as $fields ) {
			fputcsv( $fp, $fields );
		}
		$filename = 'wdf-ips-export-' . gmdate( 'ymdHis' ) . '.csv';
		fseek( $fp, 0 );
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		// Make php send the generated csv lines to the browser.
		fpassthru( $fp );
		exit();
	}

	/**
	 * @param Request $request
	 *
	 * @return void
	 * @throws \Exception
	 * @defender_route
	 */
	public function ip_action( Request $request ): void {
		$data = $request->get_data( [
			'ip' => [
				'type' => 'string',
				'sanitize' => 'sanitize_text_field'
			],
			'behavior' => [
				'type' => 'string',
				'sanitize' => 'sanitize_text_field'
			],
		] );

		$ip = $data['ip'];
		$action = $data['behavior'];
		$models = Lockout_Ip::get( $ip, $action, true );

		foreach( $models as $model )  {
			if ( 'unban' === $action ) {
				$model->status = Lockout_Ip::STATUS_NORMAL;
				$model->save();
			} elseif ( 'ban' === $action ) {
				$model->status = Lockout_Ip::STATUS_BLOCKED;
				$model->save();
			}
		}

		$this->query_locked_ips( $request );
	}

	/**
	 * Bulk ban or unban IPs.
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Exception
	 * @defender_route
	 */
	public function bulk_ip_action( Request $request ) {
		$data = $request->get_data( [
			'behavior' => [
				'type' => 'string',
				'sanitize' => 'sanitize_text_field'
			],
			'ips' => [
				'type' => 'string',
				'sanitize' => 'sanitize_text_field'
			],
		] );

		$status = 'unban' === $data['behavior'] ? Lockout_Ip::STATUS_BLOCKED : Lockout_Ip::STATUS_NORMAL;
		$ips = null;
		$bulk_ips = null;
		$limit	 = 50;

		if ( ! empty( $data['ips'] ) ) {
			$ips = json_decode( $data['ips'] );
			$first_nth_ips = array_slice( $ips, 0, $limit );
			$bulk_ips = wp_list_pluck( $first_nth_ips, 'ip' );
		}

		try {
			$models = Lockout_Ip::get_bulk( $status, $bulk_ips, $limit );
			foreach( $models as $model )  {
				$model->status = ( 'unban' === $data['behavior'] ) ? Lockout_Ip::STATUS_NORMAL : Lockout_Ip::STATUS_BLOCKED;
				$model->save();
			}
			// While bulk banning the IPs, needs to slice the IPs array for next iteration.
			if ( 'ban' === $data['behavior'] ) {
				$ips = array_slice( $ips, $limit );
			}
			// If the queried models are less than the limit it means we are on the last set of IPs.
			if ( (is_array($models) || $models instanceof \Countable ? count( $models ) : 0) < $limit ) {
				return new Response( true, [
					'status' => 'done'
				] );
			}
		} catch( \Exception $e ) {
			return new Response( true, [
				'status' => 'error'
			] );
		}

		return new Response( true, [
			'status' => 'continue',
			'ips' => $ips
		] );
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Exception
	 * @defender_route
	 */
	public function query_locked_ips( Request $request ) {
		$results = \WP_Defender\Model\Lockout_Ip::query_locked_ip();
		$locked_ips = [];
		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $locked_ip ) {
				$locked_ips[] = [
					'id' => $locked_ip['id'],
					'ip' => $locked_ip['ip'],
					'status' => $locked_ip['status'],
				];
			}
		}

		return new Response( true, [
			'ips' => $locked_ips
		] );
	}

	/**
	 * Export the data of this module, we will use this for export to HUB, create a preset etc.
	 */
	public function to_array() {}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	private function adapt_data( array $data ): array {
		$adapted_data = [
			'ip_blacklist' => $data['ip_blacklist'],
			'ip_whitelist' => $data['ip_whitelist'],
			'ip_lockout_message' => $data['ip_lockout_message'],
		];
		if ( isset( $data['geoIP_db'] ) && file_exists( $data['geoIP_db'] ) ) {
			$adapted_data['geodb_path'] = $data['geoIP_db'];
			if ( isset( $data['country_blacklist'] ) ) {
				$adapted_data['country_blacklist'] = $data['country_blacklist'];
			}
			if ( isset( $data['country_whitelist'] ) ) {
				$adapted_data['country_whitelist'] = $data['country_whitelist'];
			}
		}

		return array_merge( $data, $adapted_data );
	}

	/**
	 * Import the data of other source into this, it can be when HUB trigger the import, or user apply a preset.
	 *
	 * @param array $data
	 *
	 * @return null|void
	 */
	public function import_data( $data ) {
		if ( ! empty( $data ) ) {
			// Upgrade for old versions.
			$data = $this->adapt_data( $data );
		} else {
			return;
		}

		$model = $this->model;
		$model->import( $data );
		if ( $model->validate() ) {
			$model->save();
		}
	}

	/**
	 * Remove all settings, configs generated in this container runtime.
	 */
	public function remove_settings() {}

	/**
	 * Remove all data.
	 */
	public function remove_data() {}

	/**
	 * @return array
	 */
	public function export_strings() {
		return [];
	}

	/**
	 * Importing IPs from exporter.
	 *
	 * @param Request $request
	 *
	 * @defender_route
	 * @return Response
	 */
	public function import_ips( Request $request ) {
		$data = $request->get_data(
			[
				'id' => [
					'type' => 'int',
				],
			]
		);
		$attached_id = $data['id'];
		if ( ! is_object( get_post( $attached_id ) ) ) {
			return new Response(
				false,
				[
					'message' => __( 'Your file is invalid!', 'defender-security' ),
				]
			);
		}

		$file = get_attached_file( $attached_id );
		if ( ! is_file( $file ) ) {
			return new Response(
				false,
				[
					'message' => __( 'Your file is invalid!', 'defender-security' ),
				]
			);
		}

		$data = $this->service->verify_import_file( $file );
		if ( ! $data ) {
			return new Response(
				false,
				[
					'message' => __( 'Your file content is invalid!', 'defender-security' ),
				]
			);
		}

		// All good, start to import.
		foreach ( $data as $line ) {
			$this->model->add_to_list( $line[0], $line[1] );
		}

		return new Response(
			true,
			[
				'message' => __( 'Your allowlist/blocklist has been successfully imported.', 'defender-security' ),
				'interval' => 1,
			]
		);
	}

	/**
	 * Update the geolocation database.
	 *
	 * @since 2.8.0
	 *
	 * @return null|void
	 */
	public function update_database() {
		if ( empty( $this->model->maxmind_license_key ) ) {
			return;
		}

		$service_geo = wd_di()->get( MaxMind_Geolocation::class );
		$service_geo->delete_database();

		$tmp = $service_geo->get_downloaded_url( $this->model->maxmind_license_key );
		if ( is_wp_error( $tmp ) ) {
			$this->log( 'CRON error downloading from MaxMind: ' . $tmp->get_error_message() );
			return;
		}

		$geodb_path = $service_geo->extract_db_archive( $tmp );
		if ( is_wp_error( $geodb_path ) ) {
			$this->log( 'CRON error extracting MaxMind archive: ' . $geodb_path->get_error_message() );
			return;
		}
		$this->model->geodb_path = $geodb_path;
		$this->model->save();
	}

	/**
	 * Get Global IP list from DB for frontend data.
	 *
	 * @since 3.4.0
	 * @return array
	 */
	public function get_global_ip_list(): array {
		/**
		 * @var WPMUDEV
		 */
		$wpmudev = wd_di()->get( WPMUDEV::class );
		$global_ip_list = $wpmudev->get_global_ip_list();
		if ( ! is_wp_error( $global_ip_list ) ) {
			return $this->format_global_ip_list( $global_ip_list );
		}

		return [];
	}

	/**
	 * Format Global IP list for frontend.
	 *
	 * @param mixed $data Data that needs to be formatted for frontend.
	 *
	 * @since 3.4.0
	 * @return array
	 */
	public function format_global_ip_list( $data ): array {
		return [
			'allow_list' => ! empty( $data['allow_list'] ) && is_array( $data['allow_list'] ) ?
				implode( PHP_EOL, $data['allow_list'] ) :
				'',
			'block_list' => ! empty( $data['block_list'] ) && is_array( $data['block_list'] ) ?
				implode( PHP_EOL, $data['block_list'] ) :
				'',
			'last_update_time_utc' => ! empty( $data['last_update_time_utc'] ) ?
				date( 'F j, Y', strtotime( $data['last_update_time_utc'] ) ) :
				__( 'Never', 'defender-security' ),
			'last_update_time' => ! empty( $data['last_update_time'] ) ?
				date( 'F j, Y', strtotime( $data['last_update_time'] ) ) :
				__( 'Never', 'defender-security' ),
		];
	}

	/**
	 * Fetch Global IP list from HUB.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function fetch_global_ip_list(): void {
		if ( true === $this->model->global_ip_list ) {
			wd_di()->get( WPMUDEV::class )->fetch_global_ip_list();
		}
	}

	/**
	 * Refresh Global IP list.
	 *
	 * @param Request $request
	 *
	 * @since 3.4.0
	 * @return Response
	 * @defender_route
	 */
	public function refresh_global_ip_list( Request $request ) {
		$data = wd_di()->get( WPMUDEV::class )->fetch_global_ip_list();

		if ( ! is_wp_error( $data ) ) {
			return new Response( true, [
				'message' => __(
					'The global IP addresses have been updated.',
					'defender-security'
				),
				'global_ip_list' => $this->format_global_ip_list( $data ),
			] );
		} else {
			return new Response( false, [
				'message' =>  __(
					'An error occurred while synchronizing the global IPs.',
					'defender-security'
				),
			] );
		}
	}
}
