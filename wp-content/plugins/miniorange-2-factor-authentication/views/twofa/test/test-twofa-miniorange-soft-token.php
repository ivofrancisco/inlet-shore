<?php
/**
 * This file contains Test miniOrange Soft Token frontend.
 *
 * @package miniorange-2-factor-authentication/views/twofa/test
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Function to Test miniOrange Soft Token frontend.
 *
 * @param object $user User object.
 * @return void
 */
function mo2f_test_miniorange_soft_token( $user ) { ?>
		<div style="width:100%;">
			<h3><?php esc_html_e( 'Test Soft Token', 'miniorange-2-factor-authentication' ); ?></h3>
			<hr>
			<p><?php esc_html_e( 'Open your', 'miniorange-2-factor-authentication' ); ?>
				<b><?php esc_html_e( 'miniOrange Authenticator App ', 'miniorange-2-factor-authentication' ); ?></b> <?php esc_html_e( 'and ', 'miniorange-2-factor-authentication' ); ?>
				<?php esc_html_e( 'enter the', 'miniorange-2-factor-authentication' ); ?>
				<b><?php esc_html_e( 'one time passcode', 'miniorange-2-factor-authentication' ); ?></b> <?php esc_html_e( 'shown in the App under your account.', 'miniorange-2-factor-authentication' ); ?>
			</p>
			<form name="f" method="post" action="" id="mo2f_test_token_form">
				<input type="hidden" name="option" value="mo2f_validate_soft_token"/>
				<input type="hidden" name="mo2f_validate_soft_token_nonce"
								value="<?php echo esc_attr( wp_create_nonce( 'mo2f-validate-soft-token-nonce' ) ); ?>"/>
				<input class="mo2f_table_textbox" style="width:200px;" autofocus="true" type="text" name="otp_token" required
					placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>" style="width:95%;"/>

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
		</div>
	<script>
		jQuery('#go_back').click(function () {
			jQuery('#mo2f_go_back_form').submit();
		});
	</script>
<?php } ?>
