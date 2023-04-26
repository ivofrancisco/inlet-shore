<?php
/**
 * User profile 2fa update file.
 *
 * @package miniOrange-2-factor-authentication/handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$nonce = isset( $_POST['mo2f-update-mobile-nonce'] ) ? sanitize_key( wp_unslash( $_POST['mo2f-update-mobile-nonce'] ) ) : '';

if ( ! wp_verify_nonce( $nonce, 'mo2f-update-mobile-nonce' ) || ! current_user_can( 'manage_options' ) ) {
	$mo2f_error = new WP_Error();
	$mo2f_error->add( 'empty_username', '<strong>' . __( 'ERROR', 'miniorange-2-factor-authentication' ) . '</strong>: ' . __( 'Invalid Request.', 'miniorange-2-factor-authentication' ) );
	return $mo2f_error;
} else {
	if ( isset( $_POST['method'] ) ) {
		$method = sanitize_text_field( wp_unslash( $_POST['method'] ) );
	} else {
		return;
	}
	global $mo2fdb_queries;
	$email   = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user_id );
	$method  = MO2f_Utility::mo2f_decode_2_factor( $method, 'wpdb' );
	$email   = sanitize_email( $email );
	$enduser = new Two_Factor_Setup();
	if ( isset( $_POST['verify_phone'] ) ) {
		$phone = strlen( $_POST['verify_phone'] > 4 ) ? sanitize_text_field( wp_unslash( $_POST['verify_phone'] ) ) : null;
	} else {
		$phone = null;
	}
	$response = json_decode( $enduser->mo2f_update_userinfo( $email, MO2f_Utility::mo2f_decode_2_factor( $method, 'server' ), $phone, null, null ), true );
	if ( 'SUCCESS' !== $response['status'] ) {
		return;
	}
	$userid    = get_current_user_id();
	$method    = MO2f_Utility::mo2f_decode_2_factor( $method, 'wpdb' );
	$tfastatus = ( $userid === $user_id ) ? 'MO_2_FACTOR_PLUGIN_SETTINGS' : 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR';
	switch ( $method ) {
		case 'miniOrange QR Code Authentication':
		case 'miniOrange Push Notification':
		case 'miniOrange Soft Token':
			if ( $userid !== $user_id ) {
				send_reconfiguration_on_email( $email, $user_id, $method );
			} elseif ( isset( $_POST['mo2f_configuration_status'] ) && sanitize_text_field( wp_unslash( $_POST['mo2f_configuration_status'] ) ) !== 'SUCCESS' ) {
				return;
			}
			delete_user_meta( $user_id, 'configure_2FA' );
			update_user_meta( $user_id, 'mo2f_2FA_method_to_configure', $method );
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mobile_registration_status'          => true,
					'mo2f_miniOrangeQRCodeAuthentication_config_status' => true,
					'mo2f_miniOrangeSoftToken_config_status' => true,
					'mo2f_miniOrangePushNotification_config_status' => true,
					'mo2f_configured_2FA_method'          => $method,
					'user_registration_with_miniorange'   => 'SUCCESS',
					'mo2f_2factor_enable_2fa_byusers'     => '1',
					'mo_2factor_user_registration_status' => $tfastatus,
				)
			);
			break;
		case 'Google Authenticator':
			if ( $userid !== $user_id ) {
				send_reconfiguration_on_email( $email, $user_id, $method );
			} elseif ( isset( $_POST['mo2f_configuration_status'] ) && sanitize_text_field( wp_unslash( $_POST['mo2f_configuration_status'] ) ) !== 'SUCCESS' ) {
				return;
			}
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_GoogleAuthenticator_config_status' => true,
					'mo2f_configured_2FA_method'          => 'Google Authenticator',
					'mo2f_AuthyAuthenticator_config_status' => false,
					'user_registration_with_miniorange'   => 'SUCCESS',
					'mo_2factor_user_registration_status' => $tfastatus,
					'mo2f_2factor_enable_2fa_byusers'     => 1,
					'mo2f_user_email'                     => $email,
				)
			);
			if ( ! MO2F_IS_ONPREM ) {
				update_user_meta( $user_id, 'mo2f_external_app_type', 'Google Authenticator' );
			}
			break;
		case 'Authy Authenticator':
			if ( $userid !== $user_id ) {
				send_reconfiguration_on_email( $email, $user_id, $method );
			} elseif ( isset( $_POST['mo2f_configuration_status'] ) && sanitize_text_field( wp_unslash( $_POST['mo2f_configuration_status'] ) ) !== 'SUCCESS' ) {
				return;
			}
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_GoogleAuthenticator_config_status' => false,
					'mo2f_configured_2FA_method'          => 'Authy Authenticator',
					'mo2f_AuthyAuthenticator_config_status' => true,
					'user_registration_with_miniorange'   => 'SUCCESS',
					'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					'mo2f_2factor_enable_2fa_byusers'     => 1,
					'mo2f_user_email'                     => $email,
				)
			);
			if ( ! MO2F_IS_ONPREM ) {
				update_user_meta( $user_id, 'mo2f_external_app_type', 'Authy Authenticator' );
			}
			break;
		case 'OTP Over SMS':
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_configured_2FA_method'          => 'OTP Over SMS',
					'mo2f_OTPOverSMS_config_status'       => true,
					'user_registration_with_miniorange'   => 'SUCCESS',
					'mo2f_2factor_enable_2fa_byusers'     => '1',
					'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
				)
			);
			break;
		case 'Security Questions':
			$obj    = new Miniorange_Authentication();
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
			if ( MO2f_Utility::mo2f_check_empty_or_null( $kba_q1 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_a1 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_q2 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_a2 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_q3 ) || MO2f_Utility::mo2f_check_empty_or_null( $kba_a3 ) ) {
				update_option( 'mo2f_message', Mo2fConstants::lang_translate( 'INVALID_ENTRY' ) );
				return;
			}

			if ( 0 === strcasecmp( $kba_q1, $kba_q2 ) || 0 === strcasecmp( $kba_q2, $kba_q3 ) || 0 === strcasecmp( $kba_q3, $kba_q1 ) ) {
				update_option( 'mo2f_message', 'The questions you select must be unique.' );
				return;
			}
			$kba_registration = new Two_Factor_Setup();
			$kba_reg_reponse  = json_decode( $kba_registration->mo2f_register_kba_details( $email, $kba_q1, $kba_a1, $kba_q2, $kba_a2, $kba_q3, $kba_a3, $user_id ), true );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				if ( 'SUCCESS' === $response['status'] ) {
					$mo2fdb_queries->update_user_details(
						$user_id,
						array(
							'mo2f_configured_2FA_method' => 'Security Questions',
							'user_registration_with_miniorange' => 'SUCCESS',
							'mo2f_SecurityQuestions_config_status' => true,
							'mo2f_2factor_enable_2fa_byusers' => '1',
							'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
						),
						true
					);

				} else {
					update_option( 'mo2f_message', Mo2fConstants::lang_translate( 'ERROR_DURING_PROCESS' ) );
					$obj->mo2f_auth_show_error_message();

				}
			}

			break;
		case 'OTP Over Email':
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_configured_2FA_method'          => 'OTP Over Email',
					'mo2f_OTPOverEmail_config_status'     => true,
					'mo2f_user_email'                     => $email,
					'mo2f_2factor_enable_2fa_byusers'     => '1',
					'mo_2factor_user_registration_status' => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					'user_registration_with_miniorange'   => 'SUCCESS',
				)
			);
			delete_user_meta( $user_id, 'configure_2FA' );
			delete_user_meta( $user_id, 'test_2FA' );
			break;
		case 'Email Verification':
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_configured_2FA_method'           => 'Email Verification',
					'mo2f_user_email'                      => $email,
					'user_registration_with_miniorange'    => 'SUCCESS',
					'mo2f_2factor_enable_2fa_byusers'      => '1',
					'mo_2factor_user_registration_status'  => 'MO_2_FACTOR_PLUGIN_SETTINGS',
					'mo2f_EmailVerification_config_status' => true,
				)
			);
			break;
	}
}
/**
 * Sends the 2fa method reconfiguration link on user's email id.
 *
 * @param string  $email User's email id.
 * @param integer $user_id User id of the user.
 * @param string  $method Name of the 2fa method.
 * @return void
 */
