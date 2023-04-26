<?php
/**
 * This file contains functions related to login flow.
 *
 * @package miniorange-2-factor-authentication/controllers/twofa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prompts mfa form for users.
 *
 * @param array  $configure_array_method array of methods.
 * @param string $session_id_encrypt encrypted session id.
 * @param string $redirect_to redirect to url.
 * @return void
 */
function mo2fa_prompt_mfa_form_for_user( $configure_array_method, $session_id_encrypt, $redirect_to ) {
	?>
	<html>
			<head>
				<meta charset="utf-8"/>
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<?php
					mo2f_inline_css_and_js();
				?>
			</head>
			<body>
				<div class="mo2f_modal1" tabindex="-1" role="dialog" id="myModal51">
					<div class="mo2f-modal-backdrop"></div>
					<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
						<div class="login mo_customer_validation-modal-content">
							<div class="mo2f_modal-header">
								<h3 class="mo2f_modal-title"><button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close" title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>" onclick="mologinback();"><span aria-hidden="true">&times;</span></button>

								<?php esc_html_e( 'Select 2 Factor method for authentication', 'miniorange-2-factor-authentication' ); ?></h3>
							</div>
							<div class="mo2f_modal-body">
									<?php
									foreach ( $configure_array_method as $key => $value ) {
										echo '<span  >
                                    		<label>
                                    			<input type="radio"  name="mo2f_selected_mfactor_method" class ="mo2f-styled-radio_conf" value="' . esc_html( $value ) . '"/>';
												echo '<span class="mo2f-styled-radio-text_conf">';
												echo esc_html( $value );
											echo ' </span> </label>
                                			<br>
                                			<br>
                                		</span>';

									}
									mo2f_customize_logo();
									?>
							</div>
						</div>
					</div>
				</div>
				<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>" style="display:none;">
					<input type="hidden" name="miniorange_mobile_validation_failed_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
					<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
				</form>
				<form name="f" method="post" action="" id="mo2f_select_mfa_methods_form" style="display:none;">
					<input type="hidden" name="mo2f_selected_mfactor_method" />
					<input type="hidden" name="mo2f_miniorange_2factor_method_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mo2f_miniorange-2factor-method-nonce' ) ); ?>" />
					<input type="hidden" name="option" value="miniorange_mfactor_method" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
					<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
				</form>
			<script>
				function mologinback(){
					jQuery('#mo2f_backto_mo_loginform').submit();
				}
				jQuery('input:radio[name=mo2f_selected_mfactor_method]').click(function() {
					var selectedMethod = jQuery(this).val();
					document.getElementById("mo2f_select_mfa_methods_form").elements[0].value = selectedMethod;
					jQuery('#mo2f_select_mfa_methods_form').submit();
				});				
			</script>
			</body>
		</html>
		<?php
}

/**
 * This function returns user roles.
 *
 * @param object $user object containing user details.
 * @return array
 */
function miniorange_get_user_role( $user ) {
	return $user->roles;
}

/**
 * This function redirect user to given url.
 *
 * @param object $user object containing user details.
 * @param string $redirect_to redirect url.
 * @return void
 */
function redirect_user_to( $user, $redirect_to ) {
	$roles        = $user->roles;
	$current_role = array_shift( $roles );

	if ( is_multisite() ) {
		$blog_id = get_current_blog_id();
		if ( is_super_admin( $user->ID ) ) {

			$redirect_url = isset( $redirect_to ) && ! empty( $redirect_to ) ? $redirect_to : admin_url();

		} elseif ( 'administrator' === $current_role ) {

			$redirect_url = empty( $redirect_to ) ? admin_url() : $redirect_to;

		} else {

			$redirect_url = empty( $redirect_to ) ? home_url() : $redirect_to;
		}
	} else {
		if ( 'administrator' === $current_role ) {
			$redirect_url = empty( $redirect_to ) ? admin_url() : $redirect_to;
		} else {
			$redirect_url = empty( $redirect_to ) ? home_url() : $redirect_to;
		}
	}

	if ( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ) {
		$redirect = array(
			'redirect' => $redirect_url,
		);

			wp_send_json_success( $redirect );
	} else {

		wp_safe_redirect( $redirect_url );
		exit();
	}
}


/**
 * Function checks if 2fa enabled for given user roles (used in shortcode addon)
 *
 * @param array $current_roles array containing roles of user.
 * @return boolean
 */
function miniorange_check_if_2fa_enabled_for_roles( $current_roles ) {
	if ( empty( $current_roles ) ) {
		return 0;
	}

	foreach ( $current_roles as $value ) {
		if ( get_option( 'mo2fa_' . $value ) ) {
			return 1;
		}
	}

	return 0;
}

/**
 * This function returns status of 2nd factor
 *
 * @param object $user object containing user details.
 * @return string
 */
function mo2f_get_user_2ndfactor( $user ) {
	global $mo2fdb_queries;
	$mo2f_user_email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $user->ID );
	$enduser         = new Two_Factor_Setup();
	$userinfo        = json_decode( $enduser->mo2f_get_userinfo( $mo2f_user_email ), true );
	if ( json_last_error() === JSON_ERROR_NONE ) {
		if ( 'ERROR' === $userinfo['status'] ) {
			$mo2f_second_factor = 'NONE';
		} elseif ( 'SUCCESS' === $userinfo['status'] ) {
			$mo2f_second_factor = $userinfo['authType'];
		} elseif ( 'FAILED' === $userinfo['status'] ) {
			$mo2f_second_factor = 'USER_NOT_FOUND';
		} else {
			$mo2f_second_factor = 'NONE';
		}
	} else {
		$mo2f_second_factor = 'NONE';
	}

	return $mo2f_second_factor;
}

/**
 * This function prompts forgot phone form.
 *
 * @param string $login_status login status of user.
 * @param string $login_message message used to show success/failed login actions.
 * @param string $redirect_to redirect url.
 * @param string $session_id_encrypt encrypted session id.
 * @return void
 */
