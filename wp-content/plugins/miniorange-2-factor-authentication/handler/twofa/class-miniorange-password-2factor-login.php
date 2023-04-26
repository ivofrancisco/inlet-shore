<?php
/** It enables user to log in through mobile authentication as an additional layer of security over password.
 *
 * @package        miniorange-2-factor-authentication/handler/twofa
 * @license        http://www.gnu.org/copyleft/gpl.html MIT/Expat, see LICENSE.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 */
require 'class-miniorange-mobile-login.php';

if ( ! class_exists( 'Miniorange_Password_2Factor_Login' ) ) {
	/**
	 * Class will help to set two factor on login
	 */
	class Miniorange_Password_2Factor_Login {

		/**
		 *  It will store the KBA Question
		 *
		 * @var string .
		 */
		private $mo2f_kbaquestions;
		/**
		 * For user id variable
		 *
		 * @var string
		 */
		private $mo2f_user_id;
		/**
		 * It will store the rba status
		 *
		 * @var string .
		 */
		private $mo2f_rbastatus;
		/**
		 * It will strore the transaction id
		 *
		 * @var string .
		 */

		private $mo2f_transactionid;
		/**
		 * First 2FA
		 *
		 * @var string .
		 */
		private $fstfactor;
		/**
		 * This function will invoke to prompt 2fa on login
		 *
		 * @return null
		 */
		public function mo2f_inline_login() {
			global $mo_wpns_utility;
			$nonce = isset( $_POST['mo2f_inline_nonce'] ) ? sanitize_key( $_POST['mo2f_inline_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'mo2f-inline-login-nonce' ) ) {
				$error = new WP_Error();
				return $error;
			}
			$email              = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			$password           = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- No need to sanitize password as Strong Passwords contain special symbol.
			$session_id_encrypt = isset( $_POST['session_id'] ) ? wp_unslash( $_POST['session_id'] ) : null; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- No need to sanitize password as Strong Passwords contain special symbol.
			$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
			$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
			if ( $mo_wpns_utility->check_empty_or_null( $email ) || $mo_wpns_utility->check_empty_or_null( $password ) ) {
				$login_message = MoWpnsMessages::show_message( 'REQUIRED_FIELDS' );
				$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
				return;
			}
			$this->mo2f_inline_get_current_customer( $user_id, $email, $password, $redirect_to, $session_id_encrypt );
		}
		/**
		 * This function will help you to register 2fa on login
		 *
		 * @return object
		 */
		public function mo2f_inline_register() {
			global $mo_wpns_utility, $mo2fdb_queries;
			$nonce = isset( $_POST['mo2f_inline_register_nonce'] ) ? sanitize_key( $_POST['mo2f_inline_register_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'mo2f-inline-register-nonce' ) ) {
				$error = new WP_Error();
				return $error;
			}

			$email              = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			$company            = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
			$password           = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- No need to sanitize password as Strong Passwords contain special symbol.
			$confirm_password   = isset( $_POST['confirmPassword'] ) ? wp_unslash( $_POST['confirmPassword'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- No need to sanitize password as Strong Passwords contain special symbol.
			$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
			$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

			$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
			if ( strlen( $password ) < 6 || strlen( $confirm_password ) < 6 ) {
				$login_message = MoWpnsMessages::show_message( 'PASS_LENGTH' );
				$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
				return;

			}
			if ( $password !== $confirm_password ) {
				$login_message = MoWpnsMessages::show_message( 'PASS_MISMATCH' );
				$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
				return;

			}
			if ( MoWpnsUtility::check_empty_or_null( $email ) || MoWpnsUtility::check_empty_or_null( $password )
				|| MoWpnsUtility::check_empty_or_null( $confirm_password ) ) {
				$login_message = MoWpnsMessages::show_message( 'REQUIRED_FIELDS' );
				$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
				return;

			}

			update_option( 'mo2f_email', $email );

			update_option( 'mo_wpns_company', $company );

			update_option( 'mo_wpns_password', $password );

			$customer = new MocURL();
			$content  = json_decode( $customer->check_customer( $email ), true );
			$mo2fdb_queries->insert_user( $user_id );
			switch ( $content['status'] ) {
				case 'CUSTOMER_NOT_FOUND':
					$customer_key  = json_decode( $customer->create_customer( $email, $company, $password, $phone = '', $first_name = '', $last_name = '' ), true );
					$login_message = isset( $customer_key['message'] ) ? $customer_key['message'] : __( 'Error occured while creating an account.', 'miniorange-2-factor-authentication' );

					if ( strcasecmp( $customer_key['status'], 'SUCCESS' ) === 0 ) {
						$id         = isset( $customer_key['id'] ) ? $customer_key['id'] : '';
						$api_key    = isset( $customer_key['apiKey'] ) ? $customer_key['apiKey'] : '';
						$token      = isset( $customer_key['token'] ) ? $customer_key['token'] : '';
						$app_secret = isset( $customer_key['appSecret'] ) ? $customer_key['appSecret'] : '';
						$this->mo2f_inline_save_success_customer_config( $user_id, $email, $id, $api_key, $token, $app_secret );
						$this->mo2f_inline_get_current_customer( $user_id, $email, $password, $redirect_to, $session_id_encrypt );
						return;
					} else {
						$login_status = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
						$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
						return;
					}
					break;
				default:
					$this->mo2f_inline_get_current_customer( $user_id, $email, $password, $redirect_to, $session_id_encrypt );
					return;
			}
			$login_message = __( 'Error Occured while registration', 'miniorange-2-factor-authentication' );
			$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
			$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
		}
		/**
		 * It is to download the backup code
		 *
		 * @return string
		 */
		public function mo2f_download_backup_codes_inline() {
			$nonce   = isset( $_POST['mo2f_inline_backup_nonce'] ) ? sanitize_key( wp_unslash( $_POST['mo2f_inline_backup_nonce'] ) ) : '';
			$backups = isset( $_POST['mo2f_inline_backup_codes'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_inline_backup_codes'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-backup-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				return $error;
			} else {
				$codes      = explode( ',', $backups );
				$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
				$id         = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id, 'mo2f_current_user_id' );

				MO2f_Utility::mo2f_download_backup_codes( $id, $codes );
			}
		}
		/**
		 * This function will redirect to wp dashboard
		 *
		 * @return string
		 */
		public function mo2f_goto_wp_dashboard() {
			global $mo2fdb_queries;
			$nonce = isset( $_POST['mo2f_inline_wp_dashboard_nonce'] ) ? sanitize_key( wp_unslash( $_POST['mo2f_inline_wp_dashboard_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-wp-dashboard-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				return $error;
			} else {
				$pass2fa = new Miniorange_Password_2Factor_Login();
				$pass2fa->mo2fa_pass2login( isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '', isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '' );
				exit;
			}
		}
		/**
		 * This will validate or Use the backcode
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function mo2f_use_backup_codes( $posted ) {
			$nonce = sanitize_text_field( $posted['miniorange_backup_nonce'] );
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-backup-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				return $error;
			} else {
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt  = isset( $posted['session_id'] ) ? sanitize_text_field( $posted['session_id'] ) : null;
				$redirect_to         = isset( $posted['redirect_to'] ) ? esc_url_raw( $posted['redirect_to'] ) : null;
				$mo2fa_login_message = __( 'Please provide your backup codes.', 'miniorange-2-factor-authentication' );
				$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
		/**
		 * This function will invoke for back up code validation
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_backup_codes_validation( $posted ) {
			global $mo2fdb_queries;
			$nonce              = sanitize_text_field( $posted['miniorange_validate_backup_nonce'] );
			$session_id_encrypt = isset( $posted['session_id'] ) ? sanitize_text_field( $posted['session_id'] ) : null;
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-validate-backup-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				return $error;
			} else {
				$this->miniorange_pass2login_start_session();
				$currentuser_id = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
				$redirect_to    = isset( $posted['redirect_to'] ) ? esc_url_raw( $posted['redirect_to'] ) : null;
				if ( isset( $currentuser_id ) ) {
					if ( MO2f_Utility::mo2f_check_empty_or_null( $posted['mo2f_backup_code'] ) ) {
						$mo2fa_login_message = __( 'Please provide backup code.', 'miniorange-2-factor-authentication' );
						$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					}
					$backup_codes     = get_user_meta( $currentuser_id, 'mo2f_backup_codes', true );
					$mo2f_backup_code = sanitize_text_field( $posted['mo2f_backup_code'] );
					$mo2f_user_email  = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $currentuser_id );

					if ( ! empty( $backup_codes ) ) {
						$mo2f_backup_code = md5( $mo2f_backup_code );
						if ( in_array( $mo2f_backup_code, $backup_codes, true ) ) {
							foreach ( $backup_codes as $key => $value ) {
								if ( $value === $mo2f_backup_code ) {
									unset( $backup_codes[ $key ] );
									update_user_meta( $currentuser_id, 'mo2f_backup_codes', $backup_codes );
									$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
								}
							}
						} else {
							$mo2fa_login_message = __( 'The code you provided is already used or incorrect.', 'miniorange-2-factor-authentication' );
							$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
						}
					} else {
						if ( isset( $mo2f_backup_code ) ) {
							$generate_backup_code = new Customer_Cloud_Setup();
							$data                 = $generate_backup_code->mo2f_validate_backup_codes( $mo2f_backup_code, $mo2f_user_email );
							if ( 'success' === $data ) {
								$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
							} elseif ( 'error_in_validation' === $data ) {
								$mo2fa_login_message = __( 'Error occurred while validating the backup codes.', 'miniorange-2-factor-authentication' );
								$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
								$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
							} elseif ( 'used_code' === $data ) {
								$mo2fa_login_message = __( 'The code you provided is already used or incorrect.', 'miniorange-2-factor-authentication' );
								$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
								$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
							} elseif ( 'total_code_used' === $data ) {
								$mo2fa_login_message = __( 'You have used all the backup codes. Please contact <a herf="mailto:2fasupport@xecurify.com">2fasupport@xecurify.com</a>', 'miniorange-2-factor-authentication' );
								$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
								$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
							} elseif ( 'backup_code_not_generated' === $data ) {
								$mo2fa_login_message = __( 'Backup code has not generated for you.', 'miniorange-2-factor-authentication' );
								$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
								$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
							} elseif ( 'TokenNotFound' === $data ) {
								$mo2fa_login_message = __( 'Validation request authentication failed' );
								$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
								$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
							} elseif ( 'DBConnectionerror' === $data ) {
								$mo2fa_login_message = __( 'Error occurred while establising connection.', 'miniorange-2-factor-authentication' );
								$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
								$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
							} elseif ( 'missingparameter' === $data ) {
								$mo2fa_login_message = __( 'Some parameters are missing while validating backup codes.' );
								$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
								$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
							} else {
								$current_user = get_userdata( $currentuser_id );
								if ( in_array( 'administrator', $current_user->roles, true ) ) {
									$mo2fa_login_message = __( 'Error occured while connecting to server. Please follow the <a href="https://faq.miniorange.com/knowledgebase/i-am-locked-cant-access-my-account-what-do-i-do/" target="_blank">Locked out guide</a> to get immediate access to your account.', 'miniorange-2-factor-authentication' );
								} else {
									$mo2fa_login_message = __( 'Error occured while connecting to server. Please contact your administrator.', 'miniorange-2-factor-authentication' );
								}
								$mo2fa_login_status = 'MO_2_FACTOR_CHALLENGE_BACKUP';
								$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
							}
						} else {
							$mo2fa_login_message = __( 'Please enter backup code.', 'miniorange-2-factor-authentication' );
							$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
						}
					}
				} else {
					$this->remove_current_activity( $session_id_encrypt );
					return new WP_Error( 'invalid_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Please try again..', 'miniorange-2-factor-authentication' ) );
				}
			}
		}
		/**
		 * This function will help for generating the backupcode
		 *
		 * @return string
		 */
		public function mo2f_create_backup_codes() {
			$nonce = isset( $_POST['miniorange_generate_backup_nonce'] ) ? sanitize_key( wp_unslash( $_POST['miniorange_generate_backup_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-generate-backup-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				return $error;
			} else {
				global $mo2fdb_queries;

				$redirect_to     = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
				$session_id      = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
				$id              = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id, 'mo2f_current_user_id' );
				$mo2f_user_email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $id );
				if ( empty( $mo2f_user_email ) ) {
					$currentuser     = get_user_by( 'id', $id );
					$mo2f_user_email = $currentuser->user_email;
				}
				$generate_backup_code = new Customer_Cloud_Setup();
				$codes                = $generate_backup_code->mo_2f_generate_backup_codes( $mo2f_user_email, site_url() );

				if ( 'InternetConnectivityError' === $codes ) {
					$mo2fa_login_message = 'Error in sending backup codes.';
					$mo2fa_login_status  = isset( $_POST['login_status'] ) ? sanitize_text_field( wp_unslash( $_POST['login_status'] ) ) : '';
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				} elseif ( 'AllUsed' === $codes ) {
					$mo2fa_login_message = 'You have already used all the backup codes for this user and domain.';
					$mo2fa_login_status  = sanitize_text_field( wp_unslash( $_POST['login_status'] ) );
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				} elseif ( 'UserLimitReached' === $codes ) {
					$mo2fa_login_message = 'Backup code generation limit has reached for this domain.';
					$mo2fa_login_status  = sanitize_text_field( wp_unslash( $_POST['login_status'] ) );
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				} elseif ( 'LimitReached' === $codes ) {
					$mo2fa_login_message = 'backup code generation limit has reached for this user.';
					$mo2fa_login_status  = sanitize_text_field( wp_unslash( $_POST['login_status'] ) );
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				} elseif ( 'invalid_request' === $codes ) {
					$mo2fa_login_message = 'Invalid request.';
					$mo2fa_login_status  = sanitize_text_field( wp_unslash( $_POST['login_status'] ) );
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				}
				$codes = explode( ' ', $codes );

				$mo2f_user_email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $id );
				if ( empty( $mo2f_user_email ) ) {
					$currentuser     = get_user_by( 'id', $id );
					$mo2f_user_email = $currentuser->user_email;
				}
				$result = MO2f_Utility::mo2f_email_backup_codes( $codes, $mo2f_user_email );

				if ( $result ) {
					$mo2fa_login_message = 'An email containing the backup codes has been sent. Please click on Use backup codes to login using the backup codes.';
					update_user_meta( $id, 'mo_backup_code_generated', 1 );
				} else {
					$mo2fa_login_message = " If you haven\'t configured SMTP, please set your SMTP to get the backup codes on email.";
					update_user_meta( $id, 'mo_backup_code_generated', 0 );
				}

				$mo2fa_login_status = sanitize_text_field( wp_unslash( $_POST['login_status'] ) );

				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
			}
		}
		/**
		 * It is for getting the user id or current customer
		 *
		 * @param string $user_id  It will carry the user id.
		 * @param string $email It will carry the email address.
		 * @param string $password It will store the password .
		 * @param string $redirect_to It will carry the redirect url.
		 * @param string $session_id_encrypt  It will carry the session id.
		 * @return void
		 */
		public function mo2f_inline_get_current_customer( $user_id, $email, $password, $redirect_to, $session_id_encrypt ) {
			global $mo2fdb_queries;
			$customer     = new MocURL();
			$content      = $customer->get_customer_key( $email, $password );
			$customer_key = json_decode( $content, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				if ( 'SUCCESS' === $customer_key['status'] ) {
					if ( isset( $customer_key['phone'] ) ) {
						update_option( 'mo_wpns_admin_phone', $customer_key['phone'] );
						$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_user_phone' => $customer_key['phone'] ) );
					}
					update_option( 'mo2f_email', $email );
					$id         = isset( $customer_key['id'] ) ? $customer_key['id'] : '';
					$api_key    = isset( $customer_key['apiKey'] ) ? $customer_key['apiKey'] : '';
					$token      = isset( $customer_key['token'] ) ? $customer_key['token'] : '';
					$app_secret = isset( $customer_key['appSecret'] ) ? $customer_key['appSecret'] : '';
					$this->mo2f_inline_save_success_customer_config( $user_id, $email, $id, $api_key, $token, $app_secret );
					$login_message = MoWpnsMessages::show_message( 'REG_SUCCESS' );
					$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
					return;
				} else {
					$mo2fdb_queries->update_user_details( $user_id, array( 'mo_2factor_user_registration_status' => 'MO_2_FACTOR_VERIFY_CUSTOMER' ) );
					$login_message = MoWpnsMessages::show_message( 'ACCOUNT_EXISTS' );
					$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
					return;
				}
			} else {
				$login_message = is_string( $content ) ? $content : '';
				$login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$this->miniorange_pass2login_form_fields( $login_status, $login_message, $redirect_to, null, $session_id_encrypt );
				return;
			}

		}
		/**
		 * It is to save the inline settings
		 *
		 * @param string $user_id It will carry the user id .
		 * @param string $email It will carry the email .
		 * @param string $id It will carry the id .
		 * @param string $api_key It will carry the api key .
		 * @param string $token It will carry the token value .
		 * @param string $app_secret It will carry the secret data .
		 * @return void
		 */
		public function mo2f_inline_save_success_customer_config( $user_id, $email, $id, $api_key, $token, $app_secret ) {
			global $mo2fdb_queries;
			update_option( 'mo2f_customerKey', $id );
			update_option( 'mo2f_api_key', $api_key );
			update_option( 'mo2f_customer_token', $token );
			update_option( 'mo2f_app_secret', $app_secret );
			update_option( 'mo_wpns_enable_log_requests', true );
			update_option( 'mo2f_miniorange_admin', $id );
			update_option( 'mo_2factor_admin_registration_status', 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' );
			update_option( 'mo_2factor_user_registration_status', 'MO_2_FACTOR_PLUGIN_SETTINGS' );
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_user_email' => sanitize_email( $email ),
				)
			);
		}
		/**
		 * It is to validate the otp in inline
		 *
		 * @return string
		 */
		public function mo2f_inline_validate_otp() {
			if ( isset( $_POST['miniorange_inline_validate_otp_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['miniorange_inline_validate_otp_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-validate-otp-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$otp_token           = '';
					$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					$mo2fa_login_message = '';
					$session_id_encrypt  = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					$redirect_to         = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					if ( MO2f_Utility::mo2f_check_empty_or_null( isset( $_POST['otp_token'] ) ? sanitize_text_field( wp_unslash( $_POST['otp_token'] ) ) : '' ) ) {
						$mo2fa_login_message = __( 'All the fields are required. Please enter valid entries.', 'miniorange-2-factor-authentication' );
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					} else {
						$otp_token = isset( $_POST['otp_token'] ) ? sanitize_key( wp_unslash( $_POST['otp_token'] ) ) : '';
					}
					$current_user = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$selected_2factor_method = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $current_user );

					if ( 'OTP Over Telegram' === $selected_2factor_method ) {
						$userid        = $current_user;
						$otp           = $otp_token;
						$otp_token     = get_user_meta( $userid, 'mo2f_otp_token', true );
						$time          = get_user_meta( $userid, 'mo2f_telegram_time', true );
						$accepted_time = time() - 300;
						$time          = (int) $time;

						if ( $otp === $otp_token ) {
							if ( $accepted_time < $time ) {
								update_user_meta( $userid, 'mo2f_chat_id', get_user_meta( $userid, 'mo2f_temp_chatID', true ) );
								delete_user_meta( $userid, 'mo2f_temp_chatID' );
								delete_user_meta( $userid, 'mo2f_otp_token' );
								delete_user_meta( $userid, 'mo2f_telegram_time' );
								$mo2fdb_queries->update_user_details(
									$userid,
									array(
										'mo2f_configured_2fa_method' => 'OTP Over Telegram',
										'mo2f_OTPOverTelegram_config_status' => true,
										'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
									)
								);
								$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
							} else {
								delete_user_meta( $userid, 'mo2f_otp_token' );
								delete_user_meta( $userid, 'mo2f_telegram_time' );
								$mo2fa_login_message = __( 'OTP has been expired please initiate a new transaction by clicking on verify button.', 'miniorange-2-factor-authentication' );
							}
						} else {
							$mo2fa_login_message = __( 'Invalid OTP. Please try again.', 'miniorange-2-factor-authentication' );
						}
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					}

					$user_phone = $mo2fdb_queries->get_user_detail( 'mo2f_user_phone', $current_user );
					$customer   = new Customer_Setup();
					$content    = json_decode( $customer->validate_otp_token( $selected_2factor_method, null, get_user_meta( $current_user, 'mo2f_transactionId', true ), $otp_token, get_site_option( 'mo2f_customerKey' ), get_site_option( 'mo2f_api_key' ) ), true );
					if ( 'ERROR' === $content['status'] ) {
						$mo2fa_login_message = Mo2fConstants::lang_translate( $content['message'] );
					} elseif ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
						$phone = get_user_meta( $current_user, 'mo2f_user_phone', true );
						if ( $user_phone && strlen( $user_phone ) >= 4 ) {
							if ( $phone !== $user_phone ) {
								$mo2fdb_queries->update_user_details(
									$current_user,
									array(
										'mobile_registration_status' => false,
									)
								);
							}
						}

						$email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user );
						if ( ! ( $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $current_user ) === 'OTP OVER EMAIL' ) ) {
							$mo2fdb_queries->update_user_details(
								$current_user,
								array(
									'mo2f_OTPOverSMS_config_status' => true,
									'mo2f_user_phone' => $phone,
								)
							);
						} else {
							$mo2fdb_queries->update_user_details( $current_user, array( 'mo2f_email_otp_registration_status' => true ) );
						}
						$mo2fdb_queries->update_user_details(
							$current_user,
							array(
								'mo2f_configured_2fa_method' => 'OTP Over SMS',
								'mo_2factor_user_registration_status'               => 'MO_2_FACTOR_PLUGIN_SETTINGS',
							)
						);
						$twof_setup         = new Two_Factor_Setup();
						$response           = json_decode( $twof_setup->mo2f_update_userinfo( $email, 'SMS', null, null, null ), true );
						$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
					} else {  // OTP Validation failed.
						$mo2fa_login_message = __( 'Invalid OTP. Please try again.', 'miniorange-2-factor-authentication' );
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * This function will invoke to send otp in inline form
		 *
		 * @return string
		 */
		public function mo2f_inline_send_otp() {
			if ( isset( $_POST['miniorange_inline_verify_phone_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['miniorange_inline_verify_phone_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-verify-phone-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					if ( isset( $_POST['verify_phone'] ) ) {
						$phone = sanitize_text_field( wp_unslash( $_POST['verify_phone'] ) );
					}
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;

					$current_user = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$redirect_to             = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					$customer                = new Customer_Setup();
					$selected_2factor_method = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $current_user );
					$parameters              = array();
					$email                   = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user );

					$mo2fa_login_message = '';
					$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					if ( 'SMS' === $selected_2factor_method || 'PHONE VERIFICATION' === $selected_2factor_method || 'SMS AND EMAIL' === $selected_2factor_method ) {
						$phone = sanitize_text_field( wp_unslash( $_POST['verify_phone'] ) );
						if ( MO2f_Utility::mo2f_check_empty_or_null( $phone ) ) {
							$mo2fa_login_message = __( 'Please enter your phone number.', 'miniorange-2-factor-authentication' );
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
						}
						$phone = str_replace( ' ', '', $phone );
						update_user_meta( $current_user, 'mo2f_user_phone', $phone );
					}
					if ( 'OTP_OVER_SMS' === $selected_2factor_method || 'SMS' === $selected_2factor_method ) {
						$current_method = 'SMS';
					} elseif ( 'SMS AND EMAIL' === $selected_2factor_method ) {
						$current_method = 'OTP_OVER_SMS_AND_EMAIL';
						$parameters     = array(
							'phone' => $phone,
							'email' => $email,
						);
					} elseif ( 'PHONE VERIFICATION' === $selected_2factor_method ) {
						$current_method = 'PHONE_VERIFICATION';
					} elseif ( 'OTP OVER EMAIL' === $selected_2factor_method ) {
						$current_method = 'OTP_OVER_EMAIL';
						$parameters     = $email;
					} elseif ( 'OTP Over Telegram' === $selected_2factor_method ) {
						$current_method = 'OTP Over Telegram';
						$user_id        = $current_user;
						$chatid         = isset( $_POST['mo2f_verify_chatID'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_verify_chatID'] ) ) : '';
						$otp_token      = '';
						for ( $i = 1;$i < 7;$i++ ) {
							$otp_token .= wp_rand( 0, 9 );
						}

						update_user_meta( $user_id, 'mo2f_otp_token', $otp_token );
						update_user_meta( $user_id, 'mo2f_telegram_time', time() );
						update_user_meta( $user_id, 'mo2f_temp_chatID', $chatid );
						$url      = esc_url( MoWpnsConstants::TELEGRAM_OTP_LINK );
						$postdata = array(
							'mo2f_otp_token' => $otp_token,
							'mo2f_chatid'    => $chatid,
						);

						$args = array(
							'method'    => 'POST',
							'timeout'   => 10,
							'sslverify' => false,
							'headers'   => array(),
							'body'      => $postdata,
						);

						$mo2f_api = new Mo2f_Api();
						$data     = $mo2f_api->mo2f_wp_remote_post( $url, $args );

						if ( 'SUCCESS' === $data ) {
							$mo2fa_login_message = 'An OTP has been sent to your given chat ID. Please enter it below for verification.';
						} else {
							$mo2fa_login_message = 'There were an erroe while sending the OTP. Please confirm your chatID and try again.';
						}

						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					}
					if ( 'SMS AND EMAIL' === $selected_2factor_method ) {
						$content = json_decode( $customer->send_otp_token( $parameters, $current_method, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					} elseif ( 'OTP OVER EMAIL' === $selected_2factor_method ) {
						$content = json_decode( $customer->send_otp_token( $email, $current_method, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					} else {
						$content = json_decode( $customer->send_otp_token( $phone, $current_method, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					}
					if ( json_last_error() === JSON_ERROR_NONE ) { /* Generate otp token */
						if ( 'ERROR' === $content['status'] ) {
							$mo2fa_login_message = Mo2fConstants::lang_translate( $content['message'] );
						} elseif ( 'SUCCESS' === $content['status'] ) {
							update_user_meta( $current_user, 'mo2f_transactionId', $content['txId'] );
							if ( 'SMS' === $selected_2factor_method ) {
								if ( get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) > 0 ) {
									update_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z', get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) - 1 );
								}
								$mo2fa_login_message = __( 'The One Time Passcode has been sent to', 'miniorange-2-factor-authentication' ) . $phone . '.' . __( 'Please enter the one time passcode below to verify your number.', 'miniorange-2-factor-authentication' );
							} elseif ( 'SMS AND EMAIL' === $selected_2factor_method ) {
								$mo2fa_login_message = 'The One Time Passcode has been sent to ' . $parameters['phone'] . ' and ' . $parameters['email'] . '. Please enter the one time passcode sent to your email and phone to verify.';
							} elseif ( 'OTP OVER EMAIL' === $selected_2factor_method ) {
								$mo2fa_login_message = __( 'The One Time Passcode has been sent to ', 'miniorange-2-factor-authentication' ) . $parameters . '.' . __( 'Please enter the one time passcode sent to your email to verify.', 'miniorange-2-factor-authentication' );
							} elseif ( 'PHONE VERIFICATION' === $selected_2factor_method ) {
								$mo2fa_login_message = __( 'You will receive a phone call on this number ', 'miniorange-2-factor-authentication' ) . $phone . '.' . __( 'Please enter the one time passcode below to verify your number.', 'miniorange-2-factor-authentication' );
							}
						} elseif ( 'FAILED' === $content['status'] ) {
							$mo2fa_login_message = esc_textarea( $content['message'], 'miniorange-2-factor-authentication' );
						} else {
							$mo2fa_login_message = __( 'An error occured while validating the user. Please Try again.', 'miniorange-2-factor-authentication' );
						}
					} else {
						$mo2fa_login_message = __( 'Invalid request. Please try again', 'miniorange-2-factor-authentication' );
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * It is for validating the kba
		 *
		 * @return string
		 */
		public function mo2f_inline_validate_kba() {
			if ( isset( $_POST['mo2f_inline_save_kba_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['mo2f_inline_save_kba_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-save-kba-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$session_id_encrypt  = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					$redirect_to         = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					$mo2fa_login_message = '';
					$mo2fa_login_status  = isset( $_POST['mo2f_inline_kba_status'] ) ? 'MO_2_FACTOR_SETUP_SUCCESS' : 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

					$kba_q1 = isset( $_POST['mo2f_kbaquestion_1'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_1'] ) ) : '';
					$kba_a1 = isset( $_POST['mo2f_kba_ans1'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans1'] ) ) : '';
					$kba_q2 = isset( $_POST['mo2f_kbaquestion_2'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_2'] ) ) : '';
					$kba_a2 = isset( $_POST['mo2f_kba_ans2'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans2'] ) ) : '';
					$kba_q3 = isset( $_POST['mo2f_kbaquestion_3'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_3'] ) ) : '';
					$kba_a3 = isset( $_POST['mo2f_kba_ans3'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans3'] ) ) : '';

					$temp_array    = array( $kba_q1, $kba_q2, $kba_q3 );
					$kba_questions = array();
					foreach ( $temp_array as $question ) {
						if ( MO2f_Utility::mo2f_check_empty_or_null( $question ) ) {
							$mo2fa_login_message = __( 'All the fields are required. Please enter valid entries.', 'miniorange-2-factor-authentication' );
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
						} else {
							$ques = sanitize_text_field( $question );
							$ques = addcslashes( stripslashes( $ques ), '"\\' );
							array_push( $kba_questions, $ques );
						}
					}
					if ( ! ( array_unique( $kba_questions ) === $kba_questions ) ) {
						$mo2fa_login_message = __( 'The questions you select must be unique.', 'miniorange-2-factor-authentication' );
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					}
					$temp_array_ans = array( $kba_a1, $kba_a2, $kba_a3 );
					$kba_answers    = array();
					foreach ( $temp_array_ans as $answer ) {
						if ( MO2f_Utility::mo2f_check_empty_or_null( $answer ) ) {
							$mo2fa_login_message = __( 'All the fields are required. Please enter valid entries.', 'miniorange-2-factor-authentication' );
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
						} else {
							$ques   = sanitize_text_field( $answer );
							$answer = strtolower( $answer );
							array_push( $kba_answers, $answer );
						}
					}
					$size         = count( $kba_questions );
					$kba_q_a_list = array();
					for ( $c = 0; $c < $size; $c++ ) {
						array_push( $kba_q_a_list, $kba_questions[ $c ] );
						array_push( $kba_q_a_list, $kba_answers[ $c ] );
					}

					$user_id = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$current_user       = get_user_by( 'id', $user_id );
					$email              = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
					$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
					$mo2fdb_queries->update_user_details(
						$current_user->ID,
						array(
							'mo2f_SecurityQuestions_config_status' => true,
							'mo_2factor_user_registration_status'  => 'MO_2_FACTOR_PLUGIN_SETTINGS',
						)
					);
					if ( ! MO2F_IS_ONPREM ) {
						$kba_q1 = isset( $_POST['mo2f_kbaquestion_1'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_1'] ) ) : '';
						$kba_a1 = isset( $_POST['mo2f_kba_ans1'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans1'] ) ) : '';
						$kba_q2 = isset( $_POST['mo2f_kbaquestion_2'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_2'] ) ) : '';
						$kba_a2 = isset( $_POST['mo2f_kba_ans2'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans2'] ) ) : '';
						$kba_q3 = isset( $_POST['mo2f_kbaquestion_3'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kbaquestion_3'] ) ) : '';
						$kba_a3 = isset( $_POST['mo2f_kba_ans3'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_kba_ans3'] ) ) : '';

						$kba_q1 = addcslashes( stripslashes( $kba_q1 ), '"\\' );
						$kba_q2 = addcslashes( stripslashes( $kba_q2 ), '"\\' );
						$kba_q3 = addcslashes( stripslashes( $kba_q3 ), '"\\' );

						$kba_a1 = addcslashes( stripslashes( $kba_a1 ), '"\\' );
						$kba_a2 = addcslashes( stripslashes( $kba_a2 ), '"\\' );
						$kba_a3 = addcslashes( stripslashes( $kba_a3 ), '"\\' );

						$kba_registration = new Two_Factor_Setup();
						$kba_reg_reponse  = json_decode( $kba_registration->mo2f_register_kba_details( $email, $kba_q1, $kba_a1, $kba_q2, $kba_a2, $kba_q3, $kba_a3, $user_id ), true );

						if ( json_last_error() === JSON_ERROR_NONE ) {
							if ( 'SUCCESS' === $kba_reg_reponse['status'] ) {
								$response = json_decode( $kba_registration->mo2f_update_userinfo( $email, 'KBA', null, null, null ), true );
							}
						}
					}

					$kba_q1          = $kba_q_a_list[0];
					$kba_a1          = md5( $kba_q_a_list[1] );
					$kba_q2          = $kba_q_a_list[2];
					$kba_a2          = md5( $kba_q_a_list[3] );
					$kba_q3          = $kba_q_a_list[4];
					$kba_a3          = md5( $kba_q_a_list[5] );
					$question_answer = array(
						$kba_q1 => $kba_a1,
						$kba_q2 => $kba_a2,
						$kba_q3 => $kba_a3,
					);
					update_user_meta( $current_user->ID, 'mo2f_kba_challenge', $question_answer );
					if ( ! isset( $_POST['mo2f_inline_kba_status'] ) ) {
						update_user_meta( $current_user->ID, 'mo2f_2FA_method_to_configure', 'Security Questions' );
						$mo2fdb_queries->update_user_details( $current_user->ID, array( 'mo2f_configured_2fa_method' => 'Security Questions' ) );
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * Validating the mobile authentication
		 *
		 * @return string
		 */
		public function mo2f_inline_validate_mobile_authentication() {
			if ( isset( $_POST['mo_auth_inline_mobile_registration_complete_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['mo_auth_inline_mobile_registration_complete_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-mobile-registration-complete-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
					$user_id = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$redirect_to             = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					$selected_2factor_method = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $user_id );
					$email                   = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id );
					$mo2fa_login_message     = '';
					$mo2fa_login_status      = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					$enduser                 = new Two_Factor_Setup();
					if ( 'SOFT TOKEN' === $selected_2factor_method || 'miniOrange Soft Token' === $selected_2factor_method ) {
						$selected_2factor_method_onprem = 'miniOrange Soft Token';
					} elseif ( 'PUSH NOTIFICATIONS' === $selected_2factor_method || 'miniOrange Push Notification' === $selected_2factor_method ) {
						$selected_2factor_method_onprem = 'miniOrange Push Notification';
					} elseif ( 'MOBILE AUTHENTICATION' === $selected_2factor_method || 'miniOrange QR Code Authentication' === $selected_2factor_method ) {
						$selected_2factor_method_onprem = 'miniOrange QR Code Authentication';
					}

					$response = json_decode( $enduser->mo2f_update_userinfo( $email, $selected_2factor_method, null, null, null ), true );
					if ( JSON_ERROR_NONE === json_last_error() ) { /* Generate Qr code */
						if ( 'ERROR' === $response['status'] ) {
							$mo2fa_login_message = Mo2fConstants::lang_translate( $response['message'] );
						} elseif ( 'SUCCESS' === $response['status'] ) {
							$mo2fdb_queries->update_user_details(
								$user_id,
								array(
									'mobile_registration_status' => true,
									'mo2f_miniOrangeQRCodeAuthentication_config_status' => true,
									'mo2f_miniOrangeSoftToken_config_status'            => true,
									'mo2f_miniOrangePushNotification_config_status'     => true,
									'mo2f_configured_2fa_method' => $selected_2factor_method_onprem,
									'mo_2factor_user_registration_status'    => 'MO_2_FACTOR_PLUGIN_SETTINGS',
								)
							);
							$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
						} else {
							$mo2fa_login_message = __( 'An error occured while validating the user. Please Try again.', 'miniorange-2-factor-authentication' );
						}
					} else {
						$mo2fa_login_message = __( 'Invalid request. Please try again', 'miniorange-2-factor-authentication' );
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * This function will invoke the duo push notification
		 *
		 * @return string
		 */
		public function mo2f_duo_mobile_send_push_notification_for_inline_form() {
			if ( isset( $_POST['duo_mobile_send_push_notification_inline_form_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['duo_mobile_send_push_notification_inline_form_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'mo2f-send-duo-push-notification-inline-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
					$user_id     = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;

					$mo2fdb_queries->update_user_details(
						$user_id,
						array(
							'mobile_registration_status' => true,
						)
					);
					$mo2fa_login_message = '';

					$mo2fa_login_status = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * This function will invoke on duo authentication validation
		 *
		 * @return string
		 */
		public function mo2f_inline_validate_duo_authentication() {
			if ( isset( $_POST['mo_auth_inline_duo_auth_mobile_registration_complete_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['mo_auth_inline_duo_auth_mobile_registration_complete_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-duo_auth-registration-complete-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
					$user_id                 = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					$redirect_to             = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					$selected_2factor_method = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $user_id );
					$email                   = sanitize_email( $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id ) );
					$mo2fdb_queries->update_user_details(
						$user_id,
						array(
							'mobile_registration_status' => true,
						)
					);
					$mo2fa_login_message = '';

					include_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_duo_handler.php';
					$ikey = get_site_option( 'mo2f_d_integration_key' );
					$skey = get_site_option( 'mo2f_d_secret_key' );
					$host = get_site_option( 'mo2f_d_api_hostname' );

					$duo_preauth = preauth( $email, true, $skey, $ikey, $host );

					if ( isset( $duo_preauth['response']['stat'] ) && 'OK' === $duo_preauth['response']['stat'] ) {
						if ( isset( $duo_preauth['response']['response']['status_msg'] ) && 'Account is active' === $duo_preauth['response']['response']['status_msg'] ) {
							$mo2fa_login_message = $email . ' user is already exists, please go for step B duo will send push notification on your configured mobile.';
						} elseif ( isset( $duo_preauth['response']['response']['enroll_portal_url'] ) ) {
							$duo_enroll_url = $duo_preauth['response']['response']['enroll_portal_url'];
							update_user_meta( $user_id, 'user_not_enroll_on_duo_before', $duo_enroll_url );
							update_user_meta( $user_id, 'user_not_enroll', true );
						} else {
							$mo2fa_login_message = 'Your account is inactive from duo side, please contact to your administrator.';
						}
					} else {
						$mo2fa_login_message = 'Error through during preauth.';
					}

					$mo2fa_login_status = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * It will invoke after inline registration setup success
		 *
		 * @param string $current_user_id It will carry the user id value .
		 * @param string $redirect_to It will carry the redirect url .
		 * @param string $session_id It will carry the session id .
		 * @return void
		 */
		public function mo2f_inline_setup_success( $current_user_id, $redirect_to, $session_id ) {
			global $mo2fdb_queries;
			$mo2fdb_queries->update_user_details( $current_user_id, array( 'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS' ) );

			$mo2f_user_email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user_id );
			if ( empty( $mo2f_user_email ) ) {
				$currentuser     = get_user_by( 'id', $current_user_id );
				$mo2f_user_email = $currentuser->user_email;
			}
			$generate_backup_code = new Customer_Cloud_Setup();
			$codes                = $generate_backup_code->mo_2f_generate_backup_codes( $mo2f_user_email, site_url() );

			$code_generate = get_user_meta( $current_user_id, 'mo_backup_code_generated', false );

			if ( empty( $code_generate ) && 'InternetConnectivityError' !== $codes && 'DBConnectionIssue' !== $codes && 'UnableToFetchData' !== $codes && 'UserLimitReached' !== $codes && 'ERROR' !== $codes && 'LimitReached' !== $codes && 'AllUsed' !== $codes && 'invalid_request' !== $codes ) {
				$mo2fa_login_message = '';
				$mo2fa_login_status  = 'MO_2_FACTOR_GENERATE_BACKUP_CODES';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
			} else {
				$pass2fa = new Miniorange_Password_2Factor_Login();
				$pass2fa->mo2fa_pass2login( $redirect_to, $session_id );
				update_user_meta( $current_user_id, 'error_during_code_generation', $codes );
				exit;
			}
		}
		/**
		 * Inline qr code for mobile
		 *
		 * @param string $email It will carry the email address.
		 * @param string $id It will carry the id .
		 * @return string
		 */
		public function mo2f_inline_get_qr_code_for_mobile( $email, $id ) {
			$register_mobile = new Two_Factor_Setup();
			$content         = $register_mobile->register_mobile( $email );
			$response        = json_decode( $content, true );
			$message         = '';
			$miniorageqr     = array();
			if ( JSON_ERROR_NONE === json_last_error() ) {
				if ( 'ERROR' === $response['status'] ) {
					$miniorageqr['message'] = Mo2fConstants::lang_translate( $response['message'] );

					delete_user_meta( $id, 'miniorageqr' );
				} else {
					if ( 'IN_PROGRESS' === $response['status'] ) {
						$miniorageqr['message']                  = '';
						$miniorageqr['mo2f-login-qrCode']        = $response['qrCode'];
						$miniorageqr['mo2f-login-transactionId'] = $response['txId'];
						$miniorageqr['mo2f_show_qr_code']        = 'MO_2_FACTOR_SHOW_QR_CODE';
						update_user_meta( $id, 'miniorageqr', $miniorageqr );
					} else {
						$miniorageqr['message'] = __( 'An error occured while processing your request. Please Try again.', 'miniorange-2-factor-authentication' );
						delete_user_meta( $id, 'miniorageqr' );
					}
				}
			}
			return $miniorageqr;
		}
		/**
		 * Inline mobile configure
		 *
		 * @return string
		 */
		public function inline_mobile_configure() {
			if ( isset( $_POST['miniorange_inline_show_qrcode_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['miniorange_inline_show_qrcode_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-show-qrcode-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$redirect_to              = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					$current_user             = get_user_by( 'id', $user_id );
					$mo2fa_login_message      = '';
					$mo2fa_login_status       = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					$user_registration_status = $mo2fdb_queries->get_user_detail( 'mo_2factor_user_registration_status', $current_user->ID );
					if ( 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR' === $user_registration_status ) {
						$email               = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
						$miniorageqr         = $this->mo2f_inline_get_qr_code_for_mobile( $email, $current_user->ID );
						$mo2fa_login_message = $miniorageqr['message'];
						MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'mo2f_transactionId', $miniorageqr['mo2f-login-transactionId'] );

						$this->mo2f_transactionid = $miniorageqr['mo2f-login-transactionId'];
					} else {
						$mo2fa_login_message = __( 'Invalid request. Please register with miniOrange before configuring your mobile.', 'miniorange-2-factor-authentication' );
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, $miniorageqr, $session_id_encrypt );
				}
			}
		}
		/**
		 * It will invoke the inline and validate the google authenticator
		 *
		 * @return string
		 */
		public function inline_validate_and_set_ga() {
			if ( isset( $_POST['mo2f_inline_validate_ga_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['mo2f_inline_validate_ga_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-google-auth-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$otp_token          = isset( $_POST['google_auth_code'] ) ? sanitize_text_field( wp_unslash( $_POST['google_auth_code'] ) ) : '';
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$current_user = get_user_by( 'id', $user_id );
					$redirect_to  = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					$ga_secret    = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'secret_ga' );

					$mo2fa_login_message = '';
					$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					if ( MO2f_Utility::mo2f_check_number_length( $otp_token ) ) {
						$email           = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
						$google_auth     = new Miniorange_Rba_Attributes();
						$google_response = json_decode( $google_auth->mo2f_validate_google_auth( $email, $otp_token, $ga_secret ), true );
						if ( JSON_ERROR_NONE === json_last_error() ) {
							if ( 'SUCCESS' === $google_response['status'] ) {
								$response = $google_response;
								if ( JSON_ERROR_NONE === json_last_error() || MO2F_IS_ONPREM ) {
									if ( 'SUCCESS' === $response['status'] ) {
										$mo2fdb_queries->update_user_details(
											$current_user->ID,
											array(
												'mo2f_GoogleAuthenticator_config_status' => true,
												'mo2f_configured_2fa_method' => 'Google Authenticator',
												'mo2f_AuthyAuthenticator_config_status' => false,
												'mo_2factor_user_registration_status'               => 'MO_2_FACTOR_PLUGIN_SETTINGS',
											)
										);

										if ( MO2F_IS_ONPREM ) {
											update_user_meta( $current_user->ID, 'mo2f_2FA_method_to_configure', 'GOOGLE AUTHENTICATOR' );
											$gauth_obj = new Google_auth_onpremise();
											$gauth_obj->mo_g_auth_set_secret( $current_user->ID, $ga_secret );
										}
										update_user_meta( $current_user->ID, 'mo2f_external_app_type', 'GOOGLE AUTHENTICATOR' );
										$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';

										// When user sets method of another admin from USers section.
										if ( ! empty( get_user_meta( $current_user->ID, 'mo2fa_set_Authy_inline' ) ) ) {
											$mo2fdb_queries->update_user_details(
												$current_user->ID,
												array(
													'mo2f_GoogleAuthenticator_config_status' => false,
													'mo2f_AuthyAuthenticator_config_status'  => true,
													'mo2f_configured_2fa_method'             => 'Authy Authenticator',
													'user_registration_with_miniorange'      => 'SUCCESS',
													'mo_2factor_user_registration_status'    => 'MO_2_FACTOR_PLUGIN_SETTINGS',
												)
											);
											update_user_meta( $current_user->ID, 'mo2f_external_app_type', 'Authy Authenticator' );
											delete_user_meta( $current_user->ID, 'mo2fa_set_Authy_inline' );
										}
									} else {
										$mo2fa_login_message = __( 'An error occured while setting up Google/Authy Authenticator. Please Try again.', 'miniorange-2-factor-authentication' );
									}
								} else {
									$mo2fa_login_message = __( 'An error occured while processing your request. Please Try again.', 'miniorange-2-factor-authentication' );
								}
							} else {
								$mo2fa_login_message = __( 'An error occured while processing your request. Please Try again.', 'miniorange-2-factor-authentication' );
							}
						} else {
							$mo2fa_login_message = __( 'An error occured while validating the user. Please Try again.', 'miniorange-2-factor-authentication' );
						}
					} else {
						$mo2fa_login_message = __( 'Only digits are allowed. Please enter again.', 'miniorange-2-factor-authentication' );
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * Back to select 2fa methods
		 *
		 * @return string
		 */
		public function back_to_select_2fa() {
			if ( isset( $_POST['miniorange_inline_two_factor_setup'] ) ) { /* return back to choose second factor screen */
				$nonce = sanitize_key( wp_unslash( $_POST['miniorange_inline_two_factor_setup'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-setup-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;

					$user_id = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$redirect_to  = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					$current_user = get_user_by( 'id', $user_id );
					$mo2fdb_queries->update_user_details( $current_user->ID, array( 'mo2f_configured_2fa_method' => '' ) );
					$mo2fa_login_message = '';
					$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * It will help to create user in miniorange
		 *
		 * @param string $current_user_id It will carry the current user id .
		 * @param string $email It will carry the email address .
		 * @param string $current_method It will carry the current method .
		 * @return string
		 */
		public function create_user_in_miniorange( $current_user_id, $email, $current_method ) {
			$tempemail = get_user_meta( $current_user_id, 'mo2f_email_miniOrange', true );
			if ( isset( $tempemail ) && ! empty( $tempemail ) ) {
				$email = $tempemail;
			}
			global $mo2fdb_queries;

			$enduser = new Two_Factor_Setup();
			if ( get_option( 'mo2f_miniorange_admin' === $current_user_id ) ) {
				$email = get_option( 'mo2f_email' );
			}

			$check_user = json_decode( $enduser->mo_check_user_already_exist( $email ), true );

			if ( JSON_ERROR_NONE === json_last_error() ) {
				if ( 'ERROR' === $check_user['status'] ) {
					return $check_user;
				} elseif ( strcasecmp( $check_user['status'], 'USER_FOUND' ) === 0 ) {
					$mo2fdb_queries->update_user_details(
						$current_user_id,
						array(
							'user_registration_with_miniorange' => 'SUCCESS',
							'mo2f_user_email' => $email,
							'mo_2factor_user_registration_status' => 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR',
						)
					);
					update_site_option( base64_encode( 'totalUsersCloud' ), get_site_option( base64_encode( 'totalUsersCloud' ) ) + 1 ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not using for obfuscation

					$mo2fa_login_status = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					return $check_user;
				} elseif ( strcasecmp( 0 === $check_user['status'], 'USER_NOT_FOUND' ) ) {
					$current_user = get_user_by( 'id', $current_user_id );
					$content      = json_decode( $enduser->mo_create_user( $current_user, $email ), true );

					if ( JSON_ERROR_NONE === json_last_error() ) {
						if ( 0 === strcasecmp( $content['status'], 'SUCCESS' ) ) {
							update_site_option( base64_encode( 'totalUsersCloud' ), get_site_option( base64_encode( 'totalUsersCloud' ) ) + 1 ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not using for obfuscation
							$mo2fdb_queries->update_user_details(
								$current_user_id,
								array(
									'user_registration_with_miniorange' => 'SUCCESS',
									'mo2f_user_email' => $email,
									'mo_2factor_user_registration_status' => 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR',
								)
							);

							$mo2fa_login_message = '';
							$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
							return $check_user;
						} else {
							$check_user['status']  = 'ERROR';
							$check_user['message'] = 'There is an issue in user creation in miniOrange. Please skip and contact miniorange';
							return $check_user;
						}
					}
				} elseif ( 0 === strcasecmp( $check_user['status'], 'USER_FOUND_UNDER_DIFFERENT_CUSTOMER' ) ) {
					$mo2fa_login_message   = __( 'The email associated with your account is already registered. Please contact your admin to change the email.', 'miniorange-2-factor-authentication' );
					$check_user['status']  = 'ERROR';
					$check_user['message'] = $mo2fa_login_message;
					return $check_user;
				}
			}
		}
		/**
		 * It will invoke to Skip 2fa setup
		 *
		 * @return string
		 */
		public function mo2f_skip_2fa_setup() {
			if ( isset( $_POST['miniorange_skip_2fa_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['miniorange_skip_2fa_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-skip-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					global $mo2fdb_queries;
					$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
					$session_id_encrypt = sanitize_text_field( $session_id_encrypt );
					$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					$currentuser        = get_user_by( 'id', $user_id );

					$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_2factor_enable_2fa_byusers' => 1 ) );

					$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
				}
			}
		}
		/**
		 * This will invoke to save 2fa method on inline
		 *
		 * @return string
		 */
		public function save_inline_2fa_method() {
			if ( isset( $_POST['miniorange_inline_save_2factor_method_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['miniorange_inline_save_2factor_method_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-save-2factor-method-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$mo2fa_login_message = '';
					$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

					$user_id = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$redirect_to                       = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					$current_user                      = get_user_by( 'id', $user_id );
					$current_user_id                   = $current_user->ID;
					$email                             = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
					$user_registration_with_miniorange = $mo2fdb_queries->get_user_detail( 'user_registration_with_miniorange', $current_user->ID );
					if ( 'SUCCESS' === $user_registration_with_miniorange ) {
						$selected_method = isset( $_POST['mo2f_selected_2factor_method'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_selected_2factor_method'] ) ) : 'NONE';

						if ( 'OUT OF BAND EMAIL' === $selected_method ) {
							if ( ! MO2F_IS_ONPREM ) {
								$current_user = get_userdata( $current_user_id );
								$email        = $current_user->user_email;
								$response     = $this->create_user_in_miniorange( $current_user_id, $email, $selected_method );

								if ( 'ERROR' === $response['status'] ) {
									$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
									$mo2fa_login_message = $response['message'] . 'Skip the two-factor for login';
								} else {
									$enduser = new Two_Factor_Setup();

									$mo2fdb_queries->update_user_details(
										$current_user_id,
										array(
											'mo2f_email_verification_status' => true,
											'mo2f_configured_2fa_method' => 'Email Verification',
											'mo2f_user_email' => $email,
										)
									);
									$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
								}
							} else {
								$enduser = new Two_Factor_Setup();

								$mo2fdb_queries->update_user_details(
									$current_user_id,
									array(
										'mo2f_email_verification_status' => true,
										'mo2f_configured_2fa_method' => 'Email Verification',
										'mo2f_user_email' => $email,
									)
								);
								$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';
							}
						} elseif ( 'OTP OVER EMAIL' === $selected_method ) {
							$email = $current_user->user_email;
							if ( ! MO2F_IS_ONPREM ) {
								$current_user = get_userdata( $current_user_id );
								$response     = $this->create_user_in_miniorange( $current_user_id, $email, $selected_method );
								if ( 'ERROR' === $response['status'] ) {
									$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
									$mo2fa_login_message = $response['message'] . 'Skip the two-factor for login';
								} else {
									$user_email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
									if ( ! empty( $user_email ) && ! is_null( $user_email ) ) {
										$email = $user_email;
									}
									$this->mo2f_otp_over_email_send( $email, $redirect_to, $session_id_encrypt, $current_user );
								}
							} else {
								$this->mo2f_otp_over_email_send( $email, $redirect_to, $session_id_encrypt, $current_user );
							}
						} elseif ( 'GOOGLE AUTHENTICATOR' === $selected_method ) {
							$this->miniorange_pass2login_start_session();
							$mo2fa_login_message = '';
							$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
							$google_auth         = new Miniorange_Rba_Attributes();

							$gauth_name          = get_site_option( 'mo2f_google_appname' );
							$google_account_name = $gauth_name ? $gauth_name : 'miniOrangeAu';

							$email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );

							if ( MO2F_IS_ONPREM ) { // this should not be here .
								$mo2fdb_queries->update_user_details(
									$current_user->ID,
									array(
										'mo2f_configured_2fa_method' => $selected_method,
									)
								);
								include_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-google-auth-onpremise.php';
								$gauth_obj = new Google_auth_onpremise();

								$onpremise_secret              = $gauth_obj->mo2f_create_secret();
								$issuer                        = get_site_option( 'mo2f_google_appname', 'miniOrangeAu' );
								$url                           = $gauth_obj->mo2f_geturl( $onpremise_secret, $issuer, $email );
								$mo2f_google_auth              = array();
								$mo2f_google_auth['ga_qrCode'] = $url;
								$mo2f_google_auth['ga_secret'] = $onpremise_secret;

								MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'secret_ga', $onpremise_secret );
								MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'ga_qrCode', $url );
							} else {
								$current_user = get_userdata( $current_user_id );
								$email        = $current_user->user_email;
								$tempemail    = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user_id );

								if ( ! isset( $tempemail ) && ! is_null( $tempemail ) && ! empty( $tempemail ) ) {
									$email = $tempemail;
								}

								$response = $this->create_user_in_miniorange( $current_user_id, $email, $selected_method );
								if ( 'ERROR' === $response['status'] ) {
									$mo2fa_login_message = $response['message'];
									$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
								} else {
									$mo2fdb_queries->update_user_details(
										$current_user->ID,
										array(
											'mo2f_configured_2fa_method' => $selected_method,
										)
									);
									$google_response = json_decode( $google_auth->mo2f_google_auth_service( $email, $google_account_name ), true );
									if ( JSON_ERROR_NONE === json_last_error() ) {
										if ( 'SUCCESS' === $google_response['status'] ) {
											$mo2f_google_auth              = array();
											$mo2f_google_auth['ga_qrCode'] = $google_response['qrCodeData'];
											$mo2f_google_auth['ga_secret'] = $google_response['secret'];

											MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'secret_ga', $mo2f_google_auth['ga_secret'] );
											MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'ga_qrCode', $mo2f_google_auth['ga_qrCode'] );
										} else {
											$mo2fa_login_message = __( 'Invalid request. Please register with miniOrange to configure 2 Factor plugin.', 'miniorange-2-factor-authentication' );
										}
									}
								}
							}
						} elseif ( 'DUO PUSH NOTIFICATIONS' === $selected_method ) {
							$this->miniorange_pass2login_start_session();
							$mo2fa_login_message = '';
							$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

							$selected_method = 'Duo Authenticator';

							$mo2fdb_queries->update_user_details(
								$current_user->ID,
								array(
									'mo2f_configured_2fa_method' => $selected_method,
								)
							);
						} else {
							if ( ! MO2F_IS_ONPREM || 'MOBILE AUTHENTICATION' === $selected_method || 'PUSH NOTIFICATIONS' === $selected_method || 'SOFT TOKEN' === $selected_method ) {
								$current_user = get_userdata( $current_user_id );
								$email        = $current_user->user_email;
								$response     = $this->create_user_in_miniorange( $current_user_id, $email, $selected_method );
								if ( ! is_null( $response ) && 'ERROR' === $response['status'] ) {
									$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
									$mo2fa_login_message = $response['message'] . 'Skip the two-factor for login';
								} else {
									if ( 'OTP OVER TELEGRAM' === $selected_method ) {
										$selected_method = 'OTP Over Telegram';
									}
									$mo2fdb_queries->update_user_details( $current_user->ID, array( 'mo2f_configured_2fa_method' => $selected_method ) );
								}
							} else {
								if ( 'OTP OVER TELEGRAM' === $selected_method ) {
									$selected_method = 'OTP Over Telegram';
								}
								$mo2fdb_queries->update_user_details(
									$current_user->ID,
									array(
										'mo2f_configured_2fa_method' => $selected_method,
									)
								);
							}
						}
					} else {
						$mo2fa_login_message = __( 'Invalid request. Please register with miniOrange to configure 2 Factor plugin.', 'miniorange-2-factor-authentication' );
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * This function will help to check Kba answers and validated it
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_kba_validation( $posted ) {
			global $mo_wpns_utility;
			if ( isset( $posted['miniorange_kba_nonce'] ) ) { /*check kba validation*/
				$nonce = $posted['miniorange_kba_nonce'];
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-kba-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
					return $error;
				} else {
					$this->miniorange_pass2login_start_session();
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					if ( isset( $user_id ) ) {
						if ( MO2f_Utility::mo2f_check_empty_or_null( isset( $_POST['mo2f_answer_1'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_answer_1'] ) ) : '' ) || MO2f_Utility::mo2f_check_empty_or_null( isset( $_POST['mo2f_answer_2'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_answer_2'] ) ) : '' ) ) {
							MO2f_Utility::mo2f_debug_file( 'Please provide both the answers of KBA User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
							$mo2fa_login_message = 'Please provide both the answers.';
							$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
						}
						$otp_token          = array();
						$kba_questions      = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo_2_factor_kba_questions' );
						$otp_token[0]       = $kba_questions[0]['question'];
						$otp_token[1]       = isset( $_POST['mo2f_answer_1'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_answer_1'] ) ) : '';
						$otp_token[2]       = $kba_questions[1]['question'];
						$otp_token[3]       = isset( $_POST['mo2f_answer_2'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_answer_2'] ) ) : '';
						$check_trust_device = isset( $_POST['mo2f_trust_device'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_trust_device'] ) ) : 'false';
						// if the php session folder has insufficient permissions, cookies to be used .
						$mo2f_login_transaction_id = MO2f_Utility::mo2f_retrieve_user_temp_values( 'mo2f_transactionId', $session_id_encrypt );
						MO2f_Utility::mo2f_debug_file( 'Transaction Id-' . $mo2f_login_transaction_id . ' User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
						$kba_validate          = new Customer_Setup();
						$kba_validate_response = json_decode( $kba_validate->validate_otp_token( 'KBA', null, $mo2f_login_transaction_id, $otp_token, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
						global $mo2fdb_queries;
						if ( 0 === strcasecmp( $kba_validate_response['status'], 'SUCCESS' ) ) {
							MO2f_Utility::mo2f_debug_file( 'Logged in successfully User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
							$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
						} else {
							MO2f_Utility::mo2f_debug_file( 'The answers you have provided for KBA are incorrect User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
							$mo2fa_login_message = 'The answers you have provided are incorrect.';
							$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
						}
					} else {
						MO2f_Utility::mo2f_debug_file( 'User id not found User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
						$this->remove_current_activity( $session_id_encrypt );
						return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again..' ) );
					}
				}
			}
		}
		/**
		 * This function will help to redirect back to inline form
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function miniorange2f_back_to_inline_registration( $posted ) {
			$nonce = isset( $_POST['miniorange_back_inline_reg_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['miniorange_back_inline_reg_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-back-inline-reg-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
				return $error;
			} else {
				$session_id_encrypt  = sanitize_text_field( $posted['session_id'] );
				$redirect_to         = esc_url_raw( $posted['redirect_to'] );
				$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				$mo2fa_login_message = '';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
		/**
		 * This is called on forgot phone option
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_challenge_forgotphone( $posted ) {
			/*check kba validation*/
			$nonce = isset( $_POST['miniorange_forgotphone'] ) ? sanitize_text_field( wp_unslash( $_POST['miniorange_forgotphone'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-forgotphone' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
				return $error;
			} else {
				$mo2fa_login_status  = isset( $_POST['request_origin_method'] ) ? sanitize_text_field( wp_unslash( $_POST['request_origin_method'] ) ) : null;
				$session_id_encrypt  = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
				$redirect_to         = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
				$mo2fa_login_message = '';
				$this->miniorange_pass2login_start_session();
				$customer = new Customer_Setup();
				$user_id  = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
				global $mo2fdb_queries;
				$user_email               = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id );
				$kba_configuration_status = $mo2fdb_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $user_id );
				if ( $kba_configuration_status ) {
					$mo2fa_login_status = 'MO_2_FACTOR_CHALLENGE_KBA_AND_OTP_OVER_EMAIL';
					$pass2fa_login      = new Miniorange_Password_2Factor_Login();
					$pass2fa_login->mo2f_pass2login_kba_verification( $user_id, $redirect_to, $session_id_encrypt );
				} else {
					$hidden_user_email = MO2f_Utility::mo2f_get_hidden_email( $user_email );
					$content           = json_decode( $customer->send_otp_token( $user_email, 'EMAIL', get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					if ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
						$session_cookie_variables = array( 'mo2f-login-qrCode', 'mo2f_transactionId' );
						MO2f_Utility::unset_session_variables( $session_cookie_variables );
						MO2f_Utility::unset_cookie_variables( $session_cookie_variables );
						MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
						// if the php session folder has insufficient permissions, cookies to be used .
						MO2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_login_message', 'A one time passcode has been sent to <b>' . $hidden_user_email . '</b>. Please enter the OTP to verify your identity.' );
						MO2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_transactionId', $content['txId'] );
						$this->mo2f_transactionid = $content['txId'];
						$mo2fa_login_message      = 'A one time passcode has been sent to <b>' . $hidden_user_email . '</b>. Please enter the OTP to verify your identity.';
						$mo2fa_login_status       = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
					} else {
						$mo2fa_login_message = 'Error occurred while sending OTP over email. Please try again.';
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
				$pass2fa_login = new Miniorange_Password_2Factor_Login();
				$pass2fa_login->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
		/**
		 * It is a alternate login method
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_alternate_login_kba( $posted ) {
			$nonce = $posted['miniorange_alternate_login_kba_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-alternate-login-kba-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
				return $error;
			} else {
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $posted['session_id'] ) ? $posted['session_id'] : null;
				$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
				$redirect_to        = isset( $posted['redirect_to'] ) ? esc_url_raw( $posted['redirect_to'] ) : null;
				$this->mo2f_pass2login_kba_verification( $user_id, $redirect_to, $session_id_encrypt );
			}
		}
		/**
		 * It is for duo push notification validation
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_duo_push_validation( $posted ) {
			global $mo_wpns_utility;
			$nonce = $posted['miniorange_duo_push_validation_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-duo-validation-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
				return $error;
			} else {
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $posted['session_id'] ) ? sanitize_text_field( wp_unslash( $posted['session_id'] ) ) : null;
				$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

				$redirect_to = isset( $posted['redirect_to'] ) ? esc_url_raw( $posted['redirect_to'] ) : null;
				MO2f_Utility::mo2f_debug_file( 'Duo push notification - Logged in successfully User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
				$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
			}
		}
		/**
		 * This will invoke Duo push validation failed
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_duo_push_validation_failed( $posted ) {
			global $mo_wpns_utility;
			$nonce = $posted['miniorange_duo_push_validation_failed_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-duo-push-validation-failed-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_textarea( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . esc_html__( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
				return $error;
			} else {
				MO2f_Utility::mo2f_debug_file( 'Denied duo push notification  User_IP-' . $mo_wpns_utility->get_client_ip() );
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $posted['session_id'] ) ? sanitize_text_field( wp_unslash( $posted['session_id'] ) ) : null;
				$this->remove_current_activity( $session_id_encrypt );
			}
		}
		/**
		 * This will invoke on mobile validation
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_mobile_validation( $posted ) {
			/*check mobile validation */
			global $mo_wpns_utility;
			$nonce = $posted['miniorange_mobile_validation_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
				return $error;
			} else {
				if ( MO2F_IS_ONPREM && ( isset( $posted['tx_type'] ) && 'PN' !== $posted['tx_type'] ) ) {
					$txid   = $posted['TxidEmail'];
					$status = get_option( $txid );
					if ( ! empty( $status ) ) {
						if ( 1 !== (int) $status ) {
							return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again.' ) );
						}
					}
				}
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $posted['session_id'] ) ? sanitize_text_field( $posted['session_id'] ) : null;
				// if the php session folder has insufficient permissions, cookies to be used .
				$mo2f_login_transaction_id = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_transactionId' );
				MO2f_Utility::mo2f_debug_file( 'Transaction_id-' . $mo2f_login_transaction_id . ' User_IP-' . $mo_wpns_utility->get_client_ip() );
				$redirect_to       = isset( $posted['redirect_to'] ) ? esc_url_raw( $posted['redirect_to'] ) : null;
				$checkmobilestatus = new Two_Factor_Setup();
				$content           = $checkmobilestatus->check_mobile_status( $mo2f_login_transaction_id );
				$response          = json_decode( $content, true );
				if ( MO2F_IS_ONPREM ) {
					MO2f_Utility::mo2f_debug_file( 'MO QR-code/push notification auth logged in successfully User_IP-' . $mo_wpns_utility->get_client_ip() );
					$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
				}
				if ( JSON_ERROR_NONE === json_last_error() ) {
					if ( 'SUCCESS' === $response['status'] ) {
						MO2f_Utility::mo2f_debug_file( 'MO QR-code/push notification auth logged in successfully User_IP-' . $mo_wpns_utility->get_client_ip() );
						$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
					} else {
						MO2f_Utility::mo2f_debug_file( 'Invalid_username User_IP-' . $mo_wpns_utility->get_client_ip() );
						$this->remove_current_activity( $session_id_encrypt );
						return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again.' ) );
					}
				} else {
					MO2f_Utility::mo2f_debug_file( 'Invalid_username User_IP-' . $mo_wpns_utility->get_client_ip() );
					$this->remove_current_activity( $session_id_encrypt );
					return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again.' ) );
				}
			}
		}
		/**
		 * This will invoke mobile validation failed
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_mobile_validation_failed( $posted ) {
			/*Back to miniOrange Login Page if mobile validation failed and from back button of mobile challenge, soft token and default login*/
			$nonce = $posted['miniorange_mobile_validation_failed_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-failed-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html( 'ERROR' ) . '</strong>: ' . esc_html( 'Invalid Request.' ) );
				return $error;
			} else {
				MO2f_Utility::mo2f_debug_file( 'MO QR-code/push notification auth denied.' );
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $posted['session_id'] ) ? $posted['session_id'] : null;
				$this->remove_current_activity( $session_id_encrypt );
			}
		}
		/**
		 * Duo authenticator setup success form
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_mo2f_duo_authenticator_success_form( $posted ) {
			if ( isset( $posted['mo2f_duo_authenticator_success_nonce'] ) ) {
				$nonce = sanitize_text_field( $posted['mo2f_duo_authenticator_success_nonce'] );
				if ( ! wp_verify_nonce( $nonce, 'mo2f-duo-authenticator-success-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
					return $error;
				} else {
					global $mo2fdb_queries;
					$this->miniorange_pass2login_start_session();
					$session_id_encrypt = isset( $posted['session_id'] ) ? sanitize_text_field( wp_unslash( $posted['session_id'] ) ) : null;
					MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
					$user_id                 = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					$redirect_to             = isset( $posted['redirect_to'] ) ? esc_url_raw( $posted['redirect_to'] ) : null;
					$selected_2factor_method = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $user_id );
					$email                   = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id );
					$mo2fa_login_message     = '';

					delete_user_meta( $user_id, 'user_not_enroll' );
					delete_site_option( 'current_user_email' );
					$mo2fdb_queries->update_user_details(
						$user_id,
						array(
							'mobile_registration_status' => true,
							'mo2f_DuoAuthenticator_config_status' => true,
							'mo2f_configured_2fa_method' => $selected_2factor_method,
							'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
						)
					);
					$mo2fa_login_status = 'MO_2_FACTOR_SETUP_SUCCESS';

					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
			}
		}
		/**
		 * Duo authenticator error function
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_inline_mo2f_duo_authenticator_error( $posted ) {
			$nonce = $posted['mo2f_inline_duo_authentcator_error_nonce'];

			if ( ! wp_verify_nonce( $nonce, 'mo2f-inline-duo-authenticator-error-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', '<strong>' . esc_html( 'ERROR' ) . '</strong>: ' . esc_html( 'Invalid Request.' ) );

				return $error;
			} else {
				global  $mo2fdb_queries;
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $posted['session_id'] ) ? sanitize_text_field( wp_unslash( $posted['session_id'] ) ) : null;
				MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
				$user_id = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

				$mo2fdb_queries->update_user_details(
					$user_id,
					array(
						'mobile_registration_status' => false,
					)
				);
			}
		}
		/**
		 * It will invoke on forgot phone
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_forgotphone( $posted ) {
			$nonce = $posted['miniorange_forgotphone'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-forgotphone' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
				return $error;
			} else {
				global $mo2fdb_queries;
				$mo2fa_login_status  = isset( $posted['request_origin_method'] ) ? $posted['request_origin_method'] : null;
				$session_id_encrypt  = isset( $posted['session_id'] ) ? sanitize_text_field( $posted['session_id'] ) : null;
				$redirect_to         = isset( $posted['redirect_to'] ) ? esc_url_raw( $posted['redirect_to'] ) : null;
				$mo2fa_login_message = '';
				$this->miniorange_pass2login_start_session();
				$customer                 = new Customer_Setup();
				$user_id                  = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
				$user_email               = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id );
				$kba_configuration_status = $mo2fdb_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $user_id );
				if ( $kba_configuration_status ) {
					$mo2fa_login_status = 'MO_2_FACTOR_CHALLENGE_KBA_AND_OTP_OVER_EMAIL';
					$pass2fa_login      = new Miniorange_Password_2Factor_Login();
					$pass2fa_login->mo2f_pass2login_kba_verification( $user_id, $redirect_to, $session_id_encrypt );
				} else {
					$hidden_user_email = MO2f_Utility::mo2f_get_hidden_email( $user_email );
					$content           = json_decode( $customer->send_otp_token( $user_email, 'EMAIL', get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					if ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
						$session_cookie_variables = array( 'mo2f-login-qrCode', 'mo2f_transactionId' );
						MO2f_Utility::unset_session_variables( $session_cookie_variables );
						MO2f_Utility::unset_cookie_variables( $session_cookie_variables );
						MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
						// if the php session folder has insufficient permissions, cookies to be used .
						MO2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_login_message', 'A one time passcode has been sent to <b>' . $hidden_user_email . '</b>. Please enter the OTP to verify your identity.' );
						MO2f_Utility::set_user_values( $session_id_encrypt, 'mo2f_transactionId', $content['txId'] );
						$this->mo2f_transactionid = $content['txId'];
						$mo2fa_login_message      = 'A one time passcode has been sent to <b>' . $hidden_user_email . '</b>. Please enter the OTP to verify your identity.';
						$mo2fa_login_status       = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
					} else {
						$mo2fa_login_message = 'Error occurred while sending OTP over email. Please try again.';
					}
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
				}
				$pass2fa_login = new Miniorange_Password_2Factor_Login();
				$pass2fa_login->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
		/**
		 * It will check the soft token
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_softtoken( $posted ) {
			/*Click on the link of phone is offline */
			$nonce = $posted['miniorange_softtoken'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-softtoken' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
				return $error;
			} else {
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt       = isset( $posted['session_id'] ) ? $posted['session_id'] : null;
				$session_cookie_variables = array( 'mo2f-login-qrCode', 'mo2f_transactionId' );
				MO2f_Utility::unset_session_variables( $session_cookie_variables );
				MO2f_Utility::unset_cookie_variables( $session_cookie_variables );
				MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );
				$redirect_to         = isset( $posted['redirect_to'] ) ? esc_url_raw( $posted['redirect_to'] ) : null;
				$mo2fa_login_message = 'Please enter the one time passcode shown in the miniOrange<b> Authenticator</b> app.';
				$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			}
		}
		/**
		 * Checking miniOrange soft token
		 *
		 * @param string $posted It will carry the post data .
		 * @return string
		 */
		public function check_miniorange_soft_token( $posted ) {
			/*Validate Soft Token,OTP over SMS,OTP over EMAIL,Phone verification */
			global $mo_wpns_utility;
			$nonce = isset( $_POST['miniorange_soft_token_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['miniorange_soft_token_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-soft-token-nonce' ) ) {
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
				return $error;
			} else {
				$this->miniorange_pass2login_start_session();
				$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
				$mo2fa_login_status = isset( $_POST['request_origin_method'] ) ? sanitize_text_field( wp_unslash( $_POST['request_origin_method'] ) ) : null;
				$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
				$softtoken          = '';
				$user_id            = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
				$attempts           = get_option( 'mo2f_attempts_before_redirect', 3 );
				if ( MO2f_utility::mo2f_check_empty_or_null( isset( $_POST['mo2fa_softtoken'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2fa_softtoken'] ) ) : '' ) ) {
					if ( $attempts > 1 || 'disabled' === $attempts ) {
						update_option( 'mo2f_attempts_before_redirect', $attempts - 1 );
						$mo2fa_login_message = 'Please enter OTP to proceed.';
						MO2f_Utility::mo2f_debug_file( 'Please enter OTP to proceed User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					} else {
						$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
						$this->remove_current_activity( $session_id_encrypt );
						MO2f_Utility::mo2f_debug_file( 'Number of attempts exceeded User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
						return new WP_Error( 'limit_exceeded', '<strong>ERROR</strong>: Number of attempts exceeded.' );
					}
				} else {
					$softtoken = isset( $_POST['mo2fa_softtoken'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2fa_softtoken'] ) ) : '';
					if ( ! MO2f_utility::mo2f_check_number_length( $softtoken ) ) {
						if ( $attempts > 1 || 'disabled' === $attempts ) {
							update_option( 'mo2f_attempts_before_redirect', $attempts - 1 );
							$mo2fa_login_message = 'Invalid OTP. Only digits within range 4-8 are allowed. Please try again.';
							MO2f_Utility::mo2f_debug_file( 'Invalid OTP. Only digits within range 4-8 are allowed User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
						} else {
							$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
							$this->remove_current_activity( $session_id_encrypt );
							update_option( 'mo2f_attempts_before_redirect', 3 );
							if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
								$data = array( 'reload' => 'reload' );
								wp_send_json_success( $data );
							} else {
								MO2f_Utility::mo2f_debug_file( 'Number of attempts exceeded  User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
								return new WP_Error( 'limit_exceeded', '<strong>ERROR</strong>: Number of attempts exceeded.' );
							}
						}
					}
				}

				global $mo2fdb_queries;
				$user_email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id );
				if ( isset( $user_id ) ) {
					$customer     = new Customer_Setup();
					$content      = '';
					$current_user = get_userdata( $user_id );
					// if the php session folder has insufficient permissions, cookies to be used .
					$mo2f_login_transaction_id = isset( $_POST['mo2fa_transaction_id'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2fa_transaction_id'] ) ) : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_transactionId' );
					MO2f_Utility::mo2f_debug_file( 'Transaction_id-' . $mo2f_login_transaction_id . ' User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id . ' Email-' . $user_email );
					if ( isset( $mo2fa_login_status ) && 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' === $mo2fa_login_status ) {
						$content = json_decode( $customer->validate_otp_token( 'EMAIL', null, $mo2f_login_transaction_id, $softtoken, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ), $current_user ), true );
					} elseif ( isset( $mo2fa_login_status ) && 'MO_2_FACTOR_CHALLENGE_OTP_OVER_WHATSAPP' === $mo2fa_login_status ) {
						$otp_token     = get_user_meta( $current_user->ID, 'mo2f_otp_token_wa', true );
						$time          = get_user_meta( $current_user->ID, 'mo2f_whatsapp_time', true );
						$accepted_time = time() - 600;
						$time          = (int) $time;
						global $mo2fdb_queries;

						if ( $otp_token === $softtoken ) {
							if ( $accepted_time < $time ) {
								update_option( 'mo2f_attempts_before_redirect', 3 );
								$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
							} else {
								$this->remove_current_activity( $session_id_encrypt );

								return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: OTP has been Expired please reinitiate another transaction.' ) );
							}
						} else {
							update_option( 'mo2f_attempts_before_redirect', $attempts - 1 );
							$message = 'Invalid OTP please enter again.';
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $message, $redirect_to, null, $session_id_encrypt );
						}
					} elseif ( isset( $mo2fa_login_status ) && 'MO_2_FACTOR_CHALLENGE_OTP_OVER_TELEGRAM' === $mo2fa_login_status ) {
						$otp_token     = get_user_meta( $current_user->ID, 'mo2f_otp_token', true );
						$time          = get_user_meta( $current_user->ID, 'mo2f_telegram_time', true );
						$accepted_time = time() - 300;
						$time          = (int) $time;
						global $mo2fdb_queries;

						if ( $otp_token === $softtoken ) {
							if ( $accepted_time < $time ) {
								update_option( 'mo2f_attempts_before_redirect', 3 );
								MO2f_Utility::mo2f_debug_file( 'OTP over Telegram - Logged in successfully User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id . ' Email-' . $user_email );
								$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
							} else {
								$this->remove_current_activity( $session_id_encrypt );
								MO2f_Utility::mo2f_debug_file( 'OTP has been Expired please reinitiate another transaction. User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id . ' Email-' . $user_email );
								return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: OTP has been Expired please reinitiate another transaction.' ) );
							}
						} else {
							if ( $attempts <= 1 ) {
								$this->remove_current_activity( $session_id_encrypt );
								update_option( 'mo2f_attempts_before_redirect', 3 );
								return new WP_Error( 'attempts failed try again ', __( '<strong>ERROR</strong>: maximum attempts.' ) );
							}
							MO2f_Utility::mo2f_debug_file( 'OTP over Telegram - Invalid OTP User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id . ' Email-' . $user_email );
							update_option( 'mo2f_attempts_before_redirect', $attempts - 1 );
							$message = 'Invalid OTP please enter again.';
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $message, $redirect_to, null, $session_id_encrypt );
						}
					} elseif ( isset( $mo2fa_login_status ) && 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS' === $mo2fa_login_status ) {
						$content = json_decode( $customer->validate_otp_token( 'SMS', null, $mo2f_login_transaction_id, $softtoken, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					} elseif ( isset( $mo2fa_login_status ) && 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION' === $mo2fa_login_status ) {
						$content = json_decode( $customer->validate_otp_token( 'PHONE VERIFICATION', null, $mo2f_login_transaction_id, $softtoken, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					} elseif ( isset( $mo2fa_login_status ) && 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' === $mo2fa_login_status ) {
						$content = json_decode( $customer->validate_otp_token( 'SOFT TOKEN', $user_email, null, $softtoken, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					} elseif ( isset( $mo2fa_login_status ) && 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION' === $mo2fa_login_status ) {
						$content = json_decode( $customer->validate_otp_token( 'GOOGLE AUTHENTICATOR', $user_email, null, $softtoken, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
					} else {
						$this->remove_current_activity( $session_id_encrypt );
						return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Invalid Request. Please try again.' ) );
					}
					if ( 0 === strcasecmp( $content['status'], 'SUCCESS' ) ) {
						update_option( 'mo2f_attempts_before_redirect', 3 );
						if ( 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' === $mo2fa_login_status ) {
							$mo2fdb_queries->update_user_details(
								$user_id,
								array(
									'mo2f_configured_2fa_method' => 'OTP Over Email',
									'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
									'mo2f_OTPOverEmail_config_status' => 1,
								)
							);
							$enduser = new Two_Factor_Setup();

							$enduser->mo2f_update_userinfo( $user_email, 'OTP Over Email', null, null, null );
						}
						MO2f_Utility::mo2f_debug_file( $mo2fa_login_status . ' Logged in successfully User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id . ' Email-' . $user_email );
						$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
					} else {
						if ( $attempts > 1 || 'disabled' === $attempts ) {
							MO2f_Utility::mo2f_debug_file( $mo2fa_login_status . ' Enter wrong OTP User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id . ' Email-' . $user_email );
							update_option( 'mo2f_attempts_before_redirect', $attempts - 1 );
							$message = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' === $mo2fa_login_status ? 'You have entered an invalid OTP.<br>Please click on <b>Sync Time</b> in the miniOrange Authenticator app to sync your phone time with the miniOrange servers and try again.' : 'Invalid OTP. Please try again.';
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $message, $redirect_to, null, $session_id_encrypt );
						} else {
							$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
							$this->remove_current_activity( $session_id_encrypt );
							update_option( 'mo2f_attempts_before_redirect', 3 );
							if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
								$data = array( 'reload' => 'reload' );
								wp_send_json_success( $data );
							} else {
								MO2f_Utility::mo2f_debug_file( 'Number of attempts exceeded User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id . ' Email-' . $user_email );
								return new WP_Error( 'limit_exceeded', '<strong>ERROR</strong>: Number of attempts exceeded.' );
							}
						}
					}
				} else {
					$this->remove_current_activity( $session_id_encrypt );
					MO2f_Utility::mo2f_debug_file( 'User id not found User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id . ' Email-' . $user_email );
					return new WP_Error( 'invalid_username', __( '<strong>ERROR</strong>: Please try again..' ) );
				}
			}
		}
		/**
		 * It will invoke on checking weather inline registration is skip or not
		 *
		 * @param string $posted It will carry the post data .
		 * @return void
		 */
		public function check_miniorange_inline_skip_registration( $posted ) {
			$error = new WP_Error();
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
		}
		/**
		 * Pass2 login redirect function
		 *
		 * @return string
		 */
		public function miniorange_pass2login_redirect() {
			do_action( 'mo2f_network_init' );
			global $mo2fdb_queries;
			if ( ! MoWpnsUtility::get_mo2f_db_option( 'mo2f_login_option', 'get_option' ) ) {
				if ( isset( $_POST['miniorange_login_nonce'] ) ) {
					$nonce      = sanitize_text_field( wp_unslash( $_POST['miniorange_login_nonce'] ) );
					$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;

					if ( is_null( $session_id ) ) {
						$session_id = $this->create_session();
					}
					if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' ) ) {
						$this->remove_current_activity( $session_id );
						$error = new WP_Error();
						$error->add( 'empty_username', '<strong>' . esc_html( 'ERROR' ) . '</strong>: ' . esc_html( 'Invalid Request.' ) );
						return $error;
					} else {
						$this->miniorange_pass2login_start_session();
						$mobile_login = new Miniorange_Mobile_Login();
						// validation and sanitization .
						$username = isset( $_POST['mo2fa_username'] ) ? sanitize_user( wp_unslash( $_POST['mo2fa_username'] ) ) : '';
						if ( MO2f_Utility::mo2f_check_empty_or_null( $username ) ) {
							MO2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Please enter username to proceed' );
							$mobile_login->mo2f_auth_show_error_message();
							return;
						} else {
							$username = sanitize_user( wp_unslash( $_POST['mo2fa_username'] ) );
						}
						if ( username_exists( $username ) ) {    /*if username exists in wp site */
							$user        = new WP_User( $username );
							$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : null;

							MO2f_Utility::mo2f_set_transient( $session_id, 'mo2f_current_user_id', $user->ID, 600 );
							MO2f_Utility::mo2f_set_transient( $session_id, 'mo2f_1stfactor_status', 'VALIDATE_SUCCESS', 600 );

							$this->fstfactor                     = 'VALIDATE_SUCCESS';
							$current_roles                       = miniorange_get_user_role( $user );
							$mo2f_configured_2fa_method          = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $user->ID );
							$email                               = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user->ID );
							$mo_2factor_user_registration_status = $mo2fdb_queries->get_user_detail( 'mo_2factor_user_registration_status', $user->ID );
							$kba_configuration_status            = $mo2fdb_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $user->ID );

							if ( MO2F_IS_ONPREM ) {
								$mo_2factor_user_registration_status = 'MO_2_FACTOR_PLUGIN_SETTINGS';
							}
							if ( $mo2f_configured_2fa_method ) {
								if ( $email && 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status || ( MO2F_IS_ONPREM && 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status ) ) {
									if ( MO2f_Utility::check_if_request_is_from_mobile_device( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' ) && $kba_configuration_status ) {
										$this->mo2f_pass2login_kba_verification( $user->ID, $redirect_to, $session_id );
									} else {
										$mo2f_second_factor = '';

										if ( MO2F_IS_ONPREM ) {
											global $mo2fdb_queries;
											$mo2f_second_factor = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $user->ID );
											if ( 'Security Questions' === $mo2f_second_factor ) {
												$mo2f_second_factor = 'KBA';
											} elseif ( 'Google Authenticator' === $mo2f_second_factor ) {
												$mo2f_second_factor = 'GOOGLE AUTHENTICATOR';
											} elseif ( 'Email Verification' === $mo2f_second_factor ) {
												$mo2f_second_factor = 'Email Verification';
											} elseif ( 'OTP Over SMS' === $mo2f_second_factor ) {
												$mo2f_second_factor = 'SMS';
											} elseif ( 'OTP Over Email' === $mo2f_second_factor ) {
												$mo2f_second_factor = 'EMAIL';
											} elseif ( 'miniOrange Soft Token' === $mo2f_second_factor ) {
												$mo2f_second_factor = 'SOFT TOKEN';
											} elseif ( 'miniOrange Push Notification' === $mo2f_second_factor ) {
												$mo2f_second_factor = 'PUSH NOTIFICATIONS';
											} elseif ( 'miniOrange QR Code Authentication' === $mo2f_second_factor ) {
												$mo2f_second_factor = 'MOBILE AUTHENTICATION';
											}
										} else {
											$mo2f_second_factor = mo2f_get_user_2ndfactor( $user );
										}
										if ( 'MOBILE AUTHENTICATION' === $mo2f_second_factor ) {
											$this->mo2f_pass2login_mobile_verification( $user, $redirect_to, $session_id );
										} elseif ( 'PUSH NOTIFICATIONS' === $mo2f_second_factor || 'OUT OF BAND EMAIL' === $mo2f_second_factor ) {
											$this->mo2f_pass2login_push_oobemail_verification( $user, $mo2f_second_factor, $redirect_to, $session_id );
										} elseif ( 'Email Verification' === $mo2f_second_factor ) {
											$this->mo2f_pass2login_push_oobemail_verification( $user, $mo2f_second_factor, $redirect_to, $session_id );
										} elseif ( 'SOFT TOKEN' === $mo2f_second_factor || 'SMS' === $mo2f_second_factor || 'PHONE VERIFICATION' === $mo2f_second_factor || 'GOOGLE AUTHENTICATOR' === $mo2f_second_factor || 'OTP Over Telegram' === $mo2f_second_factor || 'EMAIL' === $mo2f_second_factor || 'OTP Over Email' === $mo2f_second_factor ) {
											$this->mo2f_pass2login_otp_verification( $user, $mo2f_second_factor, $redirect_to, $session_id );
										} elseif ( 'KBA' === $mo2f_second_factor ) {
											$this->mo2f_pass2login_kba_verification( $user->ID, $redirect_to, $session_id );
										} else {
											$this->remove_current_activity( $session_id );
											MO2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Please try again or contact your admin.' );
											$mobile_login->mo2f_auth_show_success_message();
										}
									}
								} else {
									MO2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Please login into your account using password.' );
									$mobile_login->mo2f_auth_show_success_message( 'Please login into your account using password.' );
									update_user_meta( $user->ID, 'userMessage', 'Please login into your account using password.' );
									$mobile_login->mo2f_redirectto_wp_login();
								}
							} else {
								MO2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Please login into your account using password.' );
								$mobile_login->mo2f_auth_show_success_message( 'Please login into your account using password.' );
								update_user_meta( $user->ID, 'userMessage', 'Please login into your account using password.' );
								$mobile_login->mo2f_redirectto_wp_login();
							}
						} else {
							$mobile_login->remove_current_activity( $session_id );
							MO2f_Utility::set_user_values( $session_id, 'mo2f_login_message', 'Invalid Username.' );
							$mobile_login->mo2f_auth_show_error_message( 'Invalid Username.' );
						}
					}
				}
			}
			if ( isset( $_GET['reconfigureMethod'] ) && is_user_logged_in() ) {
				$useridget = get_current_user_id();
				$txidget   = isset( $_GET['transactionId'] ) ? sanitize_text_field( wp_unslash( $_GET['transactionId'] ) ) : '';
				$methodget = isset( $_GET['reconfigureMethod'] ) ? sanitize_text_field( wp_unslash( $_GET['reconfigureMethod'] ) ) : '';
				if ( get_site_option( $txidget ) === $useridget && ctype_xdigit( $txidget ) && ctype_xdigit( $methodget ) ) {
					$method = get_site_option( $methodget );
					$mo2fdb_queries->update_user_details(
						$useridget,
						array(
							'mo_2factor_user_registration_status' => 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS',
							'mo2f_configured_2fa_method' => $method,
						)
					);
					$is_authy_configured = $mo2fdb_queries->get_user_detail( 'mo2f_AuthyAuthenticator_config_status', $useridget );
					if ( 'Google Authenticator' === $method || $is_authy_configured ) {
						update_user_meta( $useridget, 'mo2fa_set_Authy_inline', true );
					}
					delete_site_option( $txidget );
				} else {
					$head = 'You are not authorized to perform this action';
					$body = 'Please contact to your admin';
					$this->display_email_verification( $head, $body, 'red' );
					exit();
				}
			}
			if ( isset( $_GET['Txid'] ) && isset( $_GET['accessToken'] ) ) {
				$useridget     = isset( $_GET['userID'] ) ? sanitize_text_field( wp_unslash( $_GET['userID'] ) ) : '';
				$txidget       = isset( $_GET['Txid'] ) ? sanitize_text_field( wp_unslash( $_GET['Txid'] ) ) : '';
				$otp_token     = get_site_option( $useridget );
				$txidstatus    = get_site_option( $txidget );
				$useridd       = $useridget . 'D';
				$otp_tokend    = get_site_option( $useridd );
				$mo2f_dir_name = dirname( __FILE__ );
				$mo2f_dir_name = explode( 'wp-content', $mo2f_dir_name );
				$mo2f_dir_name = explode( 'handler', $mo2f_dir_name[1] );

				$head  = 'You are not authorized to perform this action';
				$body  = 'Please contact to your admin';
				$color = 'red';
				if ( 3 === (int) $txidstatus ) {
					$time                   = 'time' . $txidget;
					$current_time_in_millis = round( microtime( true ) * 1000 );
					$generatedtimeinmillis  = get_site_option( $time );
					$difference             = ( $current_time_in_millis - $generatedtimeinmillis ) / 1000;
					if ( $difference <= 300 ) {
						$accesstokenget = isset( $_GET['accessToken'] ) ? sanitize_text_field( wp_unslash( $_GET['accessToken'] ) ) : '';
						if ( $accesstokenget === $otp_token ) {
							update_site_option( $txidget, 1 );
							$body  = 'Transaction has been successfully validated. Please continue with the transaction.';
							$head  = 'TRANSACTION SUCCESSFUL';
							$color = 'green';
						} elseif ( $accesstokenget === $otp_tokend ) {
							update_site_option( $txidget, 0 );
							$body = 'Transaction has been Canceled. Please Try Again.';
							$head = 'TRANSACTION DENIED';
						}
					}
					delete_site_option( $useridget );
					delete_site_option( $useridd );
					delete_site_option( $time );
				}

				$this->display_email_verification( $head, $body, $color );
				exit;
			} elseif ( isset( $_POST['emailInlineCloud'] ) ) {
				$nonce = isset( $_POST['miniorange_emailChange_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['miniorange_emailChange_nonce'] ) ) : '';
				if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-email-change-nonce' ) ) {
					$error = new WP_Error();
					$error->add( 'empty_username', '<strong>' . esc_html( 'ERROR' ) . '</strong>: ' . esc_html( 'Invalid Request.' ) );
					return $error;
				} else {
					$email              = sanitize_text_field( wp_unslash( $_POST['emailInlineCloud'] ) );
					$current_user_id    = isset( $_POST['current_user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['current_user_id'] ) ) : '';
					$session_id_encrypt = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
					$redirect_to        = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null;
					if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						global  $mo2fdb_queries;
						$mo2fdb_queries->update_user_details(
							$current_user_id,
							array(
								'mo2f_user_email' => $email,
								'mo2f_configured_2fa_method' => '',
							)
						);
						prompt_user_to_select_2factor_mthod_inline( $current_user_id, 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR', '', $redirect_to, $session_id_encrypt, null );
					}
				}
			} elseif ( isset( $_POST['txid'] ) ) {
				$txidpost = sanitize_text_field( wp_unslash( $_POST['txid'] ) );
				$status   = get_site_option( $txidpost );
				update_option( 'optionVal1', $status ); // ??
				if ( 1 === $status || 0 === $status ) {
					delete_site_option( $txidpost );
				}
				echo esc_html( $status );
				exit();
			} else {
				$value = isset( $_POST['option'] ) ? sanitize_text_field( wp_unslash( $_POST['option'] ) ) : false;

				switch ( $value ) {
					case 'miniorange_mfactor_method':
						$session_id     = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
						$current_userid = MO2f_Utility::mo2f_get_transient( $session_id, 'mo2f_current_user_id' );
						$currentuser    = get_user_by( 'id', $current_userid );
						$this->mo2fa_select_method( $currentuser, isset( $_POST['mo2f_selected_mfactor_method'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_selected_mfactor_method'] ) ) : '', null, $session_id, esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ), null );
						break;
					case 'miniorange_forgotphone':
						$this->check_miniorange_challenge_forgotphone( $_POST );
						break;

					case 'miniorange2f_back_to_inline_registration':
						$this->miniorange2f_back_to_inline_registration( $_POST );
						exit;

					case 'miniorange_alternate_login_kba':
						$this->check_miniorange_alternate_login_kba( $_POST );
						break;

					case 'miniorange_kba_validate':
						$this->check_kba_validation( $_POST );

						break;

					case 'miniorange_mobile_validation':
						$this->check_miniorange_mobile_validation( $_POST );
						break;

					case 'miniorange_duo_push_validation':
						$this->check_miniorange_duo_push_validation( $_POST );
						break;

					case 'mo2f_inline_duo_authenticator_success_form':
						$this->check_mo2f_duo_authenticator_success_form( $_POST );
						break;

					case 'mo2f_inline_duo_authenticator_error':
						$this->check_inline_mo2f_duo_authenticator_error( $_POST );
						break;

					case 'miniorange_mobile_validation_failed':
						$this->check_miniorange_mobile_validation_failed( $_POST );
						break;

					case 'miniorange_duo_push_validation_failed':
						$this->check_miniorange_duo_push_validation_failed( $_POST );
						break;

					case 'miniorange_softtoken':
						$this->check_miniorange_softtoken( $_POST );

						break;

					case 'miniorange_soft_token':
						$this->check_miniorange_soft_token( $_POST );
						break;

					case 'miniorange_inline_skip_registration':
						$this->check_miniorange_inline_skip_registration( $_POST );
						break;

					case 'miniorange_inline_save_2factor_method':
						$this->save_inline_2fa_method();
						break;

					case 'mo2f_skip_2fa_setup':
						$this->mo2f_skip_2fa_setup();
						break;

					case 'miniorange_back_inline':
						$this->back_to_select_2fa();
						break;

					case 'miniorange_inline_ga_validate':
						$this->inline_validate_and_set_ga();
						break;

					case 'miniorange_inline_show_mobile_config':
						$this->inline_mobile_configure();
						break;

					case 'miniorange_inline_complete_mobile':
						$this->mo2f_inline_validate_mobile_authentication();
						break;
					case 'miniorange_inline_duo_auth_mobile_complete':
						$this->mo2f_inline_validate_duo_authentication();
						break;
					case 'duo_mobile_send_push_notification_for_inline_form':
						$this->mo2f_duo_mobile_send_push_notification_for_inline_form();
						break;
					case 'mo2f_inline_kba_option':
						$this->mo2f_inline_validate_kba();
						break;

					case 'miniorange_inline_complete_otp_over_sms':
						$this->mo2f_inline_send_otp();
						break;

					case 'miniorange_inline_complete_otp':
						$this->mo2f_inline_validate_otp();
						break;

					case 'miniorange_inline_login':
						$this->mo2f_inline_login();
						break;
					case 'miniorange_inline_register':
						$this->mo2f_inline_register();
						break;
					case 'mo2f_users_backup1':
						$this->mo2f_download_backup_codes_inline();
						break;
					case 'mo2f_goto_wp_dashboard':
						$this->mo2f_goto_wp_dashboard();
						break;
					case 'miniorange_backup_nonce':
						$this->mo2f_use_backup_codes( $_POST );
						break;
					case 'miniorange_validate_backup_nonce':
						$this->check_backup_codes_validation( $_POST );
						break;
					case 'miniorange_create_backup_codes':
						$this->mo2f_create_backup_codes();
						break;
					default:
						$error = new WP_Error();
						$error->add( 'empty_username', __( '<strong>ERROR</strong>: Invalid Request.' ) );
						return $error;
				}
			}
		}
		/**
		 * It will invoke when you denied message
		 *
		 * @param string $message It will carry the message .
		 * @return string
		 */
		public function denied_message( $message ) {
			if ( empty( $message ) && get_option( 'denied_message' ) ) {
				delete_option( 'denied_message' );
			} else {
				return $message;
			}
		}
		/**
		 * Removing the current activity
		 *
		 * @param string $session_id It will carry the session id .
		 * @return void
		 */
		public function remove_current_activity( $session_id ) {
			global $mo2fdb_queries;
			$session_variables = array(
				'mo2f_current_user_id',
				'mo2f_1stfactor_status',
				'mo_2factor_login_status',
				'mo2f-login-qrCode',
				'mo2f_transactionId',
				'mo2f_login_message',
				'mo_2_factor_kba_questions',
				'mo2f_show_qr_code',
				'mo2f_google_auth',
				'mo2f_authy_keys',
			);

			$cookie_variables = array(
				'mo2f_current_user_id',
				'mo2f_1stfactor_status',
				'mo_2factor_login_status',
				'mo2f-login-qrCode',
				'mo2f_transactionId',
				'mo2f_login_message',
				'kba_question1',
				'kba_question2',
				'mo2f_show_qr_code',
				'mo2f_google_auth',
				'mo2f_authy_keys',
			);

			$temp_table_variables = array(
				'session_id',
				'mo2f_current_user_id',
				'mo2f_login_message',
				'mo2f_1stfactor_status',
				'mo2f_transactionId',
				'mo_2_factor_kba_questions',
				'ts_created',
			);

			MO2f_Utility::unset_session_variables( $session_variables );
			MO2f_Utility::unset_cookie_variables( $cookie_variables );
			$key             = get_option( 'mo2f_encryption_key' );
			$session_id      = MO2f_Utility::decrypt_data( $session_id, $key );
			$session_id_hash = md5( $session_id );
			$mo2fdb_queries->save_user_login_details(
				$session_id_hash,
				array(

					'mo2f_current_user_id'      => '',
					'mo2f_login_message'        => '',
					'mo2f_1stfactor_status'     => '',
					'mo2f_transactionId'        => '',
					'mo_2_factor_kba_questions' => '',
					'ts_created'                => '',
				)
			);
		}
		/**
		 * Mo2f ultimate member function
		 *
		 * @return void
		 */
		public function mo2f_ultimate_member_custom_login() {
			echo '<div id="mo2f_um_validate_otp" class="um-field um-field-password  um-field-user_password um-field-password um-field-type_password" data-key="user_password"><div class="um-field-label"><label for="mo2f_um_validate_otp">Two factor code*</label><div class="um-clear"></div></div><div class="um-field-area"><input class="um-form-field valid " type="text" name="mo2f_validate_otp_token" id="mo2f_um_validate_otp" value="" placeholder="" data-validate="" data-key="user_password">

				</div></div>';
		}
		/**
		 * It will use to start the session
		 *
		 * @return void
		 */
		public function miniorange_pass2login_start_session() {
			if ( ! session_id() || '' === session_status() || ! isset( $_SESSION ) ) {
				$session_path = ini_get( 'session.save_path' );
				if ( is_writable( $session_path ) && is_readable( $session_path ) ) {
					if ( PHP_SESSION_DISABLED !== session_status() ) {
						session_start();
					}
				}
			}
		}
		/**
		 * It will handle kba validation
		 *
		 * @param string $user_id It will carry the user id .
		 * @param string $redirect_to It will carry the redirect url .
		 * @param string $session_id It will carry the session id .
		 * @return string
		 */
		public function mo2f_pass2login_kba_verification( $user_id, $redirect_to, $session_id ) {
			global $mo2fdb_queries,$loginuserid;
			$loginuserid = $user_id;
			$user_email  = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id );
			if ( is_null( $session_id ) ) {
				$session_id = $this->create_session();
			}
			if ( MO2F_IS_ONPREM ) {
				$question_answers    = get_user_meta( $user_id, 'mo2f_kba_challenge', true );
				$challenge_questions = array_keys( $question_answers );
				$random_keys         = array_rand( $challenge_questions, 2 );
				$challenge_ques1     = $challenge_questions[ $random_keys[0] ];
				$challenge_ques2     = $challenge_questions[ $random_keys[1] ];
				$questions[0]        = array( 'question' => addslashes( $challenge_ques1 ) );
				$questions[1]        = array( 'question' => addslashes( $challenge_ques2 ) );
				update_user_meta( $user_id, 'kba_questions_user', $questions );
				$mo2fa_login_message = 'Please answer the following questions:';
				$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
				$mo2f_kbaquestions   = $questions;
				MO2f_Utility::mo2f_set_transient( $session_id, 'mo_2_factor_kba_questions', $questions );
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id, $this->mo2f_kbaquestions );
			} else {
				$challengekba = new Customer_Setup();
				$content      = $challengekba->send_otp_token( $user_email, 'KBA', get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) );
				$response     = json_decode( $content, true );
				if ( JSON_ERROR_NONE === json_last_error() ) { /* Generate Qr code */
					if ( 'SUCCESS' === $response['status'] ) {
						MO2f_Utility::set_user_values( $session_id, 'mo2f_transactionId', $response['txid'] );
						$this->mo2f_transactionid = $response['txid'];
						$questions                = array();
						$questions[0]             = $response['questions'][0];
						$questions[1]             = $response['questions'][1];
						MO2f_Utility::mo2f_set_transient( $session_id, 'mo_2_factor_kba_questions', $questions );
						$this->mo2f_kbaquestions = $questions;
						$mo2fa_login_message     = 'Please answer the following questions:';
						$mo2fa_login_status      = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id, $this->mo2f_kbaquestions );
					} elseif ( 'ERROR' === $response['status'] ) {
						$this->remove_current_activity( $session_id );
						$error = new WP_Error();
						$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please Try again.' ) );

						return $error;
					}
				} else {
					$this->remove_current_activity( $session_id );
					$error = new WP_Error();
					$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please Try again.' ) );

					return $error;
				}
			}
		}
		/**
		 * It will pass 2fa on login flow
		 *
		 * @param string  $mo2fa_login_status It will carry the login status message .
		 * @param string  $mo2fa_login_message It will carry the login message .
		 * @param string  $redirect_to It will carry the redirect url .
		 * @param string  $qr_code It will carry the qr code .
		 * @param string  $session_id_encrypt It will carry the session id .
		 * @param string  $show_back_button It will help to show button .
		 * @param boolean $mo2fa_transaction_id It will carry the transaction id .
		 * @return void
		 */
		public function miniorange_pass2login_form_fields( $mo2fa_login_status = null, $mo2fa_login_message = null, $redirect_to = null, $qr_code = null, $session_id_encrypt = null, $show_back_button = null, $mo2fa_transaction_id = false ) {
			$login_status  = $mo2fa_login_status;
			$login_message = $mo2fa_login_message;
			switch ( $login_status ) {
				case 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION':
					$transactionid = $this->mo2f_transactionid ? $this->mo2f_transactionid : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_transactionId' );
					mo2f_get_qrcode_authentication_prompt( $login_status, $login_message, $redirect_to, $qr_code, $session_id_encrypt, $transactionid );
					break;
				case 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					mo2f_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
					break;
				case 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					mo2f_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id, $show_back_button, $mo2fa_transaction_id );
					break;
				case 'MO_2_FACTOR_CHALLENGE_OTP_OVER_TELEGRAM':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					mo2f_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
					break;
				case 'MO_2_FACTOR_CHALLENGE_OTP_OVER_WHATSAPP':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					mo2f_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
					break;
				case 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					mo2f_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
					break;
				case 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					mo2f_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
					break;
				case 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					mo2f_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id );
					break;
				case 'MO_2_FACTOR_CHALLENGE_DUO_PUSH_NOTIFICATIONS':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
					mo2f_get_duo_push_authentication_prompt(
						$login_status,
						$login_message,
						$redirect_to,
						$session_id_encrypt,
						$user_id
					);
					break;

				case 'MO_2_FACTOR_CHALLENGE_KBA_AND_OTP_OVER_EMAIL':
					mo2f_get_forgotphone_form( $login_status, $login_message, $redirect_to, $session_id_encrypt );
					break;

				case 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS':
					$transactionid = $this->mo2f_transactionid ? $this->mo2f_transactionid : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_transactionId' );
					$user_id       = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					mo2f_get_push_notification_oobemail_prompt( $user_id, $login_status, $login_message, $redirect_to, $session_id_encrypt, $transactionid );
					break;

				case 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL':
					$transactionid = $this->mo2f_transactionid ? $this->mo2f_transactionid : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_transactionId' );
					$user_id       = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					mo2f_get_push_notification_oobemail_prompt( $user_id, $login_status, $login_message, $redirect_to, $session_id_encrypt, $transactionid );
					break;

				case 'MO_2_FACTOR_RECONFIG_GOOGLE':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$this->mo2f_redirect_shortcode_addon( $user_id, $login_status, $login_message, 'reconfigure_google' );
					break;

				case 'MO_2_FACTOR_RECONFIG_KBA':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$this->mo2f_redirect_shortcode_addon( $user_id, $login_status, $login_message, 'reconfigure_kba' );
					break;

				case 'MO_2_FACTOR_SETUP_SUCCESS':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					$this->mo2f_inline_setup_success( $user_id, $redirect_to, $session_id_encrypt );
					break;

				case 'MO_2_FACTOR_GENERATE_BACKUP_CODES':
					mo2f_backup_codes_generate( $redirect_to, $session_id_encrypt );
					exit;

				case 'MO_2_FACTOR_CHALLENGE_BACKUP':
					mo2f_backup_form( $login_status, $login_message, $redirect_to, $session_id_encrypt );
					exit;

				case 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION':
					if ( MO2F_IS_ONPREM ) {
						$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

						$ques = get_user_meta( $user_id, 'kba_questions_user' );
						mo2f_get_kba_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $ques[0] );
					} else {
						$kbaquestions = $this->mo2f_kbaquestions ? $this->mo2f_kbaquestions : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo_2_factor_kba_questions' );
						mo2f_get_kba_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $kbaquestions );
					}
					break;

				case 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS':
					$user_id = $this->mo2f_user_id ? $this->mo2f_user_id : MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

					prompt_user_to_select_2factor_mthod_inline( $user_id, $login_status, $login_message, $redirect_to, $session_id_encrypt, $qr_code );
					break;

				default:
					$this->mo_2_factor_pass2login_show_wp_login_form();
					break;
			}
			exit();
		}
		/**
		 * It will check the mobile status
		 *
		 * @param string $login_status It will store the login status message .
		 * @return boolean
		 */
		public function miniorange_pass2login_check_mobile_status( $login_status ) {
			// mobile authentication .
			if ( 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION' === $login_status ) {
				return true;
			}

			return false;
		}
		/**
		 * Pass2login otp check status
		 *
		 * @param string  $login_status It will store the login status message .
		 * @param boolean $sso It will store the softtoken message .
		 * @return boolean
		 */
		public function miniorange_pass2login_check_otp_status( $login_status, $sso = false ) {
			if ( 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' === $login_status || 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' === $login_status || 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS' === $login_status || 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION' === $login_status || 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION' === $login_status ) {
				return true;
			}

			return false;
		}
		/**
		 * Forgot phone status
		 *
		 * @param string $login_status It will store the login status message .
		 * @return boolean
		 */
		public function miniorange_pass2login_check_forgotphone_status( $login_status ) {
			// after clicking on forgotphone link when both kba and email are configured .
			if ( 'MO_2_FACTOR_CHALLENGE_KBA_AND_OTP_OVER_EMAIL' === $login_status ) {
				return true;
			}

			return false;
		}
		/**
		 * Email verification method
		 *
		 * @param string $login_status It will store the login status message .
		 * @return boolean
		 */
		public function miniorange_pass2login_check_push_oobemail_status( $login_status ) {
			// for push and out of and email .
			if ( 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' === $login_status || 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL' === $login_status ) {
				return true;
			}

			return false;
		}
		/**
		 * Rconfig Google method
		 *
		 * @param string $login_status It will store the login status message .
		 * @return boolean
		 */
		public function miniorange_pass2login_reconfig_google( $login_status ) {
			if ( 'MO_2_FACTOR_RECONFIG_GOOGLE' === $login_status ) {
				return true;
			}

			return false;
		}
		/**
		 * It will redirect to shortcode addon
		 *
		 * @param string $current_user_id .
		 * @param string $login_status It will store the login status message .
		 * @param string $login_message .
		 * @param string $identity .
		 * @return void
		 */
		public function mo2f_redirect_shortcode_addon( $current_user_id, $login_status, $login_message, $identity ) {
			do_action( 'mo2f_shortcode_addon', $current_user_id, $login_status, $login_message, $identity );
		}
		/**
		 * It will invoke to reconfig the Kba
		 *
		 * @param string $login_status It will store the login status message .
		 * @return boolean
		 */
		public function miniorange_pass2login_reconfig_kba( $login_status ) {
			if ( 'MO_2_FACTOR_RECONFIG_KBA' === $login_status ) {
				return true;
			}

			return false;
		}
		/**
		 * It will Check kba status
		 *
		 * @param string $login_status It will store the login status message .
		 * @return boolean
		 */
		public function miniorange_pass2login_check_kba_status( $login_status ) {
			if ( 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION' === $login_status ) {
				return true;
			}

			return false;
		}
		/**
		 * Pass2login woocommerce
		 *
		 * @return void
		 */
		public function mo_2_factor_pass2login_woocommerce() {
			$nonce = wp_create_nonce( 'mo_woocommerce_login_prompt' );
			?>
			<input type="hidden" name="mo_woocommerce_login_prompt" value="1">
			<input type="hidden" name="mo_woocommerce_login_prompt_nonce" value="<?php echo esc_attr( $nonce ); ?>">
			<?php
		}
		/**
		 * Pass2login for showing login form
		 *
		 * @return mixed
		 */
		public function mo_2_factor_pass2login_show_wp_login_form() {
			$session_id_encrypt = $this->create_session();
			if ( class_exists( 'Theme_My_Login' ) ) {
				wp_enqueue_script( 'tmlajax_script', plugins_url( 'includes/js/tmlajax.min.js', dirname( dirname( __FILE__ ) ) ), array( 'jQuery' ), MO2F_VERSION, false );
				wp_localize_script(
					'tmlajax_script',
					'my_ajax_object',
					array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
				);
			}
			if ( class_exists( 'LoginWithAjax' ) ) {
				wp_enqueue_script( 'login_with_ajax_script', plugins_url( 'includes/js/login_with_ajax.min.js', dirname( dirname( __FILE__ ) ) ), array( 'jQuery' ), MO2F_VERSION, false );
				wp_localize_script(
					'login_with_ajax_script',
					'my_ajax_object',
					array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
				);
			}
			?>
		<p><input type="hidden" name="miniorange_login_nonce"
				value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-login-nonce' ) ); ?>"/>

			<input type="hidden" id="sessid" name="session_id"
				value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>

		</p>

			<?php
			if ( MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'site_option' ) ) {
				echo '<p>';
				echo '<div id="mo2f_backup_code_secton"><label title="' . esc_attr__( 'If you don\'t have 2-factor authentication enabled for your WordPress account, leave this field empty.', 'miniorange-2-factor-authentication' ) . '" for="mo2f_2fa_code">' . esc_html__( '2 Factor Authentication code*', 'miniorange-2-factor-authentication' ) . '</label><span id="google-auth-info"></span><br/>';
				echo '<input type="text" placeholder="No soft Token ? Skip" class="input" style="font-size:15px;margin:0px" name="mo_softtoken" id="mo2f_2fa_code" class="mo2f_2fa_code" style="ime-mode: inactive;" />';
				echo '<p style="color:#2271b1;font-size:12px; margin-bottom:5px">* Skip the authentication code if it doesn\'t apply.</p></div>';
				echo '</p>';
				echo '<input type="checkbox" id="mo2f_use_backup_code" name="mo2f_use_backup_code" onclick="mo2f_handle_backup_codes(this);" value="mo2f_use_backup_code">
					<label for="mo2f_use_backup_code"> Use Backup Codes</label><br><br>';
				echo '<script>
					function mo2f_handle_backup_codes(e){
						if(e.checked)
						document.querySelector("#mo2f_backup_code_secton").style.display="none";
						else
						document.querySelector("#mo2f_backup_code_secton").style.display="block";

					}

				</script>';
			}
		}
		/**
		 * Mobile verification
		 *
		 * @param [type] $user It will carry the user detail .
		 * @param [type] $redirect_to It will carry the redirect url .
		 * @param [type] $session_id_encrypt It will store the session id .
		 * @return string
		 */
		public function mo2f_pass2login_mobile_verification( $user, $redirect_to, $session_id_encrypt = null ) {
			global $mo2fdb_queries,$mo_wpns_utility;
			if ( is_null( $session_id_encrypt ) ) {
				$session_id_encrypt = $this->create_session();
			}
			$user_email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user->ID );
			$useragent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			MO2f_Utility::mo2f_debug_file( 'Check user agent to check request from mobile device ' . $useragent );
			if ( MO2f_Utility::check_if_request_is_from_mobile_device( $useragent ) ) {
				$session_cookie_variables = array( 'mo2f-login-qrCode', 'mo2f_transactionId' );

				MO2f_Utility::unset_session_variables( $session_cookie_variables );
				MO2f_Utility::unset_cookie_variables( $session_cookie_variables );
				MO2f_Utility::unset_temp_user_details_in_table( 'mo2f_transactionId', $session_id_encrypt );

				$mo2fa_login_message = 'Please enter the one time passcode shown in the miniOrange<b> Authenticator</b> app.';
				$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
				MO2f_Utility::mo2f_debug_file( 'Request from mobile device so promting soft token  User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
			} else {
				$challenge_mobile = new Customer_Setup();
				$content          = $challenge_mobile->send_otp_token( $user_email, 'MOBILE AUTHENTICATION', get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) );
				$response         = json_decode( $content, true );
				if ( json_last_error() === JSON_ERROR_NONE ) { /* Generate Qr code */
					if ( 'SUCCESS' === $response['status'] ) {
						$qr_code = $response['qrCode'];
						MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'mo2f_transactionId', $response['txId'] );

						$this->mo2f_transactionid = $response['txId'];
						$mo2fa_login_message      = '';
						$mo2fa_login_status       = 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION';
						MO2f_Utility::mo2f_debug_file( $mo2fa_login_status . ' Sent miniOrange QR code Authentication successfully. User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, $qr_code, $session_id_encrypt );
					} elseif ( 'ERROR' === $response['status'] ) {
						$this->remove_current_activity( $session_id_encrypt );
						MO2f_Utility::mo2f_debug_file( $response['status'] . ' An error occured while processing your request  User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
						$error = new WP_Error();
						$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please Try again.' ) );

						return $error;
					}
				} else {
					MO2f_Utility::mo2f_debug_file( ' An error occured while processing your request  User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
					$this->remove_current_activity( $session_id_encrypt );
					$error = new WP_Error();
					$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please Try again.' ) );

					return $error;
				}
			}
		}
		/**
		 * Pass to login push verification
		 *
		 * @param string $currentuser It will carry the current user .
		 * @param string $mo2f_second_factor It will store the second factor method .
		 * @param string $redirect_to It will store the redirect url .
		 * @param string $session_id_encrypt It will carry the session id .
		 * @return void
		 */
		public function mo2f_pass2login_duo_push_verification( $currentuser, $mo2f_second_factor, $redirect_to, $session_id_encrypt ) {
			global $mo2fdb_queries;
			include_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'handler' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two_fa_duo_handler.php';
			if ( is_null( $session_id_encrypt ) ) {
				$session_id_encrypt = $this->create_session();
			}

			$mo2fa_login_message = '';
			$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_DUO_PUSH_NOTIFICATIONS';
			$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
		}
		/**
		 * Pass2login verification
		 *
		 * @param object $current_user It will carry the current user .
		 * @param string $mo2f_second_factor It will store the second factor method .
		 * @param string $redirect_to It will store the redirect url .
		 * @param string $session_id It will carry the session id .
		 * @return string
		 */
		public function mo2f_pass2login_push_oobemail_verification( $current_user, $mo2f_second_factor, $redirect_to, $session_id = null ) {
			global $mo2fdb_queries,$mo_wpns_utility;
			if ( is_null( $session_id ) ) {
				$session_id = $this->create_session();
			}
			$challenge_mobile = new Customer_Setup();
			$user_email       = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user->ID );
			if ( MO2F_IS_ONPREM && 'PUSH NOTIFICATIONS' !== $mo2f_second_factor ) {
				MO2f_Utility::mo2f_debug_file( 'Push notification has sent successfully for ' . $mo2f_second_factor . ' User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $current_user->ID . ' Email-' . $current_user->user_email );
				include_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'class-mo2f-onpremredirect.php';
				$mo2f_on_prem_redirect = new Mo2f_OnPremRedirect();
				$content               = $mo2f_on_prem_redirect->mo2f_pass2login_push_email_onpremise( $current_user, $redirect_to, $session_id );
			} else {
				$content = $challenge_mobile->send_otp_token( $user_email, $mo2f_second_factor, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) );
			}
			$response = json_decode( $content, true );
			if ( JSON_ERROR_NONE === json_last_error() ) { /* Generate Qr code */
				if ( 'SUCCESS' === $response['status'] ) {
					MO2f_Utility::mo2f_set_transient( $session_id, 'mo2f_transactionId', $response['txId'] );
					update_user_meta( $current_user->ID, 'mo2f_EV_txid', $response['txId'] );

					MO2f_Utility::mo2f_debug_file( 'Push notification has sent successfully for ' . $mo2f_second_factor . ' User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $current_user->ID . ' Email-' . $current_user->user_email );
					$this->mo2f_transactionid = $response['txId'];

					$mo2fa_login_message = 'PUSH NOTIFICATIONS' === $mo2f_second_factor ? 'A Push Notification has been sent to your phone. We are waiting for your approval.' : 'An email has been sent to ' . MO2f_Utility::mo2f_get_hidden_email( $user_email ) . '. We are waiting for your approval.';
					$mo2fa_login_status  = 'PUSH NOTIFICATIONS' === $mo2f_second_factor ? 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' : 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL';
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				} elseif ( 'ERROR' === $response['status'] || 'FAILED' === $response['status'] ) {
					MO2f_Utility::mo2f_set_transient( $session_id, 'mo2f_transactionId', $response['txId'] );
					update_user_meta( $current_user->ID, 'mo2f_EV_txid', $response['txId'] );

					MO2f_Utility::mo2f_debug_file( 'An error occured while sending push notification-' . $mo2f_second_factor . ' User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $current_user->ID . ' Email-' . $current_user->user_email );
					$this->mo2f_transactionid = $response['txId'];
					$mo2fa_login_message      = 'PUSH NOTIFICATIONS' === $mo2f_second_factor ? 'An error occured while sending push notification to your app. You can click on <b>Phone is Offline</b> button to enter soft token from app or <b>Forgot your phone</b> button to receive OTP to your registered email.' : 'An error occured while sending email. Please try again.';
					$mo2fa_login_status       = 'PUSH NOTIFICATIONS' === $mo2f_second_factor ? 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' : 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL';
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				}
			} else {
				MO2f_Utility::mo2f_debug_file( 'An error occured while processing your request. User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $current_user->ID . ' Email-' . $current_user->user_email );
				$this->remove_current_activity( $session_id );
				$error = new WP_Error();
				$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please Try again.' ) );

				return $error;
			}
		}
		/**
		 * Otp verification
		 *
		 * @param object $user It will carry the current user .
		 * @param string $mo2f_second_factor It will store the second factor method .
		 * @param string $redirect_to It will store the redirect url .
		 * @param string $session_id It will carry the session id .
		 * @return string
		 */
		public function mo2f_pass2login_otp_verification( $user, $mo2f_second_factor, $redirect_to, $session_id = null ) {
			global $mo2fdb_queries,$mo_wpns_utility;

			if ( is_null( $session_id ) ) {
				$session_id = $this->create_session();
			}
			$mo2f_external_app_type = get_user_meta( $user->ID, 'mo2f_external_app_type', true );
			if ( 'EMAIL' === $mo2f_second_factor ) {
				$mo2f_user_phone = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user->ID );
				$wdewdeqdqq      = get_site_option( base64_encode( 'remainingOTP' ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not using for obfuscation
				if ( $wdewdeqdqq > get_site_option( 'EmailTransactionCurrent', 30 ) || get_site_option( base64_encode( 'limitReached' ) ) ) { //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not using for obfuscation
					update_site_option( base64_encode( 'remainingOTP' ), 0 ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not using for obfuscation
				}
			} else {
				$mo2f_user_phone = $mo2fdb_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
			}
			if ( 'SOFT TOKEN' === $mo2f_second_factor ) {
				$mo2fa_login_message = 'Please enter the one time passcode shown in the miniOrange<b> Authenticator</b> app.';
				$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
				MO2f_Utility::mo2f_debug_file( $mo2fa_login_status . ' User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
			} elseif ( 'GOOGLE AUTHENTICATOR' === $mo2f_second_factor ) {
				$mo2fa_login_message = 'Please enter the one time passcode shown in the <b> Authenticator</b> app.';
				$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION';
				MO2f_Utility::mo2f_debug_file( $mo2fa_login_status . ' User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
				$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
			} elseif ( 'OTP Over Telegram' === $mo2f_second_factor ) {
				$chatid    = get_user_meta( $user->ID, 'mo2f_chat_id', true );
				$otp_token = '';
				for ( $i = 1;$i < 7;$i++ ) {
					$otp_token .= wp_rand( 0, 9 );
				}

				update_user_meta( $user->ID, 'mo2f_otp_token', $otp_token );
				update_user_meta( $user->ID, 'mo2f_telegram_time', time() );

				$url      = esc_url( MoWpnsConstants::TELEGRAM_OTP_LINK );
				$postdata = array(
					'mo2f_otp_token' => $otp_token,
					'mo2f_chatid'    => $chatid,
				);

				$args = array(
					'method'    => 'POST',
					'timeout'   => 10,
					'sslverify' => false,
					'headers'   => array(),
					'body'      => $postdata,
				);

				$mo2f_api = new Mo2f_Api();
				$data     = $mo2f_api->mo2f_wp_remote_post( $url, $args );

				if ( 'SUCCESS' === $data ) {
					$mo2fa_login_message = 'Please enter the one time passcode sent on your<b> Telegram</b> app.';
					$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_TELEGRAM';
					MO2f_Utility::mo2f_debug_file( $mo2fa_login_status . ' User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
				}
			} else {
				$challenge_mobile = new Customer_Setup();
				$content          = '';
				$response         = array();
				$otplimit         = 0;
				if ( MoWpnsUtility::get_mo2f_db_option( 'cmVtYWluaW5nT1RQ', 'site_option' ) > 0 || 'EMAIL' !== $mo2f_second_factor ) {
					if ( 'OTP Over SMS' === $mo2f_second_factor ) {
						$mo2f_second_factor = 'SMS';
					}
					$content  = $challenge_mobile->send_otp_token( $mo2f_user_phone, $mo2f_second_factor, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ), $user );
					$response = json_decode( $content, true );
				} else {
					MO2f_Utility::mo2f_debug_file( 'Error in sending OTP over Email or SMS. User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
					$response['status']  = 'FAILED';
					$response['message'] = '<p style = "color:red;">OTP limit has been exceeded</p>';
					$otplimit            = 1;
				}
				if ( json_last_error() === JSON_ERROR_NONE ) {
					if ( 'SUCCESS' === $response['status'] ) {
						if ( 'EMAIL' === $mo2f_second_factor ) {
							MO2f_Utility::mo2f_debug_file( ' OTP has been sent successfully over email. User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
							$cmvtywluaw5nt1rq = MoWpnsUtility::get_mo2f_db_option( 'cmVtYWluaW5nT1RQ', 'site_option' );
							if ( $cmvtywluaw5nt1rq > 0 ) {
								update_site_option( 'cmVtYWluaW5nT1RQ', $cmvtywluaw5nt1rq - 1 );
							}
						} elseif ( 'SMS' === $mo2f_second_factor ) {
							MO2f_Utility::mo2f_debug_file( ' OTP has been sent successfully over phone. User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user->ID . ' Email-' . $user->user_email );
							$mo2f_sms = get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' );
							if ( $mo2f_sms > 0 ) {
								update_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z', $mo2f_sms - 1 );
							}
						}
						if ( ! isset( $response['phoneDelivery']['contact'] ) ) {
							$response['phoneDelivery']['contact'] = '';
						}
						$message = 'The OTP has been sent to ' . MO2f_Utility::get_hidden_phone( $response['phoneDelivery']['contact'] ) . '. Please enter the OTP you received to Validate.';
						update_option( 'mo2f_number_of_transactions', MoWpnsUtility::get_mo2f_db_option( 'mo2f_number_of_transactions', 'get_option' ) - 1 );
						MO2f_Utility::mo2f_set_transient( $session_id, 'mo2f_transactionId', $response['txId'] );

						$this->mo2f_transactionid = $response['txId'];
						$mo2fa_login_message      = $message;
						$current_method           = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $user->ID );
						if ( 'EMAIL' === $mo2f_second_factor ) {
							$mo2fa_login_status = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
						} else {
							$mo2fa_login_status = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS';
						}
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
					} else {
						if ( 'TEST FAILED.' === $response['message'] ) {
							$response['message'] = 'There is an error in sending the OTP.';
						}

						$last_message = ' Or  <a href = " ' . MoWpnsConstants::RECHARGELINK . '" target="_blank">purchase transactions</a>';

						if ( 1 === $otplimit ) {
							$last_message .= 'or contact miniOrange';
						} elseif ( MO2F_IS_ONPREM && ( 'OTP Over Email' === $mo2f_second_factor || 'EMAIL' === $mo2f_second_factor || 'Email Verification' === $mo2f_second_factor ) ) {
							$last_message .= ' Or check your SMTP Server and remaining transactions.';
						} else {
							$last_message .= ' Or <a href="' . MoWpnsConstants::VIEW_TRANSACTIONS . '" target="_blank"> Check your remaining transactions </a>';
							if ( get_site_option( 'mo2f_email' ) === $user->user_email ) {
								$last_message .= 'or </br><a href="' . MoWpnsConstants::RECHARGELINK . '" target="_blank">Add SMS Transactions</a> to your account';
							}
						}
						$message = $response['message'] . ' You can click on <a href="https://faq.miniorange.com/knowledgebase/i-am-locked-cant-access-my-account-what-do-i-do/" target="_blank">I am locked out</a> to login via alternate method ' . $last_message;
						if ( ! isset( $response['txid'] ) ) {
							$response['txid'] = '';
						}
						MO2f_Utility::mo2f_set_transient( $session_id, 'mo2f_transactionId', $response['txid'] );

						$this->mo2f_transactionid = $response['txid'];
						$mo2fa_login_message      = $message;
						$mo2fa_login_status       = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
					}
				} else {
					$this->remove_current_activity( $session_id );
					$error = new WP_Error();
					$error->add( 'empty_username', __( '<strong>ERROR</strong>: An error occured while processing your request. Please Try again.' ) );
					return $error;
				}
			}
		}
		/**
		 * Pass2 login method
		 *
		 * @param string $redirect_to It will carry the redirect url.
		 * @param string $session_id_encrypted It will carry the session id.
		 * @return void
		 */
		public function mo2fa_pass2login( $redirect_to = null, $session_id_encrypted = null ) {
			if ( empty( $this->mo2f_user_id ) && empty( $this->fstfactor ) ) {
				$user_id               = MO2f_Utility::mo2f_get_transient( $session_id_encrypted, 'mo2f_current_user_id' );
				$mo2f_1stfactor_status = MO2f_Utility::mo2f_get_transient( $session_id_encrypted, 'mo2f_1stfactor_status' );
			} else {
				$user_id               = $this->mo2f_user_id;
				$mo2f_1stfactor_status = $this->fstfactor;
			}

			if ( $user_id && $mo2f_1stfactor_status && ( 'VALIDATE_SUCCESS' === $mo2f_1stfactor_status ) ) {
				$currentuser = get_user_by( 'id', $user_id );
				wp_set_current_user( $user_id, $currentuser->user_login );
				$mobile_login = new Miniorange_Mobile_Login();
				$mobile_login->remove_current_activity( $session_id_encrypted );

				delete_expired_transients( true );
				delete_site_option( $session_id_encrypted );

				wp_set_auth_cookie( $user_id, true );
				do_action( 'wp_login', $currentuser->user_login, $currentuser );
				redirect_user_to( $currentuser, $redirect_to );
				exit;
			} else {
				$this->remove_current_activity( $session_id_encrypted );
			}
		}
		/**
		 * This function will invoke to create session for user
		 *
		 * @return string
		 */
		public function create_session() {
			global $mo2fdb_queries;
			$session_id      = MO2f_Utility::random_str( 20 );
			$session_id_hash = md5( $session_id );
			$mo2fdb_queries->insert_user_login_session( $session_id_hash );
			$key                = get_option( 'mo2f_encryption_key' );
			$session_id_encrypt = MO2f_Utility::encrypt_data( $session_id, $key );
			return $session_id_encrypt;
		}
		/**
		 * It will initiate 2nd factor
		 *
		 * @param object $currentuser It will carry the current user detail .
		 * @param string $redirect_to It will carry the redirect url .
		 * @param string $otp_token It will carry the otp token .
		 * @param string $session_id_encrypt It will carry the session id .
		 * @return string
		 */
		public function miniorange_initiate_2nd_factor( $currentuser, $redirect_to = null, $otp_token = '', $session_id_encrypt = null ) {
			global $mo2fdb_queries,$mo_wpns_utility;
			MO2f_Utility::mo2f_debug_file( 'MO initiate 2nd factor User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
			$this->miniorange_pass2login_start_session();

			if ( is_null( $session_id_encrypt ) ) {
				$session_id_encrypt = $this->create_session();
			}

			if ( class_exists( 'UM_Functions' ) ) {
				MO2f_Utility::mo2f_debug_file( 'Using UM login form.' );
				if ( ! isset( $_POST['wp-submit'] ) && isset( $_POST['um_request'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Request is coming from Ultimate member login form.
					$meta = get_option( 'um_role_' . $currentuser->roles[0] . '_meta' );
					if ( isset( $meta ) && ! empty( $meta ) ) {
						if ( isset( $meta['_um_login_redirect_url'] ) ) {
							$redirect_to = $meta['_um_login_redirect_url'];
						}
						if ( empty( $redirect_to ) ) {
							$redirect_to = get_site_url();
						}
					}
					$login_form_url = '';
					if ( isset( $_POST['redirect_to'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Request is coming from Ultimate member login form.
						$login_form_url = esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Request is coming from Ultimate member login form.
					}

					if ( ! empty( $login_form_url ) && ! is_null( $login_form_url ) ) {
						$redirect_to = $login_form_url;
					}
				}
			}
			MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'mo2f_current_user_id', $currentuser->ID, 600 );
			MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'mo2f_1stfactor_status', 'VALIDATE_SUCCESS', 600 );

			$this->mo2f_user_id = $currentuser->ID;
			$this->fstfactor    = 'VALIDATE_SUCCESS';

			$is_customer_admin = true;
			if ( get_site_option( 'dG90YWxVc2Vyc0Nsb3Vk' ) < 3 ) {
				$is_customer_admin = true;
			}

			$roles             = (array) $currentuser->roles;
			$twofactor_enabled = 0;
			foreach ( $roles as $role ) {
				if ( get_option( 'mo2fa_' . $role ) === '1' ) {
					$twofactor_enabled = 1;
				}
			}
			if ( 1 !== $twofactor_enabled && is_super_admin( $currentuser->ID ) ) {
				if ( get_site_option( 'mo2fa_superadmin' ) === 1 ) {
					$twofactor_enabled = 1;
				}
			}

			if ( $is_customer_admin && $twofactor_enabled ) {
				$mo_2factor_user_registration_status = $mo2fdb_queries->get_user_detail( 'mo_2factor_user_registration_status', $currentuser->ID );
				$kba_configuration_status            = $mo2fdb_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $currentuser->ID );

				if ( MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_brute_force', 'get_option' ) ) {
					$mo2f_allwed_login_attempts = get_option( 'mo2f_allwed_login_attempts' );
				} else {
					$mo2f_allwed_login_attempts = 'disabled';
				}
				update_user_meta( $currentuser->ID, 'mo2f_user_login_attempts', $mo2f_allwed_login_attempts );

				$twofactor_transactions = new Mo2fDB();
				$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $currentuser->ID );
				$tfa_enabled            = $mo2fdb_queries->get_user_detail( 'mo2f_2factor_enable_2fa_byusers', $currentuser->ID );
				if ( 0 === $tfa_enabled && ( 'MO_2_FACTOR_PLUGIN_SETTINGS' !== $mo_2factor_user_registration_status ) && ! empty( $tfa_enabled ) ) {
					$exceeded = 1;
				}

				if ( 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status ) { // checking if user has configured any 2nd factor method .
					$mo2f_second_factor = '';
					$mo2f_second_factor = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $currentuser->ID );
					if ( ! MO2F_IS_ONPREM && 'OTP Over Telegram' !== $mo2f_second_factor ) {
						$mo2f_second_factor = mo2f_get_user_2ndfactor( $currentuser );
					}

					$configure_array_method = $this->mo2fa_return_methods_value( $currentuser->ID );
					if ( count( $configure_array_method ) > 1 && get_site_option( 'mo2f_nonce_enable_configured_methods' ) === '1' && ! MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'site_option' ) ) {
						update_site_option( 'mo2f_login_with_mfa_use', '1' );
						mo2fa_prompt_mfa_form_for_user( $configure_array_method, $session_id_encrypt, $redirect_to );
						exit;
					} else {
						$user = $this->mo2fa_select_method( $currentuser, $mo2f_second_factor, $otp_token, $session_id_encrypt, $redirect_to, $kba_configuration_status );
						return $user;
					}
				} elseif ( ! $exceeded && ( MoWpnsUtility::get_mo2f_db_option( 'mo2f_inline_registration', 'get_option' ) || $this->mo2f_is_grace_period_expired( $currentuser ) ) ) {
					$this->mo2fa_inline( $currentuser, $redirect_to, $session_id_encrypt );
				} else {
					if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
						$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
					} else {
						return $currentuser;
					}
				}
			} else { // plugin is not activated for current role then logged him in without asking 2 factor .
				$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
			}
		}
		/**
		 * This function will return the configured method value
		 *
		 * @param string $currentuserid It will carry the current user id .
		 * @return array
		 */
		public function mo2fa_return_methods_value( $currentuserid ) {
			global $mo2fdb_queries;
			$count_methods          = $mo2fdb_queries->get_user_configured_methods( $currentuserid );
			$value                  = empty( $count_methods ) ? '' : get_object_vars( $count_methods[0] );
			$configured_methods_arr = array();
			foreach ( $value as $config_status_option => $config_status ) {
				if ( strpos( $config_status_option, 'config_status' ) ) {
					$config_status_string_array = explode( '_', $config_status_option );
					$config_method              = MO2f_Utility::mo2f_decode_2_factor( $config_status_string_array[1], 'wpdb' );
					if ( '1' === $value[ $config_status_option ] ) {
						array_push( $configured_methods_arr, $config_method );
					}
				}
			}

			return $configured_methods_arr;
		}
		/**
		 * Select methods for twofa
		 *
		 * @param object $currentuser It will carry the current user .
		 * @param string $mo2f_second_factor It will carry the second factor .
		 * @param string $otp_token It will carry the otp token .
		 * @param string $session_id_encrypt It will carry the session id .
		 * @param string $redirect_to It will carry the redirect url .
		 * @param string $kba_configuration_status It will store the kba configuration status .
		 * @return string
		 */
		public function mo2fa_select_method( $currentuser, $mo2f_second_factor, $otp_token, $session_id_encrypt, $redirect_to, $kba_configuration_status ) {
			global $mo_wpns_utility;
			if ( 'OTP Over Email' === $mo2f_second_factor || 'OTP OVER EMAIL' === $mo2f_second_factor || 'EMAIL' === $mo2f_second_factor ) {
				$mo2f_second_factor = 'EMAIL';
				if ( MoWpnsUtility::get_mo2f_db_option( 'cmVtYWluaW5nT1RQ', 'site_option' ) <= 0 ) {
					update_site_option( 'bGltaXRSZWFjaGVk', 1 );
				}
			} else {
				$mo2f_second_factor = MO2f_Utility::mo2f_decode_2_factor( $mo2f_second_factor, 'server' );
			}

			if ( 'OTPOverTelegram' === $mo2f_second_factor ) {
				$mo2f_second_factor = 'OTP Over Telegram';
			}
			if ( ( ( 'GOOGLE AUTHENTICATOR' === $mo2f_second_factor ) || ( 'SOFT TOKEN' === $mo2f_second_factor ) || ( 'AUTHY AUTHENTICATOR' === $mo2f_second_factor ) ) && MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'site_option' ) && ! isset( $_POST['mo_woocommerce_login_prompt'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Request is coming from WooCommerce login form.
				$error = $this->mo2f_validate_soft_token( $currentuser, $mo2f_second_factor, $otp_token, $session_id_encrypt, $redirect_to );
				if ( is_wp_error( $error ) ) {
					return $error;
				}
			} else {
				if ( MO2f_Utility::check_if_request_is_from_mobile_device( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' ) && $kba_configuration_status ) {
					$this->mo2f_pass2login_kba_verification( $currentuser->ID, $redirect_to, $session_id_encrypt );
				} else {
					if ( 'MOBILE AUTHENTICATION' === $mo2f_second_factor ) {
						$this->mo2f_pass2login_mobile_verification( $currentuser, $redirect_to, $session_id_encrypt );
					} elseif ( 'PUSH NOTIFICATIONS' === $mo2f_second_factor || 'OUT OF BAND EMAIL' === $mo2f_second_factor || 'Email Verification' === $mo2f_second_factor ) {
						MO2f_Utility::mo2f_debug_file( 'Initiating 2fa validation template for ' . $mo2f_second_factor . 'User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
						$this->mo2f_pass2login_push_oobemail_verification( $currentuser, $mo2f_second_factor, $redirect_to, $session_id_encrypt );
					} elseif ( 'SOFT TOKEN' === $mo2f_second_factor || 'SMS' === $mo2f_second_factor || 'PHONE VERIFICATION' === $mo2f_second_factor || 'GOOGLE AUTHENTICATOR' === $mo2f_second_factor || 'EMAIL' === $mo2f_second_factor || 'OTP Over Telegram' === $mo2f_second_factor || 'OTP Over Whatsapp' === $mo2f_second_factor ) {
						MO2f_Utility::mo2f_debug_file( 'Initiating 2fa validation template for ' . $mo2f_second_factor . 'User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
						$this->mo2f_pass2login_otp_verification( $currentuser, $mo2f_second_factor, $redirect_to, $session_id_encrypt );
					} elseif ( 'KBA' === $mo2f_second_factor || 'Security Questions' === $mo2f_second_factor ) {
						MO2f_Utility::mo2f_debug_file( 'Initiating 2fa validation template for ' . $mo2f_second_factor . 'User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
						$this->mo2f_pass2login_kba_verification( $currentuser->ID, $redirect_to, $session_id_encrypt );
					} elseif ( 'Duo Authenticator' === $mo2f_second_factor ) {
						MO2f_Utility::mo2f_debug_file( 'Initiating 2fa validation template for ' . $mo2f_second_factor . 'User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
						$this->mo2f_pass2login_duo_push_verification( $currentuser, $mo2f_second_factor, $redirect_to, $session_id_encrypt );
					} elseif ( 'NONE' === $mo2f_second_factor ) {
						MO2f_Utility::mo2f_debug_file( 'mo2f_second_factor is NONE User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
						if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
							$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
						} else {
							return $currentuser;
						}
					} else {
						$this->remove_current_activity( $session_id_encrypt );
						$error = new WP_Error();
						if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
							MO2f_Utility::mo2f_debug_file( 'Two factor method has not been configured User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
							$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp; Two Factor method has not been configured.' );
							wp_send_json_success( $data );
						} else {
							MO2f_Utility::mo2f_debug_file( 'Two factor method has not been configured User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
							$error->add( 'empty_username', __( '<strong>ERROR</strong>: Two Factor method has not been configured.' ) );
							return $error;
						}
					}
				}
			}
		}
		/**
		 * Inline invoke 2fa
		 *
		 * @param object $currentuser It will carry the current user detail .
		 * @param string $redirect_to It will carry the redirect url .
		 * @param string $session_id It will carry the session id .
		 * @return void
		 */
		public function mo2fa_inline( $currentuser, $redirect_to, $session_id ) {
			global $mo2fdb_queries;
			$current_user_id = $currentuser->ID;
			$email           = $currentuser->user_email;
			$mo2fdb_queries->insert_user( $current_user_id, array( 'user_id' => $current_user_id ) );
			$mo2fdb_queries->update_user_details(
				$current_user_id,
				array(
					'user_registration_with_miniorange'   => 'SUCCESS',
					'mo2f_user_email'                     => $email,
					'mo_2factor_user_registration_status' => 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR',
				)
			);

			$mo2fa_login_message = '';
			$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';

			$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
		}
		/**
		 * This function will validating the soft token
		 *
		 * @param object $currentuser It will carry the current user detail .
		 * @param string $mo2f_second_factor It will carry the second factor method .
		 * @param string $softtoken It will carry the soft token .
		 * @param string $session_id_encrypt It will carry the session id .
		 * @param string $redirect_to It will carry the redirect url .
		 * @return string
		 */
		public function mo2f_validate_soft_token( $currentuser, $mo2f_second_factor, $softtoken, $session_id_encrypt, $redirect_to = null ) {
			global $mo2fdb_queries;
			$email    = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $currentuser->ID );
			$customer = new Customer_Setup();
			$content  = json_decode( $customer->validate_otp_token( $mo2f_second_factor, $email, null, $softtoken, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ) ), true );
			if ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
				$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
			} else {
				if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
					$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp; Invalid One Time Passcode.' );
					wp_send_json_success( $data );
				} else {
					return new WP_Error( 'invalid_one_time_passcode', '<strong>ERROR</strong>: Invalid One Time Passcode.' );
				}
			}
		}
		/**
		 * Sending the otp over email
		 *
		 * @param string $email It will carry the email address .
		 * @param string $redirect_to It will carry the redirect url .
		 * @param string $session_id_encrypt It will carry the session id .
		 * @param string $current_user It will carry the current user .
		 * @return void
		 */
		public function mo2f_otp_over_email_send( $email, $redirect_to, $session_id_encrypt, $current_user ) {
			$challenge_mobile = new Customer_Setup();
			$response         = array();
			if ( get_site_option( 'cmVtYWluaW5nT1RQ' ) > 0 ) {
				$content  = $challenge_mobile->send_otp_token( $email, 'EMAIL', get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ), $current_user );
				$response = json_decode( $content, true );
				if ( ! MO2F_IS_ONPREM ) {
					if ( isset( $response['txId'] ) ) {
						MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'mo2f_transactionid', $response['txId'] );
					}
				}
			} else {
				$response['status']  = 'FAILED';
				$response['message'] = '<p style = "color:red;">OTP limit has been exceeded</p>';
			}
			if ( json_last_error() === JSON_ERROR_NONE ) {
				if ( 'SUCCESS' === $response['status'] ) {
					$cmvtywluaw5nt1rq = get_site_option( 'cmVtYWluaW5nT1RQ' );
					if ( $cmvtywluaw5nt1rq > 0 ) {
						update_site_option( 'cmVtYWluaW5nT1RQ', $cmvtywluaw5nt1rq - 1 );
					}
					$mo2fa_login_message  = 'An OTP has been sent to ' . $email . ' please verify to set the two-factor';
					$mo2fa_login_status   = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
					$mo2fa_transaction_id = isset( $response['txId'] ) ? $response['txId'] : null;
					$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt, 1, $mo2fa_transaction_id );
				} else {
					if ( 'FAILED' === $response['status'] && 'OTP limit has been exceeded' === $response['message'] ) {
						$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
						$mo2fa_login_message = 'There was an issue while sending the OTP to ' . $email . '. Please check your remaining transactions and try again.';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					} elseif ( 'FAILED' === $response['status'] ) {
						$mo2fa_login_status  = 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
						$mo2fa_login_message = 'Your SMTP has not been set, please set your SMTP first to get OTP.';
						$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id_encrypt );
					}
				}
			}
		}
		/**
		 * It will call at the time of authentication .
		 *
		 * @param object $user It will carry the user detail.
		 * @param string $username It will carry the username .
		 * @param string $password It will carry the password .
		 * @param string $redirect_to It will carry the redirect url .
		 * @return string
		 */
		public function mo2f_check_username_password( $user, $username, $password, $redirect_to = null ) {
			global $mo2fdb_queries,$mo_wpns_utility;
			if ( is_a( $user, 'WP_Error' ) && ! empty( $user ) ) {
				if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
					$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp;Invalid User Credentials' );
					wp_send_json_success( $data );
				} else {
					return $user;
				}
			}
			if ( 'wp-login.php' === $GLOBALS['pagenow'] && isset( $_POST['mo_woocommerce_login_prompt'] ) ) {  //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Request is coming from WooCommerce login form.
				return new WP_Error( 'Unauthorized Access.', '<strong>ERROR</strong>: Access Denied.' );
			}
			// if an app password is enabled, this is an XMLRPC / APP login ? .
			if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
				$currentuser = wp_authenticate_username_password( $user, $username, $password );
				if ( is_wp_error( $currentuser ) ) {
					$error = new IXR_Error( 403, __( 'Bad login/pass combination.' ) );

					return false;
				} else {
					return $currentuser;
				}
			} else {
				$currentuser = wp_authenticate_username_password( $user, $username, $password );
				if ( is_wp_error( $currentuser ) ) {
					if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
						$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp; Invalid User Credentials' );
						wp_send_json_success( $data );
					} else {
						$currentuser->add( 'invalid_username_password', '<strong>' . esc_html( 'ERROR' ) . '</strong>: ' . esc_html( 'Invalid Username or password.' ) );
						MO2f_Utility::mo2f_debug_file( 'Invalid username and password. User_IP-' . $mo_wpns_utility->get_client_ip() );
						return $currentuser;
					}
				} else {
					$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;//phpcs:ignore WordPress.Security.NonceVerification.Missing -- Ignoring nonce verification warning as the flow is coming from multiple files.
					MO2f_Utility::mo2f_debug_file( 'Username and password  validate successfully User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
					if ( isset( $_REQUEST['woocommerce-login-nonce'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Request is coming from WooCommerce login form.
						MO2f_Utility::mo2f_debug_file( 'It is a woocommerce login form. Get woocommerce redirectUrl' );
						if ( ! empty( $_REQUEST['redirect_to'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Request is coming from WooCommerce login form.
							$redirect_to = sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Request is coming from WooCommerce login form.
						} elseif ( isset( $_REQUEST['_wp_http_referer'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Request is coming from WooCommerce login form.
							$redirect_to = sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Request is coming from WooCommerce login form.
						} else {
							if ( function_exists( 'wc_get_page_permalink' ) ) {
								$redirect_to = wc_get_page_permalink( 'myaccount' ); // function exists in WooCommerce plugin.
							}
						}
					} else {
						$redirect_to = isset( $_REQUEST['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) ) : ( isset( $_REQUEST['redirect'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['redirect'] ) ) : null ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Request is coming from WooCommerce login form.
					}
					$redirect_to                   = esc_url_raw( $redirect_to );
					$mo2f_transactions             = new Mo2fDB();
					$mo2f_configured_2fa_method    = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2fa_method', $currentuser->ID );
					$mo2f_user_registration_status = $mo2fdb_queries->get_user_detail( 'mo_2factor_user_registration_status', $currentuser->ID );
					$cloud_methods                 = array( 'MOBILE AUTHENTICATION', 'PUSH NOTIFICATIONS', 'SOFT TOKEN' );
					if ( MO2F_IS_ONPREM && 'Security Questions' === $mo2f_configured_2fa_method ) {
						MO2f_Utility::mo2f_debug_file( 'Initiating 2nd factor for KBA User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
						$this->miniorange_initiate_2nd_factor( $currentuser, $redirect_to, '', $session_id );
					} elseif ( MO2F_IS_ONPREM && 'Email Verification' === $mo2f_configured_2fa_method ) {
						MO2f_Utility::mo2f_debug_file( 'Initiating 2nd factor for email verification User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $currentuser->ID . ' Email-' . $currentuser->user_email );
						$this->miniorange_initiate_2nd_factor( $currentuser, $redirect_to, null, $session_id );
					} else {
						if ( $this->mo2f_is_new_user( $currentuser ) ) {
							$twofactor_transactions = new Mo2fDB();
							$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $currentuser->ID );
							if ( get_option( 'mo2fa_' . $currentuser->roles[0] ) && false === $exceeded ) {
								if ( get_option( 'mo2f_grace_period' ) === 'on' ) {
									update_option( 'mo2f_user_login_status_' . $currentuser->ID, 1 );
									update_option( 'mo2f_grace_period_status_' . $currentuser->ID, strtotime( current_datetime()->format( 'h:ia M d Y' ) ) );
								}
							}
						}

						if ( empty( $_POST['mo2f_use_backup_code'] ) && empty( $_POST['mo_softtoken'] ) && MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_2fa_prompt_on_login_page', 'get_option' ) && $mo2f_configured_2fa_method && ( ( 'Google Authenticator' === $mo2f_configured_2fa_method ) || ( 'miniOrange Soft Token' === $mo2f_configured_2fa_method ) || ( 'Authy Authenticator' === $mo2f_configured_2fa_method ) ) && get_option( 'mo2fa_administrator' ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Ignoring nonce verification warning as the flow is coming from multiple files.
							$currentuser = wp_authenticate_username_password( $user, $username, $password );
							if ( class_exists( 'UM_Functions' ) ) {
								$passcode = isset( $_POST['mo2f_validate_otp_token'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_validate_otp_token'] ) ) : sanitize_text_field( wp_unslash( $_POST['mo_softtoken'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Ignoring nonce verification warning as the flow is coming from multiple files.
								if ( ! is_null( $passcode ) && ! empty( $passcode ) ) {
									$passcode = sanitize_text_field( $passcode );
									$this->miniorange_pass2login_start_session();
									$session_id_encrypt = $this->create_session();
									MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'mo2f_current_user_id', $currentuser->ID, 600 );
									MO2f_Utility::mo2f_set_transient( $session_id_encrypt, 'mo2f_1stfactor_status', 'VALIDATE_SUCCESS', 6000 );

									$customer = new Customer_Setup();
									if ( 'miniOrange Soft Token' === $mo2f_configured_2fa_method ) {
										$method = 'SOFT TOKEN';
									} elseif ( 'Google Authenticator' === $mo2f_configured_2fa_method ) {
										$method = 'GOOGLE AUTHENTICATOR';
									}
									$email   = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $currentuser->ID );
									$content = json_decode( $customer->validate_otp_token( $method, $email, null, $passcode, get_option( 'mo2f_customerKey' ), get_option( 'mo2f_api_key' ), $currentuser ), true );

									if ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
										$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : null; //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Ignoring nonce verification warning as the flow is coming from multiple files.

										$this->mo2fa_pass2login( $redirect_to, $session_id_encrypt );
									} else {
										$error = new WP_Error();
										$error->add( 'WRONG PASSCODE:', esc_html( '<strong>Wrong Two-factor Authentication code.</strong>' ) );
										return $error;
									}
								} else {
									$error = new WP_Error();
									$error->add( 'EMPTY PASSCODE:', __( 'Empty Two-factor Authentication code.' ) );
									return $error;
								}
							}
							$woocommerce_nonce = isset( $_POST['mo_woocommerce_login_prompt_nonce'] ) ? sanitize_key( $_POST['mo_woocommerce_login_prompt_nonce'] ) : '';
							if ( wp_verify_nonce( $woocommerce_nonce, 'mo_woocommerce_login_prompt' ) && isset( $_POST['mo_woocommerce_login_prompt'] ) ) {
								$this->miniorange_initiate_2nd_factor( $currentuser, $redirect_to, '', $session_id );
							}
							if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
								$data = array( 'notice' => '<div style="border-left:3px solid #dc3232;">&nbsp; Please enter the One Time Passcode' );
								wp_send_json_success( $data );
							} else {
								return new WP_Error( 'one_time_passcode_empty', '<strong>ERROR</strong>: Please enter the One Time Passcode.' );
							}
							// Prevent PHP notices when using app password login .
						} else {
							$otp_token = isset( $_POST['mo_softtoken'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_softtoken'] ) ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing -- Request is coming from Wordpress login form.
						}
						$session_id       = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;//phpcs:ignore WordPress.Security.NonceVerification.Missing -- Request is coming from Wordpres login form.
						$mo2f_backup_code = isset( $_POST['mo2f_use_backup_code'] ) ? sanitize_text_field( wp_unslash( $_POST['mo2f_use_backup_code'] ) ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing -- Request is coming from Wordpres login form.

						if ( is_null( $session_id ) ) {
							$session_id = $this->create_session();
						}

						if ( 'mo2f_use_backup_code' === $mo2f_backup_code ) {  // BACKUP CODES .
							MO2f_Utility::mo2f_set_transient( $session_id, 'mo2f_1stfactor_status', 'VALIDATE_SUCCESS', 600 );
							MO2f_Utility::mo2f_set_transient( $session_id, 'mo2f_current_user_id', $currentuser->ID, 600 );
							$mo2fa_login_message = __( 'Please provide backup code.', 'miniorange-2-factor-authentication' );
							$mo2fa_login_status  = 'MO_2_FACTOR_CHALLENGE_BACKUP';
							$this->miniorange_pass2login_form_fields( $mo2fa_login_status, $mo2fa_login_message, $redirect_to, null, $session_id );
							exit;
						}

						$error = $this->miniorange_initiate_2nd_factor( $currentuser, $redirect_to, $otp_token, $session_id );

						if ( is_wp_error( $error ) ) {
							return $error;
						}
						return $error;
					}
				}
			}
		}
		/**
		 * It is for new user
		 *
		 * @param object $currentuser It will carry the current user .
		 * @return boolean
		 */
		public function mo2f_is_new_user( $currentuser ) {
			if ( get_option( 'mo2f_user_login_status_' . $currentuser->ID ) ) {
				return false;
			} else {
				return true;
			}
		}
		/**
		 * It is useful for grace period
		 *
		 * @param object $currentuser It will carry the current user .
		 * @return string
		 */
		public function mo2f_is_grace_period_expired( $currentuser ) {
			$grace_period_set_time = get_option( 'mo2f_grace_period_status_' . $currentuser->ID );

			$grace_period = get_option( 'mo2f_grace_period_value' );
			if ( get_option( 'mo2f_grace_period_type' ) === 'hours' ) {
				$grace_period = $grace_period * 60 * 60;
			} else {
				$grace_period = $grace_period * 24 * 60 * 60;
			}

			$total_grace_period = $grace_period + $grace_period_set_time;
			$current_time_stamp = strtotime( current_datetime()->format( 'h:ia M d Y' ) );

			return $total_grace_period <= $current_time_stamp;
		}

		/**
		 * It will help to display the email verification
		 *
		 * @param string $head It will carry the header .
		 * @param string $body It will carry the body .
		 * @param string $color It will carry the color .
		 * @return void
		 */
		public function display_email_verification( $head, $body, $color ) {
			global $main_dir;

			echo "<div  style='background-color: #d5e3d9; height:850px;' >
		    <div style='height:350px; background-color: #3CB371; border-radius: 2px; padding:2%;  '>
		        <div class='mo2f_tamplate_layout' style='background-color: #ffffff;border-radius: 5px;box-shadow: 0 5px 15px rgba(0,0,0,.5); width:850px;height:350px; align-self: center; margin: 180px auto; ' >
		            <img  alt='logo'  style='margin-left:400px ;
		        margin-top:10px;' src='" . esc_url( $main_dir ) . "includes/images/miniorange_logo.png'>
		            <div><hr></div>

		            <tbody>
		            <tr>
		                <td>

		                    <p style='margin-top:0;margin-bottom:10px'>
		                    <p style='margin-top:0;margin-bottom:10px'> <h1 style='color:" . esc_attr( $color ) . ";text-align:center;font-size:50px'>" . esc_attr( $head ) . "</h1></p>
		                    <p style='margin-top:0;margin-bottom:10px'>
		                    <p style='margin-top:0;margin-bottom:10px;text-align:center'><h2 style='text-align:center'>" . esc_html( $body ) . "</h2></p>
		                    <p style='margin-top:0;margin-bottom:0px;font-size:11px'>

		                </td>
		            </tr>

		        </div>
		    </div>
		</div>";
		}
		/**
		 * It will help to enqueue the default login
		 *
		 * @return void
		 */
		public function mo_2_factor_enable_jquery_default_login() {
			wp_enqueue_script( 'jquery' );
		}

	}
}
?>
