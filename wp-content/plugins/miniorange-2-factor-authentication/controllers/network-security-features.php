<?php
/**
 * File contains code related to network security.
 *
 * @package miniOrange-2-factor-authentication/controllers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $mo2f_dir_name;

$nonce = isset( $_POST['mo_security_features_nonce'] ) ? sanitize_key( wp_unslash( $_POST['mo_security_features_nonce'] ) ) : '';

if ( current_user_can( 'manage_options' ) && isset( $_POST['mo_wpns_features'] ) && wp_verify_nonce( $nonce, 'mo_2fa_security_features_nonce' ) ) {
	switch ( sanitize_text_field( wp_unslash( $_POST['mo_wpns_features'] ) ) ) {
		case 'mo_wpns_2fa_with_network_security':
			$security_features = new Mo2fa_Security_Features();
			$security_features->wpns_2fa_with_network_security( $_POST );
			break;
		case 'mo_wpns_2fa_features':
			$security_features = new Mo2fa_Security_Features();
			$security_features->wpns_2fa_features_only();
			break;


	}
}
$network_security_features = MoWpnsUtility::get_mo2f_db_option( 'mo_wpns_2fa_with_network_security', 'get_option' ) ? 'checked' : '';
