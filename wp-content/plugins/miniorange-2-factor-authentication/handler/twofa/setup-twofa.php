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
 * @package        miniorange-2-factor-authentication/handler/twofa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * This library is miniOrange Authentication Service.
 * Contains Request Calls to Customer service.
 */
	$setup_dir_name = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'setup' . DIRECTORY_SEPARATOR;
	$test_dir_name  = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR;
	require_once $setup_dir_name . 'setup-google-authenticator.php';
	require_once $setup_dir_name . 'setup-google-authenticator-onpremise.php';
	require_once $setup_dir_name . 'setup-authy-authenticator.php';
	require_once $setup_dir_name . 'setup-kba-questions.php';
	require_once $setup_dir_name . 'setup-miniorange-authenticator.php';
	require_once $setup_dir_name . 'setup-otp-over-sms.php';
	require_once $setup_dir_name . 'setup-otp-over-telegram.php';
	require_once $setup_dir_name . 'setup-duo-authenticator.php';
	require_once $test_dir_name . 'test-twofa-email-verification.php';
	require_once $test_dir_name . 'test-twofa-duo-authenticator.php';
	require_once $test_dir_name . 'test-twofa-google-authy-authenticator.php';
	require_once $test_dir_name . 'test-twofa-miniorange-qrcode-authentication.php';
	require_once $test_dir_name . 'test-twofa-kba-questions.php';
	require_once $test_dir_name . 'test-twofa-miniorange-push-notification.php';
	require_once $test_dir_name . 'test-twofa-miniorange-soft-token.php';
	require_once $test_dir_name . 'test-twofa-otp-over-sms.php';
	require_once $test_dir_name . 'test-twofa-otp-over-telegram.php';

/**
 * It is use to decode the 2fa methods.
 *
 * @param string $selected_2_factor_method It is carry the selected 2fa method.
 * @param string $decode_type It is carry the decode type .
 * @return string
 */
function mo2f_decode_2_factor( $selected_2_factor_method, $decode_type ) {

	if ( 'NONE' === $selected_2_factor_method ) {
		return $selected_2_factor_method;
	} elseif ( 'OTP Over Email' === $selected_2_factor_method ) {
		$selected_2_factor_method = 'EMAIL';
	}

	$wpdb_2fa_methods = array(
		'miniOrangeQRCodeAuthentication' => 'miniOrange QR Code Authentication',
		'miniOrangeSoftToken'            => 'miniOrange Soft Token',
		'miniOrangePushNotification'     => 'miniOrange Push Notification',
		'GoogleAuthenticator'            => 'Google Authenticator',
		'AuthyAuthenticator'             => 'Authy Authenticator',
		'SecurityQuestions'              => 'Security Questions',
		'EmailVerification'              => 'Email Verification',
		'OTPOverSMS'                     => 'OTP Over SMS',
		'OTPOverEmail'                   => 'OTP Over Email',
		'EMAIL'                          => 'OTP Over Email',
	);

	$server_2fa_methods = array(
		'miniOrange QR Code Authentication' => 'MOBILE AUTHENTICATION',
		'miniOrange Soft Token'             => 'SOFT TOKEN',
		'miniOrange Push Notification'      => 'PUSH NOTIFICATIONS',
		'Google Authenticator'              => 'GOOGLE AUTHENTICATOR',
		'Authy Authenticator'               => 'GOOGLE AUTHENTICATOR',
		'Security Questions'                => 'KBA',
		'Email Verification'                => 'OUT OF BAND EMAIL',
		'OTP Over SMS'                      => 'SMS',
		'EMAIL'                             => 'OTP Over Email',
		'OTPOverEmail'                      => 'OTP Over Email',
	);

	$server_to_wpdb_2fa_methods = array(
		'MOBILE AUTHENTICATION' => 'miniOrange QR Code Authentication',
		'SOFT TOKEN'            => 'miniOrange Soft Token',
		'PUSH NOTIFICATIONS'    => 'miniOrange Push Notification',
		'GOOGLE AUTHENTICATOR'  => 'Google Authenticator',
		'KBA'                   => 'Security Questions',
		'OUT OF BAND EMAIL'     => 'Email Verification',
		'SMS'                   => 'OTP Over SMS',
		'EMAIL'                 => 'OTP Over Email',
		'OTPOverEmail'          => 'OTP Over Email',
		'OTP OVER EMAIL'        => 'OTP Over Email',
	);
	$methodname                 = '';
	if ( 'wpdb' === $decode_type ) {
		$methodname = isset( $wpdb_2fa_methods[ $selected_2_factor_method ] ) ? $wpdb_2fa_methods[ $selected_2_factor_method ] : $selected_2_factor_method;
	} elseif ( 'server' === $decode_type ) {
		$methodname = isset( $server_2fa_methods[ $selected_2_factor_method ] ) ? $server_2fa_methods[ $selected_2_factor_method ] : $selected_2_factor_method;
	} else {
		$methodname = isset( $server_to_wpdb_2fa_methods[ $selected_2_factor_method ] ) ? $server_to_wpdb_2fa_methods[ $selected_2_factor_method ] : $selected_2_factor_method;
	}
	return $methodname;

}

/**
 * It is help to create 2fa form
 *
 * @param object $user It will carry the user .
 * @param string $category It will carry the category .
 * @param array  $auth_methods It will carry the auth methods .
 * @param string $can_display_admin_features .
 */