function send_reconfiguration_on_email( $email, $user_id, $method ) {
	global $mo2f_dir_name,$image_path;
	$method                = MO2f_Utility::mo2f_decode_2_factor( $method, 'server' );
	$method                = strval( $method );
	$reconfiguraion_method = hash( 'sha512', $method );
	update_site_option( $reconfiguraion_method, $method );
	$txid = bin2hex( openssl_random_pseudo_bytes( 32 ) );
	update_site_option( $txid, get_current_user_id() );
	update_user_meta( $user_id, 'mo2f_EV_txid', $txid );
	$subject = '2fa-reconfiguration : Scan QR';
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	update_option( 'mo2fa_reconfiguration_via_email', wp_json_encode( array( $user_id, $email, $method ) ) );
	$path    = plugins_url( DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'qr_over_email.php', dirname( __FILE__ ) ) . '?email=' . $email . '&amp;user_id=' . $user_id;
	$url     = get_site_option( 'siteurl' ) . '/wp-login.php?';
	$path    = $url . '&amp;reconfigureMethod=' . $reconfiguraion_method . '&amp;transactionId=' . $txid;
	$message = '
    <table>
    <tbody>
    <tr>
    <td>
    <table cellpadding="24" width="584px" style="margin:0 auto;max-width:584px;background-color:#f6f4f4;border:1px solid #a8adad">
    <tbody>
    <tr>
    <td><img src="' . $image_path . 'includes/images/xecurify-logo.png" alt="Xecurify" style="color:#5fb336;text-decoration:none;display:block;width:auto;height:auto;max-height:35px" class="CToWUd"></td>
    </tr>
    </tbody>
    </table>
    <table cellpadding="24" style="background:#fff;border:1px solid #a8adad;width:584px;border-top:none;color:#4d4b48;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:18px">
    <tbody>
    <tr>
    <td>
    <input type="hidden" name="user_id" id="user_id" value="' . esc_attr( $user_id ) . '">
    <input type="hidden" name="email" id="email" value="' . esc_attr( $email ) . '">
    <p style="margin-top:0;margin-bottom:20px">Dear Customer,</p>
    <p style="margin-top:0;margin-bottom:10px">Please scan the QR code from given link to set <b>2FA method</b>:</p>
    <p><a href="' . esc_url( $path ) . '" > Click to reconfigure 2nd factor</a></p>
    <p style="margin-top:0;margin-bottom:15px">Thank you,<br>miniOrange Team</p>
    <p style="margin-top:0;margin-bottom:0px;font-size:11px">Disclaimer: This email and any files transmitted with it are confidential and intended solely for the use of the individual or entity to whom they are addressed.</p>
    </div></div></td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>';
	$result  = wp_mail( $email, $subject, $message, $headers );
	if ( $result ) {
		update_site_option( 'mo2f_message', 'A OTP has been sent to you on <b> ' . esc_html( $email ) . '</b>. ' . Mo2fConstants::lang_translate( 'ACCEPT_LINK_TO_VERIFY_EMAIL' ) );
		$arr = array(
			'status'  => 'SUCCESS',
			'message' => 'Successfully validated.',
			'txid'    => '',
		);

	} else {
		$arr = array(
			'status'  => 'FAILED',
			'message' => 'TEST FAILED.',
		);
		update_site_option( 'mo2f_message', Mo2fConstants::lang_translate( 'ERROR_DURING_PROCESS_EMAIL' ) );
	}
	$content = wp_json_encode( $arr );
}

