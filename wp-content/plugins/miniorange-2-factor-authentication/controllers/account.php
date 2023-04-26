<?php
/**
 * Description: File contains functions to register, verify and save the information for customer account.
 *
 * @package miniorange-2-factor-authentication/controllers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $mo_wpns_utility,$mo2f_dir_name,$mo2fdb_queries;

$nonce = isset( $_POST['mo2f_general_nonce'] ) ? sanitize_key( wp_unslash( $_POST['mo2f_general_nonce'] ) ) : '';
if ( wp_verify_nonce( $nonce, 'miniOrange_2fa_nonce' ) ) {
	if ( isset( $_POST['option'] ) ) {
		$option = trim( isset( $_POST['option'] ) ? sanitize_text_field( wp_unslash( $_POST['option'] ) ) : null );
		switch ( $option ) {
			case 'mo_wpns_register_customer':
				mo2fa_register_customer( $_POST );
				break;
			case 'mo_wpns_verify_customer':
				mo2fa_verify_customer( $_POST );
				break;
			case 'mo_wpns_cancel':
				mo2f_revert_back_registration();
				break;
			case 'mo_wpns_reset_password':
				mo2f_reset_password();
				break;
			case 'mo2f_goto_verifycustomer':
				mo2f_goto_sign_in_page();
				break;
		}
	}
}

	$user                             = wp_get_current_user();
	$mo2f_current_registration_status = get_option( 'mo_2factor_user_registration_status' );

if ( ( get_option( 'mo_wpns_registration_status' ) === 'MO_OTP_DELIVERED_SUCCESS'
		|| get_option( 'mo_wpns_registration_status' ) === 'MO_OTP_VALIDATION_FAILURE'
		|| get_option( 'mo_wpns_registration_status' ) === 'MO_OTP_DELIVERED_FAILURE' ) && in_array( $mo2f_current_registration_status, array( 'MO_2_FACTOR_OTP_DELIVERED_SUCCESS', 'MO_2_FACTOR_OTP_DELIVERED_FAILURE' ), true ) ) {
	$admin_phone = get_option( 'mo_wpns_admin_phone' ) ? get_option( 'mo_wpns_admin_phone' ) : '';
	include $mo2f_dir_name . 'views' . DIRECTORY_SEPARATOR . 'account' . DIRECTORY_SEPARATOR . 'verify.php';
} elseif ( ( get_option( 'mo_wpns_verify_customer' ) === 'true' || ( get_option( 'mo2f_email' ) && ! get_option( 'mo2f_customerKey' ) ) ) && 'MO_2_FACTOR_VERIFY_CUSTOMER' === $mo2f_current_registration_status ) {
	$admin_email = get_option( 'mo2f_email' ) ? get_option( 'mo2f_email' ) : '';
	include $mo2f_dir_name . 'views' . DIRECTORY_SEPARATOR . 'account' . DIRECTORY_SEPARATOR . 'login.php';
} elseif ( ! $mo_wpns_utility->icr() ) {
	delete_option( 'password_mismatch' );
	update_option( 'mo_wpns_new_registration', 'true' );
	update_option( 'mo_2factor_user_registration_status', 'REGISTRATION_STARTED' );
	include $mo2f_dir_name . 'views' . DIRECTORY_SEPARATOR . 'account' . DIRECTORY_SEPARATOR . 'register.php';
} else {
	$email              = get_option( 'mo2f_email' );
	$key                = get_option( 'mo2f_customerKey' );
	$api                = get_option( 'mo2f_api_key' );
	$token              = get_option( 'mo2f_customer_token' );
	$email_transactions = MoWpnsUtility::get_mo2f_db_option( 'cmVtYWluaW5nT1RQ', 'site_option' );
	$email_transactions = $email_transactions ? $email_transactions : 0;
	$sms_transactions   = get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) ? get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) : 0;
	include $mo2f_dir_name . 'views' . DIRECTORY_SEPARATOR . 'account' . DIRECTORY_SEPARATOR . 'profile.php';
}

	/* REGISTRATION RELATED FUNCTIONS */