function mo2f_create_2fa_form( $user, $category, $auth_methods, $can_display_admin_features = '' ) {
	global $mo2fdb_queries;

	$miniorange_authenticator        = array(
		'miniOrange QR Code Authentication',
		'miniOrange Soft Token',
		'miniOrange Push Notification',
	);
	$all_two_factor_methods          = array(
		'miniOrange Authenticator',
		'Google Authenticator',
		'Security Questions',
		'OTP Over SMS',
		'OTP Over Email',
		'OTP Over Telegram',
		'Duo Authenticator',
		'Authy Authenticator',
		'Email Verification',
		'OTP Over SMS and Email',
		'Hardware Token',
	);
	$two_factor_methods_descriptions = array(
		''                             => '<b>All methods in the FREE Plan in addition to the following methods.</b>',
		'miniOrange Authenticator'     => 'Scan the QR code from the account in your miniOrange Authenticator App to login.',
		'miniOrange Soft Token'        => 'Use One Time Password / Soft Token shown in the miniOrange Authenticator App',
		'miniOrange Push Notification' => 'A Push notification will be sent to the miniOrange Authenticator App for your account,
		 Accept it to log in',
		'Google Authenticator'         => 'Use One Time Password shown in <b>Google/Authy/Microsoft Authenticator App</b> to login',
		'Security Questions'           => 'Configure and Answer Three Security Questions to login',
		'OTP Over SMS'                 => 'A One Time Passcode (OTP) will be sent to your Phone number',
		'OTP Over Email'               => 'A One Time Passcode (OTP) will be sent to your Email address',
		'Authy Authenticator'          => 'Enter Soft Token/ One Time Password from the Authy Authenticator App',
		'Email Verification'           => 'Accept the verification link sent to your email address',
		'OTP Over SMS and Email'       => 'A One Time Passcode (OTP) will be sent to your Phone number and Email address',
		'Hardware Token'               => 'Enter the One Time Passcode on your Hardware Token',
		'OTP Over Whatsapp'            => 'Enter the One Time Passcode sent to your WhatsApp account.',
		'OTP Over Telegram'            => 'Enter the One Time Passcode sent to your Telegram account',
		'Duo Authenticator'            => 'A Push notification will be sent to the Duo Authenticator App',
	);
	$two_factor_methods_doc          = array(
		'Security Questions'           => MoWpnsConstants::KBA_DOCUMENT_LINK,
		'Google Authenticator'         => MoWpnsConstants::GA_DOCUMENT_LINK,
		'Email Verification'           => MoWpnsConstants::EMAIL_VERIFICATION_DOCUMENT_LINK,
		'miniOrange Soft Token'        => MoWpnsConstants::MO_TOTP_DOCUMENT_LINK,
		'miniOrange Push Notification' => MoWpnsConstants::MO_PUSHNOTIFICATION_DOCUMENT_LINK,
		'Authy Authenticator'          => '',
		'OTP Over SMS'                 => MoWpnsConstants::OTP_OVER_SMS_DOCUMENT_LINK,
		'OTP Over Email'               => MoWpnsConstants::OTP_OVER_EMAIL_DOCUMENT_LINK,
		'OTP Over SMS and Email'       => '',
		'Hardware Token'               => '',
		'OTP Over Whatsapp'            => MoWpnsConstants::OTP_OVER_WA_DOCUMENT_LINK,
		'OTP Over Telegram'            => MoWpnsConstants::OTP_OVER_TELEGRAM_DOCUMENT_LINK,
	);
	$two_factor_methods_video        = array(
		'Security Questions'           => MoWpnsConstants::KBA_YOUTUBE,
		'Google Authenticator'         => MoWpnsConstants::GA_YOUTUBE,
		'miniOrange Authenticator'     => MoWpnsConstants::MO_AUTHENTICATOR_YOUTUBE,
		'Email Verification'           => MoWpnsConstants::EMAIL_VERIFICATION_YOUTUBE,
		'miniOrange Soft Token'        => MoWpnsConstants::MO_TOTP_YOUTUBE,
		'miniOrange Push Notification' => MoWpnsConstants::MO_PUSH_NOTIFICATION_YOUTUBE,
		'Authy Authenticator'          => MoWpnsConstants::AUTHY_AUTHENTICATOR_YOUTUBE,
		'OTP Over SMS'                 => MoWpnsConstants::OTP_OVER_SMS_YOUTUBE,
		'OTP Over Email'               => '',
		'OTP Over SMS and Email'       => '',
		'Hardware Token'               => '',
		'Duo Authenticator'            => MoWpnsConstants::DUO_AUTHENTICATOR_YOUTUBE,
		'OTP Over Telegram'            => MoWpnsConstants::OTP_OVER_TELEGRAM_YOUTUBE,
	);

	$two_factor_methods_ec = array_slice( $all_two_factor_methods, 0, 10 );
	$two_factor_methods_nc = array_slice( $all_two_factor_methods, 0, 9 );
	if ( MO2F_IS_ONPREM || 'free_plan' !== $category ) {
		$all_two_factor_methods          = array(
			'Security Questions',
			'Google Authenticator',
			'Email Verification',
			'miniOrange Authenticator',
			'Authy Authenticator',
			'OTP Over SMS',
			'OTP Over Email',
			'OTP Over SMS and Email',
			'Hardware Token',
			'OTP Over Whatsapp',
			'OTP Over Telegram',
			'Duo Authenticator',
		);
		$two_factor_methods_descriptions = array(
			''                                  => '<b>All methods in the FREE Plan in addition to the following methods.</b>',
			'miniOrange QR Code Authentication' => 'A QR Code will be displayed in the miniOrange Authenticator App for your account,
		scan it to log in',
			'miniOrange Authenticator'          => 'Supports methods like soft token, QR code Authentication, Push Notification',
			'miniOrange Push Notification'      => 'A Push notification will be sent to the miniOrange Authenticator App for your account,
		 Accept it to log in',
			'Google Authenticator'              => 'Use One Time Password shown in <b>Google/Authy/Microsoft Authenticator App</b> to login',
			'Security Questions'                => 'Configure and Answer Three Security Questions to login',
			'OTP Over SMS'                      => 'A One Time Passcode (OTP) will be sent to your Phone number',
			'OTP Over Email'                    => 'A One Time Passcode (OTP) will be sent to your Email address',
			'Authy Authenticator'               => 'Enter Soft Token/ One Time Password from the Authy Authenticator App',
			'Email Verification'                => 'Accept the verification link sent to your email address',
			'OTP Over SMS and Email'            => 'A One Time Passcode (OTP) will be sent to your Phone number and Email address',
			'Hardware Token'                    => 'Enter the One Time Passcode on your Hardware Token',
			'OTP Over Whatsapp'                 => 'Enter the One Time Passcode sent to your WhatsApp account.',
			'OTP Over Telegram'                 => 'Enter the One Time Passcode sent to your Telegram account',
			'Duo Authenticator'                 => 'A Push notification will be sent to the Duo Authenticator App',
		);
	}

	$is_customer_registered        = 'SUCCESS' === $mo2fdb_queries->get_user_detail( 'user_registration_with_miniorange', $user->ID ) ? true : false;
	$can_user_configure_2fa_method = $can_display_admin_features || ( ! $can_display_admin_features && $is_customer_registered );
	$is_nc                         = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
	$is_ec                         = ! $is_nc;

	echo '<div class="overlay1" id="overlay" hidden ></div>';
	echo '<form name="f" method="post" action="" id="mo2f_save_' . esc_attr( $category ) . '_auth_methods_form">
                        <div id="mo2f_' . esc_attr( $category ) . '_auth_methods" >
                            <br>
                            <table class="mo2f_auth_methods_table">';

	$configured_auth_method     = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
	$selected_miniorange_method = false;
	if ( in_array( $configured_auth_method, $miniorange_authenticator, true ) ) {
		$selected_miniorange_method = true;
	}
	$len = count( $auth_methods );
	for ( $i = 0; $i < $len; $i ++ ) {

		echo '<tr>';
		$index = count( $auth_methods[ $i ] );
		for ( $j = 0; $j < $index; $j ++ ) {
			$auth_method             = $auth_methods[ $i ][ $j ];
			$auth_method_abr         = str_replace( ' ', '', $auth_method );
			$configured_auth_method  = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
			$is_auth_method_selected = ( $auth_method === $configured_auth_method ? true : false );
			if ( 'miniOrange Authenticator' === $auth_method && $selected_miniorange_method ) {
				$is_auth_method_selected = true;
			}
			$is_auth_method_av = false;
			if ( ( $is_ec && in_array( $auth_method, $two_factor_methods_ec, true ) ) || ( $is_nc && in_array( $auth_method, $two_factor_methods_nc, true ) ) ) {
				$is_auth_method_av = true;
			}

			$thumbnail_height = $is_auth_method_av && 'free_plan' === $category ? 190 : 160;
			$is_image         = empty( $auth_method ) ? 0 : 1;

			echo '<td class="mo2f_column">
                        <div class="mo2f_thumbnail" id="' . esc_attr( $auth_method_abr ) . '_thumbnail_2_factor" style="height:' . esc_attr( $thumbnail_height ) . 'px; ';
			if ( MO2F_IS_ONPREM ) {
				$iscurrent_method = 0;
				$current_method   = $configured_auth_method;
				if ( $auth_method === $current_method ) {
					$iscurrent_method = 1;
				}

				echo $iscurrent_method ? '#07b52a' : 'var(--mo2f-theme-blue)';
				echo $iscurrent_method ? '#07b52a' : 'var(--mo2f-theme-blue)';
				echo ';">';
			} else {
				echo $is_auth_method_selected ? '#07b52a' : 'var(--mo2f-theme-blue)';
				echo $is_auth_method_selected ? '#07b52a' : 'var(--mo2f-theme-blue)';
				echo ';">';

			}
						echo '<div>
			                    <div class="mo2f_thumbnail_method" style="width:100%";>
			                        <div style="width: 17%; float:left;padding-top:20px;padding-left:20px;">';

			if ( $is_image ) {
				echo '<img src="' . esc_url( plugins_url( 'includes/images/authmethods/' . $auth_method_abr . '.png', dirname( dirname( __FILE__ ) ) ) ) . '" style="width: 50px;height: 50px !important; line-height: 80px; border-radius:10px; overflow:hidden" />';
			}

			echo '</div>
                        <div class="mo2f_thumbnail_method_desc" style="width: 75%;">';
			switch ( $auth_method ) {
				case 'Google Authenticator':
						echo '   <span style="float:right">
				         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
				         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
				         	</a>
				         	<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>
				         </span>';
					break;

				case 'Security Questions':
						echo '   <span style="float:right">
				         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
				         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
				           	</a>
				           	<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>

				  
				         </span>';
					break;

				case 'OTP Over SMS':
					echo '   <span style="float:right">
				         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
				         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
				         	
				         	</a>
				         	<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>
				         
				         </span>';
					break;

				case 'miniOrange Soft Token':
					echo '   <span style="float:right">
				         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
				         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
				         	
				         	</a>

				         	<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>
				         </span>';

					break;

				case 'miniOrange Authenticator':
					echo '   <span style="float:right">';
					if ( isset( $two_factor_methods_doc[ $auth_method ] ) ) {
						echo '<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
				         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
				         	</a>';
					}

					if ( isset( $two_factor_methods_video[ $auth_method ] ) ) {
						echo '<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;margin-right: 5px;"></span>
				         	</a>';
					}

									echo '</span>';
					break;

				case 'miniOrange QR Code Authentication':
					echo '   <span style="float:right">
				         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
				         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
				         	
				         	</a>
				         	<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>
				         	
				         </span>';

					break;

				case 'miniOrange Push Notification':
					echo '   <span style="float:right">
				         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
				         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
				         	
				         	</a>
				         	<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>
				         	
				         </span>';
					break;

				case 'Email Verification':
					echo '   <span style="float:right">
				         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
				         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
				         	
				         	</a>
				         	<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>
				         	
				         </span>';
					break;
				case 'OTP Over Telegram':
					echo '   <span style="float:right">
			         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
			         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
			           	</a>
						<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
						<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
						</a>
			        	</span>';
					break;
				case 'OTP Over Email':
					echo '   <span style="float:right">
			         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
			         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
			           	</a>
			           
			        	</span>';
					break;
				case 'Duo Authenticator':
					echo '   <span style="float:right">
			         		<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>
			           
			        	</span>';
					break;
				case 'OTP Over Whatsapp':
					echo '   <span style="float:right">
			         	<a href=' . esc_url( $two_factor_methods_doc[ $auth_method ] ) . ' target="_blank">
			         	<span title="View Setup Guide" class="dashicons dashicons-text-page" style="font-size:19px;color:#413c69;float: right;"></span>
			           	</a>
			           
			        	</span>';
					break;
				case 'Authy Authenticator':
					echo '   <span style="float:right">
			         	<a href=' . esc_url( $two_factor_methods_video[ $auth_method ] ) . ' target="_blank">
				         	<span title="Watch Setup Video" class="dashicons dashicons-video-alt3" style="font-size:18px;color:red;float: right;    margin-right: 5px;"></span>
				         	</a>
			           
			        	</span>';
					break;
				default:
					echo '';
					break;
			}
			echo ' <b>' . esc_html( $auth_method ) .
					'</b><br>
                        <p style="padding:0px; padding-left:0px;font-size: 14px;"> ' . wp_kses_post( $two_factor_methods_descriptions[ $auth_method ] ) . '</p>
                        
                        </div>
                        </div>
                        </div>';

			if ( $is_auth_method_av && 'free_plan' === $category ) {
				$is_auth_method_configured = 0;
				if ( 'miniOrangeAuthenticator' === $auth_method_abr ) {
					$is_auth_method_configured = $mo2fdb_queries->get_user_detail( 'mo2f_miniOrangeSoftToken_config_status', $user->ID );
				} else {
					$is_auth_method_configured = $mo2fdb_queries->get_user_detail( 'mo2f_' . $auth_method_abr . '_config_status', $user->ID );
				}
				if ( ( 'OUT OF BAND EMAIL' === $auth_method || 'OTP Over Email' === $auth_method ) && ! MO2F_IS_ONPREM ) {
					$is_auth_method_configured = 1;
				}
				$chat_id = get_user_meta( $user->ID, 'mo2f_chat_id', true );
				echo '<div style="height:40px;width:100%;position: absolute;bottom: 0;background-color:';
				$iscurrent_method = 0;
				if ( MO2F_IS_ONPREM ) {
					$current_method = $configured_auth_method;
					if ( $auth_method === $current_method || ( 'miniOrange Authenticator' === $auth_method && $selected_miniorange_method ) ) {
						$iscurrent_method = 1;
					}
					echo $iscurrent_method ? '#07b52a' : 'var(--mo2f-theme-blue)';
				} else {
					echo $is_auth_method_selected ? '#07b52a' : 'var(--mo2f-theme-blue)';
				}
				if ( MO2F_IS_ONPREM ) {
					$twofactor_transactions = new Mo2fDB();
					$exceeded               = $twofactor_transactions->check_alluser_limit_exceeded( $user->ID );
					if ( $exceeded ) {
						if ( empty( $configured_auth_method ) ) {
							$can_user_configure_2fa_method = false;
						} else {
							$can_user_configure_2fa_method = true;
						}
					} else {
						$can_user_configure_2fa_method = true;
					}
					$is_customer_registered = true;
					$user                   = wp_get_current_user();
					echo ';color:white">';

					$check = $is_customer_registered ? true : false;
					$show  = 0;

					$cloud_methods = array( 'miniOrange Authenticator', 'miniOrange Soft Token', 'miniOrange Push Notification' );

					if ( 'Email Verification' === $auth_method || 'Security Questions' === $auth_method || 'Google Authenticator' === $auth_method || 'miniOrange Authenticator' === $auth_method || 'OTP Over SMS' === $auth_method || 'OTP Over Email' === $auth_method || 'OTP Over Telegram' === $auth_method || 'Duo Authenticator' === $auth_method ) {
						$show = 1;
					}

					if ( $check ) {
						echo '<div class="mo2f_configure_2_factor">
	                              <button type="button" id="' . esc_attr( $auth_method_abr ) . '_configuration" class="mo2f_configure_set_2_factor" onclick="configureOrSet2ndFactor_' . esc_attr( $category ) . '(\'' . esc_js( $auth_method_abr ) . '\', \'configure2factor\');"';
						echo 1 === $show ? '' : ' disabled ';
						echo '>';
						if ( $show ) {
							echo $is_auth_method_configured ? 'Reconfigure' : 'Configure';
						} else {
							echo 'Available in cloud solution';
						}
						echo '</button></div>';
					}
					if ( ( $is_auth_method_configured && ! $is_auth_method_selected ) || MO2F_IS_ONPREM ) {
						echo '<div class="mo2f_set_2_factor">
	                               <button type="button" id="' . esc_attr( $auth_method_abr ) . '_set_2_factor" class="mo2f_configure_set_2_factor" onclick="configureOrSet2ndFactor_' . esc_attr( $category ) . '(\'' . esc_js( $auth_method_abr ) . '\', \'select2factor\');"';
						echo $can_user_configure_2fa_method ? '' : ' disabled ';
						echo 1 === $show ? '' : ' disabled ';
						if ( 1 === $show && $is_auth_method_configured && 0 === $iscurrent_method ) {
							echo '>Set as 2-factor</button>
		                              </div>';
						} else {
							echo '
	                    	</button>
	                              </div>';
						}
					}
				} else {
					if ( get_option( 'mo2f_miniorange_admin' ) ) {
						$allowed = get_option( 'mo2f_miniorange_admin' ) === wp_get_current_user()->ID;
					} else {
						$allowed = 1;
					}
					$cloudswitch = 0;
					if ( ! $allowed ) {
						$allowed = 2;
					}
					echo ';color:white">';
					$check                     = ! $is_customer_registered ? true : ( 'Email Verification' !== $auth_method && 'OTP Over Email' === $auth_method ? true : false );
					$is_auth_method_configured = ! $is_customer_registered ? 0 : 1;
					if ( ! MO2F_IS_ONPREM && ( 'Email Verification' === $auth_method || 'OTP Over Email' === $auth_method ) ) {
						$check = 0;
					}
					if ( $check ) {
						echo '<div class="mo2f_configure_2_factor">
	                              <button type="button" id="' . esc_attr( $auth_method_abr ) . '_configuration" class="mo2f_configure_set_2_factor" onclick="configureOrSet2ndFactor_' . esc_attr( $category ) . '(\'' . esc_js( $auth_method_abr ) . '\', \'configure2factor\',' . esc_js( $cloudswitch ) . ',' . esc_js( $allowed ) . ');"';
						echo $can_user_configure_2fa_method ? '' : '  ';
						echo '>';
						echo $is_auth_method_configured ? 'Reconfigure' : 'Configure';
						echo '</button></div>';
					}

					if ( ( $is_auth_method_configured && ! $is_auth_method_selected ) || MO2F_IS_ONPREM ) {
						echo '<div class="mo2f_set_2_factor">
	                               <button type="button" id="' . esc_attr( $auth_method_abr ) . '_set_2_factor" class="mo2f_configure_set_2_factor" onclick="configureOrSet2ndFactor_' . esc_attr( $category ) . '(\'' . esc_js( $auth_method_abr ) . '\', \'select2factor\',' . esc_js( $cloudswitch ) . ',' . esc_js( $allowed ) . ');"';
						echo $can_user_configure_2fa_method ? '' : '  ';
						echo '>Set as 2-factor</button>
	                              </div>';
					}
				}
				if ( $is_auth_method_selected && 'miniOrange Authenticator' === $auth_method ) {
						echo '<select name="mo2fa_MO_methods" id="mo2fa_MO_methods" class="mo2f_set_2_factor mo2f_configure_switch_2_factor mo2f_kba_ques" style="color: white;font-weight: 700;background: #48b74b;background-size: 16px 16px;border: 1px solid #48b74b;padding: 0px 0px 0px 17px;min-height: 30px;max-width: 9em;max-width: 9em;" onchange="show_3_minorange_methods();">
							      <option value="" selected disabled hidden style="color:white!important;">Switch to >></option>
							      <option value="miniOrangeSoftToken">Soft Token</option>
							      <option value="miniOrangeQRCodeAuthentication">QR Code</option>
							      <option value="miniOrangePushNotification">Push Notification</option>
							  </select></div>
							  <br><br>

							  ';
				}
					echo '</div>';
			}
			echo '</div></div></td>';
		}

		echo '</tr>';
	}

	echo '</table>';
	if ( 'free_plan' !== $category ) {
		if ( current_user_can( 'administrator' ) ) {
			echo '<div class="mo2f_premium_footer">
                            <p style="font-size:16px;margin-left: 1%">In addition to these authentication methods, for other features in this plan, <a href="admin.php?page=mo_2fa_upgrade"><i>Click here.</i></a></p>
                 </div>';
		}
	}
	$configured_auth_method_abr = str_replace( ' ', '', $configured_auth_method );
	echo '</div> <input type="hidden" name="miniorange_save_form_auth_methods_nonce"
                   value="' . esc_attr( wp_create_nonce( 'miniorange-save-form-auth-methods-nonce' ) ) . '"/>
                <input type="hidden" name="option" value="mo2f_save_' . esc_attr( $category ) . '_auth_methods" />
                <input type="hidden" name="mo2f_configured_2FA_method_' . esc_attr( $category ) . '" id="mo2f_configured_2FA_method_' . esc_attr( $category ) . '" />
                <input type="hidden" name="mo2f_selected_action_' . esc_attr( $category ) . '" id="mo2f_selected_action_' . esc_attr( $category ) . '" />
                </form><script>
                var selected_miniorange_method = "' . esc_attr( $selected_miniorange_method ) . '";
                if(selected_miniorange_method)
                	jQuery("<input>").attr({type: "hidden",id: "miniOrangeAuthenticator",value: "' . esc_attr( $configured_auth_method_abr ) . '"}).appendTo("form");
                else                	
                	jQuery("<input>").attr({type: "hidden",id: "miniOrangeAuthenticator",value: "miniOrangeSoftToken"}).appendTo("form");
                </script>';
}

