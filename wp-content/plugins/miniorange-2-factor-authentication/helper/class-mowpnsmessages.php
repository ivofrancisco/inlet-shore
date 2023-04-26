<?php
/**
 * This file has all the notifications that are shown throughout the plugin.
 *
 * @package miniorange-2-factor-authentication/helper/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'MoWpnsMessages' ) ) {
	/**
	 * This Class has all the notifications that are shown throughout the plugin.
	 */
	class MoWpnsMessages {

		// ip-blocking messages.
		const INVALID_IP             = 'The IP address you entered is not valid or the IP Range is not valid.';
		const INVALID_RANGE          = 'IP Range is not valid, please enter a valid range';
		const IP_ALREADY_BLOCKED     = 'IP Address is already Blocked';
		const IP_PERMANENTLY_BLOCKED = 'IP Address is blocked permanently.';
		const IP_ALREADY_WHITELISTED = 'IP Address is already Whitelisted.';
		const IP_IN_WHITELISTED      = 'IP Address is Whitelisted. Please remove it from the whitelisted list.';
		const IP_UNBLOCKED           = 'IP has been unblocked successfully';
		const IP_WHITELISTED         = 'IP has been whitelisted successfully';
		const IP_UNWHITELISTED       = 'IP has been removed from the whitelisted list successfully';

		// login-security messages.
		const TWOFA_ENABLED  = 'Two Factor protection has been enabled.';
		const TWOFA_DISABLED = 'Two Factor protection has been disabled.';

		// notification messages.
		const NOTIFY_ON_IP_BLOCKED             = 'Email notification is enabled for Admin.';
		const DONOT_NOTIFY_ON_IP_BLOCKED       = 'Email notification is disabled for Admin.';
		const NOTIFY_ON_UNUSUAL_ACTIVITY       = 'Email notification is enabled for user for unusual activities.';
		const DONOT_NOTIFY_ON_UNUSUAL_ACTIVITY = 'Email notification is disabled for user for unusual activities.';
		const NONCE_ERROR                      = 'Nonce Error.';
		const TWO_FA_ON_LOGIN_PROMPT_ENABLED   = '2FA prompt on the WP Login Page Enabled.';
		const TWO_FA_ON_LOGIN_PROMPT_DISABLED  = '2FA prompt on the WP Login Page Disabled.';
		const TWO_FA_PROMPT_LOGIN_PAGE         = 'Please disable Login with 2nd factor only to enable 2FA prompt on login page.';

		// registration security.
		const ENABLE_ADVANCED_USER_VERIFY  = 'Advanced user verification is Enabled.';
		const DISABLE_ADVANCED_USER_VERIFY = 'Advanced user verification is Disable.';

		// Advanced security.
		const INVALID_IP_FORMAT = 'Please enter Valid IP Range.';

		// support form.
		const SUPPORT_FORM_VALUES = 'Please submit your query along with email.';
		const SUPPORT_FORM_SENT   = 'Thanks for getting in touch! We shall get back to you shortly.';

		const SUPPORT_FORM_ERROR = 'Your query could not be submitted. Please try again.';
		// request demo form.
		const DEMO_FORM_ERROR = 'Please fill out all the fields.';
		// feedback Form.
		const DEACTIVATE_PLUGIN = 'Plugin deactivated successfully';

		// common messages.
		const UNKNOWN_ERROR    = 'Error processing your request. Please try again.';
		const CONFIG_SAVED     = 'Configuration saved successfully.';
		const REQUIRED_FIELDS  = 'Please enter all the required fields';
		const SELECT_A_PLAN    = 'Please select a plan';
		const RESET_PASS       = 'You password has been reset successfully and sent to your registered email. Please check your mailbox.';
		const TEMPLATE_SAVED   = 'Email template saved.';
		const GET_BACKUP_CODES = "<div class='mo2f-custom-notice notice notice-warning backupcodes-notice'><p><p class='notice-message'><b>Please download backup codes using the 'Get backup codes' button to avoid getting locked out. Backup codes will be emailed as well as downloaded.</b></p><button class='backup_codes_dismiss notice-button'><i>NEVER SHOW AGAIN</i></button></p></div>";

		const CLOUD2FA_SINGLEUSER = "<div class='mo2f-custom-notice notice notice-warning whitelistself-notice'><p><p class='notice-message'>The current solution is cloud which supports 2-factor for only one user. Either upgrade your plan or contact your administrator.</p></p></div>";

		// registration messages.
		const PASS_LENGTH                = 'Choose a password with minimum length 6.';
		const ERR_OTP_EMAIL              = 'There was an error in sending email. Please click on Resend OTP to try again.';
		const OTP_SENT                   = 'A passcode is sent to {{method}}. Please enter the otp below.';
		const REG_SUCCESS                = 'Your account has been retrieved successfully.';
		const ACCOUNT_EXISTS             = 'You already have an account with miniOrange. Please enter a valid password.';
		const TRANSACTION_LIMIT_EXCEEDED = 'The transaction limit has been exceeded.';
		const INVALID_UP                 = 'Invalid username or password.';
		const ACCOUT_NOTEXISTS           = 'Account does not exist. Please create one to use Two-factor Authentication';
		const INVALID_CREDENTIALS        = 'You have entered incorrect credentials, please try again.';

		const INVALID_CRED       = 'Invalid username or password. Please try again.';
		const REQUIRED_OTP       = 'Please enter a value in OTP field.';
		const INVALID_OTP        = 'Invalid one time passcode. Please enter a valid passcode.';
		const INVALID_PHONE      = 'Please enter a valid phone number.';
		const INVALID_INPUT      = 'Please enter a valid value in the input fields.';
		const PASS_MISMATCH      = 'Password and Confirm Password do not match.';
		const INVALID_EMAIL      = 'Please enter valid Email ID';
		const EMAIL_SAVED        = 'Email ID saved successfully';
		const INVALID_HOURS      = 'For scheduled backup, please enter number of hours greater than 1.';
		const ALL_ENABLED        = 'All Website security features are available.';
		const ALL_DISABLED       = 'All Website security features are disabled.';
		const TWO_FACTOR_ENABLE  = 'Two-factor is enabled. Configure it in the Two-Factor tab.';
		const TWO_FACTOR_DISABLE = 'Two-factor is disabled.';
		const LOGIN_ENABLE       = 'Login security and spam protection features are available. Configure it in the Login and Spam tab.';
		const LOGIN_DISABLE      = 'Login security and spam protection features are disabled.';
		const DELETE_FILE        = 'Someone has deleted the backup by going to directory please refreash the page';
		const NOT_ADMIN          = 'You are not a admin. Only admin can download';
		const ADV_BLOCK_ENABLE   = 'Advanced blocking features are available. Configure it in the Advanced blocking tab.';
		const ADV_BLOCK_DISABLE  = 'Advanced blocking features are disabled.';
		const REPORT_ENABLE      = 'Login and error reports are available in the Reports tab.';
		const REPORT_DISABLE     = 'Login and error reports are disabled.';
		const NOTIF_ENABLE       = 'Notification options are available. Configure it in the Notification tab.';
		const NOTIF_DISABLE      = 'Notifications are disabled.';

		const WHITELIST_SELF       = "<div class='mo2f-custom-notice notice notice-warning whitelistself-notice MOWrn'><p><p class='notice-message'>It looks like you have not whitelisted your IP. Whitelist your IP as you can get blocked from your site.</p><button class='whitelist_self notice-button'><i>WhiteList</i></button></p></div>";
		const ADMIN_IP_WHITELISTED = "<div class='mo2f-custom-notice notice notice-warning MOWrn'>
                                                       <p class='notice-message'>Your IP has been whitelisted. In the IP Blocking settings, you can remove your IP address from the whitelist if you want to do so.</p>
                                                   </div>";

		const LOW_SMS_TRANSACTIONS = "<div class='mo2f-custom-notice notice notice-warning low_sms-notice MOWrn'><p><p class='notice-message'><img style='width:15px;' src='" . MO2F_PLUGIN_URL . '/includes/images/miniorange_icon.png' . "'>&nbsp;&nbsp;You have left very few SMS transaction. We advise you to recharge or change 2FA method before you have no SMS left.</p><a class='notice-button' href='" . MoWpnsConstants::RECHARGELINK . "' target='_blank' style='margin-right: 15px;'>RECHARGE</a><a class='notice-button' href='admin.php?page=mo_2fa_two_fa' id='setuptwofa_redirect' style='margin-right: 15px;'>SET UP ANOTHER 2FA</a><button class='sms_low_dismiss notice-button' style='margin-right: 15px;'><i>DISMISS</i></button><button class='sms_low_dismiss_always notice-button'><i>NEVER SHOW AGAIN</i></button></p></div>";

		const LOW_EMAIL_TRANSACTIONS = "<div class='mo2f-custom-notice notice notice-warning low_email-notice MOWrn'><p><p class='notice-message'><img style='width:15px;' src='" . MO2F_PLUGIN_URL . '/includes/images/miniorange_icon.png' . "'>&nbsp;&nbsp;You have left very few Email transaction. We advise you to recharge or change 2FA method before you have no Email left.</p><a class='notice-button' href='" . MoWpnsConstants::RECHARGELINK . "' target='_blank' style='margin-right: 15px;'>RECHARGE</a><a class='notice-button' href='admin.php?page=mo_2fa_two_fa' id='setuptwofa_redirect' style='margin-right: 15px;'>SET UP ANOTHER 2FA</a><button class='email_low_dismiss notice-button' style='margin-right: 15px;'><i>DISMISS</i></button><button class='email_low_dismiss_always notice-button'><i>NEVER SHOW AGAIN</i></button></p></div>";

		/**
		 * Return actual messages according to the key.
		 *
		 * @param string $message key of the message to be shown.
		 * @return string
		 */
		public static function show_message( $message ) {
			$message = constant( 'self::' . $message );
			return $message;
		}
	}
}
