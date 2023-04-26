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

/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 **/

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-mo2f-api.php';

if ( ! class_exists( 'Two_Factor_Setup' ) ) {
	/**
	 * Class contains function to create, update users and to check curl is enabled or not.
	 */
	class Two_Factor_Setup {
		/**
		 * Email id of user.
		 *
		 * @var string
		 */
		public $email;
		/**
		 * Function to check if the device registered with miniOrange or not.
		 *
		 * @param string $t_id Transaction id to verify the status with miniOrange server.
		 * @return string
		 */
		public function check_mobile_status( $t_id ) {

			$url               = MO_HOST_NAME . '/moas/api/auth/auth-status';
			$fields            = array(
				'txid' => $t_id,
			);
			$mo2f_api          = new Mo2f_Api();
			$http_header_array = $mo2f_api->get_http_header_array();

			return $mo2f_api->mo2f_http_request( $url, $fields, $http_header_array );
		}

		/**
		 * Function to check curl enabled or not.
		 *
		 * @return array
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
		 * Function registers user email id with miniOrange.
		 *
		 * @param string $useremail Email id of user.
		 * @return string
		 */
		public function register_mobile( $useremail ) {

			$url          = MO_HOST_NAME . '/moas/api/auth/register-mobile';
			$customer_key = get_option( 'mo2f_customerKey' );
			$fields       = array(
				'customerId' => $customer_key,
				'username'   => $useremail,
			);
			$mo2f_api     = new Mo2f_Api();

			$http_header_array = $mo2f_api->get_http_header_array();

			return $mo2f_api->mo2f_http_request( $url, $fields, $http_header_array );
		}

		/**
		 * Function to check user email already exist with miniOrange or not.
		 *
		 * @param string $email Email id of user.
		 * @return string
		 */
		public function mo_check_user_already_exist( $email ) {

			$url               = MO_HOST_NAME . '/moas/api/admin/users/search';
			$customer_key      = get_option( 'mo2f_customerKey' );
			$fields            = array(
				'customerKey' => $customer_key,
				'username'    => $email,
			);
			$mo2f_api          = new Mo2f_Api();
			$http_header_array = $mo2f_api->get_http_header_array();

			return $mo2f_api->mo2f_http_request( $url, $fields, $http_header_array );
		}
		/**
		 * Function to create user with miniOrange.
		 *
		 * @param object $currentuser Contains details of current user.
		 * @param string $email Email id of user.
		 * @return string
		 */
		public function mo_create_user( $currentuser, $email ) {

			$url               = MO_HOST_NAME . '/moas/api/admin/users/create';
			$customer_key      = get_option( 'mo2f_customerKey' );
			$fields            = array(
				'customerKey' => $customer_key,
				'username'    => $email,
				'firstName'   => $currentuser->user_firstname,
				'lastName'    => $currentuser->user_lastname,
			);
			$mo2f_api          = new Mo2f_Api();
			$http_header_array = $mo2f_api->get_http_header_array();

			return $mo2f_api->mo2f_http_request( $url, $fields, $http_header_array );
		}
		/**
		 * Function to get the information of user.
		 *
		 * @param string $email Email id of user.
		 * @return string
		 */
		public function mo2f_get_userinfo( $email ) {

			$url               = MO_HOST_NAME . '/moas/api/admin/users/get';
			$customer_key      = get_option( 'mo2f_customerKey' );
			$fields            = array(
				'customerKey' => $customer_key,
				'username'    => $email,
			);
			$mo2f_api          = new Mo2f_Api();
			$http_header_array = $mo2f_api->get_http_header_array();

			$data = $mo2f_api->mo2f_http_request( $url, $fields, $http_header_array );

			if ( is_array( $data ) ) {
				return wp_json_encode( $data );
			} else {
				return $data;
			}

		}
		/**
		 * Function to update the user information.
		 *
		 * @param string  $email Email id of user.
		 * @param string  $auth_type Authentication method of user.
		 * @param int     $phone Phone number of user.
		 * @param string  $tname Transaction name to verify the form of transaction.
		 * @param boolean $enable_admin_second_factor Second factor for user enabled by admin or not.
		 * @return string
		 */
		public function mo2f_update_userinfo( $email, $auth_type, $phone, $tname, $enable_admin_second_factor ) {
			$cloud_methods = array( 'MOBILE AUTHENTICATION', 'PUSH NOTIFICATIONS', 'SMS', 'SOFT TOKEN' );
			if ( MO2F_IS_ONPREM && ! in_array( $auth_type, $cloud_methods, true ) ) {
				$response = wp_json_encode( array( 'status' => 'SUCCESS' ) );
			} else {

				$url          = MO_HOST_NAME . '/moas/api/admin/users/update';
				$customer_key = get_option( 'mo2f_customerKey' );

				$fields = array(
					'customerKey'            => $customer_key,
					'username'               => $email,
					'phone'                  => $phone,
					'authType'               => $auth_type,
					'transactionName'        => $tname,
					'adminLoginSecondFactor' => $enable_admin_second_factor,
				);

				$mo2f_api = new Mo2f_Api();

				$http_header_array = $mo2f_api->get_http_header_array();

				$response = $mo2f_api->mo2f_http_request( $url, $fields, $http_header_array );
			}
			return $response;
		}
		/**
		 * Function to register the kba information with miniOrange.
		 *
		 * @param string $email Email id of user.
		 * @param string $question1 Question 1 selected by user.
		 * @param string $answer1 Answer 1 given by the user.
		 * @param string $question2 Question 2 selected by user.
		 * @param string $answer2 Answer 2 given by the user.
		 * @param string $question3 Question 3 selected by user.
		 * @param string $answer3 Answer 3 given by the user.
		 * @param int    $user_id Id of user.
		 * @return string
		 */
		public function mo2f_register_kba_details( $email, $question1, $answer1, $question2, $answer2, $question3, $answer3, $user_id = null ) {

			if ( MO2F_IS_ONPREM ) {
				$answer1         = md5( $answer1 );
				$answer2         = md5( $answer2 );
				$answer3         = md5( $answer3 );
				$question_answer = array(
					$question1 => $answer1,
					$question2 => $answer2,
					$question3 => $answer3,
				);
				update_user_meta( $user_id, 'mo2f_kba_challenge', $question_answer );
				global $mo2fdb_queries;
				$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_configured_2FA_method' => 'Security Questions' ) );
				$response = wp_json_encode( array( 'status' => 'SUCCESS' ) );
			} else {

				$url          = MO_HOST_NAME . '/moas/api/auth/register';
				$customer_key = get_option( 'mo2f_customerKey' );
				$q_and_a_list = '[{"question":"' . $question1 . '","answer":"' . $answer1 . '" },{"question":"' . $question2 . '","answer":"' . $answer2 . '" },{"question":"' . $question3 . '","answer":"' . $answer3 . '" }]';
				$field_string = '{"customerKey":"' . $customer_key . '","username":"' . $email . '","questionAnswerList":' . $q_and_a_list . '}';

				$mo2f_api          = new Mo2f_Api();
				$http_header_array = $mo2f_api->get_http_header_array();

				$response = $mo2f_api->mo2f_http_request( $url, $field_string, $http_header_array );
			}
			return $response;

		}
	}
}