/**
 * It will use to activate Second factor
 *
 * @param object $user It will carry the user .
 * @return string
 */
function mo2f_get_activated_second_factor( $user ) {

	global $mo2fdb_queries;
	$user_registration_status = $mo2fdb_queries->get_user_detail( 'mo_2factor_user_registration_status', $user->ID );
	$is_customer_registered   = 'SUCCESS' === $mo2fdb_queries->get_user_detail( 'user_registration_with_miniorange', $user->ID ) ? true : false;
	$useremail                = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user->ID );

	if ( 'MO_2_FACTOR_SUCCESS' === $user_registration_status ) {
		// checking this option for existing users.
		$mo2fdb_queries->update_user_details( $user->ID, array( 'mobile_registration_status' => true ) );
		$mo2f_second_factor = 'MOBILE AUTHENTICATION';

		return $mo2f_second_factor;
	} elseif ( 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR' === $user_registration_status ) {
		return 'NONE';
	} else {
		if ( 'MO_2_FACTOR_PLUGIN_SETTINGS' === $user_registration_status && $is_customer_registered ) {
			$enduser  = new Two_Factor_Setup();
			$userinfo = json_decode( $enduser->mo2f_get_userinfo( $useremail ), true );
			if ( JSON_ERROR_NONE === json_last_error() ) {
				if ( 'ERROR' === $userinfo['status'] ) {
					update_option( 'mo2f_message', Mo2fConstants::lang_translate( $userinfo['message'] ) );
					$mo2f_second_factor = 'NONE';
				} elseif ( 'SUCCESS' === $userinfo['status'] ) {
					$mo2f_second_factor = mo2f_update_and_sync_user_two_factor( $user->ID, $userinfo );
				} elseif ( 'FAILED' === $userinfo['status'] ) {
					$mo2f_second_factor = 'NONE';
					update_option( 'mo2f_message', Mo2fConstants::lang_translate( 'ACCOUNT_REMOVED' ) );
				} else {
					$mo2f_second_factor = 'NONE';
				}
			} else {
				update_option( 'mo2f_message', Mo2fConstants::lang_translate( 'INVALID_REQ' ) );
				$mo2f_second_factor = 'NONE';
			}
		} else {
			$mo2f_second_factor = 'NONE';
		}

		return $mo2f_second_factor;
	}
}
/**
 * It will update and sync the two factor settings
 *
 * @param string $user_id It will carry the user id .
 * @param object $userinfo It will carry the user info .
 * @return string
 */
