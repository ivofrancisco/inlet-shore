<?php
/**
 * This file contains the ajax request handler.
 *
 * @package miniorange-2-factor-authentication/controllers/twofa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mo_2f_Ajax' ) ) {
	/**
	 * Class Mo_2f_Ajax
	 */
	class Mo_2f_Ajax {
		/**
		 * Constructor of class.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'mo_2f_two_factor' ) );
		}
		/**
		 * Function for handling ajax requests.
		 *
		 * @return void
		 */
		public function mo_2f_two_factor() {
			add_action( 'wp_ajax_mo_two_factor_ajax', array( $this, 'mo_two_factor_ajax' ) );
			add_action( 'wp_ajax_nopriv_mo_two_factor_ajax', array( $this, 'mo_two_factor_ajax' ) );
		}
		/**
		 * Call functions as per ajax requests.
		 *
		 * @return void
		 */
		public function mo_two_factor_ajax() {
			$GLOBALS['mo2f_is_ajax_request'] = true;
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'class-mo2f-ajax' );
			}
			switch ( isset( $_POST['mo_2f_two_factor_ajax'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_2f_two_factor_ajax'] ) ) : '' ) {
				case 'mo2f_ajax_login_redirect':
					$this->mo2f_ajax_login_redirect();
					break;
				case 'mo2f_save_email_verification':
					$this->mo2f_save_email_verification();
					break;
				case 'mo2f_unlimitted_user':
					$this->mo2f_unlimitted_user();
					break;
				case 'mo2f_check_user_exist_miniOrange':
					$this->mo2f_check_user_exist_miniorange();
					break;
				case 'mo2f_single_user':
					$this->mo2f_single_user();
					break;
				case 'CheckEVStatus':
					$this->check_email_verification_status();
					break;
				case 'mo2f_role_based_2_factor':
					$this->mo2f_role_based_2_factor();
					break;
				case 'mo2f_enable_disable_twofactor':
					$this->mo2f_enable_disable_twofactor();
					break;
				case 'mo2f_enable_disable_inline':
					$this->mo2f_enable_disable_inline();
					break;
				case 'mo2f_enable_disable_configurd_methods':
					$this->mo2f_enable_disable_configurd_methods();
					break;
				case 'mo2f_shift_to_onprem':
					$this->mo2f_shift_to_onprem();
					break;
				case 'mo2f_enable_disable_twofactor_prompt_on_login':
					$this->mo2f_enable_disable_twofactor_prompt_on_login();
					break;
				case 'mo2f_save_custom_form_settings':
					$this->mo2f_save_custom_form_settings();
					break;
				case 'mo2f_enable_disable_debug_log':
					$this->mo2f_enable_disable_debug_log();
					break;
				case 'mo2f_delete_log_file':
					$this->mo2f_delete_log_file();
					break;
				case 'mo2f_grace_period_save':
					$this->mo2f_grace_period_save();
					break;
				case 'select_method_setup_wizard':
					$this->mo2f_select_method_setup_wizard();
					break;
				case 'mo2f_skiptwofactor_wizard':
					$this->mo2f_skiptwofactor_wizard();
					break;
				case 'mo_wpns_register_verify_customer':
					$this->mo_wpns_register_verify_customer();
					break;
				case 'mo_2fa_configure_GA_setup_wizard':
					$this->mo_2fa_configure_ga_setup_wizard();
					break;
				case 'mo_2fa_verify_GA_setup_wizard':
					$this->mo_2fa_verify_ga_setup_wizard();
					break;
				case 'mo_2fa_configure_OTPOverSMS_setup_wizard':
					$this->mo_2fa_configure_otp_over_sms_setup_wizard();
					break;
				case 'mo_2fa_configure_OTPOverEmail_setup_wizard':
					$this->mo_2fa_configure_otp_over_email_setup_wizard();
					break;
				case 'mo_2fa_verify_OTPOverEmail_setup_wizard':
					$this->mo_2fa_verify_otp_over_email_setup_wizard();
					break;
				case 'mo_2fa_verify_OTPOverSMS_setup_wizard':
					$this->mo_2fa_verify_otp_over_sms_setup_wizard();
					break;
				case 'mo_2fa_configure_KBA_setup_wizard':
					$this->mo_2fa_configure_kba_setup_wizard();
					break;
				case 'mo_2fa_verify_KBA_setup_wizard':
					$this->mo_2fa_verify_kba_setup_wizard();
					break;
				case 'mo_2fa_send_otp_token':
					$this->mo_2fa_send_otp_token();
					break;
				case 'mo2f_set_otp_over_sms':
					$this->mo2f_set_otp_over_sms();
					break;
				case 'mo2f_set_miniorange_methods':
					$this->mo2f_set_miniorange_methods();
					break;
				case 'mo2f_set_GA':
					$this->mo2f_set_ga();
					break;
			}
		}
		/**
		 * Save settings for grace period feature
		 *
		 * @return void
		 */
		public function mo2f_grace_period_save() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( 'mo2f-ajax' );

			} else {

				$enable = isset( $_POST['mo2f_graceperiod_use'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_graceperiod_use'] ) ) : '';
				if ( 'true' === $enable ) {
					update_option( 'mo2f_grace_period', 'on' );
					$grace_type = isset( $_POST['mo2f_graceperiod_hour'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_graceperiod_hour'] ) ) : '';
					if ( 'true' === $grace_type ) {
						update_option( 'mo2f_grace_period_type', 'hours' );
					} else {
						update_option( 'mo2f_grace_period_type', 'days' );
					}
					if ( isset( $_POST['mo2f_graceperiod_value'] ) && $_POST['mo2f_graceperiod_value'] > 0 && $_POST['mo2f_graceperiod_value'] <= 10 ) {
						update_option( 'mo2f_grace_period_value', sanitize_text_field( wp_unslash( $_POST['mo2f_graceperiod_value'] ) ) );
					} else {
						update_option( 'mo2f_grace_period_value', 1 );
						wp_send_json_error( 'invalid_input' );
					}
				} else {
					update_option( 'mo2f_grace_period', 'off' );

					update_option( 'mo2f_inline_registration', 1 );

				}
				wp_send_json_success( 'true' );
			}
		}
		/**
		 * Verify and register Security Questions for user.
		 *
		 * @return void
		 */
		public function mo_2fa_verify_kba_setup_wizard() {
			global $mo2fdb_queries;
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'mo2f-ajax' );
			}
			$kba_q1 = isset( $_POST['mo2f_kbaquestion_1'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_1'] ) ) : null;
			$kba_a1 = isset( $_POST['mo2f_kba_ans1'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans1'] ) ) : null;
			$kba_q2 = isset( $_POST['mo2f_kbaquestion_2'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_2'] ) ) : null;
			$kba_a2 = isset( $_POST['mo2f_kba_ans2'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans2'] ) ) : null;
			$kba_q3 = isset( $_POST['mo2f_kbaquestion_3'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_3'] ) ) : null;
			$kba_a3 = isset( $_POST['mo2f_kba_ans3'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans3'] ) ) : null;
			$user   = wp_get_current_user();
			$this->mo2f_check_and_create_user( $user->ID );
			if ( MO2f_Utility::mo2f_check_empty_or_null( $kba_q1 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_a1 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_q2 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_a2 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_q3 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_a3 ) ) {
				wp_send_json_error( 'Invalid Questions or Answers' );
			}
			if ( strcasecmp( $kba_q1, $kba_q2 ) === 0 || strcasecmp( $kba_q2, $kba_q3 ) === 0 || strcasecmp( $kba_q3, $kba_q1 ) === 0 ) {
				wp_send_json_error( 'The questions you select must be unique.' );
			}
			$kba_q1           = addcslashes( stripslashes( $kba_q1 ), '"\\' );
			$kba_q2           = addcslashes( stripslashes( $kba_q2 ), '"\\' );
			$kba_q3           = addcslashes( stripslashes( $kba_q3 ), '"\\' );
			$kba_a1           = addcslashes( stripslashes( $kba_a1 ), '"\\' );
			$kba_a2           = addcslashes( stripslashes( $kba_a2 ), '"\\' );
			$kba_a3           = addcslashes( stripslashes( $kba_a3 ), '"\\' );
			$email            = $user->user_email;
			$kba_registration = new Two_Factor_Setup();
			$mo2fdb_queries->update_user_details(
				$user->ID,
				array(
					'mo2f_SecurityQuestions_config_status' => true,
					'mo_2factor_user_registration_status'  => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					'mo2f_user_email'                      => $email,
				)
			);
			$kba_reg_reponse = json_decode( $kba_registration->mo2f_register_kba_details( $email, $kba_q1, $kba_a1, $kba_q2, $kba_a2, $kba_q3, $kba_a3, $user->ID ), true );

			if ( 'SUCCESS' === $kba_reg_reponse['status'] ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( 'An error has occured while saving KBA details. Please try again.' );
			}
		}
		/**
		 * Function to send OTP on mobile.
		 *
		 * @return void
		 */
		public function mo_2fa_send_otp_token() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( 'mo2f-ajax' );
			} else {
				$enduser               = new Customer_Setup();
				$customer_key          = get_site_option( 'mo2f_customerKey' );
				$api_key               = get_site_option( 'mo2f_api_key' );
				$selected_2_f_a_method = isset( $_POST['selected_2FA_method'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_2FA_method'] ) ) : null;
				$user_id               = wp_get_current_user()->ID;
				$contact_info          = '';
				if ( 'OTP Over Email' === $selected_2_f_a_method ) {
					$contact_info = isset( $_POST['mo2f_contact_info'] ) ? sanitize_email( wp_unslash( $_POST['mo2f_contact_info'] ) ) : '';
					update_user_meta( $user_id, 'tempRegEmail', $contact_info );
					if ( ! filter_var( $contact_info, FILTER_VALIDATE_EMAIL ) ) {
						$email_err = 'Invalid email format';
						wp_send_json_error( $email_err );
					}
				} elseif ( strpos( $selected_2_f_a_method, 'SMS' ) !== false ) {
					$contact_info = isset( $_POST['mo2f_contact_info'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_contact_info'] ) ) : null;
					$contact_info = str_replace( ' ', '', $contact_info );
				}
				$content = $enduser->send_otp_token( $contact_info, $selected_2_f_a_method, $customer_key, $api_key );
				$content = json_decode( $content );

				if ( 'SUCCESS' === $content->status ) {
					update_user_meta( $user_id, 'txid', $content->txId ); //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- $txid is a parameter fetched from cloud. Hence cannot follow naming convention here.	
					update_user_meta( $user_id, 'tempRegPhone', $contact_info );
					wp_send_json_success();
				} elseif ( 'FAILED' === $content->status && 'OTP Over Email' === $selected_2_f_a_method ) {
					wp_send_json_error( 'SMTPNOTSET' );
				} else {
					wp_send_json_error( 'An error has occured while sending the OTP.' );
				}
			}
		}
		/**
		 * Function to check and create user
		 *
		 * @param int $user_id User ID.
		 * @return void
		 */
		public function mo2f_check_and_create_user( $user_id ) {
			global $mo2fdb_queries;
			$twofactor_transactions = new Mo2fDB();
			$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $user_id );
			if ( $exceeded ) {
				echo 'User Limit has been exceeded';
				exit;
			}
			$mo2fdb_queries->insert_user( $user_id );
		}
		/**
		 * Function to verify OTP in setup wizard flow.
		 *
		 * @return void
		 */
		public function mo_2fa_verify_otp_over_sms_setup_wizard() {
			global $mo2fdb_queries;
			$enduser = new Customer_Setup();
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'mo2f-ajax' );
			}
			$otp_token = isset( $_POST['mo2f_otp_token'] ) ? intval( wp_unslash( $_POST['mo2f_otp_token'] ) ) : null;
			$user_id   = wp_get_current_user()->ID;
			$email     = get_user_meta( $user_id, 'tempRegPhone', true );
			$content   = json_decode( $enduser->validate_otp_token( 'SMS', null, get_user_meta( $user_id, 'txid', true ), $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );

			if ( 'SUCCESS' === $content['status'] ) {
				$this->mo2f_check_and_create_user( $user_id );
				$mo2fdb_queries->update_user_details(
					$user_id,
					array(
						'mo2f_OTPOverSMS_config_status' => true,
						'mo2f_configured_2FA_method'    => 'OTP Over SMS',
						'mo2f_user_phone'               => $email,
						'user_registration_with_miniorange' => 'SUCCESS',
						'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					)
				);
				wp_send_json_success();
			} else {
				wp_send_json_error( 'mo2f-ajax' );
			}
			exit;

		}
		/**
		 * Function to verify OTP over Email in setup wizard.
		 *
		 * @return void
		 */
		public function mo_2fa_verify_otp_over_email_setup_wizard() {
			global $mo2fdb_queries;
			$enduser      = new Customer_Setup();
			$current_user = wp_get_current_user();
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'mo2f-ajax' );
			}
			$otp_token = isset( $_POST['mo2f_otp_token'] ) ? intval( wp_unslash( $_POST['mo2f_otp_token'] ) ) : null;
			$user_id   = wp_get_current_user()->ID;
			$email     = get_user_meta( $user_id, 'tempRegEmail', true );
			$content   = json_decode( $enduser->validate_otp_token( 'OTP_OVER_EMAIL', null, get_user_meta( $current_user->ID, 'mo2f_transactionId', true ), $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );

			if ( 'SUCCESS' === $content['status'] ) {
				$this->mo2f_check_and_create_user( $user_id );
				$mo2fdb_queries->update_user_details(
					$user_id,
					array(
						'mo2f_OTPOverEmail_config_status' => true,
						'mo2f_configured_2FA_method'      => 'OTP Over Email',
						'mo2f_user_email'                 => $email,
						'user_registration_with_miniorange' => 'SUCCESS',
						'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					)
				);
				wp_send_json_success();
			} else {
				wp_send_json_error( 'Invalid OTP' );
			}
			exit;
		}
		/**
		 * Function for verifying OTP for Google Authenticator in setup wizard.
		 *
		 * @return void
		 */
		public function mo_2fa_verify_ga_setup_wizard() {
			global $mo2fdb_queries;
			$path = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'class-google-auth-onpremise.php';
			include_once $path;
			$obj_google_auth = new Google_auth_onpremise();
			$user_id         = wp_get_current_user()->ID;
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'mo2f-ajax' );
			}
			$otp_token          = isset( $_POST['mo2f_google_auth_code'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_google_auth_code'] ) ) : null;
			$session_id_encrypt = isset( $_POST['mo2f_session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_session_id'] ) ) : null;
			$secret             = $obj_google_auth->mo_a_auth_get_secret( $user_id );
			if ( $session_id_encrypt ) {
				$secret = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'secret_ga' );
			}
			$content = $obj_google_auth->mo2f_verify_code( $secret, $otp_token );
			$content = json_decode( $content );
			if ( 'false' === $content->status ) {
				wp_send_json_error( 'Invalid One time Passcode. Please enter again' );
			} else {
				$obj_google_auth->mo_g_auth_set_secret( $user_id, $secret );
				$this->mo2f_check_and_create_user( $user_id );
				$mo2fdb_queries->update_user_details(
					$user_id,
					array(
						'mo2f_GoogleAuthenticator_config_status' => true,
						'mo2f_AuthyAuthenticator_config_status' => false,
						'mo2f_configured_2FA_method' => 'Google Authenticator',
						'user_registration_with_miniorange' => 'SUCCESS',
						'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					)
				);

				wp_send_json_success();
			}
			exit;
		}
		/**
		 * Function to configure GA in setup wizard
		 *
		 * @return void
		 */
		public function mo_2fa_configure_ga_setup_wizard() {
			$path = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'class-google-auth-onpremise.php';
			include_once $path;
			$obj_google_auth = new Google_auth_onpremise();
			$gauth_name      = isset( $_SERVER['SERVER_NAME'] ) ? esc_url_raw( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : null;
			$gauth_name      = preg_replace( '#^https?://#i', '', $gauth_name ); // To remove http:// or https:// from the Google Authenticator Appname.
			update_option( 'mo2f_google_appname', $gauth_name );
			update_option( 'mo2f_wizard_selected_method', 'GA' );
			$obj_google_auth->mo_g_auth_get_details( true );
			exit;
		}
		/**
		 * Function to configure OTP over SMS in setup wizard
		 *
		 * @return void
		 */
		public function mo_2fa_configure_otp_over_sms_setup_wizard() {
			global $mo2fdb_queries;
			$user               = wp_get_current_user();
			$mo2f_user_phone    = $mo2fdb_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
			$user_phone         = $mo2f_user_phone ? $mo2f_user_phone : get_option( 'user_phone_temp' );
			$session_id_encrypt = MO2f_Utility::random_str( 20 );
			update_option( 'mo2f_wizard_selected_method', 'SMS-OTP' );
			?>
		<div class="mo2f-inline-block">
			<h4> Remaining SMS Transactions: <b><?php echo intval( esc_html( get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) ) ); ?> </b></h4>
		</div>
		<form name="f" method="post" action="" id="mo2f_verifyphone_form">
			<input type="hidden" name="option" value="mo2f_configure_otp_over_sms_send_otp"/>
			<input type="hidden" name="mo2f_session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
			<input type="hidden" name="mo2f_configure_otp_over_sms_send_otp_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-otp-over-sms-send-otp-nonce' ) ); ?>"/>

			<div style="display:inline;">
			<b>Phone no.: </b>
				<input class="mo2f_table_textbox_phone" style="width:200px;height: 30px;" type="text" name="phone" id="mo2f_contact_info"
					value="<?php echo esc_attr( $user_phone ); ?>" pattern="[\+]?[0-9]{1,4}\s?[0-9]{7,12}"
					title="<?php esc_attr_e( 'Enter phone number without any space or dashes', 'miniorange-2-factor-authentication' ); ?>"/><br>
				<input type="button" name="mo2f_send_otp" id="mo2f_send_otp" class="mo2f-modal__btn button"
					value="<?php esc_attr_e( 'Send OTP', 'miniorange-2-factor-authentication' ); ?>"/>
			</div>
		</form>
		<br>
		<form name="f" method="post" action="" id="mo2f_validateotp_form">
			<input type="hidden" name="option" value="mo2f_configure_otp_over_sms_validate"/>
			<input type="hidden" name="mo2f_session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
			<input type="hidden" name="mo2f_configure_otp_over_sms_validate_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-otp-over-sms-validate-nonce' ) ); ?>"/>
			<p><?php esc_html_e( 'Enter One Time Passcode', 'miniorange-2-factor-authentication' ); ?></p>
			<input class="mo2f_table_textbox_phone" style="width:200px;height: 30px" autofocus="true" type="text" name="mo2f_otp_token" id="mo2f_otp_token"
				placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>" style="width:95%;"/>
			<br><br>
		</form><br>	
			<?php
			exit;
		}
		/**
		 * Configure OTP over Email in setup wizard.
		 *
		 * @return void
		 */
		public function mo_2fa_configure_otp_over_email_setup_wizard() {
			$session_id_encrypt = MO2f_Utility::random_str( 20 );
			$user_email         = wp_get_current_user()->user_email;
			update_option( 'mo2f_wizard_selected_method', 'Email-OTP' );
			?>
		<div class="mo2f-inline-block">
			<h4> Remaining Email Transactions: <b><?php echo intval( esc_html( get_site_option( 'cmVtYWluaW5nT1RQ' ) ) ); ?> </b></h4>
		</div>
		<form name="f" method="post" action="" id="mo2f_verifyemail_form">
			<input type="hidden" name="option" value="mo2f_configure_otp_over_email_send_otp"/>
			<input type="hidden" name="mo2f_session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
			<input type="hidden" name="mo2f_configure_otp_over_email_send_otp_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-otp-over-email-send-otp-nonce' ) ); ?>"/>

			<div style="display:inline;">
				<b>Email Address: </b>
				<input class="mo2f_table_textbox" style="width:280px;height: 30px;" type="text" pattern="[^@\s]+@[^@\s]+\.[^@\s]+" name="verify_phone" id="mo2f_contact_info"
					value="<?php echo esc_attr( $user_email ); ?>" 
					title="<?php esc_attr_e( 'Enter your email address without any space or dashes', 'miniorange-2-factor-authentication' ); ?>"/><br><br>
				<input type="button" name="mo2f_send_otp" id="mo2f_send_otp" class="mo2f-modal__btn button"
					value="<?php esc_attr_e( 'Send OTP', 'miniorange-2-factor-authentication' ); ?>"/>
			</div>
		</form>
		<br><br>
		<form name="f" method="post" action="" id="mo2f_validateotp_form">
			<input type="hidden" name="option" value="mo2f_configure_otp_over_sms_validate"/>
			<input type="hidden" name="mo2f_session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
			<input type="hidden" name="mo2f_configure_otp_over_email_validate_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-otp-over-email-validate-nonce' ) ); ?>"/>
			<b><?php esc_html_e( 'Enter One Time Passcode:', 'miniorange-2-factor-authentication' ); ?>
			<input class="mo2f_table_textbox" style="width:200px;height: 30px;"  type="text"  name="mo2f_otp_token" id ="mo2f_otp_token" 
				placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>" style="width:95%;"/></b>
			<br><br>
		</form><br>
		<script>
			var input = jQuery("#mo2f_contact_info");
			var len = input.val().length;
			input[0].focus();
			input[0].setSelectionRange(len, len);
		</script>
			<?php
			exit;
		}
		/**
		 * Function to configure KBA in Setup wizard
		 *
		 * @return void
		 */
		public function mo_2fa_configure_kba_setup_wizard() {
			update_option( 'mo2f_wizard_selected_method', 'KBA' );
			?>
			<div class="mo2f_kba_header"><?php esc_html_e( 'Please choose 3 questions', 'miniorange-2-factor-authentication' ); ?></div>
	<br>
	<table cellspacing="10">
		<tr class="mo2f_kba_header">
			<th style="width: 10%;">
				<?php esc_html_e( 'Sr. No.', 'miniorange-2-factor-authentication' ); ?>
			</th>
			<th class="mo2f_kba_tb_data">
				<?php esc_html_e( 'Questions', 'miniorange-2-factor-authentication' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Answers', 'miniorange-2-factor-authentication' ); ?>
			</th>
		</tr>
		<tr class="mo2f_kba_body">
			<td>
				<div class="mo2fa_text-align-center">1.</div>
			</td>
			<td class="mo2f_kba_tb_data">
				<select name="mo2f_kbaquestion_1" id="mo2f_kbaquestion_1" class="mo2f_kba_ques" required="true"
						>
					<option value="" selected disabled>
						------------<?php esc_html_e( 'Select your question', 'miniorange-2-factor-authentication' ); ?>
						------------
					</option>
					<option id="mq1_1"
							value="What is your first company name?"><?php esc_html_e( 'What is your first company name?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq2_1"
							value="What was your childhood nickname?"><?php esc_html_e( 'What was your childhood nickname?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq3_1"
							value="In what city did you meet your spouse/significant other?"><?php esc_html_e( 'In what city did you meet your spouse/significant other?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq4_1"
							value="What is the name of your favorite childhood friend?"><?php esc_html_e( 'What is the name of your favorite childhood friend?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq5_1"
							value="What school did you attend for sixth grade?"><?php esc_html_e( 'What school did you attend for sixth grade?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq6_1"
							value="In what city or town was your first job?"><?php esc_html_e( 'In what city or town was your first job?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq7_1"
							value="What is your favourite sport?"><?php esc_html_e( 'What is your favourite sport?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq8_1"
							value="Who is your favourite sports player?"><?php esc_html_e( 'Who is your favourite sports player?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq9_1"
							value="What is your grandmother's maiden name?"><?php esc_html_e( "What is your grandmother's maiden name?", 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq10_1"
							value="What was your first vehicle's registration number?"><?php esc_html_e( "What was your first vehicle's registration number?", 'miniorange-2-factor-authentication' ); ?></option>
				</select>
			</td>
			<td style="text-align: end;">
				<input class="mo2f_table_textbox_KBA" type="password" name="mo2f_kba_ans1" id="mo2f_kba_ans1"
					title="<?php esc_attr_e( 'Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed.', 'miniorange-2-factor-authentication' ); ?>"
					pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}" required="true" 
					placeholder="<?php esc_attr_e( 'Enter your answer', 'miniorange-2-factor-authentication' ); ?>"/>
			</td>
		</tr>
		<tr class="mo2f_kba_body">
			<td>
				<div class="mo2fa_text-align-center">2.</div>
			</td>
			<td class="mo2f_kba_tb_data">
				<select name="mo2f_kbaquestion_2" id="mo2f_kbaquestion_2" class="mo2f_kba_ques" required="true"
						>
					<option value="" selected disabled>
						------------<?php esc_html_e( 'Select your question', 'miniorange-2-factor-authentication' ); ?>
						------------
					</option>
					<option id="mq1_2"
							value="What is your first company name?"><?php esc_html_e( 'What is your first company name?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq2_2"
							value="What was your childhood nickname?"><?php esc_html_e( 'What was your childhood nickname?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq3_2"
							value="In what city did you meet your spouse/significant other?"><?php esc_html_e( 'In what city did you meet your spouse/significant other?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq4_2"
							value="What is the name of your favorite childhood friend?"><?php esc_html_e( 'What is the name of your favorite childhood friend?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq5_2"
							value="What school did you attend for sixth grade?"><?php esc_html_e( 'What school did you attend for sixth grade?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq6_2"
							value="In what city or town was your first job?"><?php esc_html_e( 'In what city or town was your first job?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq7_2"
							value="What is your favourite sport?"><?php esc_html_e( 'What is your favourite sport?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq8_2"
							value="Who is your favourite sports player?"><?php esc_html_e( 'Who is your favourite sports player?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq9_2"
							value="What is your grandmother's maiden name?"><?php esc_html_e( 'What is your grandmother\'s maiden name?', 'miniorange-2-factor-authentication' ); ?></option>
					<option id="mq10_2"
							value="What was your first vehicle's registration number?"><?php esc_html_e( 'What was your first vehicle\'s registration number?', 'miniorange-2-factor-authentication' ); ?></option>
				</select>
			</td>
			<td style="text-align: end;">
				<input class="mo2f_table_textbox_KBA" type="password" name="mo2f_kba_ans2" id="mo2f_kba_ans2"
					title="<?php esc_attr_e( 'Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed.', 'miniorange-2-factor-authentication' ); ?>"
					pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}" required="true"
					placeholder="<?php esc_attr_e( 'Enter your answer', 'miniorange-2-factor-authentication' ); ?>"/>
			</td>
		</tr>
		<tr class="mo2f_kba_body">
			<td>
				<div class="mo2fa_text-align-center">3.</div>
			</td>
			<td class="mo2f_kba_tb_data">
				<input class="mo2f_kba_ques" type="text" style="width: 100%;"name="mo2f_kbaquestion_3" id="mo2f_kbaquestion_3"
					required="true"
					placeholder="<?php esc_attr_e( 'Enter your custom question here', 'miniorange-2-factor-authentication' ); ?>"/>
			</td>
			<td style="text-align: end;">
				<input class="mo2f_table_textbox_KBA" type="password" name="mo2f_kba_ans3" id="mo2f_kba_ans3"
					title="<?php esc_attr_e( 'Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed.', 'miniorange-2-factor-authentication' ); ?>"
					pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}" required="true"
					placeholder="<?php esc_attr_e( 'Enter your answer', 'miniorange-2-factor-authentication' ); ?>"/>
			</td>
		</tr>
	</table>
	<script type="text/javascript">
		var mo_option_to_hide1;
		var mo_option_to_hide2;
		function mo_option_hide(list) {
			var list_selected = document.getElementById("mo2f_kbaquestion_" + list).selectedIndex;
			if (typeof (mo_option_to_hide1) != "undefined" && mo_option_to_hide1 !== null && list == 2) {
				mo_option_to_hide1.style.display = 'block';
			} else if (typeof (mo_option_to_hide2) != "undefined" && mo_option_to_hide2 !== null && list == 1) {
				mo_option_to_hide2.style.display = 'block';
			}
			if (list == 1) {
				if (list_selected != 0) {
					mo_option_to_hide2 = document.getElementById("mq" + list_selected + "_2");
					mo_option_to_hide2.style.display = 'none';
				}
			}
			if (list == 2) {
				if (list_selected != 0) {
					mo_option_to_hide1 = document.getElementById("mq" + list_selected + "_1");
					mo_option_to_hide1.style.display = 'none';
				}
			}
		}  

	</script>

			<?php
			exit;
		}
		/**
		 * Function to register customer
		 *
		 * @param array $post $_POST array.
		 * @return void
		 */
		public function mo2f_register_customer( $post ) {
			global $mo2fdb_queries;
			$user    = wp_get_current_user();
			$email   = isset( $post['email'] ) ? sanitize_email( wp_unslash( $post['email'] ) ) : '';
			$company = isset( $_SERVER['SERVER_NAME'] ) ? esc_url_raw( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : null;

			$password         = $post['password'];
			$confirm_password = $post['confirmPassword'];

			if ( strlen( $password ) < 6 || strlen( $confirm_password ) < 6 ) {
				wp_send_json_error( MoWpnsMessages::show_message( 'PASS_LENGTH' ) );
			}

			if ( $password !== $confirm_password ) {
				wp_send_json_error( MoWpnsMessages::show_message( 'PASS_MISMATCH' ) );
			}
			if ( MoWpnsUtility::check_empty_or_null( $email ) || MoWpnsUtility::check_empty_or_null( $password )
			|| MoWpnsUtility::check_empty_or_null( $confirm_password ) ) {
				wp_send_json_error( MoWpnsMessages::show_message( 'REQUIRED_FIELDS' ) );
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
					$message      = isset( $customer_key['message'] ) ? $customer_key['message'] : __( 'Error occured while creating an account.', 'miniorange-2-factor-authentication' );
					if ( strcasecmp( $customer_key['status'], 'SUCCESS' ) === 0 ) {
						update_site_option( base64_encode( 'totalUsersCloud' ), get_site_option( base64_encode( 'totalUsersCloud' ) ) + 1 ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- We need to obfuscate the option as it will be stored in database.
						update_option( 'mo2f_email', $email );
						$id         = isset( $customer_key['id'] ) ? $customer_key['id'] : '';
						$api_key    = isset( $customer_key['apiKey'] ) ? $customer_key['apiKey'] : '';
						$token      = isset( $customer_key['token'] ) ? $customer_key['token'] : '';
						$app_secret = isset( $customer_key['appSecret'] ) ? $customer_key['appSecret'] : '';
						$this->mo2f_save_success_customer_config( $email, $id, $api_key, $token, $app_secret );
						$this->mo2f_get_current_customer( $email, $password );
						wp_send_json_success( $message );
					} else {
						wp_send_json_error( $message );
					}

					break;
				default:
					$res = $this->mo2f_get_current_customer( $email, $password );
					if ( 'SUCCESS' === $res ) {
						wp_send_json_success( MoWpnsMessages::show_message( 'REG_SUCCESS' ) );
					}
					$message = __( 'Email is already registered in miniOrange. Please try to login to your account.', 'miniorange-2-factor-authentication' );
					wp_send_json_success( $message );

			}
				$message = __( 'Error Occured while registration', 'miniorange-2-factor-authentication' );
				wp_send_json_error( $message );

		}
		/**
		 * Function to verify customer.
		 *
		 * @param array $post $_POST array.
		 * @return object
		 */
		public function mo2f_verify_customer( $post ) {
			global $mo_wpns_utility;
			$email    = isset( $post['email'] ) ? sanitize_email( wp_unslash( $post['email'] ) ) : '';
			$password = $post['password'];

			if ( $mo_wpns_utility->check_empty_or_null( $email ) || $mo_wpns_utility->check_empty_or_null( $password ) ) {
				wp_send_json_error( MoWpnsMessages::show_message( 'REQUIRED_FIELDS' ) );
			}
			return $this->mo2f_get_current_customer( $email, $password );
		}
		/**
		 * Function to get current customer
		 *
		 * @param string $email User email.
		 * @param string $password User password.
		 * @return void
		 */
		public function mo2f_get_current_customer( $email, $password ) {
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
					$this->mo2f_save_success_customer_config( $email, $id, $api_key, $token, $app_secret );
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
					} elseif ( 'SUCCESS' === $content['status'] ) {
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
					wp_send_json_success( MoWpnsMessages::show_message( 'REG_SUCCESS' ) );
				} else {
					update_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_VERIFY_CUSTOMER' );
					update_option( 'mo_wpns_verify_customer', 'true' );
					delete_option( 'mo_wpns_new_registration' );
					wp_send_json_error( MoWpnsMessages::show_message( 'ACCOUNT_EXISTS' ) );
				}
			} else {
				$mo2f_message = is_string( $content ) ? $content : '';
				wp_send_json_error( Mo2fConstants::lang_translate( $mo2f_message ) );
			}
		}

		/**
		 * Function to save configuration of customer.
		 *
		 * @param string $email User email.
		 * @param int    $id User ID.
		 * @param string $api_key API key.
		 * @param string $token token.
		 * @param string $app_secret App secret.
		 * @return void
		 */
		public function mo2f_save_success_customer_config( $email, $id, $api_key, $token, $app_secret ) {
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

			delete_option( 'mo_wpns_verify_customer' );
			delete_option( 'mo_wpns_registration_status' );
			delete_option( 'mo_wpns_password' );
		}
		/**
		 * Function to register and verify customer.
		 *
		 * @return void
		 */
		public function mo_wpns_register_verify_customer() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'mo2f-ajax' );
			}
			$res = '';
			if ( isset( $_POST['Login_and_Continue'] ) && sanitize_text_field( wp_unslash( $_POST['Login_and_Continue'] ) ) === 'Login and Continue' ) {
				$res = $this->mo2f_verify_customer( $_POST );

			} else {
				$res = $this->mo2f_register_customer( $_POST );
			}
			wp_send_json( $res );
		}
		/**
		 * Function to select method in setup wizard.
		 *
		 * @return void
		 */
		public function mo2f_select_method_setup_wizard() {
			global $mo2fdb_queries;
			if ( ! check_ajax_referer( 'select-method-setup-wizard-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'mo2f-ajax' );
			}

			$current_user          = wp_get_current_user();
			$selected_2_f_a_method = isset( $_POST['mo2f_method'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_method'] ) ) : null;
			update_option( 'mo2f_wizard_selected_method', 'DUO/Telegram' );

			if ( ! MO2F_IS_ONPREM ) {
				update_option( 'mo_2factor_user_registration_status', 'REGISTRATION_STARTED' );
				update_user_meta( $current_user->ID, 'register_account_popup', 1 );
				update_user_meta( $current_user->ID, 'mo2f_2FA_method_to_configure', $selected_2_f_a_method );
				wp_send_json( 'SUCCESS' );

			}

			$exceeded = $mo2fdb_queries->check_alluser_limit_exceeded( $current_user->ID );
			if ( ! $exceeded ) {
				$mo2fdb_queries->insert_user( $current_user->ID );
			}

			if ( 'OTP Over Email' === $selected_2_f_a_method ) {
				wp_send_json( 'SUCCESS' );
			}
			update_user_meta( $current_user->ID, 'mo2f_2FA_method_to_configure', $selected_2_f_a_method );

			$mo_2factor_admin_registration_status = get_option( 'mo_2factor_admin_registration_status' );
			if ( 'OTP Over SMS' === $selected_2_f_a_method && 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' !== $mo_2factor_admin_registration_status ) {
				update_option( 'mo_2factor_user_registration_status', 'REGISTRATION_STARTED' );
				update_user_meta( $current_user->ID, 'register_account_popup', 1 );
			} else {
				update_user_meta( $current_user->ID, 'configure_2FA', 1 );
			}
			wp_send_json( 'SUCCESS' );
		}
		/**
		 * Function to skip 2-factor on setup wizard.
		 *
		 * @return void
		 */
		public function mo2f_skiptwofactor_wizard() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( 'mo2f-ajax' );
				exit;
			} else {
				$skip_wizard_2fa_stage = isset( $_POST['twofactorskippedon'] ) ? sanitize_text_field( wp_unslash( $_POST['twofactorskippedon'] ) ) : null;

				update_option( 'mo2f_wizard_skipped', $skip_wizard_2fa_stage );
			}
		}
		/**
		 * Function to set miniOrange authenticator methods.
		 *
		 * @return void
		 */
		public function mo2f_set_miniorange_methods() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( 'mo2f-ajax' );
			}
			global $mo2fdb_queries;
			$transient_id = isset( $_POST['transient_id'] ) ? sanitize_text_field( wp_unslash( $_POST['transient_id'] ) ) : null;
			$user_id      = MO2f_Utility::mo2f_get_transient( $transient_id, 'mo2f_user_id' );
			if ( empty( $user_id ) ) {
				wp_send_json_error( 'UserIdNotFound' );
			}
			$user      = get_user_by( 'id', $user_id );
			$email     = ! empty( $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id ) ) ? $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id ) : $user->user_email;
			$otp_token = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : null;
			$customer  = new Customer_Setup();
			$content   = json_decode( $customer->validate_otp_token( 'SOFT TOKEN', $email, null, $otp_token, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
			wp_send_json_success( $content );
		}
		/**
		 * Function to set OTP over SMS of user.
		 *
		 * @return void
		 */
		public function mo2f_set_otp_over_sms() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( 'mo2f-ajax' );
				exit;
			}
			global $mo2fdb_queries;
			$transient_id = isset( $_POST['transient_id'] ) ? sanitize_text_field( wp_unslash( $_POST['transient_id'] ) ) : null;
			$user_id      = MO2f_Utility::mo2f_get_transient( $transient_id, 'mo2f_user_id' );
			if ( empty( $user_id ) ) {
				wp_send_json_error( 'UserIdNotFound' );
			}
			$new_phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : null;
			$new_phone = str_replace( ' ', '', $new_phone );
			$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_user_phone' => $new_phone ) );
			$user_phone = $mo2fdb_queries->get_user_detail( 'mo2f_user_phone', $user_id );
			wp_send_json_success( $user_phone );
		}
		/**
		 * Function to set Google Authenticator method of user.
		 *
		 * @return void
		 */
		public function mo2f_set_ga() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( 'mo2f-ajax' );
			}
			include_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'class-google-auth-onpremise.php';
			global $mo2fdb_queries;
			$transient_id = isset( $_POST['transient_id'] ) ? sanitize_text_field( wp_unslash( $_POST['transient_id'] ) ) : null;
			$user_id      = MO2f_Utility::mo2f_get_transient( $transient_id, 'mo2f_user_id' );
			if ( empty( $user_id ) ) {
				wp_send_json_error( 'UserIdNotFound' );
			}
			$google_auth = new Miniorange_Rba_Attributes();
			$user        = get_user_by( 'id', $user_id );
			$email       = ! empty( $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id ) ) ? $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id ) : $user->user_email;
			$otp_token   = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : null;
			$ga_secret   = isset( $_POST['ga_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['ga_secret'] ) ) : null;
			if ( MO2F_IS_ONPREM ) {
				$gauth_obj = new Google_auth_onpremise();
				$gauth_obj->mo_g_auth_set_secret( $user_id, $ga_secret );
			} else {

				$google_auth     = new Miniorange_Rba_Attributes();
				$google_response = json_decode( $google_auth->mo2f_google_auth_service( $email, 'miniOrangeAu' ), true );
			}
			$google_response = json_decode( $google_auth->mo2f_validate_google_auth( $email, $otp_token, $ga_secret ), true );
			wp_send_json_success( $google_response['status'] );
		}
		/**
		 * Function to redirect user on ajax login.
		 *
		 * @return void
		 */
		public function mo2f_ajax_login_redirect() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'mo2f-ajax' );
			}
			$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : null;
			$password = isset( $_POST['password'] ) ? $_POST['password'] : null; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,  WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Password should not be sanitized.
			apply_filters( 'authenticate', null, $username, $password );
		}
		/**
		 * Function to save setings for custom login form.
		 *
		 * @return string
		 */
		public function mo2f_save_custom_form_settings() {
			$custom_form = false;
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json( 'error' );
			}
			if ( ! current_user_can( 'administrator' ) ) {
				wp_send_json( 'error' );
			}
			if ( isset( $_POST['submit_selector'] ) &&
			isset( $_POST['email_selector'] ) &&
			isset( $_POST['authType'] ) &&
			isset( $_POST['customForm'] ) &&
			isset( $_POST['form_selector'] ) &&

			sanitize_text_field( wp_unslash( $_POST['submit_selector'] ) ) !== '' &&
			sanitize_text_field( wp_unslash( $_POST['email_selector'] ) ) !== '' &&
			sanitize_text_field( wp_unslash( $_POST['customForm'] ) ) !== '' &&
			sanitize_text_field( wp_unslash( $_POST['form_selector'] ) ) !== '' ) {
				$submit_selector  = sanitize_text_field( wp_unslash( $_POST['submit_selector'] ) );
				$form_selector    = sanitize_text_field( wp_unslash( $_POST['form_selector'] ) );
				$email_selector   = sanitize_text_field( wp_unslash( $_POST['email_selector'] ) );
				$phone_selector   = isset( $_POST['phone_selector'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_selector'] ) ) : '';
				$auth_type        = sanitize_text_field( wp_unslash( $_POST['authType'] ) );
				$custom_form      = sanitize_text_field( wp_unslash( $_POST['customForm'] ) );
				$enable_shortcode = isset( $_POST['enableShortcode'] ) ? sanitize_text_field( wp_unslash( $_POST['enableShortcode'] ) ) : '';
				$form_submit      = isset( $_POST['formSubmit'] ) ? sanitize_text_field( wp_unslash( $_POST['formSubmit'] ) ) : '';

				switch ( $form_selector ) {
					case '.bbp-login-form':
						update_site_option( 'mo2f_custom_reg_bbpress', true );
						update_site_option( 'mo2f_custom_reg_wocommerce', false );
						update_site_option( 'mo2f_custom_reg_custom', false );
						update_site_option( 'mo2f_custom_reg_pmpro', false );
						break;
					case '.woocommerce-form woocommerce-form-register':
						update_site_option( 'mo2f_custom_reg_bbpress', false );
						update_site_option( 'mo2f_custom_reg_wocommerce', true );
						update_site_option( 'mo2f_custom_reg_custom', false );
						update_site_option( 'mo2f_custom_reg_pmpro', false );
						break;
					case '#pmpro_form':
						update_site_option( 'mo2f_custom_reg_bbpress', false );
						update_site_option( 'mo2f_custom_reg_wocommerce', false );
						update_site_option( 'mo2f_custom_reg_custom', false );
						update_site_option( 'mo2f_custom_reg_pmpro', true );
						update_site_option( 'mo2f_activate_plugin', false );
						break;
					default:
						update_site_option( 'mo2f_custom_reg_bbpress', false );
						update_site_option( 'mo2f_custom_reg_wocommerce', false );
						update_site_option( 'mo2f_custom_reg_custom', true );
						update_site_option( 'mo2f_custom_reg_pmpro', false );
				}

				update_site_option( 'mo2f_custom_form_name', $form_selector );
				update_site_option( 'mo2f_custom_email_selector', $email_selector );
				update_site_option( 'mo2f_custom_phone_selector', $phone_selector );
				update_site_option( 'mo2f_custom_submit_selector', $submit_selector );
				update_site_option( 'mo2f_custom_auth_type', $auth_type );
				update_site_option( 'mo2f_form_submit_after_validation', $form_submit );

				update_site_option( 'enable_form_shortcode', $enable_shortcode );
				$saved = true;
			} else {
				$submit_selector = 'NA';
				$form_selector   = 'NA';
				$email_selector  = 'NA';
				$auth_type       = 'NA';
				$saved           = false;
			}
			$return = array(
				'authType'        => $auth_type,
				'submit'          => $submit_selector,
				'emailSelector'   => $email_selector,
				'phone_selector'  => $phone_selector,
				'form'            => $form_selector,
				'saved'           => $saved,
				'customForm'      => $custom_form,
				'formSubmit'      => $form_submit,
				'enableShortcode' => $enable_shortcode,
			);

			return wp_send_json( $return );
		}
		/**
		 * Function to check if user exists with miniOrange.
		 *
		 * @return void
		 */
		public function mo2f_check_user_exist_miniorange() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				echo 'NonceDidNotMatch';
				exit;
			}

			if ( ! get_option( 'mo2f_customerKey' ) ) {
				echo 'NOTLOGGEDIN';
				exit;
			}
			$user = wp_get_current_user();
			global $mo2fdb_queries;
			$email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user->ID );
			if ( empty( $email ) || is_null( $email ) ) {
				$email = $user->user_email;
			}

			if ( isset( $_POST['email'] ) ) {
				$email = sanitize_email( wp_unslash( $_POST['email'] ) );
			}

			$enduser    = new Two_Factor_Setup();
			$check_user = json_decode( $enduser->mo_check_user_already_exist( $email ), true );

			if ( strcasecmp( $check_user['status'], 'USER_FOUND_UNDER_DIFFERENT_CUSTOMER' ) === 0 ) {
				echo 'alreadyExist';
				exit;
			} else {

				update_user_meta( $user->ID, 'mo2f_email_miniOrange', $email );
				echo 'USERCANBECREATED';
				exit;
			}

		}
		/**
		 * Function to shift user to Onpremise.
		 *
		 * @return void
		 */
		public function mo2f_shift_to_onprem() {

			$current_user    = wp_get_current_user();
			$current_user_id = $current_user->ID;
			$miniorange_id   = get_option( 'mo2f_miniorange_admin' );
			if ( is_null( $miniorange_id ) || empty( $miniorange_id ) ) {
				$is_customer_admin = true;
			} else {
				$is_customer_admin = $miniorange_id === $current_user_id;
			}
			if ( $is_customer_admin ) {
				update_option( 'is_onprem', 1 );
				wp_send_json_success();
			} else {
				$admin_user = get_user_by( 'id', $miniorange_id );
				$email      = $admin_user->user_email;
				wp_send_json_error( $email );
			}
		}

		/**
		 * Function to delete generated log file.
		 *
		 * @return void
		 */
		public function mo2f_delete_log_file() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( $error );
			} else {
				$debug_log_path = wp_upload_dir();
				$debug_log_path = $debug_log_path['basedir'];
				$file_name      = 'miniorange_debug_log.txt';
				$status         = file_exists( $debug_log_path . DIRECTORY_SEPARATOR . $file_name );
				if ( $status ) {
					unlink( $debug_log_path . DIRECTORY_SEPARATOR . $file_name );
					wp_send_json_success( 'true' );
				} else {
					wp_send_json_error( 'false' );
				}
			}
		}
		/**
		 * Function to enable and disable debug log.
		 *
		 * @return void
		 */
		public function mo2f_enable_disable_debug_log() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
			}
			$enable = isset( $_POST['mo2f_enable_debug_log'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_enable_debug_log'] ) ) : null;
			if ( 'true' === $enable ) {
				update_site_option( 'mo2f_enable_debug_log', 1 );
				wp_send_json_success();
			} else {
				update_site_option( 'mo2f_enable_debug_log', 0 );
				wp_send_json_error( 'mo2f-ajax' );
			}
		}
		/**
		 * Function to enable and disable 2-factor for users
		 *
		 * @return void
		 */
		public function mo2f_enable_disable_twofactor() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				wp_send_json_error( 'error' );
			}
			if ( ! current_user_can( 'administrator' ) ) {
				wp_send_json_error( 'error' );
			}
			$enable = isset( $_POST['mo2f_enable_2fa'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_enable_2fa'] ) ) : null;
			if ( 'true' === $enable ) {
				update_option( 'mo2f_activate_plugin', 1 );
				wp_send_json_success();
			} else {
				update_option( 'mo2f_activate_plugin', 0 );
				wp_send_json_error( 'false' );
			}
		}
		/**
		 * Function to enable/disable 2FA prompt on login form
		 *
		 * @return void
		 */
		public function mo2f_enable_disable_twofactor_prompt_on_login() {

			global $mo2fdb_queries;
			$user        = wp_get_current_user();
			$auth_method = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html__( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );

			}
			$enable = isset( $_POST['mo2f_enable_2fa_prompt_on_login'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_enable_2fa_prompt_on_login'] ) ) : null;
			if ( ! ( 'Google Authenticator' === $auth_method || 'miniOrange Soft Token' === $auth_method || 'Authy Authenticator' === $auth_method ) ) {
				update_site_option( 'mo2f_enable_2fa_prompt_on_login_page', false );
				if ( ! MO2F_IS_ONPREM ) {
					wp_send_json_error( 'false_method_cloud' );
				} else {
					wp_send_json_error( 'false_method_onprem' );
				}
			} elseif ( 'true' === $enable ) {
				update_site_option( 'mo2f_enable_2fa_prompt_on_login_page', true );
				wp_send_json_success();
			} else {
				update_site_option( 'mo2f_enable_2fa_prompt_on_login_page', false );
				wp_send_json_error( 'false' );
			}
		}
		/**
		 * Function to enable or disable inline registration.
		 *
		 * @return void
		 */
		public function mo2f_enable_disable_inline() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'error' );
			}
			$enable = isset( $_POST['mo2f_inline_registration'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_inline_registration'] ) ) : null;
			if ( 'true' === $enable ) {
				update_site_option( 'mo2f_inline_registration', 1 );
				wp_send_json_success();
			} else {
				update_site_option( 'mo2f_inline_registration', 0 );
				wp_send_json_error( 'false' );
			}
		}
		/**
		 * Function to enable/disable configured methods
		 *
		 * @return void
		 */
		public function mo2f_enable_disable_configurd_methods() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'error' );
			}
			$enable = isset( $_POST['mo2f_nonce_enable_configured_methods'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_nonce_enable_configured_methods'] ) ) : null;

			if ( 'true' === $enable ) {
				update_site_option( 'mo2f_nonce_enable_configured_methods', true );
				wp_send_json_success();
			} else {
				update_site_option( 'mo2f_nonce_enable_configured_methods', false );
				wp_send_json_error( 'false' );
			}
		}
		/**
		 * Function for role based 2-factor settings.
		 *
		 * @return void
		 */
		public function mo2f_role_based_2_factor() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error();
			}
			global $wp_roles;
			foreach ( $wp_roles->role_names as $id => $name ) {
				update_option( 'mo2fa_' . $id, 0 );
			}
			if ( isset( $_POST['enabledrole'] ) ) {
				$enabledrole = isset( $_POST['enabledrole'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['enabledrole'] ) ) : null;
			} else {
				$enabledrole = array();
			}

			foreach ( $enabledrole as $role ) {
				update_option( $role, 1 );
			}
			wp_send_json_success();
		}
		/**
		 * Function to check if customer is admin.
		 *
		 * @return void
		 */
		public function mo2f_single_user() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				echo 'NonceDidNotMatch';
				exit;
			} else {
				$current_user      = wp_get_current_user();
				$current_user_id   = $current_user->ID;
				$miniorange_id     = get_option( 'mo2f_miniorange_admin' );
				$is_customer_admin = $miniorange_id === $current_user_id ? true : false;

				if ( is_null( $miniorange_id ) || empty( $miniorange_id ) ) {
					$is_customer_admin = true;
				}

				if ( $is_customer_admin ) {
					update_option( 'is_onprem', 0 );
					wp_send_json( 'true' );
				} else {
					$admin_user = get_user_by( 'id', $miniorange_id );
					$email      = $admin_user->user_email;
					wp_send_json( $email );
				}
			}
		}
		/**
		 * Function to check if On-premise is active or not.
		 *
		 * @return void
		 */
		public function mo2f_unlimitted_user() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_success();
			} else {
				if ( isset( $_POST['enableOnPremise'] ) && sanitize_text_field( wp_unslash( $_POST['enableOnPremise'] ) ) === 'on' ) {
					global $wp_roles;
					foreach ( $wp_roles->role_names as $id => $name ) {
						add_site_option( 'mo2fa_' . $id, 1 );
						if ( 'administrator' === $id ) {
							add_option( 'mo2fa_' . $id . '_login_url', admin_url() );
						} else {
							add_option( 'mo2fa_' . $id . '_login_url', home_url() );
						}
					}
					wp_send_json_success( 'OnPremiseActive' );
				} else {
					wp_send_json_success( 'OnPremiseDeactive' );
				}
			}
		}
		/**
		 * Function to save email verification settings.
		 *
		 * @return void
		 */
		public function mo2f_save_email_verification() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				echo 'NonceDidNotMatch';
				exit;
			} else {
				$user_id                = get_current_user_id();
				$twofactor_transactions = new Mo2fDB();
				$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $user_id );

				if ( $exceeded ) {
					echo 'USER_LIMIT_EXCEEDED';
					exit;
				}
				$email          = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : null;
				$current_method = isset( $_POST['current_method'] ) ? sanitize_text_field( wp_unslash( $_POST['current_method'] ) ) : null;
				$error          = false;

				$customer_key = get_site_option( 'mo2f_customerKey' );
				$api_key      = get_site_option( 'mo2f_api_key' );

				if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$error = true;
				}
				if ( ! empty( $email ) && ! $error ) {
					if ( 'EmailVerification' === $current_method ) {

						if ( MO2F_IS_ONPREM ) {

							update_user_meta( $user_id, 'tempEmail', $email );
							$enduser = new Customer_Setup();
							$content = $enduser->send_otp_token( $email, 'OUT OF BAND EMAIL', $customer_key, $api_key );
							$decoded = json_decode( $content, true );
							if ( 'FAILED' === $decoded['status'] ) {
								echo 'smtpnotset';
								exit;
							}

							update_user_meta( $user_id, 'Mo2fTxid', $decoded['txId'] );
							$otp_token  = '';
							$otp_token .= wp_rand( 0, 9 );
							update_user_meta( $user_id, 'Mo2fOtpToken', $otp_token );

						}

						// for cloud.
						if ( ! MO2F_IS_ONPREM ) {
							$enduser = new Two_Factor_Setup();
							$enduser->mo2f_update_userinfo( $email, 'OUT OF BAND EMAIL', null, null, null );
						}
						// }

						echo 'settingsSaved';
						exit;
					} elseif ( 'OTPOverEmail' === $current_method ) {
						update_user_meta( $user_id, 'tempEmail', $email );
						$enduser = new Customer_Setup();
						$content = $enduser->send_otp_token( $email, 'OTP Over Email', $customer_key, $api_key );

						$decoded = json_decode( $content, true );
						if ( 'FAILED' === $decoded['status'] ) {
							echo 'smtpnotset';
							exit;
						}
						MO2f_Utility::mo2f_debug_file( 'OTP has been sent successfully over Email' );
						update_user_meta( $user_id, 'configure_2FA', 1 );
						update_user_meta( $user_id, 'Mo2fOtpOverEmailtxid', $decoded['txId'] );

					}
					update_user_meta( $user_id, 'tempRegEmail', $email );
					echo 'settingsSaved';
					exit;
				} else {
					echo 'invalidEmail';
					exit;
				}
			}

		}
		/**
		 * Function to check email verification status.
		 *
		 * @return void
		 */
		public function check_email_verification_status() {
			if ( ! check_ajax_referer( 'mo-two-factor-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( 'ERROR' );
			}
			if ( isset( $_POST['txid'] ) ) {
				$txid   = isset( $_POST['txid'] ) ? sanitize_text_field( wp_unslash( $_POST['txid'] ) ) : null;
				$status = get_site_option( $txid );
				if ( 1 === $status || 0 === $status ) {
					delete_site_option( $txid );
				}
				wp_send_json_success( $status );
			}
			echo 'empty txid';
			exit;
		}


	}
	new Mo_2f_Ajax();
}
?>