/**
 * Description: Function to register the customer in miniOrange.
 *
 * @param array $post array of customer details .
 * @return void
 */
function mo2fa_register_customer( $post ) {
	global $mo2fdb_queries;
	$user    = wp_get_current_user();
	$email   = sanitize_email( $post['email'] );
	$company = isset( $_SERVER['SERVER_NAME'] ) ? esc_url_raw( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : null;

	$password         = $post['password'];
	$confirm_password = $post['confirmPassword'];

	if ( strlen( $password ) < 6 || strlen( $confirm_password ) < 6 ) {
		do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'PASS_LENGTH' ), 'ERROR' );
		return;
	}

	if ( $password !== $confirm_password ) {
		do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'PASS_MISMATCH' ), 'ERROR' );
		return;
	}
	if ( MoWpnsUtility::check_empty_or_null( $email ) || MoWpnsUtility::check_empty_or_null( $password )
		|| MoWpnsUtility::check_empty_or_null( $confirm_password ) ) {
		do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'REQUIRED_FIELDS' ), 'ERROR' );
		return;
	}

	update_option( 'mo2f_email', $email );

	update_option( 'mo_wpns_company', $company );

	update_option( 'mo_wpns_password', $password );

	$customer = new MocURL();
	$content  = json_decode( $customer->check_customer( $email ), true );
	$mo2fdb_queries->insert_user( $user->ID );
	switch ( $content['status'] ) {
		case 'CUSTOMER_NOT_FOUND':
			$customer_key = json_decode( $customer->create_customer( $email, $company, $password, $phone = '', $first_name = '', $last_name = '' ), true );
			$mo2f_message = isset( $customer_key['message'] ) ? $customer_key['message'] : __( 'Error occured while creating an account.', 'miniorange-2-factor-authentication' );

			if ( strcasecmp( $customer_key['status'], 'SUCCESS' ) === 0 ) {
					update_site_option( base64_encode( 'totalUsersCloud' ), get_site_option( base64_encode( 'totalUsersCloud' ) ) + 1 ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not used for obfuscation
					update_option( 'mo2f_email', $email );
					$id         = isset( $customer_key['id'] ) ? $customer_key['id'] : '';
					$api_key    = isset( $customer_key['apiKey'] ) ? $customer_key['apiKey'] : '';
					$token      = isset( $customer_key['token'] ) ? $customer_key['token'] : '';
					$app_secret = isset( $customer_key['appSecret'] ) ? $customer_key['appSecret'] : '';
					mo2fa_save_success_customer_config( $email, $id, $api_key, $token, $app_secret );
					mo2fa_get_current_customer( $email, $password );
					return;
			} else {
				do_action( 'wpns_show_message', Mo2fConstants::lang_translate( $mo2f_message ), 'ERROR' );
				return;
			}
			break;
		default:
			mo2fa_get_current_customer( $email, $password );
			return;
	}
		$message = __( 'Error Occured while registration', 'miniorange-2-factor-authentication' );
		do_action( 'wpns_show_message', Mo2fConstants::lang_translate( $message ), 'ERROR' );
}

/**
 * Description: Function redirects the user to signin page after verification.
 *
 * @return void
 */
function mo2f_goto_sign_in_page() {
	update_option( 'mo_wpns_verify_customer', 'true' );
	update_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_VERIFY_CUSTOMER' );
}
/**
 * Description: Function to redirect the user back to registration page.
 *
 * @return void
 */
function mo2f_revert_back_registration() {
	delete_option( 'mo2f_email' );
	delete_option( 'mo_wpns_registration_status' );
	delete_option( 'mo_wpns_verify_customer' );
	update_option( 'mo_2factor_user_registration_status', '' );
}


/**
 * Description: Function to reset password of account
 *
 * @return void
 */
function mo2f_reset_password() {
	$customer                 = new MocURL();
	$forgot_password_response = json_decode( $customer->mo_wpns_forgot_password() );
	if ( 'SUCCESS' === $forgot_password_response->status ) {
		do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'RESET_PASS' ), 'SUCCESS' );
	}
}


