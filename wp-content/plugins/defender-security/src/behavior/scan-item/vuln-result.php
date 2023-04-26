<?php

namespace WP_Defender\Behavior\Scan_Item;

use Calotes\Component\Behavior;
use WP_Defender\Component\Error_Code;
use WP_Defender\Model\Scan;
use WP_Defender\Model\Scan_Item;
use WP_Defender\Traits\Formats;

class Vuln_Result extends Behavior {
	use Formats;

	/**
	 * @return array
	 */
	public function to_array(): array {
		$data = $this->owner->raw_data;
		if ( isset( $data['name'], $data['version'], $data['bugs'] ) ) {
			if ( 'wp_core' === $data['type'] ) {
				// Check if the current WP version is the latest.
				$upgrade = ( new \WP_Defender\Component\Security_Tweaks\WP_Version() )->check() ? 'disabled' : 'enabled';
			} else {
				$upgrade = 'enabled';
			}

			return [
				'id' => $this->owner->id,
				'type' => Scan_Item::TYPE_VULNERABILITY,
				'file_name' => $data['name'],
				'short_desc' => sprintf( __( 'Vulnerability found in %s.', 'defender-security' ), $data['version'] ),
				'details' => isset( $data['new_structure'] )
					? $this->get_details_as_array( $data )
					: $this->get_detail_as_string( $data ),
				// Need for all scan items for WP-CLI command. Full path = base slug for this item.
				'full_path' => $data['slug'],
				'new_structure' => isset( $data['new_structure'] ) ? 'yes' : 'no',
				'upgrade' => $upgrade,
			];
		}

		return [];
	}

	/**
	 * @return array
	 */
	public function ignore(): array {
		$scan = Scan::get_last();
		$scan->ignore_issue( $this->owner->id );

		return [
			'message' => __( 'The suspicious file has been successfully ignored.', 'defender-security' ),
		];
	}

	/**
	 * @return array
	 */
	public function unignore(): array {
		$scan = Scan::get_last();
		$scan->unignore_issue( $this->owner->id );

		return [
			'message' => __( 'The suspicious file has been successfully restored.', 'defender-security' ),
		];
	}

	/**
	 * @return mixed
	 */
	public function resolve() {
		$data = $this->owner->raw_data;
		// Redirect for WordPress-type.
		if ( 'wp_core' === $data['type'] ) {
			return [
				'url' => network_admin_url( 'update-core.php' ),
			];
		}

		if ( 'plugin' === $data['type'] ) {
			return $this->upgrade_plugin( $data['slug'] );
		} elseif ( 'theme' === $data['type'] ) {
			return $this->upgrade_theme( $data['base_slug'] );
		}
		// If type does not match.
		return new \WP_Error( Error_Code::INVALID, __( 'Please try again! We could not find the issue type.', 'defender-security' ) );
	}

	/**
	 * @param $slug
	 *
	 * @return array|bool|\WP_Error
	 */
	private function upgrade_theme( $slug ) {
		$skin = new Silent_Skin();
		$upgrader = new \Theme_Upgrader( $skin );
		$ret = $upgrader->upgrade( $slug );

		if ( true === $ret ) {
			$model = Scan::get_last();
			$model->remove_issue( $this->owner->id );

			return [
				'message' => __( 'This item has been resolved.', 'defender-security' ),
			];
		}

		// This is WP error.
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		// Sometimes it returns false because of it could not complete the update process.
		return new \WP_Error( Error_Code::INVALID, __( "We couldn't update your theme. Please try updating with another method.", 'defender-security' ) );
	}

	/**
	 * @param string $slug
	 *
	 * @return array
	 * @since 2.8.1 Change Upgrade plugin logic.
	 */
	private function upgrade_plugin( $slug ): array {
		$skin = new Plugin_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result = $upgrader->bulk_upgrade( [ $slug ] );

		if ( is_wp_error( $skin->result ) ) {
			return [
				'type_notice' => 'error',
				'message' => $skin->result->get_error_message(),
			];
		} elseif ( $skin->get_errors()->has_errors() ) {
			return [
				'type_notice' => 'error',
				'message' => $skin->get_error_messages(),
			];
		} elseif ( is_array( $result ) && ! empty( $result[ $slug ] ) ) {
			/*
			 * Plugin is already at the latest version.
			 *
			 * This may also be the return value if the `update_plugins` site transient is empty,
			 * e.g. when you update two plugins in quick succession before the transient repopulates.
			 *
			 * Preferably something can be done to ensure `update_plugins` isn't empty.
			 * For now, surface some sort of error here.
			 */
			if ( true === $result[ $slug ] ) {
				return [
					'type_notice' => 'error',
					'message' => $upgrader->strings['up_to_date'],
				];
			}
			$model = Scan::get_last();
			$model->remove_issue( $this->owner->id );

			return [
				'message' => __( 'This item has been resolved.', 'defender-security' ),
			];
		} elseif ( false === $result ) {
			return [
				'type_notice' => 'error',
				'message' => __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'defender-security' ),
			];
		}

		return [
			'type_notice' => 'info',
			'message' => __( 'There is no update available for this plugin.', 'defender-security' ),
		];
	}

	/**
	 * @param array $bug
	 *
	 * @return string
	 */
	protected function get_vulnerability_body( array $bug ): string {
		$text = '#' . $bug['title'] . PHP_EOL;
		$text .= '-' . __( 'Vulnerability type:', 'defender-security' ) . ' ' . $bug['vuln_type'] . PHP_EOL;
		if ( empty( $bug['fixed_in'] ) ) {
			$text .= '-' . __( 'No Update Available', 'defender-security' ) . PHP_EOL;
		} else {
			$text .= '-' . __( 'This bug has been fixed in version:', 'defender-security' ) . ' ' . $bug['fixed_in'] . PHP_EOL;
		}

		return $text;
	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	public function get_detail_as_string( $data ): string {
		$strings = [];
		foreach ( $data['bugs'] as $bug ) {
			$strings[] = $this->get_vulnerability_body( $bug );
		}

		return implode( PHP_EOL, $strings );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function get_details_as_array( $data ): array {
		$arr = [];
		foreach ( $data['bugs'] as $bug ) {
			$text = $this->get_vulnerability_body( $bug );
			$arr[] = [
				'score' => $bug['cvss_score'],
				'detail' => str_replace( PHP_EOL, '<br/>', $text ),
			];
		}

		return $arr;
	}
}

if ( ! class_exists( \WP_Upgrader::class ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}
if ( ! class_exists( \Theme_Upgrader::class ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
}

class Silent_Skin extends \Automatic_Upgrader_Skin {
	public function footer() {
		return;
	}

	public function header() {
		return;
	}

	public function feedback( $data, ...$args ) {
		return '';
	}
}

class Plugin_Skin extends \WP_Ajax_Upgrader_Skin {
	public function feedback( $data, ...$args ) {
		return '';
	}
}