function mo2f_get_forgotphone_form( $login_status, $login_message, $redirect_to, $session_id_encrypt ) {
	$mo2f_forgotphone_enabled     = MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_option' );
	$mo2f_email_as_backup_enabled = get_option( 'mo2f_enable_forgotphone_email' );
	$mo2f_kba_as_backup_enabled   = get_option( 'mo2f_enable_forgotphone_kba' );
	?>
	<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		echo_js_css_files();
		?>
	</head>
	<body>
	<div class="mo2f_modal" tabindex="-1" role="dialog">
		<div class="mo2f-modal-backdrop"></div>
		<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
			<div class="login mo_customer_validation-modal-content">
				<div class="mo2f_modal-header">
					<h4 class="mo2f_modal-title">
						<button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
								title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>"
								onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php esc_html_e( 'How would you like to authenticate yourself?', 'miniorange-2-factor-authentication' ); ?>
					</h4>
				</div>
				<div class="mo2f_modal-body">
					<?php
					if ( $mo2f_forgotphone_enabled ) {
						if ( isset( $login_message ) && ! empty( $login_message ) ) {
							?>
							<div id="otpMessage" class="mo2fa_display_message_frontend">
								<p class="mo2fa_display_message_frontend"><?php echo wp_kses( $login_message, array( 'b' => array() ) ); ?></p>
							</div>
						<?php } ?>
						<p class="mo2f_backup_options"><?php esc_html_e( 'Please choose the options from below:', 'miniorange-2-factor-authentication' ); ?></p>
						<div class="mo2f_backup_options_div">
							<?php if ( $mo2f_email_as_backup_enabled ) { ?>
								<input type="radio" name="mo2f_selected_forgotphone_option"
									value="One Time Passcode over Email"
									checked="checked"/><?php esc_html_e( 'Send a one time passcode to my registered email', 'miniorange-2-factor-authentication' ); ?>
								<br><br>
								<?php
							}
							if ( $mo2f_kba_as_backup_enabled ) {
								?>
								<input type="radio" name="mo2f_selected_forgotphone_option"
									value="KBA"/><?php esc_html_e( 'Answer your Security Questions (KBA)', 'miniorange-2-factor-authentication' ); ?>
							<?php } ?>
							<br><br>
							<input type="button" name="miniorange_validate_otp" value="<?php esc_attr_e( 'Continue', 'miniorange-2-factor-authentication' ); ?>" class="miniorange_validate_otp"
								onclick="mo2fselectforgotphoneoption();"/>
						</div>
						<?php
						mo2f_customize_logo();
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
		class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_mobile_validation_failed_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>
	<form name="f" id="mo2f_challenge_forgotphone_form" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="mo2f_configured_2FA_method"/>
		<input type="hidden" name="miniorange_challenge_forgotphone_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-challenge-forgotphone-nonce' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_challenge_forgotphone">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>

	<script>
		function mologinback() {
			jQuery('#mo2f_backto_mo_loginform').submit();
		}

		function mo2fselectforgotphoneoption() {
			var option = jQuery('input[name=mo2f_selected_forgotphone_option]:checked').val();
			document.getElementById("mo2f_challenge_forgotphone_form").elements[0].value = option;
			jQuery('#mo2f_challenge_forgotphone_form').submit();
		}
	</script>
	</body>
	</html>
	<?php
}

/**
 * This function user prompts KBA authentication prompt
 *
 * @param string $login_status login status of user.
 * @param string $login_message message used to show success/failed login actions.
 * @param string $redirect_to redirect url.
 * @param string $session_id_encrypt encrypted session id.
 * @param array  $cookievalue conatins kba questions.
 * @return void
 */
function mo2f_get_kba_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $cookievalue ) {
	global $mo_wpns_utility;
	$mo_wpns_config = new MoWpnsHandler();
	$user_id        = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
	MO2f_Utility::mo2f_debug_file( 'Prompted KBA validation screen User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
	?>
	<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		echo_js_css_files();
		?>
	</head>
	<body>
	<div class="mo2f_modal" tabindex="-1" role="dialog">
		<div class="mo2f-modal-backdrop"></div>
		<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
			<div class="login mo_customer_validation-modal-content">
				<div class="mo2f_modal-header">
					<h4 class="mo2f_modal-title">
						<button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
								title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>"
								onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php
						esc_html_e( 'Validate Security Questions', 'miniorange-2-factor-authentication' );
						?>
					</h4>
				</div>
				<div class="mo2f_modal-body">
					<div id="kbaSection" class="kbaSectiondiv">
						<div id="otpMessage">
							<p style="font-size:13px;"
							class="mo2fa_display_message_frontend">
							<?php
								$login_message = isset( $login_message ) && ! empty( $login_message ) ? $login_message : __( 'Please answer the following questions:', 'miniorange-2-factor-authentication' );
								echo wp_kses( $login_message, array( 'b' => array() ) );
							?>
							</p>
						</div>
						<form name="f" id="mo2f_submitkba_loginform" method="post">
						<input type="hidden" name="mo2f_validate_kba_details_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-validate-kba-details-nonce' ) ); ?>"/>							<div id="mo2f_kba_content">
								<p style="font-size:15px;">
									<?php
									$kba_questions = $cookievalue;
									echo esc_html( $kba_questions[0]['question'] );
									?>
									<br>
									<input class="mo2f-textbox" type="password" name="mo2f_answer_1" id="mo2f_answer_1"
										required="true" autofocus="true"
										pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}"
										title="Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed."
										autocomplete="off"><br>
									<?php echo esc_html( $kba_questions[1]['question'] ); ?><br>
									<input class="mo2f-textbox" type="password" name="mo2f_answer_2" id="mo2f_answer_2"
										required="true" pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+\-\s]{1,100}"
										title="Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed."
										autocomplete="off">

								</p>
							</div>
							<input type="submit" name="miniorange_kba_validate" id="miniorange_kba_validate"
								class="miniorange_kba_validate" style="float:left;"
								value="<?php esc_attr_e( 'Validate', 'miniorange-2-factor-authentication' ); ?>"/>
							<input type="hidden" name="miniorange_kba_nonce"
								value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-kba-nonce' ) ); ?>"/>
							<input type="hidden" name="option"
								value="miniorange_kba_validate"/>
							<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
							<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
						</form>
						<br>
					</div><br>
					<?php
					if ( empty( get_user_meta( $user_id, 'mo_backup_code_generated', true ) ) ) {
						?>
						<div>
							<a href="#mo2f_backup_generate">
								<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Send backup codes on email', 'miniorange-2-factor-authentication' ); ?></p>
							</a>
						</div>
					<?php } else { ?>
						<div>
							<a href="#mo2f_backup_option">
								<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Use Backup Codes', 'miniorange-2-factor-authentication' ); ?></p>
							</a>
						</div>
						<?php
					}
					?>
					<div style="padding:10px;">
						<p><a href="<?php echo esc_url( $mo_wpns_config->locked_out_link() ); ?>" target="_blank" style="color:#ca2963;font-weight:bold;">I'm locked out & unable to login.</a></p>
					</div>

					<?php
						mo2f_customize_logo();
						mo2f_create_backup_form( $redirect_to, $session_id_encrypt, $login_status, $login_message );
					?>

				</div>
			</div>
		</div>
	</div>
	<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
		class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_mobile_validation_failed_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>

	<script>
		function mologinback() {
			jQuery('#mo2f_backto_mo_loginform').submit();
		}
		var is_ajax = "<?php echo esc_js( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ); ?>";
		if(is_ajax){
			jQuery('#mo2f_answer_1').keypress(function (e) {
				if (e.which === 13) {//Enter key pressed
					e.preventDefault();
					mo2f_kba_ajax(); 
				}
			});
			jQuery('#mo2f_answer_2').keypress(function (e) {
				if (e.which === 13) {//Enter key pressed
					e.preventDefault(); 
					mo2f_kba_ajax();
				}
			});
			jQuery("#miniorange_kba_validate").click(function(e){
				e.preventDefault();
				mo2f_kba_ajax();
			});

		function mo2f_kba_ajax(){
			jQuery('#mo2f_answer_1').prop('disabled','true');
			jQuery('#mo2f_answer_2').prop('disabled','true');
			jQuery('#miniorange_kba_validate').prop('disabled','true');       
			var data = {
				"action"            : "mo2f_ajax",
				"mo2f_ajax_option"  : "mo2f_ajax_kba",
				"mo2f_answer_1"     : jQuery( "input[name=\'mo2f_answer_1\']" ).val(),
				"mo2f_answer_2"     : jQuery( "input[name=\'mo2f_answer_2\']" ).val(),
				"miniorange_kba_nonce" : jQuery( "input[name=\'miniorange_kba_nonce\']" ).val(),
				"session_id"        : jQuery( "input[name=\'session_id\']" ).val(),
				"redirect_to"       : jQuery( "input[name=\'redirect_to\']" ).val(),
				"mo2f_trust_device" : jQuery( "input[name=\'mo2f_trust_device\']" ).val(),
			};
			jQuery.post(my_ajax_object.ajax_url, data, function(response) {
			if ( typeof response.data === "undefined") {
				jQuery("html").html(response);
			}
			else             
				location.href = response.data.redirect;  
			}); 
		}
	}
		jQuery('a[href="#mo2f_backup_option"]').click(function() {
			jQuery('#mo2f_backup').submit();
		});
		jQuery('a[href="#mo2f_backup_generate"]').click(function() {
			jQuery('#mo2f_create_backup_codes').submit();
		});
	</script>
	</body>

	</html>
	<?php
}