/**
 * Description: Function for verifying the customer.
 *
 * @param array $post Post variable array of customer details.
 * @return void
 */
function mo2fa_verify_customer( $post ) {
	global $mo_wpns_utility;
	$email    = sanitize_email( $post['email'] );
	$password = $post['password'];

	if ( $mo_wpns_utility->check_empty_or_null( $email ) || $mo_wpns_utility->check_empty_or_null( $password ) ) {
		do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'REQUIRED_FIELDS' ), 'ERROR' );
		return;
	}
	mo2fa_get_current_customer( $email, $password );
}


/**
 * Description: Function to fetch current user
 *
 * @param string $email Email of the user.
 * @param string $password Password of the user.
 * @return void
 */
function mo2fa_get_current_customer( $email, $password ) {
	$customer     = new MocURL();
	$content      = $customer->get_customer_key( $email, $password );
	$customer_key = json_decode( $content, true );

	if ( json_last_error() === JSON_ERROR_NONE ) {
		if ( 'SUCCESS' === $customer_key['status'] ) {
			if ( isset( $customer_key['phone'] ) ) {
				update_option( 'mo_wpns_admin_phone', $customer_key['phone'] );
			}
			update_option( 'mo2f_email', $email );
			$id         = isset( $customer_key['id'] ) ? $customer_key['id'] : '';
			$api_key    = isset( $customer_key['apiKey'] ) ? $customer_key['apiKey'] : '';
			$token      = isset( $customer_key['token'] ) ? $customer_key['token'] : '';
			$app_secret = isset( $customer_key['appSecret'] ) ? $customer_key['appSecret'] : '';
			mo2fa_save_success_customer_config( $email, $id, $api_key, $token, $app_secret );
			update_site_option( base64_encode( 'totalUsersCloud' ), get_site_option( base64_encode( 'totalUsersCloud' ) ) + 1 ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- We need to obfuscate the option as it will be stored in database.
			$customer_t = new Customer_Cloud_Setup();
			$content    = json_decode( $customer_t->get_customer_transactions( get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ), 'PREMIUM' ), true );
			if ( 'SUCCESS' === $content['status'] ) {
				update_site_option( 'mo2f_license_type', 'PREMIUM' );
			} else {
				update_site_option( 'mo2f_license_type', 'DEMO' );
				$content = json_decode( $customer_t->get_customer_transactions( get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ), 'DEMO' ), true );
			}
			if ( isset( $content['smsRemaining'] ) ) {
				update_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z', $content['smsRemaining'] );
			} elseif ( isset( $content['status'] ) && 'SUCCESS' === $content['status'] ) {
				update_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z', 0 );
			}

			if ( isset( $content['emailRemaining'] ) ) {
				if ( $content['emailRemaining'] > 30 ) {
					$current_transaction = $content['emailRemaining'];
					update_site_option( 'cmVtYWluaW5nT1RQ', $current_transaction );
					update_site_option( 'EmailTransactionCurrent', $content['emailRemaining'] );
				} elseif ( 10 === $content['emailRemaining'] && get_site_option( 'cmVtYWluaW5nT1RQ' ) > 30 ) {
					update_site_option( 'cmVtYWluaW5nT1RQ', 30 );
				}
			}
			do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'REG_SUCCESS' ), 'SUCCESS' );
			return;
		} else {
			update_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_VERIFY_CUSTOMER' );
			update_option( 'mo_wpns_verify_customer', 'true' );
			delete_option( 'mo_wpns_new_registration' );
			do_action( 'wpns_show_message', MoWpnsMessages::show_message( 'ACCOUNT_EXISTS' ), 'ERROR' );
			return;
		}
	} else {
		$mo2f_message = is_string( $content ) ? $content : '';
		do_action( 'wpns_show_message', Mo2fConstants::lang_translate( $mo2f_message ), 'ERROR' );
	}
}


/**
 * Description: Save all required fields on customer registration/retrieval complete.
 *
 * @param string $email Customer Email.
 * @param int    $id Customer Id.
 * @param string $api_key Customer apikey.
 * @param string $token Customer token key.
 * @param string $app_secret Customer appSecret.
 * @return void
 */
