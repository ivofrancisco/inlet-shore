<?php
/**
 * This file shows setup wizard for OTP over Telegram method.
 *
 * @package miniorange-2-factor-authentication/views/twofa/setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Function to show frontend to configure OTP over Telegram method.
 *
 * @param object $user User Object.
 * @return void
 */
function mo2f_configure_otp_over_telegram( $user ) {

	$chat_id = get_user_meta( $user->ID, 'mo2f_chat_id', true );

	if ( empty( $chat_id ) ) {
		$chat_id = get_user_meta( $user->ID, 'mo2f_temp_chatID', true );
	}

	?>

	<h3><?php esc_html_e( 'Configure OTP over Telegram', 'miniorange-2-factor-authentication' ); ?>
	</h3>
	<h4 style="padding:10px; background-color: #a7c5eb"> Remaining Telegram Transactions: <b>Unlimited</b></h4>
	<hr>

	<form name="f" method="post" action="" id="mo2f_verifychatID_form">
		<input type="hidden" name="option" value="mo2f_configure_otp_over_Telegram_send_otp"/>
		<input type="hidden" name="mo2f_configure_otp_over_Telegram_send_otp_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-otp-over-Telegram-send-otp-nonce', 'miniorange-2-factor-authentication' ) ); ?>"/>

		<h4 class='mo_wpns_not_bold'> 1. Open the telegram app and search for miniorange2fa_bot. Click on start button or send <b>/start</b> message.</h4>
		<div style="display:inline;">
			<h4 class='mo_wpns_not_bold'> 2. Enter the recieved chat id in the below box.
			<h4>Chat ID:
			<input class="mo2f_table_textbox" style="width:200px;" type="text" name="mo2f_verify_chatID" id="phone"
				value="<?php echo esc_attr( $chat_id ); ?>" pattern="[0-9]+" 
				title="<?php esc_attr_e( 'Enter Chat ID recieved on your Telegram without any space or dashes', 'miniorange-2-factor-authentication' ); ?>"/><br></h4>
			<input type="submit" name="verify" id="verify" class="button button-primary button-large"
				value="<?php esc_attr_e( 'Verify', 'miniorange-2-factor-authentication' ); ?>"/>
		</div>
	</form>
	<form name="f" method="post" action="" id="mo2f_validateotp_form">
		<input type="hidden" name="option" value="mo2f_configure_otp_over_Telegram_validate"/>
		<input type="hidden" name="mo2f_configure_otp_over_Telegram_validate_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-otp-over-Telegram-validate-nonce' ) ); ?>"/>
		<p><?php esc_html_e( 'Enter One Time Passcode', 'miniorange-2-factor-authentication' ); ?></p>
		<input class="mo2f_table_textbox" style="width:200px;" autofocus="true" type="text" name="otp_token"
			placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>" style="width:95%;"/>
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
		jQuery('#go_back').click(function () {
			jQuery('#mo2f_go_back_form').submit();
		});
		jQuery('a[href=\"#resendtelegramSMS\"]').click(function (e) {
			jQuery('#mo2f_verifyChatID_form').submit();
		});

	</script>
	<?php
}

?>