function mo2f_update_and_sync_user_two_factor( $user_id, $userinfo ) {
	global $mo2fdb_queries;
	$mo2f_second_factor = isset( $userinfo['authType'] ) && ! empty( $userinfo['authType'] ) ? $userinfo['authType'] : 'NONE';
	if ( MO2F_IS_ONPREM ) {
		$mo2f_second_factor = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2FA_method', $user_id );
		$mo2f_second_factor = $mo2f_second_factor ? $mo2f_second_factor : 'NONE';
		return $mo2f_second_factor;
	}

	$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_user_email' => $userinfo['email'] ) );
	if ( 'OUT OF BAND EMAIL' === $mo2f_second_factor ) {
		$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_EmailVerification_config_status' => true ) );
	} elseif ( 'SMS' === $mo2f_second_factor && ! MO2F_IS_ONPREM ) {
		$phone_num = isset( $userinfo['phone'] ) ? sanitize_text_field( $userinfo['phone'] ) : '';
		$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_OTPOverSMS_config_status' => true ) );
		$_SESSION['user_phone'] = $phone_num;
	} elseif ( in_array(
		$mo2f_second_factor,
		array(
			'SOFT TOKEN',
			'MOBILE AUTHENTICATION',
			'PUSH NOTIFICATIONS',
		),
		true
	) ) {
		if ( ! MO2F_IS_ONPREM ) {
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_miniOrangeSoftToken_config_status' => true,
					'mo2f_miniOrangeQRCodeAuthentication_config_status' => true,
					'mo2f_miniOrangePushNotification_config_status' => true,
				)
			);
		}
	} elseif ( 'KBA' === $mo2f_second_factor ) {
		$mo2fdb_queries->update_user_details( $user_id, array( 'mo2f_SecurityQuestions_config_status' => true ) );
	} elseif ( 'GOOGLE AUTHENTICATOR' === $mo2f_second_factor ) {
		$app_type = get_user_meta( $user_id, 'mo2f_external_app_type', true );

		if ( 'Google Authenticator' === $app_type ) {
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_GoogleAuthenticator_config_status' => true,
				)
			);
			update_user_meta( $user_id, 'mo2f_external_app_type', 'Google Authenticator' );
		} elseif ( 'Authy Authenticator' === $app_type ) {
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_AuthyAuthenticator_config_status' => true,
				)
			);
			update_user_meta( $user_id, 'mo2f_external_app_type', 'Authy Authenticator' );
		} else {
			$mo2fdb_queries->update_user_details(
				$user_id,
				array(
					'mo2f_GoogleAuthenticator_config_status' => true,
				)
			);

			update_user_meta( $user_id, 'mo2f_external_app_type', 'Google Authenticator' );
		}
	}

	return $mo2f_second_factor;
}
/**
 * It will help to display the customer registration
 *
 * @param object $user It will to show the.
 * @return void
 */
