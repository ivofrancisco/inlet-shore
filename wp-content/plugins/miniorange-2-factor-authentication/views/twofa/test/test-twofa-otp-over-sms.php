<?php
/**
 * Description: File contains function to test otp over sms.
 *
 * @package miniorange-2-factor-authentication/views/twofa/test.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Description: Function displays form screen to test otp over sms method.
 *
 * @param  object $user Denotes object of information of current user.
 * @return void
 */
function mo2f_test_otp_over_sms( $user ) {
	?>
	<h3><?php esc_html_e( 'Test OTP Over SMS', 'miniorange-2-factor-authentication' ); ?>
	<h4> Remaining SMS Transaction: <?php echo esc_html( get_site_option( 'cmVtYWluaW5nT1RQVHJhbnNhY3Rpb25z' ) ); ?> </h4> 

		<hr>
	</h3>
	<p><?php esc_html_e( 'Enter the one time passcode sent to your registered mobile number.', 'miniorange-2-factor-authentication' ); ?></p>


	<form name="f" method="post" action="" id="mo2f_test_token_form">
		<input type="hidden" name="option" value="mo2f_validate_otp_over_sms"/>
		<input type="hidden" name="mo2f_validate_otp_over_sms_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-validate-otp-over-sms-nonce' ) ); ?>"/>

		<input class="mo2f_table_textbox" style="width:200px;" autofocus="true" type="text" name="otp_token" required
			placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>" style="width:95%;"/>
		<a href="#resendsmslink"><?php esc_html_e( 'Resend OTP ?', 'miniorange-2-factor-authentication' ); ?></a>
		<br><br>
		<input type="button" name="back" id="go_back" class="button button-primary button-large"
			value="<?php esc_attr_e( 'Back', 'miniorange-2-factor-authentication' ); ?>"/>
		<input type="submit" name="validate" id="validate" class="button button-primary button-large"
			value="<?php esc_attr_e( 'Validate OTP', 'miniorange-2-factor-authentication' ); ?>"/>

	</form>
	<form name="f" method="post" action="" id="mo2f_go_back_form">
		<input type="hidden" name="option" value="mo2f_go_back"/>
		<input type="hidden" name="mo2f_go_back_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-go-back-nonce' ) ); ?>"/>
	</form>

	<form name="f" method="post" action="" id="mo2f_2factor_test_authentication_method_form">
		<input type="hidden" name="option" value="mo_2factor_test_authentication_method"/>
		<input type="hidden" name="mo_2factor_test_authentication_method_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'mo-2factor-test-authentication-method-nonce' ) ); ?>"/>
		<input type="hidden" name="mo2f_configured_2FA_method_test" id="mo2f_configured_2FA_method_test"
			value="OTP Over SMS"/>
	</form>

		<script>
			jQuery('#go_back').click(function () {
				jQuery('#mo2f_go_back_form').submit();
			});
			jQuery('a[href=\"#resendsmslink\"]').click(function (e) {
				jQuery('#mo2f_2factor_test_authentication_method_form').submit();
			});
		</script>

<?php } ?>