/**
 * This Function prompts user`s backup method
 *
 * @param string $login_status login status of user.
 * @param string $login_message message used to show success/failed login actions.
 * @param string $redirect_to redirect url.
 * @param string $session_id_encrypt encrypted session id.
 * @return void
 */
function mo2f_backup_form( $login_status, $login_message, $redirect_to, $session_id_encrypt ) {
	?>
<html>
	<head>  
		<meta charset="utf-8"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			echo_js_css_files();
		?>
	</head>
	<body>
		<div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
		<div class="mo2f-modal-backdrop"></div>
		<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
			<div class="login mo_customer_validation-modal-content">
				<div class="mo2f_modal-header">
					<h4 class="mo2f_modal-title"><button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close" title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>" onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php esc_html_e( 'Validate Backup Code', 'miniorange-2-factor-authentication' ); ?>
					</h4>
				</div>
				<div class="mo2f_modal-body">
					<div id="kbaSection" style="padding-left:10px;padding-right:10px;">
					<div  id="otpMessage" >
						<p style="font-size:15px;">
						<?php
							$show_msg = ( isset( $login_message ) && ! empty( $login_message ) ) ? wp_kses(
								$login_message,
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							) : __( 'Please answer the following questions:', 'miniorange-2-factor-authentication' );
							echo esc_html( $show_msg );
						?>
						</p>
					</div>
					<form name="f" id="mo2f_submitbackup_loginform" method="post" action="">
						<div id="mo2f_kba_content">
							<p style="font-size:15px;">
								<input class="mo2f-textbox" type="text" name="mo2f_backup_code" id="mo2f_backup_code" required="true" autofocus="true"  title="<?php esc_attr_e( 'Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed.', 'miniorange-2-factor-authentication' ); ?>" autocomplete="off" ><br />
							</p>
						</div>
						<input type="submit" name="miniorange_backup_validate" id="miniorange_backup_validate" class="miniorange_otp_token_submit"  style="float:left;" value="<?php esc_attr_e( 'Validate', 'miniorange-2-factor-authentication' ); ?>" />
						<input type="hidden" name="miniorange_validate_backup_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-validate-backup-nonce' ) ); ?>" />
						<input type="hidden" name="option" value="miniorange_validate_backup_nonce">
						<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>" />
						<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>" />
					</form>
					</br>
					</div>
					<br /><br /><br />
					<?php mo2f_customize_logo(); ?>
				</div>
			</div>
		</div>
	</div>
	<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>" style="display:none;">
		<input type="hidden" name="miniorange_mobile_validation_failed_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>" />
	</form>
</body>
<script>
	function mologinback(){
		jQuery('#mo2f_backto_mo_loginform').submit();
	}
</script>
</html>
	<?php
}

/**
 * This function prompts duo authentication
 *
 * @param string $login_status login status of user.
 * @param string $login_message message used to show success/failed login actions.
 * @param string $redirect_to redirect url.
 * @param string $session_id_encrypt encrypted session id.
 * @param string $user_id user id.
 * @return void
 */
function mo2f_get_duo_push_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id ) {

	$mo_wpns_config = new MoWpnsHandler();

	global $mo2fdb_queries,$txid,$mo_wpns_utility;
	$mo2f_enable_forgotphone = MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_option' );
	$mo2f_kba_config_status  = $mo2fdb_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $user_id );
	$mo2f_is_new_customer    = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
	$mo2f_ev_txid            = get_user_meta( $user_id, 'mo2f_EV_txid', true );
	$user_id                 = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

	$current_user = get_user_by( 'id', $user_id );
	MO2f_Utility::mo2f_debug_file( 'Waiting for duo push notification validation User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $current_user->ID . ' Email-' . $current_user->user_email );
	update_user_meta( $user_id, 'current_user_email', $current_user->user_email );

	?>

<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			echo_js_css_files();
		?>
	</head>
	<body>
	<div class="mo2f_modal" tabindex="-1" role="dialog">
		<div class="mo2f-modal-backdrop"></div>
		<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
			<div class="login mo_customer_validation-modal-content">
				<div class="mo2f_modal-header">
					<h4 class="mo2f_modal-title">
						<button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
								title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>"
								onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php esc_html_e( 'Accept Your Transaction', 'miniorange-2-factor-authentication' ); ?></h4>
				</div>
				<div class="mo2f_modal-body">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
						<div id="otpMessage">
							<p class="mo2fa_display_message_frontend"><?php echo wp_kses( $login_message, array( 'b' => array() ) ); ?></p>
						</div>
					<?php } ?>
					<div id="pushSection">

						<div>
							<div class="mo2fa_text-align-center">
								<p class="mo2f_push_oob_message"><?php esc_html_e( 'Waiting for your approval...', 'miniorange-2-factor-authentication' ); ?></p>
					</div>
						</div>
						<div id="showPushImage">
							<div class="mo2fa_text-align-center">
								<img src="<?php echo esc_url( plugins_url( 'includes/images/ajax-loader-login.gif', dirname( dirname( __FILE__ ) ) ) ); ?>"/>
					</div>
						</div>


						<span style="padding-right:2%;">
							<?php if ( isset( $login_status ) && 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' === $login_status ) { ?>
								<div class="mo2fa_text-align-center">
									<?php if ( $mo2f_enable_forgotphone && ! $mo2f_is_new_customer ) { ?>
										<input type="button" name="miniorange_login_forgotphone"
												onclick="mologinforgotphone();" id="miniorange_login_forgotphone"
												class="miniorange_login_forgotphone"
												value="<?php esc_attr_e( 'Forgot Phone?', 'miniorange-2-factor-authentication' ); ?>"/>
									<?php } ?>
									&emsp;&emsp;
									</div>
							<?php } elseif ( isset( $login_status ) && 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL' === $login_status && $mo2f_enable_forgotphone && $mo2f_kba_config_status ) { ?>
								<div class="mo2fa_text-align-center">
								<a href="#mo2f_alternate_login_kba">
									<p class="mo2f_push_oob_backup"><?php esc_html_e( 'Didn\'t receive push nitification?', 'miniorange-2-factor-authentication' ); ?></p>
								</a>
							</div>
							<?php } ?>
						</span>
						<div class="mo2fa_text-align-center">
							<?php
							if ( empty( get_user_meta( $user_id, 'mo_backup_code_generated', true ) ) ) {
								?>
									<div>
										<a href="#mo2f_backup_generate">
											<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Send backup codes on email', 'miniorange-2-factor-authentication' ); ?></p>
										</a>
									</div>
							<?php } else { ?>
									<div>
										<a href="#mo2f_backup_option">
											<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Use Backup Codes', 'miniorange-2-factor-authentication' ); ?></p>
										</a>
									</div>
								<?php
							}
							?>
							<div style="padding:10px;">
								<p><a href="<?php echo esc_url( $mo_wpns_config->locked_out_link() ); ?>" target="_blank" style="color:#ca2963;font-weight:bold;">I'm locked out & unable to login.</a></p>
							</div>
						</div>
					</div>

					<?php
						mo2f_customize_logo();
						mo2f_create_backup_form( $redirect_to, $session_id_encrypt, $login_status, $login_message );
					?>
				</div>
			</div>
		</div>
	</div>
	<form name="f" id="mo2f_backto_duo_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
			class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_duo_push_validation_failed_nonce"
				value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-duo-push-validation-failed-nonce' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_duo_push_validation_failed">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
		<input type="hidden" name="currentMethod" value="emailVer"/>
	</form>
	<form name="f" id="mo2f_duo_push_validation_form" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_duo_push_validation_nonce"
				value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-duo-validation-nonce' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_duo_push_validation">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="tx_type"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
		<input type="hidden" name="TxidEmail" value="<?php echo esc_attr( $mo2f_ev_txid ); ?>"/>
	</form>
	<form name="f" id="mo2f_show_forgotphone_loginform" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="request_origin_method" value="<?php echo esc_attr( $login_status ); ?>"/>
		<input type="hidden" name="miniorange_forgotphone" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-forgotphone' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_forgotphone">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>
	<form name="f" id="mo2f_alternate_login_kbaform" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_alternate_login_kba_nonce"
				value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-alternate-login-kba-nonce' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_alternate_login_kba">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>

	<script>
		var timeout;

			pollPushValidation();
			function pollPushValidation()
			{   
				var ajax_url = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"; 
				var nonce = "<?php echo esc_js( wp_create_nonce( 'miniorange-2-factor-duo-nonce' ) ); ?>";
				var session_id_encrypt = "<?php echo esc_js( $session_id_encrypt ); ?>";
				var data={
					'action':'mo2f_duo_ajax_request',
					'call_type':'check_duo_push_auth_status',
					'session_id_encrypt': session_id_encrypt,
					'nonce' : nonce,
				}; 

				jQuery.post(ajax_url, data, function(response){


							if (response.success) {
								jQuery('#mo2f_duo_push_validation_form').submit();
							} else if (response.data == 'ERROR' || response.data == 'FAILED' || response.data == 'DENIED' || response.data ==0) {
								jQuery('#mo2f_backto_duo_mo_loginform').submit();
							} else {
								timeout = setTimeout(pollMobileValidation, 3000);
							}
				});
		}

		function mologinforgotphone() {
			jQuery('#mo2f_show_forgotphone_loginform').submit();
		}

		function mologinback() {
			jQuery('#mo2f_backto_duo_mo_loginform').submit();
		}

		jQuery('a[href="#mo2f_alternate_login_kba"]').click(function () {
			jQuery('#mo2f_alternate_login_kbaform').submit();
		});
		jQuery('a[href="#mo2f_backup_option"]').click(function() {
			jQuery('#mo2f_backup').submit();
		});
		jQuery('a[href="#mo2f_backup_generate"]').click(function() {
			jQuery('#mo2f_create_backup_codes').submit();
		});

	</script>
	</body>
	</html>

	<?php
}

