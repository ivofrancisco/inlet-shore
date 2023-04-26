<?php
/** The miniOrange enables user to log in through mobile authentication as an additional layer of security over password.
 * Copyright (C) 2023  miniOrange
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
if ( ! class_exists( 'Customer_Setup' ) ) {

	/**
	 *  Class contains functions to send and validate otp tokens.
	 */
	class Customer_Setup extends Customer_Cloud_Setup {


		/**
		 * Function to send otp token.
		 *
		 * @param string $u_key It can be a phone number or email id to which the otp to be sent.
		 * @param string $auth_type Authentication method of the user.
		 * @param string $c_key Customer key of the user.
		 * @param string $api_key Api key of the user.
		 * @param object $currentuser Contains details of current user.
		 * @return string
		 */
		public function send_otp_token( $u_key, $auth_type, $c_key, $api_key, $currentuser = null ) {

			$cloud_methods = array( 'MOBILE AUTHENTICATION', 'PUSH NOTIFICATIONS', 'SMS' );
			if ( MO2F_IS_ONPREM && ! in_array( $auth_type, $cloud_methods, true ) ) {
				include_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-mo2f-onpremredirect.php';
				$mo2f_on_prem_redirect = new Mo2f_OnPremRedirect();
				if ( is_null( $currentuser ) || ! isset( $currentuser ) ) {
					$currentuser = wp_get_current_user();
				}
				$content = $mo2f_on_prem_redirect->on_prem_send_redirect( $u_key, $auth_type, $currentuser );

			} else {

				$content = parent::send_otp_token( $u_key, $auth_type, $c_key, $api_key, $currentuser = null );

			}

			return $content;
		}

		/**
		 * Function to validate the otp token.
		 *
		 * @param string $auth_type Authentication method of user.
		 * @param string $username Username of user.
		 * @param string $transaction_id Transaction id which is used to validate the sent otp token.
		 * @param string $otp_token OTP token received by user.
		 * @param string $c_key Customer key of user.
		 * @param string $customer_api_key Customer api key assigned by IDP to the user.
		 * @param object $current_user Contains details of current user.
		 * @return string
		 */
		public function validate_otp_token( $auth_type, $username, $transaction_id, $otp_token, $c_key, $customer_api_key, $current_user = null ) {

			$content = '';
			if ( MO2F_IS_ONPREM && 'SOFT TOKEN' !== $auth_type && 'OTP Over Email' !== $auth_type && 'SMS' !== $auth_type && 'OTP Over SMS' !== $auth_type ) {
				include_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-mo2f-onpremredirect.php';
				$mo2f_on_prem_redirect = new Mo2f_OnPremRedirect();
				if ( ! isset( $current_user ) || is_null( $current_user ) ) {
					$current_user = wp_get_current_user();
				}
				$content = $mo2f_on_prem_redirect->on_prem_validate_redirect( $auth_type, $otp_token, $current_user );
				// change parameters as per your requirement but make sure other methods are not affected.

			} else {

				$content = parent::validate_otp_token( $auth_type, $username, $transaction_id, $otp_token, $c_key, $customer_api_key, $current_user = null );

			}
			return $content;
		}


	}
}