function mo2fa_save_success_customer_config( $email, $id, $api_key, $token, $app_secret ) {
	global $mo2fdb_queries;

	$user = wp_get_current_user();
	update_option( 'mo2f_customerKey', $id );
	update_option( 'mo2f_api_key', $api_key );
	update_option( 'mo2f_customer_token', $token );
	update_option( 'mo2f_app_secret', $app_secret );
	update_option( 'mo_wpns_enable_log_requests', true );
	update_option( 'mo2f_miniorange_admin', $user->ID );
	update_option( 'mo_2factor_admin_registration_status', 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' );
	update_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_PLUGIN_SETTINGS' );

	$mo2fdb_queries->update_user_details(
		$user->ID,
		array(
			'mo2f_user_email'                   => $email,
			'user_registration_with_miniorange' => 'SUCCESS',
		)
	);
	$enduser  = new Two_Factor_Setup();
	$userinfo = json_decode( $enduser->mo2f_get_userinfo( $email ), true );

	$mo2f_second_factor = 'NONE';
	if ( json_last_error() === JSON_ERROR_NONE ) {
		if ( 'SUCCESS' === $userinfo['status'] ) {
			$mo2f_second_factor = mo2f_update_and_sync_user_two_factor( $user->ID, $userinfo );
		}
	}
	$configured_2_f_a_method = '';
	if ( 'EMAIL' === $mo2f_second_factor ) {
		$enduser->mo2f_update_userinfo( $email, 'NONE', null, '', true );
		$configured_2_f_a_method = 'NONE';
	} elseif ( 'NONE' !== $mo2f_second_factor ) {
		$configured_2_f_a_method = MO2f_Utility::mo2f_decode_2_factor( $mo2f_second_factor, 'servertowpdb' );
		if ( MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' ) === 0 ) {
			$auth_method_abr = str_replace( ' ', '', $configured_2_f_a_method );
		} else {
			if ( in_array(
				$configured_2_f_a_method,
				array(
					'Email Verification',
					'Authy Authenticator',
					'OTP over SMS',
				),
				true
			) ) {
				$enduser->mo2f_update_userinfo( $email, 'NONE', null, '', true );
			}
		}
	}

	$mo2f_message = Mo2fConstants::lang_translate( 'ACCOUNT_RETRIEVED_SUCCESSFULLY' );
	if ( 'NONE' !== $configured_2_f_a_method && MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' ) === 0 ) {
		$mo2f_message .= ' <b>' . $configured_2_f_a_method . '</b> ' . Mo2fConstants::lang_translate( 'DEFAULT_2ND_FACTOR' ) . '. ';
	}
	$mo2f_message .= '<a href=\"admin.php?page=mo_2fa_two_fa\" >' . Mo2fConstants::lang_translate( 'CLICK_HERE' ) . '</a> ' . Mo2fConstants::lang_translate( 'CONFIGURE_2FA' );

	delete_user_meta( $user->ID, 'register_account' );

	$mo2f_customer_selected_plan = get_option( 'mo2f_customer_selected_plan' );
	if ( ! empty( $mo2f_customer_selected_plan ) ) {
		delete_option( 'mo2f_customer_selected_plan' );

		if ( MoWpnsUtility::get_mo2f_db_option( 'mo2f_planname', 'site_option' ) === 'addon_plan' ) {
			?><script>window.location.href="admin.php?page=mo_2fa_addons";</script>
			<?php
		} else {
			?>
				<script>window.location.href="admin.php?page=mo_2fa_upgrade";</script>
				<?php
		}
	} elseif ( 'NONE' === $mo2f_second_factor ) {
		if ( get_user_meta( $user->ID, 'register_account_popup', true ) ) {
			update_user_meta( $user->ID, 'configure_2FA', 1 );
		}
	}

	update_option( 'mo2f_message', $mo2f_message );
	delete_user_meta( $user->ID, 'register_account_popup' );
	delete_option( 'mo_wpns_verify_customer' );
	delete_option( 'mo_wpns_registration_status' );
	delete_option( 'mo_wpns_password' );
}