/**
 * This function prompts email authentication
 *
 * @param string $id user id.
 * @param string $login_status login status of user.
 * @param string $login_message message used to show success/failed login actions.
 * @param string $redirect_to redirect url.
 * @param string $session_id_encrypt encrypted session id.
 * @param string $cookievalue cookie value.
 * @return void
 */
function mo2f_get_push_notification_oobemail_prompt( $id, $login_status, $login_message, $redirect_to, $session_id_encrypt, $cookievalue ) {

	$mo_wpns_config = new MoWpnsHandler();
	global $mo2fdb_queries,$txid,$mo_wpns_utility;
	$mo2f_enable_forgotphone = MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_option' );
	$mo2f_kba_config_status  = $mo2fdb_queries->get_user_detail( 'mo2f_SecurityQuestions_config_status', $id );
	$mo2f_is_new_customer    = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
	$mo2f_ev_txid            = get_user_meta( $id, 'mo2f_EV_txid', true );
	$user_id                 = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
	MO2f_Utility::mo2f_debug_file( 'Waiting for push notification validation User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
	?>
	<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		echo_js_css_files();
		?>
	</head>
	<body>
	<div class="mo2f_modal" tabindex="-1" role="dialog">
		<div class="mo2f-modal-backdrop"></div>
		<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
			<div class="login mo_customer_validation-modal-content">
				<div class="mo2f_modal-header">
					<h4 class="mo2f_modal-title">
						<button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
								title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>"
								onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php
							esc_html_e( 'Accept Your Transaction', 'miniorange-2-factor-authentication' );
						?>
						</h4>
				</div>
				<div class="mo2f_modal-body">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
						<div id="otpMessage">
							<p class="mo2fa_display_message_frontend"><?php echo wp_kses( $login_message, array( 'b' => array() ) ); ?></p>
						</div>
					<?php } ?>
					<div id="pushSection">

						<div>
							<div class="mo2fa_text-align-center">
								<p class="mo2f_push_oob_message"><?php esc_html_e( 'Waiting for your approval...', 'miniorange-2-factor-authentication' ); ?></p>
					</div>
						</div>
						<div id="showPushImage">
							<div class="mo2fa_text-align-center">
								<img src="<?php echo esc_url( plugins_url( 'includes/images/ajax-loader-login.gif', dirname( dirname( __FILE__ ) ) ) ); ?>"/>
					</div>
						</div>


						<span style="padding-right:2%;">
							<?php if ( isset( $login_status ) && 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' === $login_status ) { ?>
								<div class="mo2fa_text-align-center">
									<?php if ( $mo2f_enable_forgotphone && ! $mo2f_is_new_customer ) { ?>
										<input type="button" name="miniorange_login_forgotphone"
												onclick="mologinforgotphone();" id="miniorange_login_forgotphone"
												class="miniorange_login_forgotphone"
												value="<?php esc_attr_e( 'Forgot Phone?', 'miniorange-2-factor-authentication' ); ?>"/>
									<?php } ?>
									&emsp;&emsp;
								<input type="button" name="miniorange_login_offline" onclick="mologinoffline();"
										id="miniorange_login_offline" class="miniorange_login_offline"
									value="<?php esc_attr_e( 'Phone is Offline?', 'miniorange-2-factor-authentication' ); ?>"/>
									</div>
							<?php } elseif ( isset( $login_status ) && 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL' === $login_status && $mo2f_enable_forgotphone && $mo2f_kba_config_status ) { ?>
								<div class="mo2fa_text-align-center">
								<a href="#mo2f_alternate_login_kba">
									<p class="mo2f_push_oob_backup"><?php esc_html_e( 'Didn\'t receive mail?', 'miniorange-2-factor-authentication' ); ?></p>
								</a>
							</div>
							<?php } ?>
						</span>
						<div class="mo2fa_text-align-center">
							<?php
							if ( empty( get_user_meta( $user_id, 'mo_backup_code_generated', true ) ) ) {
								?>
									<div>
										<a href="#mo2f_backup_generate">
											<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Send backup codes on email', 'miniorange-2-factor-authentication' ); ?></p>
										</a>
									</div>
							<?php } else { ?>
									<div>
										<a href="#mo2f_backup_option">
											<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Use Backup Codes', 'miniorange-2-factor-authentication' ); ?></p>
										</a>
									</div>
								<?php
							}
							?>
							<div style="padding:10px;">
								<p><a href="<?php echo esc_url( $mo_wpns_config->locked_out_link() ); ?>" target="_blank" style="color:#ca2963;font-weight:bold;">I'm locked out & unable to login.</a></p>
							</div>
						</div>
					</div>

					<?php
						mo2f_customize_logo();
						mo2f_create_backup_form( $redirect_to, $session_id_encrypt, $login_status, $login_message );
					?>
				</div>
			</div>
		</div>
	</div>
	<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
			class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_mobile_validation_failed_nonce"
				value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_mobile_validation_failed">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
		<input type="hidden" name="currentMethod" value="emailVer"/>

	</form>
	<form name="f" id="mo2f_mobile_validation_form" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_mobile_validation_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-nonce' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_mobile_validation">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="tx_type"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
		<input type="hidden" name="TxidEmail" value="<?php echo esc_attr( $mo2f_ev_txid ); ?>"/>   

	</form>
	<form name="f" id="mo2f_show_softtoken_loginform" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_softtoken"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-softtoken' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_softtoken">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>
	<form name="f" id="mo2f_show_forgotphone_loginform" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="request_origin_method" value="<?php echo esc_attr( $login_status ); ?>"/>
		<input type="hidden" name="miniorange_forgotphone"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-forgotphone' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_forgotphone">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>
	<form name="f" id="mo2f_alternate_login_kbaform" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_alternate_login_kba_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-alternate-login-kba-nonce' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_alternate_login_kba">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>

	<script>
		var timeout;
		var login_status = '<?php echo esc_js( $login_status ); ?>';
		var calls     = 0;
		var onprem = '<?php echo esc_js( MO2F_IS_ONPREM ); ?>';
		if( login_status !== "MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS" && onprem == 1 )
		{
			pollPushValidation();
			function pollPushValidation()
			{   calls = calls + 1;
				var data = {'txid':'<?php echo esc_js( $mo2f_ev_txid ); ?>'};
					jQuery.ajax({
					url: '<?php echo esc_url( get_site_option( 'siteurl' ) ); ?>'+"/wp-login.php",
					type: "POST",
					data: data,
					success: function (result) {

					var status = result;
						if (status == 1) {
							jQuery('input[name="tx_type"]').val("EV");
							jQuery('#mo2f_mobile_validation_form').submit();
						} else if (status == 'ERROR' || status == 'FAILED' || status == 'DENIED' || status == 0) {
							jQuery('#mo2f_backto_mo_loginform').submit();
						} else {
							if(calls<300)
							{
							timeout = setTimeout(pollPushValidation, 1000);
							}
							else
							{
								jQuery('#mo2f_backto_mo_loginform').submit();
							}
						}
					}
				});
			}


		}
		else
		{
			pollPushValidation();
			function pollPushValidation() {
				var transId = "<?php echo esc_js( $cookievalue ); ?>";
				var jsonString = "{\"txId\":\"" + transId + "\"}";
				var postUrl = "<?php echo esc_url( MO_HOST_NAME ); ?>" + "/moas/api/auth/auth-status";

				jQuery.ajax({
					url: postUrl,
					type: "POST",
					dataType: "json",
					data: jsonString,
					contentType: "application/json; charset=utf-8",
					success: function (result) {
						var status = JSON.parse(JSON.stringify(result)).status;
						if (status === 'SUCCESS') {
							jQuery('input[name="tx_type"]').val("PN");
							jQuery('#mo2f_mobile_validation_form').submit();
						} else if (status == 'ERROR' || status == 'FAILED' || status == 'DENIED') {
							jQuery('#mo2f_backto_mo_loginform').submit();
						} else {
							timeout = setTimeout(pollPushValidation, 3000);
						}
					}
				});
			}
		}

		function mologinoffline() {
			jQuery('#mo2f_show_softtoken_loginform').submit();
		}

		function mologinforgotphone() {
			jQuery('#mo2f_show_forgotphone_loginform').submit();
		}

		function mologinback() {
			jQuery('#mo2f_backto_mo_loginform').submit();
		}

		jQuery('a[href="#mo2f_alternate_login_kba"]').click(function () {
			jQuery('#mo2f_alternate_login_kbaform').submit();
		});
		jQuery('a[href="#mo2f_backup_option"]').click(function() {
			jQuery('#mo2f_backup').submit();
		});
		jQuery('a[href="#mo2f_backup_generate"]').click(function() {
			jQuery('#mo2f_create_backup_codes').submit();
		});

	</script>
	</body>
	</html>
	<?php
}

