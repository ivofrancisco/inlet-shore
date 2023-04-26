<?php
/**
 * File contains function for strings translation.
 *
 * @package miniOrange-2-factor-authentication/handler/twofa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Mo2fConstants' ) ) {
	/**
	 * Class Mo2fConstants
	 */
	class Mo2fConstants {

		/**
		 * Translates the strings.
		 *
		 * @param string $text The string to be translated.
		 * @return string
		 */
		public static function lang_translate( $text ) {
			switch ( $text ) {
				case 'Successfully validated.':
					return __( 'Successfully validated.', 'miniorange-2-factor-authentication' );

				case 'SCAN_QR_CODE':
					return __( 'Please scan the QR Code now.', 'miniorange-2-factor-authentication' );

				case 'miniOrange QR Code Authentication':
					return __( 'miniOrange QR Code Authentication', 'miniorange-2-factor-authentication' );

				case 'miniOrange Push Notification':
					return __( 'miniOrange Push Notification', 'miniorange-2-factor-authentication' );

				case 'miniOrange Soft Token':
					return __( 'miniOrange Soft Token', 'miniorange-2-factor-authentication' );

				case 'Security Questions':
					return __( 'Security Questions', 'miniorange-2-factor-authentication' );

				case 'Google Authenticator':
					return __( 'Google Authenticator', 'miniorange-2-factor-authentication' );

				case 'Authy Authenticator':
					return __( 'Authy Authenticator', 'miniorange-2-factor-authentication' );

				case 'Email Verification':
					return __( 'Email Verification', 'miniorange-2-factor-authentication' );

				case 'OTP Over SMS':
					return __( 'OTP Over SMS', 'miniorange-2-factor-authentication' );

				case 'OTP Over SMS And Email':
					return __( 'OTP Over SMS And Email', 'miniorange-2-factor-authentication' );

				case 'Your license has expired. Please renew your license to continue using our service.':
					return __( 'Your license has expired. Please renew your license to continue using our service.', 'miniorange-2-factor-authentication' );

				case 'The total transaction limit has been exceeded. Please upgrade your premium plan.':
					return __( 'The total transaction limit has been exceeded. Please upgrade your premium plan.', 'miniorange-2-factor-authentication' );

				case 'The transaction limit has exceeded.':
					return __( 'The transaction limit has exceeded.', 'miniorange-2-factor-authentication' );

				case 'GenerateOtpRequest is null':
					return __( 'GenerateOtpRequest is null', 'miniorange-2-factor-authentication' );

				case 'The sms transaction limit has been exceeded. Please refer to the Licensing Plans tab for purchasing your SMS transactions.':
					return __( 'The sms transaction limit has been exceeded. Please refer to the Licensing Plans tab for purchasing your SMS transactions.', 'miniorange-2-factor-authentication' );

				case 'The email transaction limit has been exceeded. Please refer to the Licensing Plans tab for purchasing your SMS transactions.':
					return __( 'The email transaction limit has been exceeded. Please refer to the Licensing Plans tab for purchasing your SMS transactions.', 'miniorange-2-factor-authentication' );

				case 'Transaction limit exceeded. Please contact your administrator':
					return __( 'Transaction limit exceeded. Please contact your administrator', 'miniorange-2-factor-authentication' );

				case 'Invalid format.':
					return __( 'Invalid format.', 'miniorange-2-factor-authentication' );

				case 'Mobile registration failed.':
					return __( 'Mobile registration failed.', 'miniorange-2-factor-authentication' );

				case 'Invalid mobile authentication request.':
					return __( 'Invalid mobile authentication request.', 'miniorange-2-factor-authentication' );

				case 'Exception during SMS sending':
					return __( 'Exception during SMS sending', 'miniorange-2-factor-authentication' );

				case 'There was an error during sending an SMS.':
					return __( 'There was an error during sending an SMS.', 'miniorange-2-factor-authentication' );

				case 'Exception during logUserTransaction':
					return __( 'Exception during logUserTransaction', 'miniorange-2-factor-authentication' );

				case 'There was an error processing the challenge user request.':
					return __( 'There was an error processing the challenge user request.', 'miniorange-2-factor-authentication' );

				case 'What is your first company name?':
					return __( 'What is your first company name?', 'miniorange-2-factor-authentication' );

				case 'What was your childhood nickname?':
					return __( 'What was your childhood nickname?', 'miniorange-2-factor-authentication' );

				case 'In what city did you meet your spouse/significant other?':
					return __( 'In what city did you meet your spouse/significant other?', 'miniorange-2-factor-authentication' );

				case 'What is the name of your favorite childhood friend?':
					return __( 'What is the name of your favorite childhood friend?', 'miniorange-2-factor-authentication' );

				case "What was your first vehicle's registration number?":
					return __( "What was your first vehicle's registration number?", 'miniorange-2-factor-authentication' );

				case "What is your grandmother's maiden name?":
					return __( "What is your grandmother's maiden name?", 'miniorange-2-factor-authentication' );

				case 'Who is your favourite sports player?':
					return __( 'Who is your favourite sports player?', 'miniorange-2-factor-authentication' );

				case 'What is your favourite sport?':
					return __( 'What is your favourite sport?', 'miniorange-2-factor-authentication' );

				case 'In what city or town was your first job':
					return __( 'In what city or town was your first job', 'miniorange-2-factor-authentication' );

				case 'What school did you attend for sixth grade?':
					return __( 'What school did you attend for sixth grade?', 'miniorange-2-factor-authentication' );

				case 'G_AUTH':
					return __( 'Google Authenticator', 'miniorange-2-factor-authentication' );

				case 'AUTHY_2FA':
					return __( 'Authy 2-Factor Authentication', 'miniorange-2-factor-authentication' );

				case 'An unknown error occurred while creating the end user.':
					return __( 'An unknown error occurred while creating the end user.', 'miniorange-2-factor-authentication' );

				case 'An unknown error occurred while challenging the user':
					return __( 'An unknown error occurred while challenging the user.', 'miniorange-2-factor-authentication' );

				case 'An unknown error occurred while generating QR Code for registering mobile.':
					return __( 'An unknown error occurred while generating QR Code for registering mobile.', 'miniorange-2-factor-authentication' );

				case 'An unknown error occurred while validating the user\'s identity.':
					return __( 'An unknown error occurred while validating the user\'s identity.', 'miniorange-2-factor-authentication' );

				case 'Customer not found.':
					return __( 'Customer not found.', 'miniorange-2-factor-authentication' );

				case 'The customer is not valid ':
					return __( 'The customer is not valid', 'miniorange-2-factor-authentication' );

				case 'The user is not valid ':
					return __( 'The user is not valid ', 'miniorange-2-factor-authentication' );

				case 'Customer already exists.':
					return __( 'Customer already exists.', 'miniorange-2-factor-authentication' );

				case 'Customer Name is null':
					return __( 'Customer Name is null', 'miniorange-2-factor-authentication' );

				case 'Customer check request failed.':
					return __( 'Customer check request failed.', 'miniorange-2-factor-authentication' );

				case 'Invalid username or password. Please try again.':
					return __( 'Invalid username or password. Please try again.', 'miniorange-2-factor-authentication' );

				case 'You are not authorized to perform this operation.':
					return __( 'You are not authorized to perform this operation.', 'miniorange-2-factor-authentication' );

				case 'Invalid request. No such challenge request was initiated.':
					return __( 'Invalid request. No such challenge request was initiated.', 'miniorange-2-factor-authentication' );

				case 'No OTP Token for the given request was found.':
					return __( 'No OTP Token for the given request was found.', 'miniorange-2-factor-authentication' );

				case 'Query submitted.':
					return __( 'Query submitted.', 'miniorange-2-factor-authentication' );

				case 'Invalid parameters.':
					return __( 'Invalid parameters.', 'miniorange-2-factor-authentication' );

				case 'Alternate email cannot be same as primary email.':
					return __( 'Alternate email cannot be same as primary email.', 'miniorange-2-factor-authentication' );

				case 'CustomerId is null.':
					return __( 'CustomerId is null.', 'miniorange-2-factor-authentication' );

				case 'You are not authorized to create users. Please upgrade to premium plan. ':
					return __( 'You are not authorized to create users. Please upgrade to premium plan. ', 'miniorange-2-factor-authentication' );

				case 'Your user creation limit has been completed. Please upgrade your license to add more users.':
					return __( 'Your user creation limit has been completed. Please upgrade your license to add more users.', 'miniorange-2-factor-authentication' );

				case 'Username cannot be blank.':
					return __( 'Username cannot be blank.', 'miniorange-2-factor-authentication' );

				case 'End user created successfully.':
					return __( 'End user created successfully.', 'miniorange-2-factor-authentication' );

				case 'There was an exception processing the update user request.':
					return __( 'There was an exception processing the update user request.', 'miniorange-2-factor-authentication' );

				case 'End user found.':
					return __( 'End user found.', 'miniorange-2-factor-authentication' );

				case 'End user found under different customer. ':
					return __( 'End user found under different customer. ', 'miniorange-2-factor-authentication' );

				case 'End user not found.':
					return __( 'End user not found.', 'miniorange-2-factor-authentication' );

				case 'Invalid OTP provided. Please try again.':
					return __( 'Invalid OTP provided. Please try again.', 'miniorange-2-factor-authentication' );

				case 'Successfully Validated':
					return __( 'Successfully Validated', 'miniorange-2-factor-authentication' );

				case 'Customer successfully registered.':
					return __( 'Customer successfully registered.', 'miniorange-2-factor-authentication' );

				case 'This username already exists. Please select a different username.':
					return __( 'This username already exists. Please select a different username.', 'miniorange-2-factor-authentication' );

				case 'Customer registration failed.':
					return __( 'Customer registration failed.', 'miniorange-2-factor-authentication' );

				case 'There was an error processing the register mobile request.':
					return __( 'There was an error processing the register mobile request.', 'miniorange-2-factor-authentication' );

				case 'There was an exception processing the get user request.':
					return __( 'There was an exception processing the get user request.', 'miniorange-2-factor-authentication' );

				case 'End User retrieved successfully.':
					return __( 'End User retrieved successfully.', 'miniorange-2-factor-authentication' );

				case 'COMPLETED_TEST':
					return __( 'You have successfully completed the test.', 'miniorange-2-factor-authentication' );

				case 'INVALID_EMAIL_VER_REQ':
					return __( 'Invalid request. Test case failed.', 'miniorange-2-factor-authentication' );

				case 'INVALID_ENTRY':
					return __( 'All the fields are required. Please enter valid entries.', 'miniorange-2-factor-authentication' );

				case 'INVALID_PASSWORD':
					return __( 'You already have an account with miniOrange. Please enter a valid password.', 'miniorange-2-factor-authentication' );

				case 'INVALID_REQ':
					return __( 'Invalid request. Please try again', 'miniorange-2-factor-authentication' );

				case 'INVALID_OTP':
					return __( 'Invalid OTP. Please try again.', 'miniorange-2-factor-authentication' );

				case 'INVALID_EMAIL_OR_PASSWORD':
					return __( 'Invalid email or password. Please try again.', 'miniorange-2-factor-authentication' );

				case 'PASSWORDS_MISMATCH':
					return __( 'Password and Confirm password do not match.', 'miniorange-2-factor-authentication' );

				case 'ENTER_YOUR_EMAIL_PASSWORD':
					return __( 'Please enter your registered email and password.', 'miniorange-2-factor-authentication' );

				case 'OTP_SENT':
					return __( 'One Time Passcode has been sent for verification to ', 'miniorange-2-factor-authentication' );

				case 'ERROR_IN_SENDING_OTP_OVER_EMAIL':
					return __( 'There was an error in sending OTP over email. Please click on Resend OTP to try again.', 'miniorange-2-factor-authentication' );

				case 'ERROR_DURING_REGISTRATION':
					return __( 'Error occured while registration. Please try again.', 'miniorange-2-factor-authentication' );

				case 'ERROR_DURING_PROCESS':
					return __( 'An error occured while processing your request. Please Try again.', 'miniorange-2-factor-authentication' );

				case 'ERROR_DURING_PROCESS_EMAIL':
					return __( 'An error occured while processing your request. Please check your SMTP server is configured.', 'miniorange-2-factor-authentication' );

				case 'ERROR_WHILE_SENDING_SMS':
					return __( 'There was an error in sending sms. Please click on Resend OTP to try again.', 'miniorange-2-factor-authentication' );

				case 'ERROR_DURING_USER_REGISTRATION':
					return __( 'Error occurred while registering the user. Please try again.', 'miniorange-2-factor-authentication' );

				case 'VALIDATE_DUO':
					return __( 'Duo push notification validate successfully.', 'miniorange-2-factor-authentication' );

				case 'SET_AS_2ND_FACTOR':
					return __( 'is set as your 2 factor authentication method.', 'miniorange-2-factor-authentication' );

				case 'ERROR_WHILE_SAVING_KBA':
					return __( 'Error occured while saving your kba details. Please try again.', 'miniorange-2-factor-authentication' );

				case 'ANSWER_SECURITY_QUESTIONS':
					return __( 'Please answer the following security questions.', 'miniorange-2-factor-authentication' );

				case 'BACKUP_CODE_LIMIT_REACH':
					return __( 'You have already downloaded the backup codes for this domain.', 'miniorange-2-factor-authentication' );

				case 'BACKUP_CODE_DOMAIN_LIMIT_REACH':
					return __( 'User Limit is reached for your domain.', 'miniorange-2-factor-authentication' );

				case 'BACKUP_CODE_INVALID_REQUEST':
					return __( 'Invalid request.', 'miniorange-2-factor-authentication' );

				case 'USED_ALL_BACKUP_CODES':
					return __( 'You have used all of the backup codes', 'miniorange-2-factor-authentication' );

				case 'INTERNET_CONNECTIVITY_ERROR':
					return __( 'Unable to generate backup codes. Please check your internet and try again.', 'miniorange-2-factor-authentication' );

				case 'TRANSIENT_ACTIVE':
					return __( 'Please try again after some time.', 'miniorange-2-factor-authentication' );

				case 'RESET_DUO_CONFIGURATON':
					return __( 'Your Duo configuration has been reset successfully.', 'miniorange-2-factor-authentication' );

				case 'ERROR_FETCHING_QUESTIONS':
					return __( 'There was an error fetching security questions. Please try again.', 'miniorange-2-factor-authentication' );

				case 'INVALID_ANSWERS':
					return __( 'Invalid Answers. Please try again.', 'miniorange-2-factor-authentication' );

				case 'MIN_PASS_LENGTH':
					return __( 'Choose a password with minimum length 6.', 'miniorange-2-factor-authentication' );

				case 'ACCOUNT_RETRIEVED_SUCCESSFULLY':
					return __( 'Your account has been retrieved successfully.', 'miniorange-2-factor-authentication' );

				case 'DEFAULT_2ND_FACTOR':
					return __( 'has been set as your default 2nd factor method', 'miniorange-2-factor-authentication' );

				case 'RESENT_OTP':
					return __( 'Another One Time Passcode has been sent', 'miniorange-2-factor-authentication' );

				case 'VERIFY':
					return __( 'for verification to', 'miniorange-2-factor-authentication' );

				case 'ERROR_IN_SENDING_EMAIL':
					return __( 'There was an error in sending email. Please click on Resend OTP to try again.', 'miniorange-2-factor-authentication' );

				case 'EMAIL_IN_USE':
					return __( 'The email is already used by other user. Please register with other email.', 'miniorange-2-factor-authentication' );

				case 'EMAIL_MANDATORY':
					return __( 'Please submit your query with email', 'miniorange-2-factor-authentication' );

				case 'ERROR_WHILE_SUBMITTING_QUERY':
					return __( 'Your query could not be submitted. Please try again.', 'miniorange-2-factor-authentication' );

				case 'QUERY_SUBMITTED_SUCCESSFULLY':
					return __( 'Thanks for getting in touch! We shall get back to you shortly.', 'miniorange-2-factor-authentication' );

				case 'SETTINGS_SAVED':
					return __( 'Your settings are saved successfully.', 'miniorange-2-factor-authentication' );

				case 'AUTHENTICATION_FAILED':
					return __( 'Authentication failed. Please try again to test the configuration.', 'miniorange-2-factor-authentication' );

				case 'REGISTER_WITH_MO':
					return __( 'Invalid request. Please register with miniOrange before configuring your mobile.', 'miniorange-2-factor-authentication' );

				case 'ENTER_EMAILID':
					return __( 'Please enter email-id to register.', 'miniorange-2-factor-authentication' );

				case 'ENTER_VALUE':
					return __( 'Please enter a value to test your authentication.', 'miniorange-2-factor-authentication' );

				case 'ENTER_OTP':
					return __( 'Please enter the one time passcode below.', 'miniorange-2-factor-authentication' );

				case 'ERROR_IN_SENDING_OTP':
					return __( 'There was an error in sending one-time passcode. Your transaction limit might have exceeded. Please contact miniOrange or upgrade to our premium plan.', 'miniorange-2-factor-authentication' );

				case 'ERROR_IN_SENDING_OTP_ONPREM':
					return __( 'There was an error in sending one-time passcode. Please check your SMTP Setup and remaining transactions.', 'miniorange-2-factor-authentication' );

				case 'SMTP_CHECK_FOR_EMAIL_VERIFICATON':
					return __( 'Please set your SMTP to get the email to verify the email at the time of login otherwise you will get logged out', 'miniorange-2-factor-authentication' );

				case 'PUSH_NOTIFICATION_SENT':
					return __( 'A Push notification has been sent to your miniOrange Authenticator App.', 'miniorange-2-factor-authentication' );

				case 'ERROR_WHILE_VALIDATING_OTP':
					return __( 'Error occurred while validating the OTP. Please try again.', 'miniorange-2-factor-authentication' );

				case 'TEST_GAUTH_METHOD':
					return __( 'to test Google Authenticator method.', 'miniorange-2-factor-authentication' );

				case 'ERROR_IN_SENDING_OTP_CAUSES':
					return __( 'Error occurred while validating the OTP. Please try again. Possible causes:', 'miniorange-2-factor-authentication' );

				case 'APP_TIME_SYNC':
					return __( 'Your App Time is not in sync.Go to settings and tap on tap on Sync Time now .', 'miniorange-2-factor-authentication' );

				case 'SERVER_TIME_SYNC':
					return __( 'Please make sure your System and device have the same time as the displayed Server time.', 'miniorange-2-factor-authentication' );

				case 'ERROR_WHILE_VALIDATING_USER':
					return __( 'Error occurred while validating the user. Please try again.', 'miniorange-2-factor-authentication' );

				case 'ONLY_DIGITS_ALLOWED':
					return __( 'Only digits are allowed. Please enter again.', 'miniorange-2-factor-authentication' );

				case 'TEST_AUTHY_2FA':
					return __( 'to test Authy 2-Factor Authentication method.', 'miniorange-2-factor-authentication' );

				case 'METHOD':
					return __( 'method.', 'miniorange-2-factor-authentication' );

				case 'TO_TEST':
					return __( 'to test', 'miniorange-2-factor-authentication' );

				case 'SET_2FA':
					return __( 'is set as your Two-Factor method.', 'miniorange-2-factor-authentication' );

				case 'SET_2FA_otp':
					return __( 'is set as your Two-Factor method.', 'miniorange-2-factor-authentication' );

				case 'VERIFICATION_EMAIL_SENT':
					return __( 'A verification email is sent to', 'miniorange-2-factor-authentication' );

				case 'ACCEPT_LINK_TO_VERIFY_EMAIL':
					return __( 'Please click on accept link to verify your email.', 'miniorange-2-factor-authentication' );

				case 'ACCOUNT_CREATED':
					return __( 'Your account has been created successfully.', 'miniorange-2-factor-authentication' );

				case 'ACCOUNT_REMOVED':
					return __( 'Your account has been removed. Please contact your administrator.', 'miniorange-2-factor-authentication' );

				case 'REGISTRATION_SUCCESS':
					return __( 'You are registered successfully.', 'miniorange-2-factor-authentication' );

				case 'DENIED_REQUEST':
					return __( 'You have denied the request.', 'miniorange-2-factor-authentication' );

				case 'DENIED_DUO_REQUEST':
					return __( 'You have denied the request or you have not set duo push notification yet', 'miniorange-2-factor-authentication' );

				case 'DISABLED_2FA':
					return __( 'Two-Factor plugin has been disabled.', 'miniorange-2-factor-authentication' );

				case 'ERROR_WHILE_SAVING_SETTINGS':
					return __( 'Error occurred while saving the settings.Please try again.', 'miniorange-2-factor-authentication' );

				case 'INVALID_REQUEST':
					return __( 'Invalid request. Please register with miniOrange and configure 2-Factor to save your login settings.', 'miniorange-2-factor-authentication' );

				case 'ACCOUNT_ALREADY_EXISTS':
					return __( 'You already have an account with miniOrange, please sign in.', 'miniorange-2-factor-authentication' );

				case 'CONFIGURE_2FA':
					return __( 'to configure another 2 Factor authentication method.', 'miniorange-2-factor-authentication' );

				case 'PHONE_NOT_CONFIGURED':
					return __( 'Your phone number is not configured. Please configure it before selecting OTP Over SMS as your 2-factor method.', 'miniorange-2-factor-authentication' );

				case 'CLICK_HERE':
					return __( 'Click Here', 'miniorange-2-factor-authentication' );

				case 'ERROR_CREATE_ACC_OTP':
					return __( 'An error occured while creating your account. Please try again by sending OTP again.', 'miniorange-2-factor-authentication' );

				case 'LOGIN_WITH_2ND_FACTOR':
					return __( 'Please disable 2FA prompt on WP login page to enable Login with 2nd facor only.', 'miniorange-2-factor-authentication' );

				case 'USER_LIMIT_EXCEEDED':
					return __( 'Your limit of 3 users has exceeded. Please upgrade to premium plans for more users.', 'miniorange-2-factor-authentication' );

				case 'ENTER_SENT_OTP':
					return __( '. Please enter the OTP you received to Validate.', 'miniorange-2-factor-authentication' );

				case 'SENT_OTP':
					return __( 'The OTP has been sent to', 'miniorange-2-factor-authentication' );

				case 'SOMETHING_WENT_WRONG':
					return __( 'Something went wrong', 'miniorange-2-factor-authentication' );

				case 'The transaction limit has been exceeded.':
					return __( 'The transaction limit has been exceeded.', 'miniorange-2-factor-authentication' );

				default:
					return $text;
			}
		}
	}

	new Mo2fConstants();
}