function display_customer_registration_forms( $user ) {

	global $mo2fdb_queries;
	$mo2f_current_registration_status = get_option( 'mo_2factor_user_registration_status' );
	$mo2f_message                     = get_option( 'mo2f_message' );
	?>

	<div id="smsAlertModal" class="modal" role="dialog" data-backdrop="static" data-keyboard="false" >
		<div class="mo2f_modal-dialog" style="margin-left:30%;">
			<div class="modal-content">
				<div class="mo2f_modal-header">
					<h2 class="mo2f_modal-title">You are just one step away from setting up 2FA.</h2><span type="button" id="mo2f_registration_closed" class="modal-span-close" data-dismiss="modal">&times;</span>
				</div>
				<div class="mo2f_modal-body">
					<span style="color:green;cursor: pointer;float:right;" onclick="show_content();">Why Register with miniOrange?</span><br>
				<div id="mo2f_register" style="background-color:#f1f1f1;padding: 1px 4px 1px 14px;display: none;">
					<p>miniOrange Two Factor plugin uses highly secure miniOrange APIs to communicate with the plugin. To keep this communication secure, we ask you to register and assign you API keys specific to your account.			This way your account and users can be only accessed by API keys assigned to you. Also, you can use the same account on multiple applications and your users do not have to maintain multiple accounts or 2-factors.</p>
				</div>
					<?php if ( $mo2f_message ) { ?>
						<div style="padding:5px;">
							<div class="alert alert-info" style="margin-bottom:0px;padding:3px;">
								<p style="font-size:15px;margin-left: 2%;"><?php wp_kses( $mo2f_message, array( 'b' => array() ) ); ?></p>
							</div>
						</div>
						<?php
					}
					if ( in_array( $mo2f_current_registration_status, array( 'REGISTRATION_STARTED', 'MO_2_FACTOR_OTP_DELIVERED_SUCCESS', 'MO_2_FACTOR_OTP_DELIVERED_FAILURE', 'MO_2_FACTOR_VERIFY_CUSTOMER' ), true ) ) {
						mo2f_show_registration_screen( $user );
					}
					?>
				</div>
			</div>
		</div>
		<form name="f" method="post" action="" class="mo2f_registration_closed_form">
			<input type="hidden" name="mo2f_registration_closed_nonce"
							value="<?php echo esc_html( wp_create_nonce( 'mo2f-registration-closed-nonce' ) ); ?>"/>
			<input type="hidden" name="option" value="mo2f_registration_closed"/>
		</form>
	</div>
	<script>
		function show_content() {
			jQuery('#mo2f_register').slideToggle();
		}
		jQuery(function () {
			jQuery('#smsAlertModal').modal();
		});

		jQuery('#mo2f_registration_closed').click(function () {
			jQuery('.mo2f_registration_closed_form').submit();
		});
	</script>

	<?php
	wp_register_script( 'mo2f_bootstrap_js', plugins_url( 'includes/js/bootstrap.min.js', dirname( dirname( __FILE__ ) ) ), array(), MO2F_VERSION, false );
	wp_print_scripts( 'mo2f_bootstrap_js' );
}
/**
 * It will help to show the registration screen
 *
 * @param object $user .
 * @return void
 */