/**
 * This function prompts QR code authentication
 *
 * @param string $login_status login status of user.
 * @param string $login_message message used to show success/failed login actions.
 * @param string $redirect_to redirect url.
 * @param string $qr_code qr code data url.
 * @param string $session_id_encrypt encrypted session id.
 * @param object $cookievalue cookie value.
 * @return void
 */
function mo2f_get_qrcode_authentication_prompt( $login_status, $login_message, $redirect_to, $qr_code, $session_id_encrypt, $cookievalue ) {
	$mo2f_enable_forgotphone = MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_option' );
	$mo_wpns_config          = new MoWpnsHandler();
	$mo2f_is_new_customer    = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
	$user_id                 = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
	?>
	<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		echo_js_css_files();
		?>
	</head>
	<body>
	<div class="mo2f_modal" tabindex="-1" role="dialog">
		<div class="mo2f-modal-backdrop"></div>
		<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
			<div class="login mo_customer_validation-modal-content">
				<div class="mo2f_modal-header">
					<h4 class="mo2f_modal-title">
						<button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
								title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>"
								onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php esc_html_e( 'Scan QR Code', 'miniorange-2-factor-authentication' ); ?></h4>
				</div>
				<div class="mo2f_modal-body center">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
						<div id="otpMessage">
							<p class="mo2fa_display_message_frontend"><?php echo wp_kses( $login_message, array( 'b' => array() ) ); ?></p>
						</div>
						<br>
					<?php } ?>
					<div id="scanQRSection">
						<div style="margin-bottom:10%;">
							<div class="mo2fa_text-align-center">
								<p class="mo2f_login_prompt_messages"><?php esc_html_e( 'Identify yourself by scanning the QR code with miniOrange Authenticator app.', 'miniorange-2-factor-authentication' ); ?></p>
					</div>
						</div>
						<div id="showQrCode" style="margin-bottom:10%;">
							<div class="mo2fa_text-align-center"><?php echo '<img src="data:image/jpg;base64,' . esc_html( $qr_code ) . '" />'; ?></div>
						</div>
						<span style="padding-right:2%;">
						<div class="mo2fa_text-align-center">
			<?php if ( ! $mo2f_is_new_customer ) { ?>
					<?php if ( $mo2f_enable_forgotphone ) { ?>
					<input type="button" name="miniorange_login_forgotphone" onclick="mologinforgotphone();"
							id="miniorange_login_forgotphone" class="miniorange_login_forgotphone"
							style="margin-right:5%;"
							value="<?php esc_attr_e( 'Forgot Phone?', 'miniorange-2-factor-authentication' ); ?>"/>
				<?php } ?>
				&emsp;&emsp;
			<?php } ?>
							<input type="button" name="miniorange_login_offline" onclick="mologinoffline();"
									id="miniorange_login_offline" class="miniorange_login_offline"
									value="<?php esc_attr_e( 'Phone is Offline?', 'miniorange-2-factor-authentication' ); ?>"/>
					</div>
					</span>
						<?php
						if ( empty( get_user_meta( $user_id, 'mo_backup_code_generated', true ) ) ) {
							?>
								<div>
									<a href="#mo2f_backup_generate">
										<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Send backup codes on email', 'miniorange-2-factor-authentication' ); ?></p>
									</a>
								</div>
							<?php } else { ?>
								<div>
									<a href="#mo2f_backup_option">
										<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Use Backup Codes', 'miniorange-2-factor-authentication' ); ?></p>
									</a>
								</div>
							<?php
							}
							?>
						<div style="padding:10px;">
							<p><a href="<?php echo esc_url( $mo_wpns_config->locked_out_link() ); ?>" target="_blank" style="color:#ca2963;font-weight:bold;">I'm locked out & unable to login.</a></p>
						</div>
					</div>
					<?php
						mo2f_customize_logo();
						mo2f_create_backup_form( $redirect_to, $session_id_encrypt, $login_status, $login_message );
					?>
				</div>
			</div>
		</div>
	</div>
	<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
		class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_mobile_validation_failed_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>
	<form name="f" id="mo2f_mobile_validation_form" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_mobile_validation_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-nonce' ) ); ?>"/>
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="option" value="miniorange_mobile_validation">
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>
	<form name="f" id="mo2f_show_softtoken_loginform" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_softtoken"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-softtoken' ) ); ?>"/>
		<input type="hidden" name="option" value="miniorange_softtoken">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>
	<form name="f" id="mo2f_show_forgotphone_loginform" method="post" class="mo2f_display_none_forms">
		<input type="hidden" name="request_origin_method" value="<?php echo esc_attr( $login_status ); ?>"/>
		<input type="hidden" name="miniorange_forgotphone"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-forgotphone' ) ); ?>"/>
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
		<input type="hidden" name="option" value="miniorange_forgotphone">
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>

	<script>
		var timeout;
		pollMobileValidation();

		function pollMobileValidation() {
			var transId = "<?php echo esc_js( $cookievalue ); ?>";
			var jsonString = "{\"txId\":\"" + transId + "\"}";
			var postUrl = "<?php echo esc_url( MO_HOST_NAME ); ?>" + "/moas/api/auth/auth-status";
			jQuery.ajax({
				url: postUrl,
				type: "POST",
				dataType: "json",
				data: jsonString,
				contentType: "application/json; charset=utf-8",
				success: function (result) {
					var status = JSON.parse(JSON.stringify(result)).status;
					if (status === 'SUCCESS') {
						var content = "<div id='success'><div class='mo2fa_text-align-center'><img src='" + "<?php echo esc_url( plugins_url( 'includes/images/right.png', dirname( dirname( __FILE__ ) ) ) ); ?>" + "' /></div></div>";
						jQuery("#showQrCode").empty();
						jQuery("#showQrCode").append(content);
						setTimeout(function () {
							jQuery("#mo2f_mobile_validation_form").submit();
						}, 100);
					} else if (status === 'ERROR' || status === 'FAILED') {
						var content = "<div id='error'><div class='mo2fa_text-align-center'><img src='" + "<?php echo esc_url( plugins_url( 'includes/images/wrong.png', dirname( dirname( __FILE__ ) ) ) ); ?>" + "' /></div></div>";
						jQuery("#showQrCode").empty();
						jQuery("#showQrCode").append(content);
						setTimeout(function () {
							jQuery('#mo2f_backto_mo_loginform').submit();
						}, 1000);
					} else {
						timeout = setTimeout(pollMobileValidation, 3000);
					}
				}
			});
		}

		function mologinoffline() {
			jQuery('#mo2f_show_softtoken_loginform').submit();
		}

		function mologinforgotphone() {
			jQuery('#mo2f_show_forgotphone_loginform').submit();
		}

		function mologinback() {
			jQuery('#mo2f_backto_mo_loginform').submit();
		}
		jQuery('a[href="#mo2f_backup_option"]').click(function() {
			jQuery('#mo2f_backup').submit();
		});
		jQuery('a[href="#mo2f_backup_generate"]').click(function() {
			jQuery('#mo2f_create_backup_codes').submit();
		});

	</script>
	</body>
	</html>
	<?php
}

