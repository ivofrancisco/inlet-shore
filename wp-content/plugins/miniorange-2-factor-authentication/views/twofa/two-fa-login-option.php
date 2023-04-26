<?php
/**
 * This file contains the UI for various plugin settings for user login.
 *
 * @package miniorange-2-factor-authentication/views/twofa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $mo2fdb_queries,$main_dir;
	$roles = get_editable_roles();

	$mo_2factor_user_registration_status = $mo2fdb_queries->get_user_detail( 'mo_2factor_user_registration_status', $user->ID );
?>
<?php if ( ! MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' ) && MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' ) ) { ?>
	<div class="mo2f_advanced_options_EC" style="width: 85%;border: 0px;">
			<?php echo esc_html( get_standard_premium_options( $user ) ); ?>
		</div>
	<?php
} else {

	$mo2f_active_tab = '2factor_setup';
	?>
	<div class="mo_wpns_setting_layout">
		<div class="mo2f_advanced_options_EC">

			<div id="mo2f_login_options">
				<a href="#standard_premium_options" style="float:right">Show Standard/Premium
					Features</a></h3>

				<form name="f" id="login_settings_form" method="post" action="">
					<input type="hidden" name="option" value="mo_auth_login_settings_save"/>
					<input type="hidden" name="mo_auth_login_settings_save_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo-auth-login-settings-save-nonce' ) ); ?>"/>
					<div class="row">
						<h3 style="padding:10px;"><?php esc_html_e( 'Select Login Screen Options', 'miniorange-2-factor-authentication' ); ?>

					</div>
					<hr>
					<br>


					<div style="margin-left: 2%;">
						<input type="radio" name="mo2f_login_option" value="1"
						<?php
						checked( MoWpnsUtility::get_mo2f_db_option( 'mo2f_login_option', 'get_option' ) );
						if ( 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status || MO2F_IS_ONPREM ) {
							echo '!disabled';
						} else {
							echo 'disabled';
						}
						?>
							/>
						<?php esc_html_e( 'Login with password + 2nd Factor ', 'miniorange-2-factor-authentication' ); ?>
						<i>(<?php esc_html_e( 'Default & Recommended', 'miniorange-2-factor-pluign' ); ?>)&nbsp;&nbsp;</i>

						<br><br>

						<div style="margin-left:6%;">
							<input type="checkbox" id="mo2f_remember_device" name="mo2f_remember_device"
								value="1" />Enable
							'<b><?php esc_html_e( 'Remember device', 'miniorange-2-factor-authentication' ); ?></b>' <?php esc_html_e( 'option ', 'miniorange-2-factor-authentication' ); ?><br>

							<div class="mo2f_advanced_options_note"><p style="padding:5px;">
									<i><?php esc_html_e( ' Checking this option will display an option ', 'miniorange-2-factor-authentication' ); ?>
										'<b><?php esc_html_e( 'Remember this device', 'miniorange-2-factor-authentication' ); ?></b>'<?php esc_html_e( 'on 2nd factor screen. In the next login from the same device, user will bypass 2nd factor, i.e. user will be logged in through username + password only.', 'miniorange-2-factor-authentication' ); ?>
									</i></p></div>
						</div>

						<br>

						<input type="radio" name="mo2f_login_option" value="0"
							<?php
							checked( ! MoWpnsUtility::get_mo2f_db_option( 'mo2f_login_option', 'get_option' ) );
							if ( 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status || MO2F_IS_ONPREM ) {
								echo '!disabled';
							} else {
								echo 'disabled';
							}
							?>
							/>
						<?php esc_html_e( 'Login with 2nd Factor only ', 'miniorange-2-factor-authentication' ); ?>
						<i>(<?php esc_html_e( 'No password required.', 'miniorange-2-factor-authentication' ); ?>)</i> &nbsp;<a 
						data-toggle="collapse"
						id="showLoginwith2ndFactoronly"
						href="#Loginwith2ndFactoronly"
						aria-expanded="false"><?php esc_html_e( 'See preview', 'miniorange-2-factor-authentication' ); ?></a>
						<br>
						<div class="mo2f_collapse" id="Loginwith2ndFactoronly" style="height:300px; ">
						<div class="mo2fa_text-align-center"><br>
								<img style="height:300px;"
									src="<?php echo esc_url( $main_dir . 'includes/images/login-with-2fa-and-password.png' ); ?>">
						</div>
						</div>
						<br>
						<br>
						<div class="mo2f_advanced_options_note"><p style="padding:5px;">
								<i><?php esc_html_e( 'Checking this option will add login with your phone button below default login form. Click above link to see the preview.', 'miniorange-2-factor-authentication' ); ?></i>
							</p></div>
						<div id="loginphonediv" hidden><br>
							<input type="checkbox" id="mo2f_login_with_username_and_2factor"
								name="mo2f_login_with_username_and_2factor"
								value="1" 
								<?php
									checked( 1 === get_option( 'mo2f_enable_login_with_2nd_factor' ) );
								if ( 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status || MO2F_IS_ONPREM ) {
										echo '!disabled';
								} else {
										echo 'disabled';
								}
								?>
							/>
							<?php esc_html_e( '	I want to hide default login form.', 'miniorange-2-factor-authentication' ); ?> &nbsp;<a
									class=""
									data-toggle="collapse"
									href="#hideDefaultLoginForm"
									id = 'showhideDefaultLoginForm'
									aria-expanded="false"><?php esc_html_e( 'See preview', 'miniorange-2-factor-authentication' ); ?></a>
							<br>
							<div class="mo2f_collapse" id="showhideDefaultLoginForm" style="height:300px;">
							<div class="mo2fa_text-align-center"><br>
									<img style="height:300px;"
										src="<?php echo esc_url( $main_dir . 'includes/images/hide_default_login_form.png' ); ?>">
							</div>
							</div>
							<br>
							<br>
							<div class="mo2f_advanced_options_note"><p style="padding:5px;">
									<i><?php esc_html_e( 'Checking this option will hide default login form and just show login with your phone. Click above link to see the preview.', 'miniorange-2-factor-authentication' ); ?></i>
								</p></div>
						</div>
						<br>
					</div>
					<div>
						<h3 style="padding:10px;"><?php esc_html_e( 'Backup Methods', 'miniorange-2-factor-authentication' ); ?></h3></div>
					<hr>
					<br>
					<div style="margin-left: 2%">
						<input type="checkbox" id="mo2f_forgotphone" name="mo2f_forgotphone"
							value="1" 
							<?php
								checked( 1 === MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_forgotphone', 'get_option' ) );
							if ( 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status ) {
								echo '!disabled';
							} else {
								echo 'disabled';
							}
							?>
						/>
						<?php esc_html_e( 'Enable Forgot Phone.', 'miniorange-2-factor-authentication' ); ?>

						<div class="mo2f_advanced_options_note"><p style="padding:5px;">
								<i><?php esc_html_e( 'This option will provide you an alternate way of logging in to your site in case you are unable to login with your primary authentication method.', 'miniorange-2-factor-authentication' ); ?></i>
							</p></div>
						<br>

					</div>
					<div>
						<h3 style="padding:10px;">XML-RPC <?php esc_html_e( 'Settings', 'miniorange-2-factor-authentication' ); ?></h3></div>
					<hr>
					<br>
					<div style="margin-left: 2%">
						<input type="checkbox" id="mo2f_enable_xmlrpc" name="mo2f_enable_xmlrpc"
							value="1" 
							<?php
								checked( 1 === MoWpnsUtility::get_mo2f_db_option( 'mo2f_enable_xmlrpc', 'get_option' ) );
							if ( 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status ) {
									echo '!disabled';
							} else {
									echo 'disabled';
							}
							?>
						/>
						<?php esc_html_e( 'Enable XML-RPC Login.', 'miniorange-2-factor-authentication' ); ?>
						<div class="mo2f_advanced_options_note"><p style="padding:5px;">
								<i><?php esc_html_e( 'Enabling this option will decrease your overall login security. Users will be able to login through external applications which support XML-RPC without authenticating from miniOrange. ', 'miniorange-2-factor-authentication' ); ?>
									<b><?php esc_html_e( 'Please keep it unchecked.', 'miniorange-2-factor-authentication' ); ?></b></i></p></div>


					<div style="padding:10px;">
					<br><br>
					<div class="mo2fa_text-align-center">
						<?php
						if ( 'MO_2_FACTOR_PLUGIN_SETTINGS' === $mo_2factor_user_registration_status || MO2F_IS_ONPREM ) {
							?>
							<input type="submit" name="submit" value="<?php esc_attr_e( 'Save Settings', 'miniorange-2-factor-authentication' ); ?>"
							class="button button-primary button-large">
							<?php
						} else {
							?>
							<input type="submit" name="submit" value="<?php esc_attr_e( 'Save Settings', 'miniorange-2-factor-authentication' ); ?>"
							class="mo_wpns_button" disabled style="background-color: #2271b1;padding: 11px 28px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px;">
							<?php
						}
						?>
						</div>
					</div>
				</div>
					<br></form>
				<hr>
			</div>
		</div>
			<?php get_standard_premium_options( $user ); ?>
		</div>
		<?php
}
?>

		<script>

		if (jQuery("input[name=mo2f_login_option]:radio:checked").val() == 0) {
			jQuery('#loginphonediv').show();
		}
		jQuery("input[name=mo2f_login_option]:radio").change(function () {
			if (this.value == 1) {
				jQuery('#loginphonediv').hide();
			} else {
				jQuery('#loginphonediv').show();
			}
		});

		jQuery('#Loginwith2ndFactoronly').hide();
		jQuery('#showLoginwith2ndFactoronly').click(function(){
					jQuery('#Loginwith2ndFactoronly').slideToggle(700);    
		});        
		jQuery('#Loginwith2ndFactoronlyStandard').hide();
		jQuery('#showLoginwith2ndFactoronlyStandard').click(function(){
			jQuery('#Loginwith2ndFactoronlyStandard').slideToggle(700);    
		});
		jQuery('#LoginWithUsernameOnlyStandard').hide();
		jQuery('#showLoginWithUsernameOnlyStandard').click(function(){
			jQuery('#LoginWithUsernameOnlyStandard').slideToggle(700);    
		});
		jQuery('#Loginwith2ndFactoronlyPremium').hide();
		jQuery('#showLoginwith2ndFactoronlyPremium').click(function(){
			jQuery('#Loginwith2ndFactoronlyPremium').slideToggle(700);    
		});
		jQuery('#LoginWithUsernameOnlyPremium').hide();
		jQuery('#showLoginWithUsernameOnlyPremium').click(function(){
			jQuery('#LoginWithUsernameOnlyPremium').slideToggle(700);    
		});                
		jQuery('#showhideDefaultLoginForm').hide();
		jQuery('#showhideDefaultLoginForm').click(function(){
			jQuery('#showhideDefaultLoginForm').slideToggle(700);    
		});
		function show_backup_options() {
			jQuery("#backup_options").slideToggle(700);
			jQuery("#login_options").hide();
			jQuery("#customizations").hide();
			jQuery("#customizations_prem").hide();
			jQuery("#backup_options_prem").hide();
			jQuery("#inline_registration_options").hide();
		}

		function show_customizations() {
			jQuery("#login_options").hide();
			jQuery("#inline_registration_options").hide();
			jQuery("#backup_options").hide();
			jQuery("#customizations_prem").hide();
			jQuery("#backup_options_prem").hide();
			jQuery("#customizations").slideToggle(700);

		}

		jQuery("#backup_options_prem").hide();

		function show_backup_options_prem() {
			jQuery("#backup_options_prem").slideToggle(700);
			jQuery("#login_options").hide();
			jQuery("#customizations").hide();
			jQuery("#customizations_prem").hide();
			jQuery("#inline_registration_options").hide();
			jQuery("#backup_options").hide();
		}

		jQuery("#login_options").hide();

		function show_login_options() {
			jQuery("#inline_registration_options").hide();
			jQuery("#customizations").hide();
			jQuery("#backup_options").hide();
			jQuery("#backup_options_prem").hide();
			jQuery("#customizations_prem").hide();
			jQuery("#login_options").slideToggle(700);
		}

		jQuery("#inline_registration_options").hide();

		function show_inline_registration_options() {
			jQuery("#login_options").hide();
			jQuery("#customizations").hide();
			jQuery("#backup_options").hide();
			jQuery("#backup_options_prem").hide();
			jQuery("#customizations_prem").hide();
			jQuery("#inline_registration_options").slideToggle(700);

		}

		jQuery("#customizations_prem").hide();

		function show_customizations_prem() {
			jQuery("#inline_registration_options").hide();
			jQuery("#login_options").hide();
			jQuery("#customizations").hide();
			jQuery("#backup_options").hide();
			jQuery("#backup_options_prem").hide();
			jQuery("#customizations_prem").slideToggle(700);

		}

		function showLoginOptions() {
			jQuery("#mo2f_login_options").show();
		}

		function showLoginOptions() {
			jQuery("#mo2f_login_options").show();
		}


	</script>
<?php
/**
 * This function is used to show plugin's premium feature.
 *
 * @param object $user used to get the current user's email or id.
 * @return void
 */