function mo2f_show_registration_screen( $user ) {
	global $mo2f_dir_name;

	include $mo2f_dir_name . 'controllers' . DIRECTORY_SEPARATOR . 'account.php';

}
/**
 * It will help to show the 2fa screen
 *
 * @param object $user .
 * @param string $selected_2fa_method  .
 * @return void
 */
function mo2f_show_2fa_configuration_screen( $user, $selected_2fa_method ) {
	global $mo2f_dir_name;
	switch ( $selected_2fa_method ) {
		case 'Google Authenticator':
			if ( MO2F_IS_ONPREM ) {
				include_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-google-auth-onpremise.php';
				$obj = new Google_auth_onpremise();
				$obj->mo_g_auth_get_details();
			} else {
				if ( ! get_user_meta( $user->ID, 'mo2f_google_auth', true ) ) {
					Miniorange_Authentication::mo2f_get_g_a_parameters( $user );
				}
				echo '<div class="mo2f_table_layout mo2f_table_layout1">';
				mo2f_configure_google_authenticator( $user );
				echo '</div>';
			}
			break;
		case 'Authy Authenticator':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_authy_authenticator( $user );
			echo '</div>';
			break;
		case 'Security Questions':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_for_mobile_suppport_kba( $user );
			echo '</div>';
			break;
		case 'Email Verification':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_for_mobile_suppport_kba( $user );
			echo '</div>';
			break;
		case 'OTP Over SMS':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_otp_over_sms( $user );
			echo '</div>';
			break;
		case 'miniOrange Soft Token':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_miniorange_authenticator( $user );
			echo '</div>';
			break;
		case 'miniOrange QR Code Authentication':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_miniorange_authenticator( $user );
			echo '</div>';
			break;
		case 'miniOrange Push Notification':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_miniorange_authenticator( $user );
			echo '</div>';
			break;
		case 'OTP Over Email':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_test_otp_over_email( $user, $selected_2fa_method );
			echo '</div>';
			break;
		case 'OTP Over Telegram':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_otp_over_Telegram( $user );
			echo '</div>';
			break;
		case 'DuoAuthenticator':
		case 'Duo Authenticator':
			echo '<div class="mo2f_table_layout mo2f_table_layout1">';
			mo2f_configure_duo_authenticator( $user );
			echo '</div>';
			break;
	}

}
/**
 * It will help to show the 2fa test screen
 *
 * @param object $user .
 * @param string $selected_2fa_method .
 * @return void
 */