/**
 * This function prompts OTP authentication
 *
 * @param string  $login_status login status of user.
 * @param string  $login_message message used to show success/failed login actions.
 * @param string  $redirect_to redirect url.
 * @param string  $session_id_encrypt encrypted session id.
 * @param string  $user_id user id.
 * @param boolean $show_back_button flag for to show back button.
 * @param string  $mo2fa_transaction_id OTP transaction id.
 * @return void
 */
function mo2f_get_otp_authentication_prompt( $login_status, $login_message, $redirect_to, $session_id_encrypt, $user_id, $show_back_button = null, $mo2fa_transaction_id = null ) {
	global $mo2fdb_queries,$mo_wpns_utility;
	$mo2f_enable_forgotphone           = MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_option' );
	$mo_wpns_config                    = new MoWpnsHandler();
	$mo2f_is_new_customer              = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
	$attempts                          = get_option( 'mo2f_attempts_before_redirect', 3 );
	$user_id                           = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );
	$mo2f_otp_over_email_config_status = $mo2fdb_queries->get_user_detail( 'mo2f_OTPOverEmail_config_status', $user_id );

	MO2f_Utility::mo2f_debug_file( 'Prompted 2fa validation screen User_IP-' . $mo_wpns_utility->get_client_ip() . ' User_Id-' . $user_id );
	?>
	<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		echo_js_css_files();
		?>
	</head>
	<body>
	<div class="mo2f_modal" tabindex="-1" role="dialog">
		<div class="mo2f-modal-backdrop"></div>
		<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
			<div class="login mo_customer_validation-modal-content">
				<div class="mo2f_modal-header">
					<h4 class="mo2f_modal-title">
						<button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close"
								title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>"
								onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
						<?php esc_html_e( 'Validate OTP', 'miniorange-2-factor-authentication' ); ?>
					</h4>
				</div>
				<div class="mo2f_modal-body center">
					<?php if ( isset( $login_message ) && ! empty( $login_message ) ) { ?>
						<div id="otpMessage">
							<p class="mo2fa_display_message_frontend">
							<?php
							echo wp_kses(
								$login_message,
								array(
									'b' => array(),
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							);
							?>
																		</p>
						</div>
					<?php } ?><br>					<span><b>Attempts left</b>:</span> <?php echo esc_html( $attempts ); ?><br>
					<?php if ( 1 === $attempts ) { ?>
					<span style='color:red;'><b>If you fail to verify your identity, you will be redirected back to login page to verify your credentials.</b></span> <br>
					<?php } ?>
					<br>
					<div id="showOTP">
						<div class="mo2f-login-container">
							<form name="f" id="mo2f_submitotp_loginform" method="post">
								<div class="mo2fa_text-align-center">
									<input type="text" name="mo2fa_softtoken" style="height:28px !important;"
										placeholder="<?php esc_attr_e( 'Enter code', 'miniorange-2-factor-authentication' ); ?>"
										id="mo2fa_softtoken" required="true" class="mo_otp_token" autofocus="true"
										pattern="[0-9]{4,8}"
										title="<?php esc_attr_e( 'Only digits within range 4-8 are allowed.', 'miniorange-2-factor-authentication' ); ?>"/>
					</div>
								<br>
								<input type="submit" name="miniorange_otp_token_submit" id="miniorange_otp_token_submit"
									class="miniorange_otp_token_submit"
									value="<?php esc_attr_e( 'Validate', 'miniorange-2-factor-authentication' ); ?>"/>
								<?php

								if ( 1 === $show_back_button ) {
									?>
										<input type="button" name="miniorange_otp_token_back" id="miniorange_otp_token_back"
										class="miniorange_otp_token_submit"
										value="<?php esc_attr_e( 'Back', 'miniorange-2-factor-authentication' ); ?>"/>
									<?php
								}
								?>
								<input type="hidden" name="request_origin_method" value="<?php echo esc_attr( $login_status ); ?>"/>
								<input type="hidden" name="miniorange_soft_token_nonce"
									value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-soft-token-nonce' ) ); ?>"/>
								<input type="hidden" name="option" value="miniorange_soft_token">
								<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
								<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
								<?php if ( null !== $mo2fa_transaction_id ) { ?>
								<input type="hidden" name="mo2fa_transaction_id" id="mo2fa_transaction_id" value="<?php echo esc_attr( $mo2fa_transaction_id ); ?>"/>
							<?php } ?>
							</form>
							<?php
							$kbaset = get_user_meta( $user_id, 'Security Questions' );
							if ( ! $mo2f_is_new_customer ) {
								?>
								<?php if ( $mo2f_enable_forgotphone && isset( $login_status ) && 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' !== $login_status && ( count( $kbaset ) !== 0 ) ) { ?>
									<a name="miniorange_login_forgotphone" onclick="mologinforgotphone();"
									id="miniorange_login_forgotphone"
									class="mo2f-link"><?php esc_html_e( 'Forgot Phone ?', 'miniorange-2-factor-authentication' ); ?></a>
								<?php } ?>
								<?php
							}
							if ( 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' !== $login_status || ( 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' === $login_status && $mo2f_otp_over_email_config_status ) ) {
								if ( empty( get_user_meta( $user_id, 'mo_backup_code_generated', true ) ) ) {
									?>
									<div>
										<a href="#mo2f_backup_generate">
											<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Send backup codes on email', 'miniorange-2-factor-authentication' ); ?></p>
										</a>
									</div>
								<?php } else { ?>
									<div>
										<a href="#mo2f_backup_option">
											<p style="font-size:14px; font-weight:bold;"><?php esc_html_e( 'Use Backup Codes', 'miniorange-2-factor-authentication' ); ?></p>
										</a>
									</div>
									<?php
								}
								?>

								<div style="padding:10px;">
									<p><a href="<?php echo esc_url( $mo_wpns_config->locked_out_link() ); ?>" target="_blank" style="color:#ca2963;font-weight:bold;">I'm locked out & unable to login.</a></p>
								</div>
							<?php } ?>
						</div>
					</div>
					<?php
						mo2f_customize_logo();
					if ( 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' !== $login_status || ( 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' === $login_status && $mo2f_otp_over_email_config_status ) ) {
						mo2f_create_backup_form( $redirect_to, $session_id_encrypt, $login_status, $login_message );
					}
					?>
				</div>
			</div>
		</div>
	</div>

	<form name="f" id="mo2f_backto_inline_registration" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
		class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_back_inline_reg_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-back-inline-reg-nonce' ) ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
		<input type="hidden" name="option" value="miniorange2f_back_to_inline_registration"> 
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>

	</form>

	<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>"
		class="mo2f_display_none_forms">
		<input type="hidden" name="miniorange_mobile_validation_failed_nonce"
			value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>"/>
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
	</form>

	<?php if ( MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_option' ) && isset( $login_status ) && 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' !== $login_status ) { ?>
		<form name="f" id="mo2f_show_forgotphone_loginform" method="post" action="" class="mo2f_display_none_forms">
			<input type="hidden" name="request_origin_method" value="<?php echo esc_attr( $login_status ); ?>"/>
			<input type="hidden" name="miniorange_forgotphone"
				value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-forgotphone' ) ); ?>"/>
			<input type="hidden" name="option" value="miniorange_forgotphone">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
			<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
		</form>

	<?php } ?>

	<script>
		jQuery('#miniorange_otp_token_back').click(function(){
			jQuery('#mo2f_backto_inline_registration').submit();
		});
		jQuery('a[href="#mo2f_backup_option"]').click(function() {
			jQuery('#mo2f_backup').submit();
		});
		jQuery('a[href="#mo2f_backup_generate"]').click(function() {
			jQuery('#mo2f_create_backup_codes').submit();
		});

		function mologinback() {
			jQuery('#mo2f_backto_mo_loginform').submit();
		}

		function mologinforgotphone() {
			jQuery('#mo2f_show_forgotphone_loginform').submit();
		}
		var is_ajax = '<?php echo esc_js( MO2f_Utility::get_index_value( 'GLOBALS', 'mo2f_is_ajax_request' ) ); ?>';
		if(is_ajax){
			jQuery('#mo2fa_softtoken').keypress(function (e) {
				if (e.which === 13) {//Enter key pressed
					e.preventDefault();
					mo2f_otp_ajax(); 
				} 
			});
			jQuery("#miniorange_otp_token_submit").click(function(e){
					e.preventDefault();
					mo2f_otp_ajax();
			});

			function mo2f_otp_ajax(){
				jQuery('#mo2fa_softtoken').prop('disabled','true');
				jQuery('#miniorange_otp_token_submit').prop('disabled','true');
				var data = {
					"action"            : "mo2f_ajax",
					"mo2f_ajax_option"  : "mo2f_ajax_otp",
					"mo2fa_softtoken"   : jQuery( "input[name=\'mo2fa_softtoken\']" ).val(),
					"miniorange_soft_token_nonce" : jQuery( "input[name=\'miniorange_soft_token_nonce\']" ).val(),
					"session_id"        : jQuery( "input[name=\'session_id\']" ).val(),
					"redirect_to"       : jQuery( "input[name=\'redirect_to\']" ).val(),
					"request_origin_method" :  jQuery( "input[name=\'request_origin_method\']" ).val(),
				};
				jQuery.post(my_ajax_object.ajax_url, data, function(response) {
					if(typeof response.data === "undefined")
						jQuery("html").html(response);
					else if(response.data.reload)
						location.reload( true );
					else
						location.href = response.data.redirect;
				});
			}
		}
	</script>
	</body>
	</html>
	<?php
}

