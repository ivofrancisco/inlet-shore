<?php
/** The miniOrange enables user to log in through mobile authentication as an additional layer of security over password.
 * Copyright (C) 2015  miniOrange
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @package        miniorange-2-factor-authentication/api
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 */

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-mo2f-api.php';
if ( ! class_exists( 'Miniorange_Rba_Attributes' ) ) {
	/**
	 * CLass for RBA attributes
	 */
	class Miniorange_Rba_Attributes {

		/**
		 * This function returns curl error message.
		 *
		 * @return object
		 */
		public function get_curl_error_message() {
			$message = __( 'Please enable curl extension.', 'miniorange-2-factor-authentication' ) .
				' <a href="admin.php?page=mo_2fa_troubleshooting">' .
				__( 'Click here', 'miniorange-2-factor-authentication' ) .
				' </a> ' .
				__( 'for the steps to enable curl.', 'miniorange-2-factor-authentication' );

			return wp_json_encode(
				array(
					'status'  => 'ERROR',
					'message' => $message,
				)
			);
		}

		/**
		 * This function return app secret
		 *
		 * @return string
		 */
		public function mo2f_get_app_secret() {

			$mo2f_api = new Mo2f_Api();

			$url          = MO_HOST_NAME . '/moas/rest/customer/getapp-secret';
			$customer_key = get_option( 'mo2f_customerKey' );
			$field_string = array(
				'customerId' => $customer_key,
			);

			$http_header_array = $mo2f_api->get_http_header_array();

			return $mo2f_api->mo2f_http_request( $url, $field_string, $http_header_array );
		}

		/**
		 * This function perform google authentication task.
		 *
		 * @param string $useremail user email.
		 * @param string $google_authenticator_name google auth name.
		 * @return string
		 */
		public function mo2f_google_auth_service( $useremail, $google_authenticator_name = '' ) {

			$mo2f_api     = new Mo2f_Api();
			$url          = MO_HOST_NAME . '/moas/api/auth/google-auth-secret';
			$customer_key = get_option( 'mo2f_customerKey' );
			$field_string = array(
				'customerKey'             => $customer_key,
				'username'                => $useremail,
				'googleAuthenticatorName' => $google_authenticator_name,
			);

			$http_header_array = $mo2f_api->get_http_header_array();

			return $mo2f_api->mo2f_http_request( $url, $field_string, $http_header_array );
		}

		/**
		 * This function validate google auth code
		 *
		 * @param string $useremail user email.
		 * @param string $otptoken otp token.
		 * @param string $secret secret.
		 * @return string
		 */
		public function mo2f_validate_google_auth( $useremail, $otptoken, $secret ) {
			if ( MO2F_IS_ONPREM ) {
				include_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'class-google-auth-onpremise.php';
				$gauth_obj          = new Google_auth_onpremise();
				$session_id_encrypt = isset( $_POST['mo2f_session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_session_id'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification has been performed.
				if ( $session_id_encrypt ) {
					$secret_ga = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'secret_ga' );
				} else {
					$secret_ga = $secret;
				}
				$content = $gauth_obj->mo2f_verify_code( $secret_ga, $otptoken );
				$value   = json_decode( $content, true );
				if ( 'SUCCESS' === $value['status'] ) {
					$user    = wp_get_current_user();
					$user_id = $user->ID;
					$gauth_obj->mo_g_auth_set_secret( $user_id, $secret_ga );
					update_user_meta( $user_id, 'mo2f_2FA_method_to_configure', 'Google Authenticator' );
					update_user_meta( $user_id, 'mo2f_external_app_type', 'Google Authenticator' );
					global $mo2fdb_queries;
					$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_configured_2FA_method' => 'Google Authenticator' ) );
				}
			} else {

				$url      = MO_HOST_NAME . '/moas/api/auth/validate-google-auth-secret';
				$mo2f_api = new Mo2f_Api();

				$customer_key = get_option( 'mo2f_customerKey' );
				$field_string = array(
					'customerKey'       => $customer_key,
					'username'          => $useremail,
					'secret'            => $secret,
					'otpToken'          => $otptoken,
					'authenticatorType' => 'GOOGLE AUTHENTICATOR',
				);

				$http_header_array = $mo2f_api->get_http_header_array();
				$content           = $mo2f_api->mo2f_http_request( $url, $field_string, $http_header_array );
			}

			return $content;
		}

	}
}


