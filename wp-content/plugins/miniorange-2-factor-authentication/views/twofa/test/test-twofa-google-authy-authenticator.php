<?php
/**
 * This file shows Google/Authy authenticator frontend.
 *
 * @package miniorange-2-factor-authentication/views/twofa/test
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Function to show Google/Authy authenticator frontend.
 *
 * @param object $user User object.
 * @param string $method 2-factor method of user.
 * @return void
 */
function mo2f_test_google_authy_authenticator( $user, $method ) {

	?>
		<h3>
			<?php
			printf(
				/* translators: %s: Name of the 2fa method */
				esc_html__( 'Test %s', 'miniorange-2-factor-authentication' ),
				esc_html( $method )
			);
			?>
			</h3>
		<hr>
	<p>
	<?php
	printf(
		/* translators: %s: Name of the 2fa method */
		esc_html__( 'Enter the verification code from the configured account in your %s app.', 'miniorange-2-factor-authentication' ),
		esc_html( $method )
	);
	?>
				</p>

	<form name="f" method="post" action="">
		<input type="hidden" name="option" value="mo2f_validate_google_authy_test"/>
		<input type="hidden" name="mo2f_validate_google_authy_test_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-validate-google-authy-test-nonce' ) ); ?>"/>

		<input class="mo2f_table_textbox" style="width:200px;" autofocus="true" type="text" name="otp_token" required
			placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>" style="width:100%;"/>
		<br><br>
			<input type="button" name="back" id="go_back" class="button button-primary button-large"
				value="<?php esc_attr_e( 'Back', 'miniorange-2-factor-authentication' ); ?>"/>
		<input type="submit" name="validate" id="validate" class="button button-primary button-large"
			value="<?php esc_attr_e( 'Submit', 'miniorange-2-factor-authentication' ); ?>"/>

	</form>
	<form name="f" method="post" action="" id="mo2f_go_back_form">
		<input type="hidden" name="option" value="mo2f_go_back"/>
		<input type="hidden" name="mo2f_go_back_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-go-back-nonce' ) ); ?>"/>
	</form>
	<script>
		jQuery('#go_back').click(function () {
			jQuery('#mo2f_go_back_form').submit();
		});
	</script>

	<?php
}

/**
 * Function to show Test OTP over Email frontend.
 *
 * @param object $user User object.
 * @param string $method 2-factor method of the user.
 * @return void
 */
function mo2f_test_otp_over_email( $user, $method ) {

	?>
		<h3>
			<?php
				printf(
					/* translators: %s: Name of the 2fa method */
					esc_html__( 'Test %s.', 'miniorange-2-factor-authentication' ),
					esc_html( $method )
				);
			?>
			</h3>
		<h4> Remaining Email Transaction: <?php echo intval( esc_html( ( MoWpnsUtility::get_mo2f_db_option( 'cmVtYWluaW5nT1RQ', 'site_option' ) ) ) ); ?> </h4> 	
		<hr>
	<p><?php esc_html_e( 'Enter the one time passcode sent to your registered email id.', 'miniorange-2-factor-authentication' ); ?></p>

	<form name="f" method="post" action="">
		<input type="hidden" name="option" value="mo2f_validate_otp_over_email"/>
		<input type="hidden" name="mo2f_validate_otp_over_email_test_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-validate-otp-over-email-test-nonce' ) ); ?>"/>

		<input class="mo2f_table_textbox" style="width:200px;" autofocus="true" type="text" name="otp_token" required placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>" style="width:95%;"/>
		<br><br>
			<input type="button" name="back" id="go_back" class="button button-primary button-large"
				value="<?php esc_attr_e( 'Back', 'miniorange-2-factor-authentication' ); ?>"/>
		<input type="submit" name="validate" id="validate" class="button button-primary button-large"
				value="<?php esc_attr_e( 'Submit', 'miniorange-2-factor-authentication' ); ?>"/>

	</form>
	<form name="f" method="post" action="" id="mo2f_go_back_form">
		<input type="hidden" name="option" value="mo2f_go_back"/>
		<input type="hidden" name="mo2f_go_back_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-go-back-nonce' ) ); ?>"/>
	</form>
	<script>
		jQuery('#go_back').click(function () {
			jQuery('#mo2f_go_back_form').submit();
		});
	</script>

	<?php
}
