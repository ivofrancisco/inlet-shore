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
if ( ! class_exists( 'Customer_Cloud_Setup' ) ) {

	/**
	 *  Class contains functions to setup customer with miniOrange.
	 */
	class Customer_Cloud_Setup {

		/**
		 * Email id of user.
		 *
		 * @var $email string.
		 */
		public $email;
		/**
		 * Phone number of user.
		 *
		 * @var int.
		 */
		public $phone;
		/**
		 * Customer key of user.
		 *
		 * @var string
		 */
		public $customer_key;
		/**
		 * Transaction id of the customer to send OTP via SMS or Email.
		 *
		 * @var string
		 */
		public $transaction_id;

		/**
		 * Function to check if customer exists or not.
		 *
		 * @return string
		 */
		public function check_customer() {
			$url          = MO_HOST_NAME . '/moas/rest/customer/check-if-exists';
			$email        = get_option( 'mo2f_email' );
			$mo2f_api     = new Mo2f_Api();
			$fields       = array(
				'email' => $email,
			);
			$field_string = wp_json_encode( $fields );
			$response     = $mo2f_api->mo2f_http_request( $url, $field_string );
			return $response;
		}
		/**
		 * Function to add the customer on miniOrange idp.
		 *
		 * @return string
		 */
		public function create_customer() {
			global $mo2fdb_queries;
			$url      = MO_HOST_NAME . '/moas/rest/customer/add';
			$mo2f_api = new Mo2f_Api();
			global $user;
			$user        = wp_get_current_user();
			$this->email = get_option( 'mo2f_email' );
			$this->phone = $mo2fdb_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
			$password    = get_option( 'mo2f_password' );
			$company     = get_option( 'mo2f_admin_company' ) !== '' ? get_option( 'mo2f_admin_company' ) : ( isset( $_SERVER['SERVER_NAME'] ) ? esc_url_raw( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : null );

			$fields       = array(
				'companyName'     => $company,
				'areaOfInterest'  => '',
				'productInterest' => '',
				'email'           => $this->email,
				'phone'           => $this->phone,
				'password'        => $password,
			);
			$field_string = wp_json_encode( $fields );

			$headers = array(
				'Content-Type'  => 'application/json',
				'charset'       => 'UTF-8',
				'Authorization' => 'Basic',
			);

			$content = $mo2f_api->mo2f_http_request( $url, $field_string, $headers );

			return $content;
		}

		/**
		 * Function to get customer key of user.
		 *
		 * @return string
		 */
		public function get_customer_key() {

			$url = MO_HOST_NAME . '/moas/rest/customer/key';

			$email        = get_option( 'mo2f_email' );
			$password     = get_option( 'mo2f_password' );
			$mo2f_api     = new Mo2f_Api();
			$fields       = array(
				'email'    => $email,
				'password' => $password,
			);
			$field_string = wp_json_encode( $fields );

			$headers = array(
				'Content-Type'  => 'application/json',
				'charset'       => 'UTF-8',
				'Authorization' => 'Basic',
			);

			$content = $mo2f_api->mo2f_http_request( $url, $field_string );

			return $content;
		}

		/**
		 * Function to send otp to the user via miniOrange service.
		 *
		 * @param string $u_key It can be a phone number or email id to which the otp to be sent.
		 * @param string $auth_type Authentication method of the user.
		 * @param string $c_key Customer key of the user.
		 * @param string $api_key Api key of the user.
		 * @param object $currentuser Contains details of current user.
		 * @return string
		 */
		public function send_otp_token( $u_key, $auth_type, $c_key, $api_key, $currentuser = null ) {

			$url      = MO_HOST_NAME . '/moas/api/auth/challenge';
			$mo2f_api = new Mo2f_Api();
			/* The customer Key provided to you */
			$customer_key = $c_key;

			/* The customer API Key provided to you */
			$api_key = $api_key;

			/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
			$current_time_in_millis = $mo2f_api->get_timestamp();

			/* Creating the Hash using SHA-512 algorithm */
			$string_to_hash = $customer_key . $current_time_in_millis . $api_key;
			$hash_value     = hash( 'sha512', $string_to_hash );

			$headers = $mo2f_api->get_http_header_array();

			$fields = '';
			if ( 'EMAIL' === $auth_type || 'OTP Over Email' === $auth_type || 'OUT OF BAND EMAIL' === $auth_type ) {
				$fields = array(
					'customerKey'     => $customer_key,
					'email'           => $u_key,
					'authType'        => $auth_type,
					'transactionName' => 'WordPress 2 Factor Authentication Plugin',
				);
			} elseif ( 'SMS' === $auth_type ) {
				$auth_type = 'SMS';
				$fields    = array(
					'customerKey' => $customer_key,
					'phone'       => $u_key,
					'authType'    => $auth_type,
				);
			} else {
				$fields = array(
					'customerKey'     => $customer_key,
					'username'        => $u_key,
					'authType'        => $auth_type,
					'transactionName' => 'WordPress 2 Factor Authentication Plugin',
				);
			}

			$field_string = wp_json_encode( $fields );

			$content = $mo2f_api->mo2f_http_request( $url, $field_string, $headers );

			$content1 = json_decode( $content, true );

			if ( 'SUCCESS' === $content1['status'] ) {
				if ( 4 === get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) && 'SMS' === $auth_type ) {
					Miniorange_Authentication::mo2f_low_otp_alert( 'sms' );
				}
				if ( 5 === get_site_option( 'cmVtYWluaW5nT1RQ' ) && 'OTP Over Email' === $auth_type ) {
					Miniorange_Authentication::mo2f_low_otp_alert( 'email' );
				}
			}

			return $content;
		}
		/**
		 * Function to get remaining otp transactions of the user.
		 *
		 * @param int    $c_key Customer key of the user.
		 * @param string $api_key Api key of the user.
		 * @param string $license_type License type assigned by miniOrange to check whether the user is onPremise or cloud.
		 * @return string
		 */
		public function get_customer_transactions( $c_key, $api_key, $license_type ) {
			$url = MO_HOST_NAME . '/moas/rest/customer/license';

			$customer_key           = $c_key;
			$api_key                = $api_key;
			$mo2f_api               = new Mo2f_Api();
			$current_time_in_millis = $mo2f_api->get_timestamp();
			$string_to_hash         = $customer_key . $current_time_in_millis . $api_key;
			$hash_value             = hash( 'sha512', $string_to_hash );

			$fields = '';
			if ( 'DEMO' === $license_type ) {
				$fields = array(
					'customerId'      => $customer_key,
					'applicationName' => '-1',
					'licenseType'     => $license_type,
				);
			} else {
				$fields = array(
					'customerId'      => $customer_key,
					'applicationName' => 'otp_recharge_plan',
					'licenseType'     => $license_type,
				);
			}

			$field_string = wp_json_encode( $fields );

			$headers = $mo2f_api->get_http_header_array();

			$content = $mo2f_api->mo2f_http_request( $url, $field_string, $headers );

			return $content;
		}
		/**
		 * Function to request the Backup Code generation.
		 *
		 * @param string $mo2f_user_email Email id of the user.
		 * @param string $site_url Domain of the user.
		 * @return mixed
		 */
		public function mo_2f_generate_backup_codes( $mo2f_user_email, $site_url ) {
			$url = MoWpnsConstants::GENERATE_BACK_CODE;

			$data = $this->mo_2f_autnetication_backup_code_request( $mo2f_user_email, $site_url );

			$postdata = array(
				'mo2f_email'                 => $mo2f_user_email,
				'mo2f_domain'                => $site_url,
				'HTTP_AUTHORIZATION'         => 'Bearer|' . $data,
				'mo2f_generate_backup_codes' => 'initiated_backup_codes',
			);

			return $this->mo_2f_remote_call_function( $url, $postdata );
		}
		/**
		 * Function to request backup codes from the server.
		 *
		 * @param string $mo2f_user_email Email id of the user.
		 * @param string $site_url Domain of the user.
		 * @return array
		 */
		public function mo_2f_autnetication_backup_code_request( $mo2f_user_email, $site_url ) {
			$url = MoWpnsConstants::AUTHENTICATE_REQUEST;

			$postdata = array(
				'mo2f_email'   => $mo2f_user_email,
				'mo2f_domain'  => $site_url,
				'mo2f_cKey'    => MoWpnsConstants::DEFAULT_CUSTOMER_KEY,
				'mo2f_cSecret' => MoWpnsConstants::DEFAULT_API_KEY,
			);

			return $this->mo_2f_remote_call_function( $url, $postdata );
		}
		/**
		 * Function to retrieve/get the Backup codes.
		 *
		 * @param string $url Domain of the user.
		 * @param array  $postdata Contains parameters to be sent to the server.
		 * @return mixed
		 */
		public function mo_2f_remote_call_function( $url, $postdata ) {
			$args = array(
				'method'    => 'POST',
				'timeout'   => 45,
				'sslverify' => false,
				'headers'   => array(),
				'body'      => $postdata,

			);

			$mo2f_api    = new Mo2f_Api();
			$data        = $mo2f_api->mo2f_wp_remote_post( $url, $args );
			$status_code = wp_remote_retrieve_response_code( wp_remote_post( $url, $args ) );
			$data1       = json_decode( $data, true );
			if ( is_array( $data1 ) && 'ERROR' === $data1['status'] || 200 !== $status_code ) {
				return 'InternetConnectivityError';
			} else {
				return $data;
			}
		}
		/**
		 * Function to validate backup codes.
		 *
		 * @param string $mo2f_backup_code Backup codes sent to the user.
		 * @param string $mo2f_user_email Email id of user.
		 * @return object
		 */
		public function mo2f_validate_backup_codes( $mo2f_backup_code, $mo2f_user_email ) {
			$url      = MoWpnsConstants::VALIDATE_BACKUP_CODE;
			$site_url = site_url();
			$data     = $this->mo_2f_autnetication_backup_code_request( $mo2f_user_email, $site_url );

			$postdata = array(
				'mo2f_otp_token'     => $mo2f_backup_code,
				'mo2f_user_email'    => $mo2f_user_email,
				'HTTP_AUTHORIZATION' => 'Bearer|' . $data,
				'mo2f_site_url'      => $site_url,
			);

			$args = array(
				'method'    => 'POST',
				'timeout'   => 45,
				'sslverify' => false,
				'headers'   => array(),
				'body'      => $postdata,
			);

			$data = wp_remote_post( $url, $args );

			$data = wp_remote_retrieve_body( $data );

			return $data;
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

			$url      = MO_HOST_NAME . '/moas/api/auth/validate';
			$mo2f_api = new Mo2f_Api();
			/* The customer Key provided to you */
			$customer_key = $c_key;

			/* The customer API Key provided to you */
			$api_key = $customer_api_key;

			/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
			$current_time_in_millis = $mo2f_api->get_timestamp();

			/* Creating the Hash using SHA-512 algorithm */
			$string_to_hash = $customer_key . $current_time_in_millis . $api_key;
			$hash_value     = hash( 'sha512', $string_to_hash );

			$headers = $mo2f_api->get_http_header_array();
			$fields  = '';
			if ( 'SOFT TOKEN' === $auth_type || 'GOOGLE AUTHENTICATOR' === $auth_type ) {
				/*check for soft token*/
				$fields = array(
					'customerKey' => $customer_key,
					'username'    => $username,
					'token'       => $otp_token,
					'authType'    => $auth_type,
				);
			} elseif ( 'KBA' === $auth_type ) {
				$fields = array(
					'txId'    => $transaction_id,
					'answers' => array(
						array(
							'question' => $otp_token[0],
							'answer'   => $otp_token[1],
						),
						array(
							'question' => $otp_token[2],
							'answer'   => $otp_token[3],
						),
					),
				);
			} else {
				// *check for otp over sms/email
				$fields = array(
					'txId'  => $transaction_id,
					'token' => $otp_token,
				);
			}
			$field_string = wp_json_encode( $fields );

			$content = $mo2f_api->mo2f_http_request( $url, $field_string, $headers );
			return $content;
		}
		/**
		 * Function to raise support query.
		 *
		 * @param string $q_email Email id of customer to be sent to the query.
		 * @param int    $q_phone Phone number of customer to be sent to the query.
		 * @param string $query Query raised by the customer.
		 * @return boolean
		 */
		public function submit_contact_us( $q_email, $q_phone, $query ) {

			$url = MO_HOST_NAME . '/moas/rest/customer/contact-us';
			global $user;
			$user              = wp_get_current_user();
			$is_nc_with_1_user = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' ) && MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NNC', 'get_option' );
			$is_ec_with_1_user = ! MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );

			$mo2f_api         = new Mo2f_Api();
			$customer_feature = '';

			if ( $is_ec_with_1_user ) {
				$customer_feature = 'V1';
			} elseif ( $is_nc_with_1_user ) {
				$customer_feature = 'V3';
			}
			global $mo_wpns_utility;

			$query        = '[WordPress 2 Factor Authentication Plugin: ' . $customer_feature . ' - V ' . MO2F_VERSION . ' ]: ' . $query;
			$fields       = array(
				'firstName' => $user->user_firstname,
				'lastName'  => $user->user_lastname,
				'company'   => isset( $_SERVER['SERVER_NAME'] ) ? esc_url_raw( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : null,
				'email'     => $q_email,
				'ccEmail'   => '2fasupport@xecurify.com',
				'phone'     => $q_phone,
				'query'     => $query,
			);
			$field_string = wp_json_encode( $fields );

			$headers = array(
				'Content-Type'  => 'application/json',
				'charset'       => 'UTF-8',
				'Authorization' => 'Basic',
			);

			$content = $mo2f_api->mo2f_http_request( $url, $field_string );

			return true;
		}
	}
}
