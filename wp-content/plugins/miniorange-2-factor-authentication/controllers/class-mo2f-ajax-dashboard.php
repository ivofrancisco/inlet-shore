<?php
/**
 * File contains the functions related to the network security.
 *
 * @package miniOrange-2-factor-authentication/controllers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Mo2f_Ajax_Dashboard' ) ) {
	/**
	 * Class Mo2f_Ajax_Dashboard
	 */
	class Mo2f_Ajax_Dashboard {

		/**
		 * Class Mo2f_Ajax_Dashboard constructor
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'mo2f_switch_functions' ) );
		}

		/**
		 * Calls to network security functions according to the switch case.
		 *
		 * @return void
		 */
		public function mo2f_switch_functions() {
			if ( isset( $_POST ) && isset( $_POST['option'] ) ) {
				if ( isset( $_POST['mo_security_features_nonce'] ) && wp_verify_nonce( 'mo_2fa_security_features_nonce', sanitize_key( wp_unslash( $_POST['mo_security_features_nonce'] ) ) ) ) {
					$tab_count = get_site_option( 'mo2f_tab_count', 0 );
					if ( 5 === $tab_count ) {
						update_site_option( 'mo_2f_switch_all', 1 );
					} elseif ( 0 === $tab_count ) {
						update_site_option( 'mo_2f_switch_all', 0 );
					}
					$sanitized_post = isset( $_POST['switch_val'] ) ? sanitize_text_field( wp_unslash( $_POST['switch_val'] ) ) : null;

					switch ( sanitize_text_field( wp_unslash( $_POST['option'] ) ) ) {
						case 'tab_all_switch':
							$this->mo2f_handle_all_enable( $sanitized_post );
							break;
						case 'tab_2fa_switch':
							$this->mo2f_handle_2fa_enable( $sanitized_post );
							break;
						case 'tab_block_switch':
							$this->mo2f_handle_block_enable( $sanitized_post );
							break;

					}
				}
			}
		}


		/**
		 * Calls the network security functions and updates the option in options table.
		 *
		 * @param integer $posted 1 if respective security method enable.
		 * @return void
		 */
		public function mo2f_handle_all_enable( $posted ) {
			$this->mo2f_handle_block_enable( $posted );
			if ( $posted ) {
				update_option( 'mo_2f_switch_all', 1 );
				update_site_option( 'mo2f_tab_count', 5 );
				do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'ALL_ENABLED' ), 'SUCCESS' );
			} else {
				update_option( 'mo_2f_switch_all', 0 );
				update_site_option( 'mo2f_tab_count', 0 );
				do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'ALL_DISABLED' ), 'ERROR' );
			}
		}

		/**
		 * Shows the 2fa enable/disable message.
		 *
		 * @param integer $posted 1 if 2fa method is enabled.
		 * @return void
		 */
		public function mo2f_handle_2fa_enable( $posted ) {
			global $mo2fdb_queries;
			if ( ! check_ajax_referer( 'mo_2fa_security_features_nonce', 'mo_security_features_nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( $error );
			} else {
				$user    = wp_get_current_user();
				$user_id = $user->ID;
				if ( $posted ) {
					$mo2fdb_queries->update_user_details( $user_id, array( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_PLUGIN_SETTINGS' ) );
					if ( isset( $_POST['tab_2fa_switch'] ) && sanitize_text_field( wp_unslash( $_POST['tab_2fa_switch'] ) ) ) {
						do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'TWO_FACTOR_ENABLE' ), 'SUCCESS' );
					}
				} else {
					$mo2fdb_queries->update_user_details( $user_id, array( 'mo_2factor_user_registration_status', 0 ) );
					if ( isset( $_POST['tab_2fa_switch'] ) && sanitize_text_field( wp_unslash( $_POST['tab_2fa_switch'] ) ) ) {
						do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'TWO_FACTOR_DISABLE' ), 'ERROR' );
					}
				}
			}
		}
		/**
		 * Handles the flow when ip block switch is changed.
		 *
		 * @param integer $posted 1 if switch is enabled.
		 * @return void
		 */
		public function mo2f_handle_block_enable( $posted ) {
			if ( ! check_ajax_referer( 'mo_2fa_security_features_nonce', 'mo_security_features_nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( $error );
			} else {
				if ( $posted ) {
					update_site_option( 'mo_2f_switch_adv_block', 1 );
					update_site_option( 'mo2f_tab_count', get_site_option( 'mo2f_tab_count' ) + 1 );
					if ( isset( $_POST['option'] ) ) {
						if ( 'tab_block_switch' === sanitize_text_field( wp_unslash( $_POST['option'] ) ) ) {
							do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'ADV_BLOCK_ENABLE' ), 'SUCCESS' );
						}
					}
				} else {
					update_site_option( 'mo_2f_switch_adv_block', 0 );
					update_site_option( 'mo2f_tab_count', get_site_option( 'mo2f_tab_count' ) - 1 );
					update_site_option( 'mo_wpns_iprange_count', 0 );
					update_site_option( 'mo_wpns_enable_htaccess_blocking', 0 );
					update_site_option( 'mo_wpns_enable_user_agent_blocking', 0 );
					update_site_option( 'mo_wpns_referrers', false );
					update_site_option( 'mo_wpns_countrycodes', false );
					if ( 'tab_block_switch' === sanitize_text_field( wp_unslash( $_POST['option'] ) ) ) {
						do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'ADV_BLOCK_DISABLE' ), 'ERROR' );
					}
				}
			}
		}


	}
}
new Mo2f_ajax_dashboard();

