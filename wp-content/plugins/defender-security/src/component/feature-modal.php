<?php

namespace WP_Defender\Component;

use WP_Defender\Component;

/**
 * Use different actions for "What's new" modals.
 *
 * Class Feature_Modal
 * @package WP_Defender\Component
 * @since 2.5.5
 */
class Feature_Modal extends Component {
	/**
	 * Feature data for the last active "What's new" modal.
	*/
	public const FEATURE_SLUG = 'wd_show_feature_global_ip';
	public const FEATURE_VERSION = '3.6.0';

	/**
	 * Get modals that are displayed on the Dashboard page.
	 *
	 * @return array
	 * @since 2.7.0 Use one template for Welcome modal and dynamic data.
	 */
	public function get_dashboard_modals(): array {
		$title = sprintf(
		/* translators: %s: separator */
			__( "NEW: Manage global IPs with the Hub", 'defender-security' ),
			'<br/>'
		);
		$desc = __( 'You can now create and manage a global IP allowlist and blocklist in your Hub, and sync those lists with any connected website you want.', 'defender-security' );

		return [
			'show_welcome_modal' => $this->display_last_modal( self::FEATURE_SLUG ),
			'welcome_modal' => [
				'title' => $title,
				'desc' => $desc,
				'banner_1x' => defender_asset_url( '/assets/img/modal/welcome-modal.png' ),
				'banner_2x' => defender_asset_url( '/assets/img/modal/welcome-modal@2x.png' ),
				'banner_alt' => __( 'Modal for Global IP lists', 'defender-security' ),
				'button_title' => __( 'Manage Global Ips', 'defender-security' ),
				// Additional information.
				'additional_text' => $this->additional_text(),
			],
		];
	}

	/**
	 * Display modal with the latest changes if:
	 * plugin settings have been reset before -> this is not fresh install,
	 * Whitelabel > Documentation, Tutorials and What’s New Modal > checked Show tab OR Whitelabel is disabled.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function display_last_modal( $key ): bool {
		$info = defender_white_label_status();

		return (bool) get_site_option( 'wd_nofresh_install' )
			&& (bool) get_site_option( $key )
			&& ! $info['hide_doc_link'];
	}

	public function upgrade_site_options() {
		$db_version = get_site_option( 'wd_db_version' );
		$feature_slugs = [
			// Important slugs to display Onboarding, e.g. after the click on Reset settings.
			[
				'slug' => 'wp_defender_shown_activator',
				'vers' => '2.4.0',
			],
			[
				'slug' => 'wp_defender_is_free_activated',
				'vers' => '2.4.0',
			],
			// The latest feature.
			[
				'slug' => 'wd_show_feature_recaptcha_integration',
				'vers' => '3.3.0',
			],
			// The current feature.
			[
				'slug' => self::FEATURE_SLUG,
				'vers' => self::FEATURE_VERSION,
			],
		];
		foreach ( $feature_slugs as $feature ) {
			if ( version_compare( $db_version, $feature['vers'], '==' ) ) {
				// The current feature
				update_site_option( $feature['slug'], true );
			} else {
				// and old one.
				delete_site_option( $feature['slug'] );
			}
		}
	}

	/**
	 * Get additional text.
	 *
	 * @return string
	 */
	private function additional_text(): string {
		return '';
	}
}
