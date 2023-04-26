<?php
/**
 * Frontend for Authy Authenticator set up.
 *
 * @package miniorange-2-factor-authentication/views/twofa/setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Shows frontend for Authy Authenticator set up.
 *
 * @param object $user User object.
 * @return void
 */
function mo2f_configure_authy_authenticator( $user ) {
	$mo2f_authy_auth = isset( $_SESSION['mo2f_authy_keys'] ) ? $_SESSION['mo2f_authy_keys'] : null;
	$data            = isset( $_SESSION['mo2f_authy_keys'] ) ? $mo2f_authy_auth['authy_qrCode'] : null;
	$authy_secret    = isset( $_SESSION['mo2f_authy_keys'] ) ? $mo2f_authy_auth['mo2f_authy_secret'] : null;
	?>
	<table>
		<tr>
			<td class="mo2f_authy_step1">
				<h3><?php esc_html_e( 'Step-1: Configure Authy Authenticator App.', 'miniorange-2-factor-authentication' ); ?>
				</h3>
				<hr/>
				<form name="f" method="post" id="mo2f_configure_google_authy_form1" action="">
					<input type="submit" name="mo2f_authy_configure" class="button button-primary button-large"
						style="width:60%;"
						value="<?php esc_attr_e( 'Configure', 'miniorange-2-factor-authentication' ); ?> "/>
					<input type="hidden" name="mo2f_configure_authy_authenticator_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-authy-authenticator-nonce' ) ); ?>"/>
						<br><br>
					<input type="hidden" name="option" value="mo2f_configure_authy_authenticator"/>
				</form>
				<form name="f" method="post" action="" id="mo2f_go_back_form">
					<input type="hidden" name="option" value="mo2f_go_back"/>
					<input type="hidden" name="mo2f_go_back_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-go-back-nonce' ) ); ?>"/>
					<input type="submit" name="back" id="go_back" class="button button-primary button-large"
						style="width:60%;"
						value="<?php esc_attr_e( 'Back', 'miniorange-2-factor-authentication' ); ?>"/>
				</form>
			</td>
			<td class="mo2f_vertical_line"></td>
			<td class="mo2f_authy_step2">
				<h3><?php esc_html_e( 'Step-2: Set up Authy 2-Factor Authentication App', 'miniorange-2-factor-authentication' ); ?></h3>
				<h3></h3>
				<hr>
				<div style="<?php echo isset( $_SESSION['mo2f_authy_keys'] ) ? 'display:block' : 'display:none'; ?>">
					<h4><?php esc_html_e( 'Install the Authy 2-Factor Authentication App.', 'miniorange-2-factor-authentication' ); ?></h4>
					<h4><?php esc_html_e( 'Now open and configure Authy 2-Factor Authentication App.', 'miniorange-2-factor-authentication' ); ?></h4>
					<h4> <?php esc_html_e( 'Tap on Add Account and then tap on SCAN QR CODE in your App and scan the qr code.', 'miniorange-2-factor-authentication' ); ?></h4>
					<div style="text-align:center"><br>
						<div id="displayQrCode"><?php echo '<img src="data:image/jpg;base64,' . esc_attr( $data ) . '" />'; ?></div>
					</div>
					<br>
					<div><a data-toggle="collapse" href="#mo2f_scanbarcode_a" aria-expanded="false">
							<b><?php esc_html_e( 'Can\'t scan the QR Code?', 'miniorange-2-factor-authentication' ); ?> </b></a>
					</div>

					<div class="mo2f_collapse" id="mo2f_scanbarcode_a">
						<ol class="mo2f_ol">
							<li><?php esc_html_e( 'In Authy 2-Factor Authentication App, tap on ENTER KEY MANUALLY.', 'miniorange-2-factor-authentication' ); ?>          </li>
							<li><?php esc_html_e( 'In the pop up "Adding New Account", type your secret key:', 'miniorange-2-factor-authentication' ); ?></li>
							<div class="mo2f_google_authy_secret_outer_div">
								<div class="mo2f_google_authy_secret_inner_div">
									<?php echo esc_html( $authy_secret ); ?>
								</div>
								<div class="mo2f_google_authy_secret_text">
									<?php esc_html_e( 'Spaces don\'t matter.', 'miniorange-2-factor-authentication' ); ?>
								</div>
							</div>
							<li><?php esc_html_e( 'Tap OK.', 'miniorange-2-factor-authentication' ); ?></li>
						</ol>
					</div>
				</div>
			</td>
			<td class="mo2f_vertical_line"></td>
			<td class="mo2f_google_authy_step3">
				<h3><?php esc_html_e( 'Step-3: Verify and Save', 'miniorange-2-factor-authentication' ); ?></h3>
				<hr>
				<div style="<?php echo isset( $_SESSION['mo2f_authy_keys'] ) ? 'display:block' : 'display:none'; ?>">
					<h4><?php esc_html_e( 'After you have scanned the QR code and created an account, enter the verification code from the scanned account here.', 'miniorange-2-factor-authentication' ); ?></h4>
					<br>
					<form name="f" method="post" action="">
						<span>
							<b><?php esc_html_e( 'Code:' ); ?> </b>&nbsp;
							<input class="mo2f_table_textbox" style="width:200px;" autofocus="true" required="true"
								type="text" name="mo2f_authy_token"
								placeholder="<?php esc_attr_e( 'Enter OTP', 'miniorange-2-factor-authentication' ); ?>"
								style="width:95%;"/>
						</span>
						<br><br>
						<input type="submit" name="validate" id="validate" class="button button-primary button-large"
							style="margin-left:12%;"
							value="<?php esc_attr_e( 'Verify and Save', 'miniorange-2-factor-authentication' ); ?>"/>
						<input type="hidden" name="mo2f_authy_secret" value="<?php echo esc_attr( $authy_secret ); ?>"/>
						<input type="hidden" name="option" value="mo2f_configure_authy_authenticator_validate"/>
						<input type="hidden" name="mo2f_configure_authy_authenticator_validate_nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'mo2f-configure-authy-authenticator-validate-nonce' ) ); ?>"/>
					</form>
				</div>
			</td>
		</tr>
		<br>
	</table>
	<script>
		jQuery('html,body').animate({scrollTop: jQuery(document).height()}, 600);
	</script>
	<?php
}

?>