function mo2f_show_2fa_test_screen( $user, $selected_2fa_method ) {

	switch ( $selected_2fa_method ) {
		case 'miniOrange QR Code Authentication':
			mo2f_test_miniorange_qr_code_authentication( $user );
			break;
		case 'miniOrange Push Notification':
			mo2f_test_miniorange_push_notification( $user );
			break;
		case 'miniOrange Soft Token':
			mo2f_test_miniorange_soft_token( $user );
			break;
		case 'Email Verification':
			mo2f_test_email_verification( $user );
			break;
		case 'OTP Over SMS':
			mo2f_test_otp_over_sms( $user );
			break;
		case 'OTP Over Telegram':
			mo2f_test_otp_over_Telegram( $user );
			break;
		case 'Security Questions':
			mo2f_test_kba_security_questions( $user );
			break;
		case 'OTP Over Email':
			mo2f_test_otp_over_email( $user, $selected_2fa_method );
			break;
		case 'Duo Authenticator':
			mo2f_test_duo_authenticator( $user );
			break;
		default:
			mo2f_test_google_authy_authenticator( $user, $selected_2fa_method );
	}

}
/**
 * It will help to display the name
 *
 * @param object $user .
 * @param string $mo2f_second_factor .
 * @return string
 */
function mo2f_method_display_name( $user, $mo2f_second_factor ) {

	if ( 'GOOGLE AUTHENTICATOR' === $mo2f_second_factor ) {
		$app_type = get_user_meta( $user->ID, 'mo2f_external_app_type', true );

		if ( 'Google Authenticator' === $app_type ) {
			$selected_method = 'Google Authenticator';
		} elseif ( 'Authy Authenticator' === $app_type ) {
			$selected_method = 'Authy Authenticator';
		} else {
			$selected_method = 'Google Authenticator';
			update_user_meta( $user->ID, 'mo2f_external_app_type', $selected_method );
		}
	} else {
		$selected_method = MO2f_Utility::mo2f_decode_2_factor( $mo2f_second_factor, 'servertowpdb' );
	}
	return $selected_method;

}
/**
 * It will help to personalization
 *
 * @param string $mo2f_user_email .
 * @return void
 */