function get_standard_premium_options( $user ) {
	global $main_dir;
	$is_n_c = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );

	?>
	<div >
		<div id="standard_premium_options" style="text-align: center;">
			<p style="font-size:22px;color:darkorange;padding:10px;"><?php esc_html_e( 'Features in the Standard Plan', 'miniorange-2-factor-authentication' ); ?></p>

		</div>

		<hr>
		<?php if ( $is_n_c ) { ?>
			<div>
				<a class="mo2f_view_backup_options" onclick="show_backup_options()">
					<img src="<?php echo esc_url( plugins_url( 'includes/images/right-arrow.png', dirname( dirname( __FILE__ ) ) ) ); ?>"
						class="mo2f_advanced_options_images"/>

					<p class="mo2f_heading_style"><?php esc_html_e( 'Backup Options', 'miniorange-2-factor-authentication' ); ?></p>
				</a>

			</div>
			<div id="backup_options" style="margin-left: 5%;">

				<div class="mo2f_advanced_options_note"><p style="padding:5px;">
						<i>
						<?php
						esc_html_e( 'Use these backup options to login to your site in case your phone is lost / not accessible or if you are not able to login using your primary authentication method.', 'miniorange-2-factor-authentication' );
						?>
							</i></p></div>

				<ol class="mo2f_ol">
					<li><?php esc_html_e( 'KBA (Security Questions)', 'miniorange-2-factor-authentication' ); ?></li>
				</ol>

			</div>
		<?php } ?>

		<div>
			<a class="mo2f_view_customizations" onclick="show_customizations()">
			<p class="mo2f_heading_style"><?php esc_html_e( 'Customizations', 'miniorange-2-factor-authentication' ); ?></p>
			</a>
		</div>


		<div id="customizations" style="margin-left: 5%;">

			<p style="font-size:15px;font-weight:bold">1. <?php esc_html_e( 'Login Screen Options', 'miniorange-2-factor-authentication' ); ?></p>
			<div>
				<ul style="margin-left:4%" class="mo2f_ol">
					<li><?php esc_html_e( 'Login with WordPress username/password and 2nd Factor', 'miniorange-2-factor-authentication' ); ?> <a
								class="" data-toggle="collapse" id="showLoginwith2ndFactoronlyStandard" href="#Loginwith2ndFactoronlyStandard"
								aria-expanded="false">[ <?php esc_html_e( 'See Preview', 'miniorange-2-factor-authentication' ); ?>
							]</a>
							<div class="mo2f_collapse" id="Loginwith2ndFactoronlyStandard" style="height:300px;">
							<div class="mo2fa_text-align-center"><br>
								<img style="height:300px;"
								src="<?php echo esc_url( $main_dir . 'includes/images/login-with-2fa-and-password.png' ); ?>">
						</div>
						</div>
					</li><br>
					<li><?php esc_html_e( 'Login with WordPress username and 2nd Factor only', 'miniorange-2-factor-authentication' ); ?> <a
								class="" data-toggle="collapse" id="showLoginWithUsernameOnlyStandard" href="#LoginWithUsernameOnlyStandard"
								aria-expanded="false">[ <?php esc_html_e( 'See Preview', 'miniorange-2-factor-authentication' ); ?>
							]</a>
						<br>
						<div class="mo2f_collapse" id="LoginWithUsernameOnlyStandard" style="height:300px;">
						<div class="mo2fa_text-align-center"><br>
								<img style="height:300px;"
								src="<?php echo esc_url( $main_dir . 'includes/images/hide_default_login_form.png' ); ?>">
						</div>
						</div>
						<br>
					</li>
				</ul>


			</div>
			<br>
			<p style="font-size:15px;font-weight:bold">2. <?php esc_html_e( 'Custom Redirect URLs', 'miniorange-2-factor-authentication' ); ?></p>
			<p style="margin-left:4%">
			<?php
			esc_html_e( 'Enable Custom Relay state URL\'s (based on user roles in WordPress) to which the users will get redirected to, after the 2-factor authentication', 'miniorange-2-factor-authentication' );
			?>
										'.</p>


			<br>
			<p style="font-size:15px;font-weight:bold">3. <?php esc_html_e( 'Custom Security Questions (KBA)', 'miniorange-2-factor-authentication' ); ?></p>
			<div id="mo2f_customKBAQuestions1">
				<p style="margin-left:4%">
				<?php
				esc_html_e( 'Add up to 16 Custom Security Questions for Knowledge based authentication (KBA). You also have the option to select how many standard and custom questions should be shown to the users', 'miniorange-2-factor-authentication' );
				?>
											.</p>

			</div>
			<br>
			<p style="font-size:15px;font-weight:bold">
				4. <?php esc_html_e( 'Custom account name in Google Authenticator App', 'miniorange-2-factor-authentication' ); ?></p>
			<div id="mo2f_editGoogleAuthenticatorAccountName1">

				<p style="margin-left:4%"><?php esc_html_e( 'Customize the Account name in the Google Authenticator App', 'miniorange-2-factor-authentication' ); ?>
					.</p>

			</div>
			<br>
		</div>
		<div id="standard_premium_options" style="text-align: center;">
			<p style="font-size:22px;color:darkorange;padding:10px;"><?php esc_html_e( 'Features in the Premium Plan', 'miniorange-2-factor-authentication' ); ?></p>

		</div>
		<hr>
		<div>
			<a class="mo2f_view_customizations_prem" onclick="show_customizations_prem()">
			<p class="mo2f_heading_style"><?php esc_html_e( 'Customizations', 'miniorange-2-factor-authentication' ); ?></p>
			</a>
		</div>


		<div id="customizations_prem" style="margin-left: 5%;">

			<p style="font-size:15px;font-weight:bold">1. <?php esc_html_e( 'Login Screen Options', 'miniorange-2-factor-authentication' ); ?></p>
			<div>
				<ul style="margin-left:4%" class="mo2f_ol">
					<li><?php esc_html_e( 'Login with WordPress username/password and 2nd Factor', 'miniorange-2-factor-authentication' ); ?> <a
								data-toggle="collapse" id="showLoginwith2ndFactoronlyPremium" href="#Loginwith2ndFactoronlyPremium"
								aria-expanded="false">[ <?php esc_html_e( 'See Preview', 'miniorange-2-factor-authentication' ); ?>
							]</a>
						<div class="mo2f_collapse" id="Loginwith2ndFactoronlyPremium" style="height:300px;">
						<div class="mo2fa_text-align-center"><br>
								<img style="height:300px;"
								src="<?php echo esc_url( $main_dir . 'includes/images/login-with-2fa-and-password.png' ); ?>">
						</div>

						</div>
						<br></li>
					<li><?php esc_html_e( 'Login with WordPress username and 2nd Factor only', 'miniorange-2-factor-authentication' ); ?> <a
								data-toggle="collapse" id="showLoginWithUsernameOnlyPremium" href="#LoginWithUsernameOnlyPremium"
								aria-expanded="false">[ <?php esc_html_e( 'See Preview', 'miniorange-2-factor-authentication' ); ?>
							]</a>
						<br>
						<div class="mo2f_collapse" id="LoginWithUsernameOnlyPremium" style="height:300px;">
						<div class="mo2fa_text-align-center"><br>
								<img style="height:300px;"
								src="<?php echo esc_url( $main_dir . 'includes/images/hide_default_login_form.png' ); ?>">
						</div>
						</div>
						<br>
					</li>
				</ul>


			</div>
			<br>
			<p style="font-size:15px;font-weight:bold">2. <?php esc_html_e( 'Custom Redirect URLs', 'miniorange-2-factor-authentication' ); ?></p>
			<p style="margin-left:4%">
			<?php
			esc_html_e( 'Enable Custom Relay state URL\'s (based on user roles in WordPress) to which the users will get redirected to, after the 2-factor authentication', 'miniorange-2-factor-authentication' );
			?>
										'.</p>


			<br>
			<p style="font-size:15px;font-weight:bold">3. <?php esc_html_e( 'Custom Security Questions (KBA)', 'miniorange-2-factor-authentication' ); ?></p>
			<div id="mo2f_customKBAQuestions1">
				<p style="margin-left:4%">
				<?php
				esc_html_e( 'Add up to 16 Custom Security Questions for Knowledge based authentication (KBA). You also have the option to select how many standard and custom questions should be shown to the users', 'miniorange-2-factor-authentication' );
				?>
											.</p>

			</div>
			<br>
			<p style="font-size:15px;font-weight:bold">
				4. <?php esc_html_e( 'Custom account name in Google Authenticator App', 'miniorange-2-factor-authentication' ); ?></p>
			<div id="mo2f_editGoogleAuthenticatorAccountName1">

				<p style="margin-left:4%"><?php esc_html_e( 'Customize the Account name in the Google Authenticator App', 'miniorange-2-factor-authentication' ); ?>
					.</p>

			</div>
			<br>
		</div>
		<div>
			<a class="mo2f_view_backup_options_prem" onclick="show_backup_options_prem()">
			<p class="mo2f_heading_style"><?php esc_html_e( 'Backup Options', 'miniorange-2-factor-authentication' ); ?></p>
			</a>

		</div>
		<div id="backup_options_prem" style="margin-left: 5%;">

			<div class="mo2f_advanced_options_note"><p style="padding:5px;">
					<i>
					<?php
					esc_html_e( 'Use these backup options to login to your site in case your phone is lost / not accessible or if you are not able to login using your primary authentication method.', 'miniorange-2-factor-authentication' );
					?>
						</i></p></div>

			<ol class="mo2f_ol">
				<li><?php esc_html_e( 'KBA (Security Questions)', 'miniorange-2-factor-authentication' ); ?></li>
				<li><?php esc_html_e( 'OTP Over Email', 'miniorange-2-factor-authentication' ); ?></li>
				<li><?php esc_html_e( 'Backup Codes', 'miniorange-2-factor-authentication' ); ?></li>
			</ol>

		</div>


		<div>
			<a class="mo2f_view_inline_registration_options" onclick="show_inline_registration_options()">
			<p class="mo2f_heading_style"><?php esc_html_e( 'Inline Registration Options', 'miniorange-2-factor-authentication' ); ?></p>
			</a>
		</div>


		<div id="inline_registration_options" style="margin-left: 5%;">

			<div class="mo2f_advanced_options_note"><p style="padding:5px;">
					<i>
					<?php
					esc_html_e( 'Inline Registration is the registration process the users go through the first time they setup 2FA.', 'miniorange-2-factor-authentication' );
					?>
						<br>
						<?php
						esc_html_e( 'If Inline Registration is enabled by the admin for the users, the next time the users login to the website, they will be prompted to set up the 2FA of their choice by creating an account with miniOrange.', 'miniorange-2-factor-authentication' );
						?>


					</i></p></div>


			<p style="font-size:15px;font-weight:bold"><?php esc_html_e( 'Features', 'miniorange-2-factor-authentication' ); ?>:</p>
			<ol style="margin-left: 5%" class="mo2f_ol">
				<li><?php esc_html_e( 'Invoke 2FA Registration & Setup for Users during first-time login (Inline Registration)', 'miniorange-2-factor-authentication' ); ?>
				</li>

				<li><?php esc_html_e( 'Verify Email address of User during Inline Registration', 'miniorange-2-factor-authentication' ); ?></li>
				<li><?php esc_html_e( 'Remove Knowledge Based Authentication(KBA) setup during inline registration', 'miniorange-2-factor-authentication' ); ?></li>
				<li><?php esc_html_e( 'Enable 2FA for specific Roles', 'miniorange-2-factor-authentication' ); ?></li>
				<li><?php esc_html_e( 'Enable specific 2FA methods to Users during Inline Registration', 'miniorange-2-factor-authentication' ); ?>:
					<ul style="padding-top:10px;">
						<li style="margin-left: 5%;">
							1. <?php esc_html_e( 'Show specific 2FA methods to All Users', 'miniorange-2-factor-authentication' ); ?></li>
						<li style="margin-left: 5%;">
							2. <?php esc_html_e( 'Show specific 2FA methods to Users based on their roles', 'miniorange-2-factor-authentication' ); ?></li>
					</ul>
				</li>
			</ol>
		</div>


		<div>
			<a class="mo2f_view_login_options" onclick="show_login_options()">                
				<p class="mo2f_heading_style"><?php esc_html_e( 'User Login Options', 'miniorange-2-factor-authentication' ); ?></p>
			</a>
		</div>

		<div id="login_options" style="margin-left: 5%;">

			<div class="mo2f_advanced_options_note"><p style="padding:5px;">
					<i><?php esc_html_e( 'These are the options customizable for your users.', 'miniorange-2-factor-authentication' ); ?>


					</i></p></div>

			<ol style="margin-left: 5%" class="mo2f_ol">
				<li><?php esc_html_e( 'Enable 2FA during login for specific users on your site', 'miniorange-2-factor-authentication' ); ?>.</li>

				<li><?php esc_html_e( 'Enable login from external apps that support XML-RPC. (eg. WordPress App)', 'miniorange-2-factor-authentication' ); ?>
					<br>
					<div class="mo2f_advanced_options_note"><p style="padding:5px;">
							<i>
							<?php
							esc_html_e( 'Use the Password generated in the 2FA plugin to login to your WordPress Site from any application that supports XML-RPC.', 'miniorange-2-factor-authentication' );
							?>


							</i></p></div>


				<li>
				<?php
				esc_html_e( 'Enable KBA (Security Questions) as 2FA for Users logging in to the site from mobile phones.', 'miniorange-2-factor-authentication' );
				?>
				</li>


			</ol>
			<br>
		</div>
	</div>
	<?php
}
