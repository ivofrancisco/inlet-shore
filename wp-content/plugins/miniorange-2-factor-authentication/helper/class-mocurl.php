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
 * @package        miniorange-2-factor-authentication/helper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'MocURL' ) ) {
	/**
	 * This library is miniOrange Authentication Service.
	 * Contains Request Calls to Customer service.
	 **/
	class MocURL {

		/**
		 * This function is invoke to create the customer after registration
		 *
		 * @param string $email .
		 * @param string $company .
		 * @param string $password .
		 * @param string $phone .
		 * @param string $first_name .
		 * @param string $last_name .
		 * @return string
		 */
		public static function create_customer( $email, $company, $password, $phone = '', $first_name = '', $last_name = '' ) {
			$url          = MO_HOST_NAME . '/moas/rest/customer/add';
			$customer_key = MoWpnsConstants::DEFAULT_CUSTOMER_KEY;
			$api_key      = MoWpnsConstants::DEFAULT_API_KEY;

			$fields      = array(
				'companyName'     => $company,
				'areaOfInterest'  => '',
				'productInterest' => '',
				'firstname'       => $first_name,
				'lastname'        => $last_name,
				'email'           => $email,
				'phone'           => $phone,
				'password'        => $password,
			);
			$json        = wp_json_encode( $fields );
			$auth_header = self::create_auth_header( $customer_key, $api_key );
			$response    = self::call_api( $url, $json, $auth_header );
			return $response;
		}
		/**
		 * It will help to get customer key
		 *
		 * @param string $email It will get the customer key.
		 * @param string $password It will get the password.
		 * @return string
		 */
		public static function get_customer_key( $email, $password ) {
			$url      = MO_HOST_NAME . '/moas/rest/customer/key';
			$fields   = array(
				'email'    => $email,
				'password' => $password,
			);
			$json     = wp_json_encode( $fields );
			$response = self::call_api( $url, $json );

			return $response;
		}
		/**
		 * It will help to submit the contact form .
		 *
		 * @param  string  $q_email It is carrying the email address .
		 * @param  string  $q_phone .
		 * @param  string  $query .
		 * @param  boolean $call_setup .
		 * @return string
		 */
		public function submit_contact_us( $q_email, $q_phone, $query, $call_setup = false ) {
			$current_user = wp_get_current_user();
			$url          = MO_HOST_NAME . '/moas/rest/customer/contact-us';

			$is_nc_with_1_user = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' ) && MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NNC', 'get_option' );
			$is_ec_with_1_user = ! MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
			$onprem            = MO2F_IS_ONPREM ? 'O' : 'C';

			$customer_feature = '';

			if ( $is_ec_with_1_user ) {
				$customer_feature = 'V1';
			} elseif ( $is_nc_with_1_user ) {
				$customer_feature = 'V3';
			}
			global $mo_wpns_utility;
			if ( $call_setup ) {
				$query = '[Call Request - WordPress 2 Factor Authentication Plugin: ' . $onprem . $customer_feature . ' - V ' . MO2F_VERSION . ' ]: ' . $query;
			} else {
				$query = '[WordPress 2 Factor Authentication Plugin: ' . $onprem . $customer_feature . ' - V ' . MO2F_VERSION . ' ]: ' . $query;
			}

			$fields       = array(
				'firstName' => $current_user->user_firstname,
				'lastName'  => $current_user->user_lastname,
				'company'   => isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '',
				'email'     => $q_email,
				'ccEmail'   => '2fasupport@xecurify.com',
				'phone'     => $q_phone,
				'query'     => $query,
			);
			$field_string = wp_json_encode( $fields );
			$response     = self::call_api( $url, $field_string );

			return true;
		}
		/**
		 * It will give the details of IP
		 *
		 * @param string $ip .
		 * @return string .
		 */
		public function lookup_ip( $ip ) {
			$url      = MO_HOST_NAME . '/moas/rest/security/iplookup';
			$fields   = array(
				'ip' => $ip,
			);
			$json     = wp_json_encode( $fields );
			$response = self::call_api( $url, $json );
			return $response;
		}
		/**
		 * It is use for sending the otp token
		 *
		 * @param string $auth_type .
		 * @param string $phone .
		 * @param string $email .
		 * @return string
		 */
		public function send_otp_token( $auth_type, $phone, $email ) {
			$url          = MO_HOST_NAME . '/moas/api/auth/challenge';
			$customer_key = MoWpnsConstants::DEFAULT_CUSTOMER_KEY;
			$api_key      = MoWpnsConstants::DEFAULT_API_KEY;

			$fields      = array(
				'customerKey'     => $customer_key,
				'email'           => $email,
				'phone'           => $phone,
				'authType'        => $auth_type,
				'transactionName' => 'miniOrange 2-Factor',
			);
			$json        = wp_json_encode( $fields );
			$auth_header = $this->create_auth_header( $customer_key, $api_key );
			$response    = self::call_api( $url, $json, $auth_header );
			return $response;
		}
		/**
		 * It will be use for validating the otp
		 *
		 * @param string $transaction_id .
		 * @param string $otp_token .
		 * @return string .
		 */
		public function validate_otp_token( $transaction_id, $otp_token ) {
			$url          = MO_HOST_NAME . '/moas/api/auth/validate';
			$customer_key = MoWpnsConstants::DEFAULT_CUSTOMER_KEY;
			$api_key      = MoWpnsConstants::DEFAULT_API_KEY;

			$fields = array(
				'txid'  => $transaction_id,
				'token' => $otp_token,
			);

			$json        = wp_json_encode( $fields );
			$auth_header = $this->create_auth_header( $customer_key, $api_key );
			$response    = self::call_api( $url, $json, $auth_header );
			return $response;
		}
		/**
		 * It will check the customer.
		 *
		 * @param string $email .
		 * @return string
		 */
		public function check_customer( $email ) {
			$url      = MO_HOST_NAME . '/moas/rest/customer/check-if-exists';
			$fields   = array(
				'email' => $email,
			);
			$json     = wp_json_encode( $fields );
			$response = self::call_api( $url, $json );
			return $response;
		}
		/**
		 * Call in forgot password
		 *
		 * @return string
		 */
		public function mo_wpns_forgot_password() {
			$url          = MO_HOST_NAME . '/moas/rest/customer/password-reset';
			$email        = get_option( 'mo2f_email' );
			$customer_key = get_option( 'mo2f_customerKey' );
			$api_key      = get_option( 'mo2f_api_key' );

			$fields      = array(
				'email' => $email,
			);
			$json        = wp_json_encode( $fields );
			$auth_header = $this->create_auth_header( $customer_key, $api_key );
			$response    = self::call_api( $url, $json, $auth_header );
			return $response;
		}
		/**
		 * This will use for notification
		 *
		 * @param string $to_email .
		 * @param string $subject .
		 * @param string $content .
		 * @param string $from_email .
		 * @param string $from_name .
		 * @param string $to_name .
		 * @return string
		 */
		public function send_notification( $to_email, $subject, $content, $from_email, $from_name, $to_name ) {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";

			$headers .= 'From: ' . $from_name . '<' . $from_email . '>' . "\r\n";

			mail( $to_email, $subject, $content, $headers );

			return wp_json_encode(
				array(
					'status'        => 'SUCCESS',
					'statusMessage' => 'SUCCESS',
				)
			);
		}

		// added for feedback.
		/**
		 * Send the email alert
		 *
		 * @param string $email .
		 * @param string $phone .
		 * @param string $message .
		 * @param string $feedback_option .
		 * @return string
		 */
		public function send_email_alert( $email, $phone, $message, $feedback_option ) {
			global $mo_wpns_utility;
			global $user;
			$url          = MO_HOST_NAME . '/moas/api/notify/send';
			$customer_key = MoWpnsConstants::DEFAULT_CUSTOMER_KEY;
			$api_key      = MoWpnsConstants::DEFAULT_API_KEY;
			$from_email   = 'no-reply@xecurify.com';
			$di           = get_site_option( 'No_of_days_active_work' );
			$di           = intval( $di );
			if ( 'mo_wpns_skip_feedback' === $feedback_option ) {
				$subject = 'Deactivate [Feedback Skipped]: WordPress miniOrange 2-Factor Plugin :' . $di;

			} elseif ( 'mo_wpns_feedback' === $feedback_option ) {

				$subject = 'Feedback: WordPress miniOrange 2-Factor Plugin - ' . $email . ' : ' . $di;
			}

			$user = wp_get_current_user();

			$is_nc_with_1_user = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' ) && MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NNC', 'get_option' );
			$is_ec_with_1_user = ! MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
			$onprem            = MO2F_IS_ONPREM ? 'O' : 'C';

			$customer_feature = '';

			if ( $is_ec_with_1_user ) {
				$customer_feature = 'V1';
			} elseif ( $is_nc_with_1_user ) {
				$customer_feature = 'V3';
			}

			$query   = '[WordPress 2 Factor Authentication Plugin: ' . $onprem . $customer_feature . ' - V ' . MO2F_VERSION . ']: ' . $message;
			$company = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
			$content = '<div >Hello, <br><br>First Name :' . $user->user_firstname . '<br><br>Last  Name :' . $user->user_lastname . '   <br><br>Company :<a href="' . $company . '" target="_blank" >' . sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) . '</a><br><br>Phone Number :' . $phone . '<br><br>Email :<a href="mailto:' . esc_html( $email ) . '" target="_blank">' . esc_html( $email ) . '</a><br><br>Query :' . wp_kses_post( $query ) . '</div>';

			$fields       = array(
				'customerKey' => $customer_key,
				'sendEmail'   => true,
				'email'       => array(
					'customerKey' => $customer_key,
					'fromEmail'   => $from_email,
					'fromName'    => 'Xecurify',
					'toEmail'     => '2fasupport@xecurify.com',
					'toName'      => '2fasupport@xecurify.com',
					'subject'     => $subject,
					'content'     => $content,
				),
			);
			$field_string = wp_json_encode( $fields );
			$auth_header  = $this->create_auth_header( $customer_key, $api_key );
			$response     = self::call_api( $url, $field_string, $auth_header );

			return $response;
		}

		/**
		 * It will help to creating header
		 *
		 * @param string $customer_key .
		 * @param string $api_key .
		 * @return string .
		 */
		private static function create_auth_header( $customer_key, $api_key ) {
			$current_timestamp_in_millis = round( microtime( true ) * 1000 );
			$current_timestamp_in_millis = number_format( $current_timestamp_in_millis, 0, '', '' );

			$string_to_hash = $customer_key . $current_timestamp_in_millis . $api_key;
			$auth_header    = hash( 'sha512', $string_to_hash );

			$header = array(
				'Content-Type'  => 'application/json',
				'Customer-Key'  => $customer_key,
				'Timestamp'     => $current_timestamp_in_millis,
				'Authorization' => $auth_header,
			);
			return $header;
		}
		/**
		 * The api function will be called for curl
		 *
		 * @param string $url .
		 * @param string $json_string .
		 * @param array  $http_header_array .
		 * @return string
		 */
		private static function call_api( $url, $json_string, $http_header_array = array(
			'Content-Type'  => 'application/json',
			'charset'       => 'UTF-8',
			'Authorization' => 'Basic',
		) ) {

			$args = array(
				'method'      => 'POST',
				'body'        => $json_string,
				'timeout'     => '5',
				'redirection' => '5',
				'sslverify'   => true,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $http_header_array,
			);

			$mo2f_api = new Mo2f_Api();
			$response = $mo2f_api->mo2f_wp_remote_post( $url, $args );
			return $response;
		}

	}
}