function mo2f_personalization_description( $mo2f_user_email ) {
	?>
	<div id="mo2f_custom_addon">
		<?php if ( get_option( 'mo2f_personalization_installed' ) ) { ?>
			<a href="<?php echo esc_url( admin_url() ); ?>plugins.php" id="mo2f_activate_custom_addon"
					class="button button-primary button-large"
					style="float:right; margin-top:2%;"><?php esc_html_e( 'Activate Plugin', 'miniorange-2-factor-authentication' ); ?></a>
				<?php } ?>
		<?php
		if ( ! get_option( 'mo2f_personalization_purchased' ) ) {
			?>
			<a
						onclick="mo2f_addonform('wp_2fa_addon_shortcode')" id="mo2f_purchase_custom_addon"
						class="button button-primary button-large"
						style="float:right;"><?php esc_html_e( 'Purchase', 'miniorange-2-factor-authentication' ); ?></a>
				<?php } ?>
		<div id="mo2f_custom_addon_hide">						
			<br>
			<div id="mo2f_hide_custom_content">
				<div class="mo2f_box">
					<h3><?php esc_html_e( 'Customize Plugin Icon', 'miniorange-2-factor-authentication' ); ?></h3>
					<hr>
					<p>
						<?php esc_html_e( 'With this feature, you can customize the plugin icon in the dashboard which is useful when you want your custom logo to be displayed to the users.', 'miniorange-2-factor-authentication' ); ?>
					</p>
					<br>
					<h3><?php esc_html_e( 'Customize Plugin Name', 'miniorange-2-factor-authentication' ); ?></h3>
					<hr>
					<p>
						<?php esc_html_e( 'With this feature, you can customize the name of the plugin in the dashboard.', 'miniorange-2-factor-authentication' ); ?>
					</p>

				</div>
				<br>
				<div class="mo2f_box">
					<h3><?php esc_html_e( 'Customize UI of Login Pop up\'s', 'miniorange-2-factor-authentication' ); ?></h3>
					<hr>
					<p>
						<?php esc_html_e( 'With this feature, you can customize the login pop-ups during two factor authentication according to the theme of                 your website.', 'miniorange-2-factor-authentication' ); ?>
					</p>
				</div>

				<br>
				<div class="mo2f_box">
					<h3><?php esc_html_e( 'Custom Email and SMS Templates', 'miniorange-2-factor-authentication' ); ?></h3>
					<hr>

					<p><?php esc_html_e( 'You can change the templates for Email and SMS which user receives during authentication.', 'miniorange-2-factor-authentication' ); ?></p>

				</div>
			</div>
		</div>
		<div id="mo2f_custom_addon_show"><?php $x = apply_filters( 'mo2f_custom', 'custom' ); ?></div> 
	</div> 
	<?php
}
/**
 * It will help add the description of shortcode
 *
 * @param string $mo2f_user_email .
 * @return void
 */
function mo2f_shortcode_description( $mo2f_user_email ) {
	?>
	<div id="mo2f_Shortcode_addon_hide">
		<?php if ( get_option( 'mo2f_shortcode_installed' ) ) { ?>
			<a href="<?php echo esc_url( admin_url() ); ?>plugins.php" id="mo2f_activate_shortcode_addon"
						class="button button-primary button-large" style="float:right; margin-top:2%;">
						<?php
							esc_html_e(
								'Activate
                        Plugin',
								'miniorange-2-factor-authentication'
							);
						?>
																											</a>
		<?php } if ( ! get_option( 'mo2f_shortcode_purchased' ) ) { ?>
				<a onclick="mo2f_addonform('wp_2fa_addon_personalization')" id="mo2f_purchase_shortcode_addon"
						class="button button-primary button-large"
						style="float:right;"><?php esc_html_e( 'Purchase', 'miniorange-2-factor-authentication' ); ?></a>
		<?php } ?>	
	<div id="shortcode" class="description">		
			<br>
			<div id="mo2f_hide_shortcode_content" class="mo2f_box">
				<h3><?php esc_html_e( 'List of Shortcodes', 'miniorange-2-factor-authentication' ); ?>:</h3>
				<hr>
				<ol style="margin-left:2%">
					<li>
						<b><?php esc_html_e( 'Enable Two Factor: ', 'miniorange-2-factor-authentication' ); ?></b> <?php esc_html_e( 'This shortcode provides an option to turn on/off 2-factor by user.', 'miniorange-2-factor-authentication' ); ?>
					</li>
					<li>
						<b><?php esc_html_e( 'Enable Reconfiguration: ', 'miniorange-2-factor-authentication' ); ?></b> <?php esc_html_e( 'This shortcode provides an option to configure the Google Authenticator and Security Questions by user.', 'miniorange-2-factor-authentication' ); ?>
					</li>
					<li>
						<b><?php esc_html_e( 'Enable Remember Device: ', 'miniorange-2-factor-authentication' ); ?></b> <?php esc_html_e( ' This shortcode provides\'Enable Remember Device\' from your custom login form.', 'miniorange-2-factor-authentication' ); ?>
					</li>
				</ol>
			</div>
			<div id="mo2f_Shortcode_addon_show"><?php $x = apply_filters( 'mo2f_shortcode', 'shortcode' ); ?></div>
		</div>
		<br>
	</div>
	<form style="display:none;" id="mo2fa_loginform" action="<?php echo esc_url( MO_HOST_NAME . '/moas/login' ); ?>" target="_blank" method="post">
		<input type="email" name="username" value="<?php echo esc_attr( $mo2f_user_email ); ?>"/>
		<input type="text" name="redirectUrl"
			value="<?php echo esc_url( MO_HOST_NAME . '/moas/initializepayment' ); ?>"/>
		<input type="text" name="requestOrigin" id="requestOrigin"/>
	</form>
	<script>
		function mo2f_addonform(planType) {
			jQuery('#requestOrigin').val(planType);
			jQuery('#mo2fa_loginform').submit();
		}
	</script>
	<?php
}

?>