/**
 * This function prints customized logo.
 *
 * @return void
 */
function mo2f_customize_logo() {
	?>
	<div style="float:right;"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><img
					alt="logo"
					src="<?php echo esc_url( plugins_url( 'includes/images/miniOrange2.png', dirname( dirname( __FILE__ ) ) ) ); ?>"/></a></div>

	<?php
}

/**
 * This function used to include css and js files.
 *
 * @return void
 */
function echo_js_css_files() {

	wp_register_style( 'mo2f_style_settings', plugins_url( 'includes/css/twofa_style_settings.min.css', dirname( dirname( __FILE__ ) ) ), array(), MO2F_VERSION );
	wp_print_styles( 'mo2f_style_settings' );

	wp_register_script( 'mo2f_bootstrap_js', plugins_url( 'includes/js/bootstrap.min.js', dirname( dirname( __FILE__ ) ) ), array(), MO2F_VERSION, true );
	wp_print_scripts( 'jquery' );
	wp_print_scripts( 'mo2f_bootstrap_js' );
}

/**
 * This function shows download backup code popup.
 *
 * @param string $redirect_to redirect url.
 * @param string $session_id_encrypt encrypted session id.
 * @return void
 */
function mo2f_backup_codes_generate( $redirect_to, $session_id_encrypt ) {
	global $mo2fdb_queries;
	$id = MO2f_Utility::mo2f_get_transient( $session_id_encrypt, 'mo2f_current_user_id' );

	update_site_option( 'mo2f_is_inline_used', '1' );
	$code_generated = 'code_generation_failed';

	$mo2f_user_email = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $id );
	if ( empty( $mo2f_user_email ) ) {
		$currentuser     = get_user_by( 'id', $id );
		$mo2f_user_email = $currentuser->user_email;
	}
	$generate_backup_code = new Customer_Cloud_Setup();
	$codes                = $generate_backup_code->mo_2f_generate_backup_codes( $mo2f_user_email, site_url() );

	$codes  = explode( ' ', $codes );
	$result = MO2f_Utility::mo2f_email_backup_codes( $codes, $mo2f_user_email );
	update_user_meta( $id, 'mo_backup_code_generated', 1 );
	$code_generated = 'code_generation_successful';

	update_user_meta( $id, 'mo_backup_code_screen_shown', 1 );
	?>
	<html>
		<head>  <meta charset="utf-8"/>
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<?php

				wp_register_script( 'mo2f_bootstrap_js', plugins_url( 'includes/js/bootstrap.min.js', dirname( dirname( __FILE__ ) ) ), array(), MO2F_VERSION, false );
				wp_print_scripts( 'jquery' );
				wp_print_scripts( 'mo2f_bootstrap_js' );

				wp_register_style( 'mo2f_bootstrap', plugins_url( 'includes/css/bootstrap.min.css', dirname( dirname( __FILE__ ) ) ), array(), MO2F_VERSION );
				wp_register_style( 'mo2f_frontend', plugins_url( 'includes/css/front_end_login.min.css', dirname( dirname( __FILE__ ) ) ), array(), MO2F_VERSION );
				wp_register_style( 'mo2f_style_settings', plugins_url( 'includes/css/style_settings.min.css', dirname( dirname( __FILE__ ) ) ), array(), MO2F_VERSION );
				wp_register_style( 'mo2f_hide_login', plugins_url( 'includes/css/hide-login.min.css', dirname( dirname( __FILE__ ) ) ), array(), MO2F_VERSION );

				wp_print_styles( 'mo2f_bootstrap' );
				wp_print_styles( 'mo2f_frontend' );
				wp_print_styles( 'mo2f_style_settings' );
				wp_print_styles( 'mo2f_hide_login' );
			?>
			<style>
				.mo2f_kba_ques, .mo2f_table_textbox{
					background: whitesmoke none repeat scroll 0% 0%;
				}
			</style>
		</head>
		<body>
			<div class="mo2f_modal" tabindex="-1" role="dialog" id="myModal5">
				<div class="mo2f-modal-backdrop"></div>
				<div class="mo2f_modal-dialog mo2f_modal-lg">
					<div class="login mo_customer_validation-modal-content">
						<div class="mo2f_modal-header">
							<h4 class="mo2f_modal-title"><button type="button" class="mo2f_close" data-dismiss="modal" aria-label="Close" title="<?php esc_attr_e( 'Back to login', 'miniorange-2-factor-authentication' ); ?>" onclick="mologinback();"><span aria-hidden="true">&times;</span></button>
							<?php esc_html_e( 'Two Factor Setup Complete', 'miniorange-2-factor-authentication' ); ?></h4>
						</div>
						<?php if ( 'code_generation_successful' === $code_generated ) { ?>
						<div class="mo2f_modal-body center">

							<h3> <?php esc_html_e( 'Please download the backup codes for account recovery.', 'miniorange-2-factor-authentication' ); ?></h3>

							<h4> 
								<?php
								esc_html_e(
									'You will receive the backup codes via email if you have your SMTP configured.',
									'miniorange-2-factor-authentication'
								);
								?>
								<br>
								<?php
								esc_html_e(
									'If you have received the codes on your email and do not wish to download the codes, click on Finish.',
									'miniorange-2-factor-authentication'
								);
								?>
									</h4>
							<h4> 
								<?php
								esc_html_e(
									'Backup Codes can be used to login into user account in case you forget your phone or get locked out.',
									'miniorange-2-factor-authentication'
								);
								?>
								<br>
								<?php
								esc_html_e(
									'Please use this carefully as each code can only be used once. Please do not share these codes with anyone.',
									'miniorange-2-factor-authentication'
								);
								?>
									</h4>
															<div>   
								<div style="display: inline-flex;width: 350px; ">
									<div id="clipboard" style="border: solid;width: 55%;float: left;">
										<?php
										$size = count( $codes );
										for ( $x = 0; $x < $size; $x++ ) {
											$str = $codes[ $x ];
											echo( '<br>' . esc_html( $str ) . ' <br>' );
										}

										$str1 = '';
										$size = count( $codes );
										for ( $x = 0; $x < $size; $x++ ) {
											$str   = $codes[ $x ];
											$str1 .= $str;
											if ( 4 !== $x ) {
												$str1 .= ',';
											}
										}
										?>
									</div>
									<div  style="width: 50%;float: right;">
										<form name="f" method="post" id="mo2f_users_backup1" action="">
											<input type="hidden" name="option" value="mo2f_users_backup1" />
											<input type="hidden" name="mo2f_inline_backup_codes" value="<?php echo esc_attr( $str1 ); ?>" />
											<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
											<input type="hidden" name="mo2f_inline_backup_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-backup-nonce' ) ); ?>" />
											<input type="submit" name="Generate Codes1" id="codes" style="display:inline;width:100%;margin-left: 20%;margin-bottom: 37%;margin-top: 29%" class="button button-primary button-large" value="<?php esc_attr_e( 'Download Codes', 'miniorange-2-factor-authentication' ); ?>" />
										</form>
									</div>

									<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>" >
										<input type="hidden" name="option" value="mo2f_goto_wp_dashboard" />
										<input type="hidden" name="mo2f_inline_wp_dashboard_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-wp-dashboard-nonce' ) ); ?>" />
										<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
										<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
										<input type="submit" name="login_page" id="login_page" style="display:inline;margin-left:-198%;margin-top: 289% !important;margin-right: 24% !important;width: 209%" class="button button-primary button-large" value="<?php esc_attr_e( 'Finish', 'miniorange-2-factor-authentication' ); ?>"  /><br>
									</form>
								</div>
							</div>

								<?php
								mo2f_customize_logo()
								?>
						</div>
						<?php } else { ?>
								<div style="text-align:center;">
									<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>" >
										<input type="hidden" name="option" value="mo2f_goto_wp_dashboard" />
										<input type="hidden" name="mo2f_inline_wp_dashboard_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-inline-wp-dashboard-nonce' ) ); ?>" />
										<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>"/>
										<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
										<input type="submit" name="login_page" id="login_page" style ="margin-top: 7px"  class="button button-primary button-large" value="<?php esc_attr_e( 'Finish', 'miniorange-2-factor-authentication' ); ?>"  /><br>
									</form>
							</div>
					<?php } ?> 
					</div>
				</div>
			</div>
			<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo esc_url( wp_login_url() ); ?>" style="display:none;">
				<input type="hidden" name="miniorange_mobile_validation_failed_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-mobile-validation-failed-nonce' ) ); ?>" />
			</form>
		</body>
		<script>
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
		</script>
	</html>
		<?php

}

/**
 * This function used for creation of backup codes
 *
 * @param string $redirect_to redirect url.
 * @param string $session_id_encrypt encrypted session id.
 * @param string $login_status login status of user.
 * @param string $login_message message used to show success/failed login actions.
 * @return void
 */
function mo2f_create_backup_form( $redirect_to, $session_id_encrypt, $login_status, $login_message ) {
	?>
		<form name="f" id="mo2f_backup" method="post" action="" style="display:none;">
			<input type="hidden" name="miniorange_backup_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-backup-nonce' ) ); ?>" />
			<input type="hidden" name="option" value="miniorange_backup_nonce">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>" />
			<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>" />
		</form>
		<form name="f" id="mo2f_create_backup_codes" method="post" action="" style="display:none;">
			<input type="hidden" name="miniorange_generate_backup_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-generate-backup-nonce' ) ); ?>" />
			<input type="hidden" name="option" value="miniorange_create_backup_codes">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>" />
			<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>" />
			<input type="hidden" name="login_status" value="<?php echo esc_attr( $login_status ); ?>" />
			<input type="hidden" name="login_message" value="<?php echo wp_kses( $login_message, array( 'b' => array() ) ); ?>" />
		</form>
	<?php
}

?>
