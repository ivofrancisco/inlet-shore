<?php
/**
 * This file show frontend to configure OTP over SMS method.
 *
 * @package miniorange-2-factor-authentication/views/twofa/setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Function to show frontend to configure OTP over SMS method.
 *
 * @param object $user User object.
 * @return void
 */
function mo2f_configure_otp_over_sms( $user ) {
	global $mo2fdb_queries;
	$mo2f_user_phone = $mo2fdb_queries->get_user_detail( 'mo2f_user_phone', $user->ID );
	$user_phone      = $mo2f_user_phone ? $mo2f_user_phone : get_option( 'user_phone_temp' );
	if ( isset( $_POST['mo2f_session_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing -- frontEnd, nonce is not needed here
		$session_id_encrypt = sanitize_text_field( wp_unslash( $_POST['mo2f_session_id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- frontEnd, nonce is not needed here
	} else {
		$pass2fa_login_session = new Miniorange_Password_2Factor_Login();
		$session_id_encrypt    = $pass2fa_login_session->create_session();
	}

	?>

	<h3><?php esc_html_e( 'Configure OTP over SMS', 'miniorange-2-factor-authentication' ); ?>
	</h3>
	<hr>
	<?php if ( current_user_can( 'administrator' ) ) { ?>
		<h3 style="padding:20px; background-color: #a7c5eb;border-radius:5px "> Remaining SMS Transactions: <b><i><?php echo intval( esc_html( get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) ) ); ?> </i></b>
		<a id="mo2f_transactions_check" class="button button-primary mo2f_check_sms">Refresh Available SMS</a>
		</h3>
	<?php } ?>
	<form name="f" method="post" action="" id="mo2f_verifyphone_form">
		<input type="hidden" name="option" value="mo2f_configure_otp_over_sms_send_otp"/>
		<input type="hidden" name="mo2f_session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
		<input type="hidden" name="mo2f_configure_otp_over_sms_send_otp_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-otp-over-sms-send-otp-nonce' ) ); ?>"/>

		<div style="display:inline;">
			<input class="mo2f_table_textbox" style="width:200px;" type="text" name="phone" id="phone"
				value="<?php echo esc_attr( $user_phone ); ?>" pattern="[\+]?[0-9]{1,4}\s?[0-9]{7,12}"
				title="<?php esc_attr_e( 'Enter phone number without any space or dashes', 'miniorange-2-factor-authentication' ); ?>"/><br>
			<input type="submit" name="verify" id="verify" class="button button-primary button-large"
				value="<?php esc_attr_e( 'Verify', 'miniorange-2-factor-authentication' ); ?>"/>
		</div>
	</form>
	<form name="f" method="post" action="" id="mo2f_validateotp_form">
		<input type="hidden" name="option" value="mo2f_configure_otp_over_sms_validate"/>
		<input type="hidden" name="mo2f_session_id" value="<?php echo esc_attr( $session_id_encrypt ); ?>"/>
		<input type="hidden" name="mo2f_configure_otp_over_sms_validate_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-otp-over-sms-validate-nonce' ) ); ?>"/>
		<p><?php esc_html_e( 'Enter One Time Passcode', 'miniorange-2-factor-authentication' ); ?></p>
		<input class="mo2f_table_textbox" style="width:200px;" autofocus="true" type="text" name="otp_token"
			placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>" style="width:95%;"/>
		<a href="#resendsmslink"><?php esc_html_e( 'Resend OTP ?', 'miniorange-2-factor-authentication' ); ?></a>
		<br><br>
		<input type="button" name="back" id="go_back" class="button button-primary button-large"
			value="<?php esc_attr_e( 'Back', 'miniorange-2-factor-authentication' ); ?>"/>
		<input type="submit" name="validate" id="validate" class="button button-primary button-large"
			value="<?php esc_attr_e( 'Validate OTP', 'miniorange-2-factor-authentication' ); ?>"/>
	</form><br>
	<form name="f" method="post" action="" id="mo2f_go_back_form">
		<input type="hidden" name="option" value="mo2f_go_back"/>
		<input type="hidden" name="mo2f_go_back_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-go-back-nonce' ) ); ?>"/>
	</form>
	<script>
		jQuery("#mo2f_transactions_check").click(function()
		{   
			var nonce = '<?php echo esc_js( wp_create_nonce( 'LoginSecurityNonce' ) ); ?>';
			var data =
			{
				'action'                  : 'wpns_login_security',
				'wpns_loginsecurity_ajax' : 'wpns_check_transaction',
				'nonce'                   :nonce
			};
			jQuery.post(ajaxurl, data, function(response) {
				window.location.reload(true);
			});
		});
		jQuery("#phone").intlTelInput();
		jQuery('#go_back').click(function () {
			jQuery('#mo2f_go_back_form').submit();
		});
		jQuery('a[href=\"#resendsmslink\"]').click(function (e) {
			jQuery('#mo2f_verifyphone_form').submit();
		});

	</script>
	<?php
}

?>
