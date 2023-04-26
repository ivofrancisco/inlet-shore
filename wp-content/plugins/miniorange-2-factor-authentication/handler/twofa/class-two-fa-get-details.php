<?php
/**
 * This file contains functions related users details.
 *
 * @package miniOrange-2-factor-authentication/handler/twofa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Two_Fa_Get_Details' ) ) {
	/**
	 * Class two_fa_get_details
	 */
	class Two_Fa_Get_Details {

		/**
		 * Gets users current 2fa method.
		 *
		 * @param integer $userid User id of the user.
		 * @return string
		 */
		public function get_user_method( $userid ) {
			$user_method = get_user_meta( $userid, 'currentMethod', true );
			return $user_method;
		}

		/**
		 * Sets users 2fa method to the given method.
		 *
		 * @param integer $userid User id of the user whose 2fa method need to be set.
		 * @param string  $current_method The 2fa method.
		 * @return bool
		 */
		public function set_user_method( $userid, $current_method ) {
			$response = update_user_meta( $userid, 'currentMethod', $current_method );
			return $response;
		}

		/**
		 * Sets user email id to the given email.
		 *
		 * @param integer $userid User id of the user whose email id need be set.
		 * @param string  $email The email id which need to be set.
		 * @return bool
		 */
		public function set_user_email( $userid, $email ) {
			$response = update_user_meta( $userid, 'email', $email );
			return $response;
		}

		/**
		 * Gets user email corresponding to given user id.
		 *
		 * @param integer $userid User id whose email id need to be fetched.
		 * @return string
		 */
		public function get_user_email( $userid ) {
			$user_email = get_user_meta( $userid, 'email', true );
			return $user_email;
		}
	}
}
