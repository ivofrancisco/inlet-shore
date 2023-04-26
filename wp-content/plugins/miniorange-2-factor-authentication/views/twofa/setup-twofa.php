<?php
/**
 * This file contains plugin's main dashboard UI.
 *
 * @package miniorange-2-factor-authentication/views/twofa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

	$user = wp_get_current_user();
	global $mo2fdb_queries;
	$mo2f_second_factor      = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
	$configured_2_f_a_method = $mo2f_second_factor;
if ( 'OTP Over Telegram' !== $mo2f_second_factor ) {
	$mo2f_second_factor = mo2f_get_activated_second_factor( $user );
}



	$is_customer_admin_registered = get_option( 'mo_2factor_admin_registration_status' );
	$configured_2_f_a_method      = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
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
	$test_method = $selected_method;
} else {
	$selected_method = mo2f_decode_2_factor( $mo2f_second_factor, 'servertowpdb' );
	$test_method     = $selected_method;
}

if ( 'NONE' === $test_method ) {
			$test_method = 'Not Configured';
}
if ( 'NONE' !== $selected_method && ! MO2F_IS_ONPREM && 'OTP Over Telegram' !== $selected_method ) {
	$mo2fdb_queries->update_user_details(
		$user->ID,
		array(
			'mo2f_configured_2FA_method' => $selected_method,
			'mo2f_' . str_replace( ' ', '', $selected_method ) . '_config_status' => true,
		)
	);
	update_option( 'mo2f_configured_2_factor_method', $selected_method );
}

if ( 'OTP Over SMS' === $configured_2_f_a_method ) {
	update_option( 'mo2f_show_sms_transaction_message', 1 );
} else {
	update_option( 'mo2f_show_sms_transaction_message', 0 );
}
	$is_customer_admin          = current_user_can( 'manage_options' );
	$can_display_admin_features = ! $is_customer_admin_registered || $is_customer_admin ? true : false;

	$is_customer_registered = 'SUCCESS' === $mo2fdb_queries->get_user_detail( 'user_registration_with_miniorange', $user->ID ) ? true : false;
if ( get_user_meta( $user->ID, 'configure_2FA', true ) ) {

	$current_selected_method = get_user_meta( $user->ID, 'mo2f_2FA_method_to_configure', true );

	mo2f_show_2fa_configuration_screen( $user, $current_selected_method );
} elseif ( get_user_meta( $user->ID, 'test_2FA', true ) ) {
	$current_selected_method = get_user_meta( $user->ID, 'mo2f_2FA_method_to_test', true );

	echo '<div class="mo2f_table_layout">';
		mo2f_show_2fa_test_screen( $user, $current_selected_method );
	echo '</div>';
} elseif ( get_user_meta( $user->ID, 'register_account_popup', true ) && $can_display_admin_features ) {
	display_customer_registration_forms( $user );
} else {
	$is_n_c                  = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
	$free_plan_existing_user = array(
		'Email Verification',
		'Security Questions',
		'OTP Over SMS',
		'OTP Over Email',
		'miniOrange Authenticator',
		'Google Authenticator',
		'Authy Authenticator',
		'OTP Over Telegram',
		'Duo Authenticator',

	);

	$free_plan_new_user = array(
		'Google Authenticator',
		'Security Questions',
		'OTP Over SMS',
		'OTP Over Email',
		'miniOrange Authenticator',
		'OTP Over Telegram',
		'Duo Authenticator',
	);

	$standard_plan_existing_user = array(
		'',
		'OTP Over Email',
		'OTP Over SMS and Email',
	);

	$standard_plan_new_user = array(
		'',
		'Email Verification',
		'OTP Over SMS',
		'OTP Over Email',
		'OTP Over SMS and Email',
		'OTP Over Whatsapp',
		'Authy Authenticator',
	);

	$premium_plan = array(
		'Hardware Token',
	);

	if ( MO2F_IS_ONPREM ) {
		$free_plan_existing_user = array(
			'Email Verification',
			'Security Questions',
			'OTP Over SMS',
			'OTP Over Email',
			'Google Authenticator',
			'miniOrange Authenticator',
			'OTP Over Telegram',
			'Duo Authenticator',

		);

		$free_plan_new_user = array(
			'Google Authenticator',
			'OTP Over SMS',
			'OTP Over Email',
			'Duo Authenticator',
			'Security Questions',
			'miniOrange Authenticator',
			'OTP Over Telegram',


		);

		$premium_plan = array(
			'Hardware Token',
			'Authy Authenticator',
			'OTP Over Whatsapp',
		);

		$standard_plan_existing_user = array(
			'',
			'OTP Over SMS and Email',
		);
		$standard_plan_new_user      = array(
			'',
			'Email Verification',
			'OTP Over SMS and Email',
		);
	}

	if ( ! current_user_can( 'administrator' ) && ! mo2f_is_customer_registered() ) { // hiding cloud methods for users if admin is not registered.
		$methods_of_users        = mo2f_get_2fa_methods_for_users();
		$free_plan_existing_user = $methods_of_users['existing_user'];
		$free_plan_new_user      = $methods_of_users['new_user'];
	}
	update_site_option( 'mo2fa_free_plan_new_user_methods', $free_plan_new_user );
	update_site_option( 'mo2fa_free_plan_existing_user_methods', $free_plan_existing_user );

	$free_plan_methods_existing_user     = array_chunk( $free_plan_existing_user, 3 );
	$free_plan_methods_new_user          = array_chunk( $free_plan_new_user, 3 );
	$standard_plan_methods_existing_user = array_chunk( $standard_plan_existing_user, 3 );
	$standard_plan_methods_new_user      = array_chunk( $standard_plan_new_user, 3 );

	$premium_plan_methods_existing_user = array_chunk( array_merge( $standard_plan_existing_user, $premium_plan ), 3 );
	$premium_plan_methods_new_user      = array_chunk( array_merge( $standard_plan_new_user, $premium_plan ), 3 );
	$show_o_t_p                         = false;
	if ( MO2F_IS_ONPREM ) {
		$selected_method        = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2FA_method', $user->ID );
		$is_customer_registered = true;
		$test_method            = $selected_method;
		if ( empty( $selected_method ) ) {
			$selected_method = 'NONE';
			$test_method     = 'Not Configured';
		}
	}
	?>


			<div class="mo2f_table_layout_method">
			<div class="mo2f-top-content">
			<div class="mo2f_view_free_plan_auth_methods" onclick="show_free_plan_auth_methods()">
						<?php if ( $can_display_admin_features ) { ?>
								<span><?php esc_html_e( 'CURRENT PLAN', 'miniorange-2-factor-authentication' ); ?></span>
							<?php } ?>
					</div>
					<div class="test_auth_button">
				<?php
				$user_id = get_current_user_id();
				if ( isset( $mo2f_two_fa_method ) && ! empty( $mo2f_two_fa_method ) && ! get_user_meta( $user_id, 'mo_backup_code_limit_reached' ) ) {
					?>
						<button class="mo2f-test-button" id="mo_2f_generate_codes">Download Backup Codes
						</button>
					<?php
				}
				global $mo2fdb_queries;
				$customer_registered = 'MO_2_FACTOR_PLUGIN_SETTINGS' === get_option( 'mo_2factor_user_registration_status' );
				$count               = $mo2fdb_queries->mo2f_get_sms_users_count();
				if ( $customer_registered && 0 !== $count && current_user_can( 'administrator' ) ) {
					?>
					<button onclick="window.open('<?php echo esc_url( MoWpnsConstants::RECHARGELINK ); ?>')" class="mo2f-test-button">Add SMS</button>
					<?php } ?>
					<button class="mo2f-test-button" id="test" onclick="testAuthenticationMethod('<?php echo esc_attr( $selected_method ); ?>');"
					<?php echo $is_customer_registered && ( 'NONE' !== $selected_method ) ? '' : ' disabled '; ?>>Test - <strong> <?php echo esc_html( $selected_method ); ?> </strong>
					</button>
				</div>
		</div>
				<?php
				if ( current_user_can( 'administrator' ) ) {

							$email_transactions = MoWpnsUtility::get_mo2f_db_option( 'cmVtYWluaW5nT1RQ', 'site_option' );
							$email_transactions = $email_transactions ? $email_transactions : 0;
							$s_m_s_transactions = get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) ? get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) : 0;
							$color_tras_sms     = $s_m_s_transactions <= 2 ? 'red' : '#f3dbdb';
							$color_tras_email   = $email_transactions <= 2 ? 'red' : '#f3dbdb';
					?>
					<?php
				}

				mo2f_create_2fa_form( $user, 'free_plan', $is_n_c ? $free_plan_methods_new_user : $free_plan_methods_existing_user, $can_display_admin_features );
				?>
			</div>

			<?php if ( $can_display_admin_features ) { ?>
				<div class="mo2f-premium-features">
				<span id="mo2f_premium_plan"> <a class="mo2f_view_premium_plan_auth_methods" onclick="show_premium_auth_methods()">
						<p><?php esc_html_e( 'PREMIUM PLAN', 'miniorange-2-factor-authentication' ); ?></p></a></span>
					<?php mo2f_create_2fa_form( $user, 'premium_plan', $is_n_c ? $premium_plan_methods_new_user : $premium_plan_methods_existing_user ); ?>

				</div>
				<br>
				<?php } ?>
				<form name="f" method="post" action="" id="mo2f_2factor_test_authentication_method_form">
					<input type="hidden" name="option" value="mo_2factor_test_authentication_method"/>
					<input type="hidden" name="mo2f_configured_2FA_method_test" id="mo2f_configured_2FA_method_test"/>
					<input type="hidden" name="mo_2factor_test_authentication_method_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'mo-2factor-test-authentication-method-nonce' ) ); ?>"/>
				</form>
				<form name="f" method="post" action="" id="mo2f_2factor_resume_flow_driven_setup_form">
					<input type="hidden" name="option" value="mo_2factor_resume_flow_driven_setup"/>
					<input type="hidden" name="mo_2factor_resume_flow_driven_setup_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'mo-2factor-resume-flow-driven-setup-nonce' ) ); ?>"/>
				</form>

				<form name="f" method="post" action="" id="mo2f_2factor_generate_backup_codes">
					<input type="hidden" name="option" value="mo2f_2factor_generate_backup_codes"/>
					<input type="hidden" name="mo_2factor_generate_backup_codes_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'mo-2factor-generate-backup-codes-nonce' ) ); ?>"/>
				</form>              

		<div id="EnterEmailCloudVerification" class="modal">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title mo2f-email-otp">Email Address for miniOrange</h3><span id="closeEnterEmailCloud" class="modal-span-close">X</span>
				</div>
				<div class="modal-body" style="height: auto">
					<h2 style="color: red;">The email associated with your account is already registered in miniOrange. Please Choose another email.</h2>
					<h2><i>Enter your Email:&nbsp;&nbsp;&nbsp;  <input type ='email' id='emailEnteredCloud' name='emailEnteredCloud' size= '40' required value="<?php echo esc_attr( $email ); ?>"/></i></h2> 
				</div>
				<div class="modal-footer">
					<button type="button" class="button button-primary button-large modal-button" id="save_entered_email_cloud">Save</button>
				</div>
			</div>
		</div>
		<div id="EnterEmail" class="modal">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title mo2f-email-otp">Email Address for OTP</h3><span id="closeEnterEmail" class="modal-span-close">X</span>
				</div>
				<div class="modal-body" style="height: auto">
					<h2><i>Enter your Email:&nbsp;&nbsp;&nbsp;  <input type ='text' id='emailEntered' pattern="[^@\s]+@[^@\s]+\.[^@\s]+" name='emailEntered'  size= '40' required value="<?php echo esc_attr( $email ); ?>"/></i></h2> 
					<?php if ( current_user_can( 'administrator' ) ) { ?>
				<i class="note">NOTE :- If you haven't configured SMTP, please set your SMTP to get the OTP over email.</i>
				<a href='<?php echo esc_url( $two_factor_premium_doc['Setup SMTP'] ); ?>'target="_blank">
			<span title="View Setup Guide" class="dashicons dashicons-text-page mo2f-setup-guide"></a></span>   
				<?php } ?>
				</div>
				<div class="modal-footer">
					<input type="text" id="current_method" hidden value=""> 
					<button type="button" class="button button-primary button-large"  id="save_entered_email" >Send OTP</button>
				</div>
			</div>
		</div>

		<div id="mo2f_cloud" class = "modal" style="display: none;">
			<div id="mo2f_cloud_modal" class="modal-content" style="width: 30%;overflow: hidden;" >

			<div class="modal-header">
			<h3 class="modal-title" style="text-align: center; font-size: 20px; color: #2980b9">
					Are you sure you want to do that?
				</h3>
			</div>
			<div class="modal-body" style="height: auto;background-color: beige;">
			<div style="text-align: center;">

				<?php
					$user_id = get_current_user_id();
					global $mo2fdb_queries;
					$current_method = $mo2fdb_queries->get_user_detail( 'mo2f_configured_2FA_method', $user_id );
				?>
				<br>
				<h4 style="color: red;">You need to reconfigure second-factor by registering in miniOrange.</h4>
				<h4 style="color: red;">It will be available for one user in free plan.</h4>

				</div></div>
			<div class="modal-footer">
				<button type="button" class="button button-primary button-large modal-button" style="width: 30%;background-color:#61ace5;" id="ConfirmCloudButton1">Confirm</button>
				<button type="button" class="button button-primary button-large modal-button" style="width: 30%;background-color:#ff4168;" id="closeConfirmCloud1">Cancel</button>

			</div>
			</div>
		</div>
<script>
	const btn = document.getElementById('save_entered_email');


		btn.addEventListener('click', function handleClick() {
							btn.textContent = 'Sending OTP';
							jQuery("#save_entered_email").attr("disabled", true);
			});
		jQuery('#closeConfirmCloud1').click(function(){
			jQuery('#mo2f_cloud').css('display', 'none');
		});
		jQuery('#OTPOverEmail_configuration').click(function(){
			jQuery(document).ready(function(){
				var input = jQuery("#emailEntered");
				var len = input.val().length;
				input[0].focus();
				input[0].setSelectionRange(len, len);
		});
	});

		jQuery('#ConfirmCloudButton1').click(function(){
			document.getElementById('mo2f_cloud').checked = false;
			document.getElementById('mo2f_cloud_modal').style.display = "none";
			var nonce = '<?php echo esc_js( wp_create_nonce( 'singleUserNonce' ) ); ?>';
			var data = {
						'action'                    : 'mo_two_factor_ajax',
						'mo_2f_two_factor_ajax'     : 'mo2f_single_user',
						'nonce' :  nonce
					};
				jQuery.post(ajaxurl, data, function(response) {
						if(response == 'true')
						{
							location.reload(true);                     

						}
						else
						{
							jQuery('#mo2f_cloud').css('display', 'none');  
							error_msg("<b>You are not authorized to perform this action</b>. Only <b>\"+response+\"</b> is allowed. For more details contact miniOrange.");
						}
				});
		});
		jQuery('#test').click(function(){
				jQuery("#test").attr("disabled", true);
			});
			jQuery('#closeEnterEmailCloud').click(function(){
				jQuery('#EnterEmailCloudVerification').css('display', 'none');
			});
			jQuery('#closeEnterEmail').click(function(){
				jQuery('#EnterEmail').css('display', 'none');
						});
			var emailinput = document.getElementById("emailEntered");
			emailinput.addEventListener("keyup", function(event) {
			if (event.keyCode === 13) {
				event.preventDefault();
				document.getElementById("save_entered_email").click();
				}   
			});
			jQuery('#save_entered_email').click(function(){
				var email   = jQuery('#emailEntered').val();
				var nonce   = '<?php echo esc_js( wp_create_nonce( 'mo-two-factor-ajax-nonce' ) ); ?>';
				var current_method = jQuery('#current_method').val();
				if(email != '')
				{
					var data = {
					'action'                        : 'mo_two_factor_ajax',
					'mo_2f_two_factor_ajax'         : 'mo2f_save_email_verification', 
					'nonce'                         : nonce,
					'email'                         : email,
					'current_method'                : current_method
					};
					jQuery.post(ajaxurl, data, function(response) {    
							var response = response.replace(/\s+/g,' ').trim();
							if(response=="settingsSaved")
							{
								var method = jQuery('#current_method').val();
								jQuery('#mo2f_configured_2FA_method_free_plan').val(method);
								jQuery('#mo2f_selected_action_free_plan').val('select2factor');
								jQuery('#mo2f_save_free_plan_auth_methods_form').submit();
							}
							else if(response == "NonceDidNotMatch")
							{   
								jQuery("#save_entered_email").attr("disabled",false);
								const btn = document.getElementById('save_entered_email');
								btn.textContent = 'Send OTP';
								error_msg("An unknown error has occured.");
							}else if(response=="USER_LIMIT_EXCEEDED"){
								jQuery("#save_entered_email").attr("disabled",false);
								const btn = document.getElementById('save_entered_email');
								btn.textContent = 'Send OTP';
								error_msg(" Your limit of 3 users has exceeded. Please upgrade to premium plans to setup 2FA for more users");
							}
							else if (response == "smtpnotset"){					   
								jQuery("#save_entered_email").attr("disabled",false);
								const btn = document.getElementById('save_entered_email');
								btn.textContent = 'Send OTP';
							error_msg(" Please set up SMTP for your website to receive emails and prevent the accidental lock out");
							}
							else
							{
								error_msg(" Invalid Email.");
								jQuery("#save_entered_email").attr("disabled",false);
								const btn = document.getElementById('save_entered_email');
								btn.textContent = 'Send OTP';

							}    
							close_modal();
						});
				}
				else
				{
					error_msg("Please enter your email");
					jQuery("#save_entered_email").attr("disabled",false);
					const btn = document.getElementById('save_entered_email');
					btn.textContent = 'Send OTP';
				}

			});

			jQuery('#mo_2f_generate_codes').click(function(){
					jQuery("#mo2f_2factor_generate_backup_codes").submit();
					jQuery("#mo2f_free_plan_auth_methods").slideToggle(1000);
				});
			function show_3_minorange_methods(){
			authMethod = jQuery("#mo2fa_MO_methods").val();
				configureOrSet2ndFactor_free_plan(authMethod,'select2factor');
			}
			function configureOrSet2ndFactor_free_plan(authMethod, action, cloudswitch=null,allowed=null) {
				var is_onprem       = '<?php echo esc_js( MO2F_IS_ONPREM ); ?>';
				if(authMethod === 'miniOrangeAuthenticator')
					authMethod = jQuery("#miniOrangeAuthenticator").val();
				<?php
				global $mo2fdb_queries;
				$current_user_info  = wp_get_current_user();
				$is_user_registered = $mo2fdb_queries->get_user_detail( 'mo2f_user_email', $current_user_info->ID ) ? true : false;

				?>
				var is_user_registered = '<?php echo esc_js( $is_user_registered ); ?>';
				if((is_onprem === 0 || authMethod==='miniOrangeSoftToken'|| authMethod==='miniOrangeQRCodeAuthentication'|| authMethod==='miniOrangePushNotification') && is_user_registered == 0)
				{
					var nonce = '<?php echo esc_js( wp_create_nonce( 'mo-two-factor-ajax-nonce' ) ); ?>';
					var data = {
								'action'                    : 'mo_two_factor_ajax',
								'mo_2f_two_factor_ajax'     : 'mo2f_check_user_exist_miniOrange',
								'nonce' :  nonce
							};
						jQuery.post(ajaxurl, data, function(response) {
						if(response == 'alreadyExist')
						{
							jQuery('#EnterEmailCloudVerification').css('display', 'block');
							jQuery('.modal-content').css('width', '35%');

							jQuery('#save_entered_email_cloud').click(function(){

								jQuery('#EnterEmailCloudVerification').css('display', 'none');
								var nonce = '<?php echo esc_js( wp_create_nonce( 'mo-two-factor-ajax-nonce' ) ); ?>';
								var email   = jQuery('#emailEnteredCloud').val();
								var data = {
									'action'                    : 'mo_two_factor_ajax',
									'mo_2f_two_factor_ajax'     : 'mo2f_check_user_exist_miniOrange',
									'email'                     : email,
									'nonce' :  nonce
								};

								jQuery.post(ajaxurl, data, function(response) {

									if(response === 'alreadyExist')
									{

										jQuery('#EnterEmailCloudVerification').css('display', 'block');
										jQuery('.modal-content').css('width', '35%');
									}
									else if(response ==="USERCANBECREATED")
									{
										jQuery('#mo2f_configured_2FA_method_free_plan').val(authMethod);
										jQuery('#mo2f_selected_action_free_plan').val(action);
										jQuery('#mo2f_save_free_plan_auth_methods_form').submit();

									}
								});

							});
						}
						else if(response =="USERCANBECREATED")
						{
							jQuery('#mo2f_configured_2FA_method_free_plan').val(authMethod);
							jQuery('#mo2f_selected_action_free_plan').val(action);
							jQuery('#mo2f_save_free_plan_auth_methods_form').submit();

						}
						else if(response == "NOTLOGGEDIN")
						{
							jQuery('#mo2f_configured_2FA_method_free_plan').val(authMethod);
							jQuery('#mo2f_selected_action_free_plan').val(action);
							jQuery('#mo2f_save_free_plan_auth_methods_form').submit();
						}else{
						}

					});
				}
				else
				{
				if(authMethod == 'EmailVerification' || authMethod == 'OTPOverEmail')
				{
					var is_registered   = '<?php echo esc_js( $email_registered ); ?>';
					jQuery('#current_method').val(authMethod);

					if(is_onprem == 1 && is_registered!=0 && action != 'select2factor')
					{
						jQuery('#EnterEmail').css('display', 'block');
						jQuery('.modal-content').css('width', '35%');
					}
					else
					{
						jQuery('#mo2f_configured_2FA_method_free_plan').val(authMethod);
						jQuery('#mo2f_selected_action_free_plan').val(action);
						jQuery('#mo2f_save_free_plan_auth_methods_form').submit();       
					}
				} 
				else
				{
					jQuery('#mo2f_configured_2FA_method_free_plan').val(authMethod);
					jQuery('#mo2f_selected_action_free_plan').val(action);
					jQuery('#mo2f_save_free_plan_auth_methods_form').submit();
		}
			}            
			}

			function testAuthenticationMethod(authMethod) {
				jQuery('#mo2f_configured_2FA_method_test').val(authMethod);
				jQuery('#loading_image').show();

				jQuery('#mo2f_2factor_test_authentication_method_form').submit();
			}

			function resumeFlowDrivenSetup() {
				jQuery('#mo2f_2factor_resume_flow_driven_setup_form').submit();
			}


			function show_free_plan_auth_methods() {
				jQuery("#mo2f_free_plan_auth_methods").slideToggle(1000);                
			}


			function show_premium_auth_methods() {
				jQuery("#mo2f_premium_plan_auth_methods").slideToggle(1000);
			}

			jQuery("#how_to_configure_2fa").hide();

			function show_how_to_configure_2fa() {
				jQuery("#how_to_configure_2fa").slideToggle(700);
			}

		</script>
<?php }

/**
 * This function is to fetch 2fa method of user.
 *
 * @package miniorange-2-factor-authentication/view/twofa
 */
function mo2f_get_2fa_methods_for_users() {
	$free_plan_existing_user = array(
		'Email Verification',
		'Security Questions',
		'OTP Over Email',
		'Google Authenticator',
		'OTP Over Telegram',
		'Duo Authenticator',

	);

	$free_plan_new_user = array(
		'Google Authenticator',
		'Security Questions',
		'OTP Over Email',
		'OTP Over Telegram',
		'Duo Authenticator',

	);

	return array(
		'existing_user' => $free_plan_existing_user,
		'new_user'      => $free_plan_new_user,
	);
}

?>
