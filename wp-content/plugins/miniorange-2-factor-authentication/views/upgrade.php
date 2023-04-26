<?php
/**
 * Pricing page of the plugin.
 *
 * @package miniorange-2-factor-authentication/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $mo2fdb_queries, $main_dir;
$user                   = wp_get_current_user();
$is_nc                  = MoWpnsUtility::get_mo2f_db_option( 'mo2f_is_NC', 'get_option' );
$is_customer_registered = 'MO_2_FACTOR_PLUGIN_SETTINGS' === get_option( 'mo_2factor_user_registration_status' );

if ( isset( $_GET['page'] ) && sanitize_text_field( ( wp_unslash( $_GET['page'] ) ) ) === 'mo_2fa_upgrade' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- Reading GET parameter from the URL for checking the tab name, doesn't require nonce verification.
	?><br><br>
	<?php
}
echo '
<a class="mo2f_back_button" style="font-size: 16px; color: #000;" href="' . esc_url( $two_fa ) . '"><span class="dashicons dashicons-arrow-left-alt" style="vertical-align: bottom;"></span> Back To Plugin Configuration</a>';
?>
<br><br>

<?php
wp_register_style( 'mo2f_upgrade_css', $main_dir . '/includes/css/upgrade.min.css', array(), MO2F_VERSION );
wp_register_style( 'mo2f_font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css', array(), MO2F_VERSION );
wp_enqueue_style( 'mo2f_upgrade_css' );
wp_enqueue_style( 'mo2f_font_awesome' );

global $image_path;
?>
<span class="cd-switch"></span>



<section class="mo2fa-popup-overlay">
	<!-- BASIC/PERSONAL 2FA  -->
	<div id="mo2fa-basic-personal-plan" class="mo2f-overlay">
		<div class="popup">
			<a href="#pricing" onclick="mo_hide_popup_feature('mo2fa-basic-personal-plan')" class="mo2fa-close-btn"></a>

			<h2 class="mo2fa-popup-title">Features</h2>
			<ul class="mo2fa-ul-list">

				<li class="mo-api-license-li feature-item">
					TOTP Based Methods</br>

					Google Authenticator</br>
					Microsoft Authenticator<br>
					Authy Authenticator</br>
					LastPass Authenticator</br>
					Duo Authenticator
				</li>

				<li class="mo-api-license-li feature-item">
					2FA Code Over Email
				</li>
				<li class="mo-api-license-li feature-item">
					2FA Code Over SMS<sup>*</sup><a href="<?php echo esc_url( MoWpnsConstants::MO2F_PLUGINS_PAGE_URL ) . '/sms-and-email-transaction-pricing-2fa'; ?>" target="_blank">Charges Apply</a>

				</li>
			</ul>

		</div>
	</div>

	<!-- 2FA FOR LMS  -->
	<div id="mo2fa-lms-plan" class="mo2f-overlay">
		<div class="popup">
			<a href="#pricing" onclick="mo_hide_popup_feature('mo2fa-lms-plan')" class="mo2fa-close-btn"></a>


			<h2 class="mo2fa-popup-title">Features</h2>
			<ul class="mo2fa-ul-list" style="padding:10px 0 10px 0px;">


				<li class="mo-api-license-li feature-item">
					QR Code Authentication/Push Notification
				</li>

				<li class="mo-api-license-li feature-item">
					TOTP Based Methods</br>

					Google Authenticator</br>
					Microsoft Authenticator<br>
					Authy Authenticator</br>
					LastPass Authenticator</br>
					Duo Authenticator
				</li>
				<li class="mo-api-license-li feature-item">
					Configurable code length and Expiration time
				</li>
			</ul>

		</div>
	</div>

	<!-- 2FA FOR MEMBERSHIP  -->
	<div id="mo2fa-membership-plan" class="mo2f-overlay">
		<div class="popup">
			<a href="#pricing" onclick="mo_hide_popup_feature('mo2fa-membership-plan')" class="mo2fa-close-btn"></a>


			<h2 class="mo2fa-popup-title">Features</h2>
			<ul class="mo2fa-ul-list">

				<li class="mo-api-license-li feature-item">
					Single-site Compatible
				</li>
				<li class="mo-api-license-li feature-item">
					White labelling
				</li>
				<li class="mo-api-license-li feature-item">
					TOTP Based Method </br>
					Google Authenticator</br>
					Microsoft Authenticator<br>
					Authy Authenticator</br>
					LastPass Authenticator</br>
					Duo Authenticator
				</li>
				<li class="mo-api-license-li feature-item">
					2FA Code Over SMS<sup>*</sup><a href="<?php echo esc_url( MoWpnsConstants::MO2F_PLUGINS_PAGE_URL ) . '/sms-and-email-transaction-pricing-2fa'; ?>" target="_blank">Charges Apply</a>
				</li>
				<li class="mo-api-license-li feature-item">
					2FA Code Over Email
				</li>
				<li class="mo-api-license-li feature-item">
					Role-Based 2FA
				</li>

				<li class="mo-api-license-li feature-item">Custom Redirection Url</li>
				<li class="mo-api-license-li feature-item">Blackup Login Methods</li>
				<li class="mo-api-license-li feature-item">2FA Code Over Telegram</li>

			</ul>

		</div>
	</div>

	<!-- 2FA FOR ECOMMERCE  -->
	<div id="mo2fa-ecommerce-plan" class="mo2f-overlay">
		<div class="popup">
			<a href="#pricing" onclick="mo_hide_popup_feature('mo2fa-ecommerce-plan')" class="mo2fa-close-btn"></a>


			<h2 class="mo2fa-popup-title">Features</h2>
			<ul class="mo2fa-ul-list">
				<li class="mo-api-license-li feature-item">
					Single-site Compatible
				</li>
				<li class="mo-api-license-li feature-item">
					White labelling
				</li>
				<li class="mo-api-license-li feature-item">
					TOTP Based Methods</br>
					Google Authenticator</br>
					Microsoft Authenticator<br>
					Authy Authenticator</br>
					LastPass Authenticator</br>
					Duo Authenticator
				</li>
				<li class="mo-api-license-li feature-item">
					2FA Code Over Email
				</li>
				<li class="mo-api-license-li feature-item">
					2FA Code Over SMS<sup>*</sup><a href="https://plugins.miniorange.com/sms-and-email-transaction-pricing-2fa" target="_blank">Charges Apply</a>

				</li>
				<li class="mo-api-license-li feature-item">
					Role-Based 2FA
				</li>
				<li class="mo-api-license-li feature-item">
					OTP Over Telegram
				</li>
				<li class="mo-api-license-li feature-item">OTP Over Whatsapp</li>
				<li class="mo-api-license-li feature-item">Custom SMS Gateway</li>
			</ul>
		</div>
	</div>

	<!-- All INCLUSIVE/BUSINESSES  -->
	<div id="mo2fa-inclusive-plan" class="mo2f-overlay">
		<div class="popup">
			<a href="#pricing" onclick="mo_hide_popup_feature('mo2fa-inclusive-plan')" class="mo2fa-close-btn"></a>


			<h2 class="mo2fa-popup-title">Features</h2>
			<ul class="mo2fa-ul-list">
				<li class="mo-api-license-li feature-item">
					Single-site Compatible
				</li>
				<li class="mo-api-license-li feature-item">
					White Labelling
				</li>
				<li class="mo-api-license-li feature-item">
					Remember Device
				</li>
				<li class="mo-api-license-li feature-item">
					Role-Based 2FA
				</li>
				<li class="mo-api-license-li feature-item">
					All TOTP Based Methods</br>
				</li>
				<li class="mo-api-license-li feature-item">
					2FA Code Over Email
				</li>
				<li class="mo-api-license-li feature-item">
					2FA Code Over SMS<sup>*</sup><a href="https://plugins.miniorange.com/sms-and-email-transaction-pricing-2fa" target="_blank">Charges Apply</a>

				</li>
				<li class="mo-api-license-li feature-item">Backup Login Method</li>
				<li class="mo-api-license-li feature-item">Custom Redirection Url</li>
				<li class="mo-api-license-li feature-item">Passwordless Login</li>
				<li class="mo-api-license-li feature-item">
					2FA via Telegram
				</li>
				<li class="mo-api-license-li feature-item">
					2FA via WhatsApp
				</li>
				<li class="mo-api-license-li feature-item">
					Custom SMS Gateway
				</li>
			</ul>

		</div>
	</div>

</section>



<div class="mo2fa-pricing-section">

	<div class="mo2fa-pricing-div">
		<h4 class="mo2fa-pricing-heading">Personal 2FA</h4><sub class="mo2fa-sub-heading">For individual requirement</sub>

		<p class="mo2fa-pricing-para">
			2FA For 100 Users
			<br>
			Role-Based 2FA
			<br>
			Backup Login Methods<span class="mo2fa_12_tooltip_methodlist"><i class="fa fa-info-circle fa-xs" aria-hidden="true"></i>
				<span class="mo2fa_methodlist">
					Security Questions(KBA)<br>
					OTP Over Email<br>
					Backup Codes
				</span>
			</span>
		</p>
		<div class="mo2fa-text-center">
			<button onclick="mo_show_popup_feature('mo2fa-basic-personal-plan')" class="mo2fa-circle-wrapper">
				<i class="fas fa-plus fa-xx"></i>
			</button>
		</div>

		<div class="one-row-price">


			<div class="item-one">
				<p class="mt"><span class="mo2f-display-1"><span>$</span><span id="dollar_mo_basic_price" class="mo_premium_price">99</span>/year<sup>*</sup></span></span></p>

			</div>
			<div class="item-two">

				<div class="container-dropdown discount-price">
					<div class="select-dropdown">
						<select class="dropdown-width mo2f-inst-btn2" id="mo_basic_price" onchange="update_site('mo_basic_price')">
							<option value="99" data-price="99"> 1 SITE </option>
							<option value="179" data-price="179"> 2 SITES</option>
							<option value="299" data-price="299"> 5 SITES</option>
							<option value="499" data-price="499"> 10 SITES</option>
							<option value="599" data-price="599"> 25 SITES</option>
						</select>


					</div>
				</div>
			</div>

		</div>
		<div class="text-align">


		<div class="mo2fa_text-align-center">
				<div id="mo2fa_custom_my_plan_2fa_mo">
					<?php if ( isset( $is_customer_registered ) && $is_customer_registered ) { ?>
						<a onclick="mo2f_upgradeform('wp_security_two_factor_basic_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } else { ?>
						<a onclick="mo2f_register_and_upgradeform('wp_security_two_factor_basic_plan','2fa_plan')" target="blank" class=" license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } ?>
				</div>
		</div>
					</div>

	</div>

	<div class="mo2fa-pricing-div">
		<h4 class="mo2fa-pricing-heading">2FA For LMS
		</h4><sub class="mo2fa-sub-heading">For e-learning sites</sub>

		<p class="mo2fa-pricing-para">

			For Unlimited Sites
			<br>
			Session Restriction
			<br>
			Prevent Credential Sharing<span class="mo2fa_15_tooltip_methodlist"><i class="fa fa-info-circle fa-xs" aria-hidden="true"></i>
				<span class="mo2fa_methodlist">
					Credential sharing is prevented through QR code authentication.
				</span>
			</span>
		</p>
		<div class="mo2fa-text-center">
			<button onclick="mo_show_popup_feature('mo2fa-lms-plan')" class="mo2fa-circle-wrapper">
				<i class="fas fa-plus fa-xx"></i>
			</button>
		</div>

		<div class="one-row-price">

			<div class="item-one">
				<p class="mt"><span class="mo2f-display-1"><span>$</span><span id="dollar_mo_lms_price" class="mo_premium_price">59</span>/year<sup>*</sup></span></span></p>

			</div>
			<div class="item-two">

				<div class="container-dropdown discount-price">
					<div class="select-dropdown">

						<select class="dropdown-width mo2f-inst-btn2" id="mo_lms_price" onchange="update_site('mo_lms_price')">
							<option value="59" data-price="59"> 5 USERS </option>
							<option value="78" data-price="78"> 10 USERS</option>
							<option value="98" data-price="98"> 25 USERS</option>
							<option value="128" data-price="128"> 50 USERS</option>
							<option value="228" data-price="228"> 100 USERS</option>
							<option value="378" data-price="378"> 500 USERS</option>
							<option value="528" data-price="528"> 1000 USERS</option>
							<option value="878" data-price="878"> 5000 USERS</option>
							<option value="1028" data-price="1028"> 10000 USERS</option>
							<option value="1478" data-price="1478"> 20000 USERS</option>
						</select>
					</div>
				</div>
			</div>

		</div>
		<div class="text-align">

		<div class="mo2fa_text-align-center">				<div id="mo2fa_custom_my_plan_2fa_mo">
					<?php if ( isset( $is_customer_registered ) && $is_customer_registered ) { ?>
						<a onclick="mo2f_upgradeform('wp_2fa_lms_plan','2fa_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } else { ?>
						<a onclick="mo2f_register_and_upgradeform('wp_2fa_lms_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } ?>
				</div>
		</div>
					</div>

	</div>

	<div class="mo2fa-pricing-div">
		<h4 class="mo2fa-pricing-heading">2FA For Membership</h4>
		<sub class="mo2fa-sub-heading">For membership sites</sub>

		<p class="mo2fa-pricing-para">

			For Unlimited Users
			<br>
			Role-Based 2FA And Custom Redirect Url
			<br>
			Session Restriction And Remember Device <span class="mo2fa_1_tooltip_methodlist"><i class="fa fa-info-circle fa-xs" aria-hidden="true"></i>
				<span class="mo2fa_methodlist">
					2FA is skipped for the remembered device.
				</span>
			</span>
		</p>

		<div class="mo2fa-text-center">
			<button onclick="mo_show_popup_feature('mo2fa-membership-plan')" class="mo2fa-circle-wrapper">
				<i class="fas fa-plus fa-xx"></i>
			</button>
		</div>

		<div class="one-row-price">

			<div class="item-one">
				<p class="mt"><span class="mo2f-display-1"><span>$</span><span id="dollar_mo_membership_price" class="mo_premium_price">199</span>/year<sup>*</sup></span></span><br></p>
			</div>

			<div class="item-two">
				<div class="container-dropdown discount-price">
					<div class="select-dropdown">
						<select class="dropdown-width mo2f-inst-btn2" id="mo_membership_price" onchange="update_site('mo_membership_price')">
							<option value="199" data-price="199"> 1 SITE </option>
							<option value="299" data-price="299"> 2 SITES</option>
							<option value="499" data-price="499"> 5 SITES</option>
							<option value="799" data-price="799"> 10 SITES</option>
							<option value="1599" data-price="1599"> 25 SITES</option>
						</select>

					</div>
				</div>
			</div>
		</div>


		<div class="text-align">

		<div class="mo2fa_text-align-center">
				<div id="mo2fa_custom_my_plan_2fa_mo">
					<?php if ( isset( $is_customer_registered ) && $is_customer_registered ) { ?>
						<a onclick="mo2f_upgradeform('wp_security_two_factor_membership_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } else { ?>
						<a onclick="mo2f_register_and_upgradeform('wp_security_two_factor_membership_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } ?>
				</div>
		</div>
					</div>


	</div>

	<div class="mo2fa-pricing-div">
		<h4 class="mo2fa-pricing-heading">2FA For Ecommerce</h4><sub class="mo2fa-sub-heading">For e-commerce website</sub>

		<p class="mo2fa-pricing-para">

			For Unlimited Users <br>
			2FA On Checkout Forms
			<br>
			Remember Device<br>
			Passwordless Login <span class="mo2fa_tooltip_methodlist"><i class="fa fa-info-circle fa-xs" aria-hidden="true"></i>
				<span class="mo2fa_methodlist">
					Passwordless Login with Phone
				</span>
			</span>
		</p>
		<div class="mo2fa-text-center">
			<button onclick="mo_show_popup_feature('mo2fa-ecommerce-plan')" class="mo2fa-circle-wrapper">
				<i class="fas fa-plus fa-xx"></i>
			</button>
		</div>

		<div class="one-row-price">
			<div class="item-one">

				<p class="mt"><span class="mo2f-display-1"><span>$</span><span id="dollar_mo_ecommerce_price" class="mo_premium_price">199</span>/year<sup>*</sup></span></span><br></p>
			</div>

			<div class="item-two">

				<div class="container-dropdown discount-price">
					<div class="select-dropdown">
						<select class="dropdown-width mo2f-inst-btn2" id="mo_ecommerce_price" onchange="update_site('mo_ecommerce_price')">
							<option value="199" data-price="199"> 1 SITE </option>
							<option value="299" data-price="299"> 2 SITES</option>
							<option value="499" data-price="499"> 5 SITES</option>
							<option value="799" data-price="799"> 10 SITES</option>
							<option value="1599" data-price="1599"> 25 SITES</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="text-align">

		<div class="mo2fa_text-align-center">
							<div id="mo2fa_custom_my_plan_2fa_mo">
					<?php if ( isset( $is_customer_registered ) && $is_customer_registered ) { ?>
						<a onclick="mo2f_upgradeform('wp_security_two_factor_ecommerce_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } else { ?>
						<a onclick="mo2f_register_and_upgradeform('wp_security_two_factor_ecommerce_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } ?>
				</div>
		</div>
					</div>

	</div>

	<div class="mo2fa-pricing-div">

		<h4 class="mo2fa-pricing-heading">All Inclusive/Business</h4><sub class="mo2fa-sub-heading">For big businesses</sub>

		<p class="mo2fa-pricing-para twofa-para-contactus">
			For Unlimited Users<br> All features in Basic 2FA, Ecommerce 2FA And<br> Membership 2FA plan
			<br> AJAX Login form support
		</p>

		<div class="mo2fa-text-center">
			<button onclick="mo_show_popup_feature('mo2fa-inclusive-plan')" class="mo2fa-circle-wrapper">
				<i class="fas fa-plus fa-xx"></i>
			</button>
		</div>

		<div class="one-row-price">

			<div class="item-one">
				<p class="mt"><span class="mo2f-display-1"><span>$</span><span id="dollar_mo_all_inclusive_price" class="mo_premium_price">249</span>/year<sup>*</sup></span></span><br></p>
			</div>

			<div class="item-two">
				<div class="container-dropdown discount-price">
					<div class="select-dropdown">
						<select class="dropdown-width mo2f-inst-btn2" id="mo_all_inclusive_price" onchange="update_site('mo_all_inclusive_price')">
							<option value="249" data-price="249"> 1 SITE </option>
							<option value="349" data-price="349"> 2 SITES</option>
							<option value="549" data-price="549"> 5 SITES</option>
							<option value="849" data-price="849"> 10 SITES</option>
							<option value="1649" data-price="1649"> 25 SITES</option>
						</select>

					</div>
				</div>
			</div>
		</div>

		<div class="text-align">
		<div class="mo2fa_text-align-center">
				<div id="mo2fa_custom_my_plan_2fa_mo">
					<?php if ( isset( $is_customer_registered ) && $is_customer_registered ) { ?>
						<a onclick="mo2f_upgradeform('wp_security_two_factor_business_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } else { ?>
						<a onclick="mo2f_register_and_upgradeform('wp_security_two_factor_business_plan','2fa_plan')" target="blank" class="license-btn-2fa-premise mo2f-license-btn-2fa">UPGRADE NOW</a>
					<?php } ?>
				</div>
		</div>
					</div>


	</div>

	<div class="mo2fa-pricing-div">
		<h4 class="mo2fa-pricing-heading">Custom Plan</h4>
		<p class="mo2fa-pricing-para tfa-pricing-para-contact-us">
			Nothing out here matches your requirement?<br>
			Don't worry, we've got you covered.<br>
			Contact us for custom solutions<br>tailor-made for your requirements.

		</p>
		<div class="mo2fa-text-center">
		<div class="mo2fa_text-align-center">
				<img class="mo2f-pricing-image" src="<?php echo esc_url( $image_path ) . 'includes/images/custom-pricing-plan.png'; ?>">
					</div>
		</div>
		<div class="text-align">
			<a href="https://mail.google.com/mail/u/0/?fs=1&amp;tf=cm&amp;source=mailto&amp;su=Two+Factor+Authentication+Plugin+-+WP+2FA+Customized+Plugin+Request.&amp;to=2fasupport@xecurify.com&amp;body=I+want+to+request+for+customized+2FA+plugin+plan.+" target="_blank" rel="nofollow" class="license-btn-2fa-premise-call-us">Contact Us</a>
		</div>
	</div>
	<div class="bottom-shapes">
	</div>
</div>



<form style="display:none;" id="mo2fa_loginform" action="<?php echo esc_url( MO_HOST_NAME . '/moas/login' ); ?>" target="_blank" method="post">
	<input type="text" name="redirectUrl" value="<?php echo esc_url( MO_HOST_NAME . '/moas/initializepayment' ); ?>">
	<input type="text" name="requestOrigin" id="requestOrigin" value="">
</form>

<script>
	jQuery("dollar_mo_basic_price").click();
	jQuery("dollar_mo_lms_price").click();
	jQuery("dollar_mo_membership_price").click();
	jQuery("dollar_mo_ecommerce_price").click();
	jQuery("dollar_mo_all_inclusive_price").click();

	function update_site(plan_name) {


		var sites = document.getElementById(plan_name).value;

		var users_addion = parseInt(sites);

		document.getElementById("dollar_" + plan_name).innerHTML = +users_addion;

	}

	function mo2f_upgradeform(planType, planname) {
		jQuery('#requestOrigin').val(planType);
		jQuery('#mo2fa_loginform').submit();

	}

	function mo_show_popup_feature(popup_id) {
		document.getElementById(popup_id).style.visibility = "visible";
		document.getElementById(popup_id).style.opacity = "1";
	}

	function mo_hide_popup_feature(popup_id) {
		document.getElementById(popup_id).style.opacity = "0";
		document.getElementById(popup_id).style.visibility = "hidden";
	}

	function showData(e) {

		var parent = e.parentElement
		var x = GetElementInsideContainer(parent, "plugin-features");
		var H = document.createElement("i");

		let childarry = e.childNodes
		let childelement = childarry[1];
		if (x.style.display == "none") {
			x.style.display = "block";
			H.setAttribute("class", "fa fa-minus-circle");


			e.replaceChild(H, childarry[1]);



		} else {
			x.style.display = "none";
			H.setAttribute("class", "fa fa-plus-circle");
			e.replaceChild(H, childarry[1]);


		}
	}

	function GetElementInsideContainer(parentElement, childID) {
		var elm = {};
		var elms = parentElement.getElementsByTagName("*");
		for (var i = 0; i < elms.length; i++) {
			if (elms[i].id === childID) {
				elm = elms[i];
				break;
			}
		}
		return elm;
	}
</script>
<div>
		<div class="mo2fa-plan-comparision-outer-box">

		<h2 class="mo2fa-pricing-heading">Plan Comparison</h2>
		<br>
		<div class="plan-comparison">
			<table class="mo2fa-comparision-table-pricing">
				<thead>
					<tr class="table-heading-border">
						<th class="mo2fa-table-heading">Features</th>
						<th class="mo2fa-table-heading-one">Personal 2FA</th>
						<th class="mo2fa-table-heading-one">2FA For Learning Management System</th>
						<th class="mo2fa-table-heading-two">2FA For Membership</th>
						<th class="mo2fa-table-heading-two">2FA For Ecommerce</th>
						<th class="mo2fa-table-heading-two">All Inclusive/Business</th>
					</tr>
				</thead>
				<tbody>
					<tr class="table-row">
						<td class="mo2fa-column-first">
							<div>&nbsp;&nbsp;Unlimited Sites</div>
						</td>
						<td class="table-checks mo2fa-column-second">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks mo2fa-column-second">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks mo2fa-column-third">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks mo2fa-column-third">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks mo2fa-column-third">
							<i class="fa fa-times"></i>
						</td>
					</tr>
					<tr class="table-row">
						<td class="mo2fa-column-first">
							<div> &nbsp;&nbsp;Unlimited Users </div>
						</td>
						<td class="table-checks mo2fa-column-second">
							For 100 Users
						</td>
						<td class="table-checks mo2fa-column-second">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks mo2fa-column-third">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks mo2fa-column-third">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks mo2fa-column-third">
							<i class="fa fa-check"></i>
						</td>
					</tr>
					<tr class="table-row">
						<td colspan="6">
							<div class="plugin-data" id="plugin-data" onclick="showData(this)">
								<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>
								&nbsp;&nbsp;Authentication Methods
							</div>
							<div id="plugin-features" class="plugin-features-class" style="display: none;">
								<table class="add-on-table">
									<tr class="table-row">
										<th class="table-row mo2fa-table-heading"></th>
										<th class="table-row mo2fa-table-heading-one"></th>
										<th class="table-row mo2fa-table-heading-one"></th>
										<th class="table-row mo2fa-table-heading-two"></th>
										<th class="table-row mo2fa-table-heading-two"></th>
										<th class="table-row mo2fa-table-heading-two"></th>
									</tr>


									<tr class="table-row">
										<td class="mo2fa-column-first">
											<div class="plugin-data TOTP Based Authenticators" id="plugin-data" onclick="showData(this)">
												<i class="fa fa-plus-circle table-plus-icon-one" aria-hidden="true" style="display:contents"></i>
												&nbsp;&nbsp;TOTP Based Authenticators
											</div>
											<div id="plugin-features" class="plugin-features-class" style="display: none;">
												<ul>
													<li>Google Authenticator</li>
													<li> Microsoft Authenticator</li>
													<li>Authy Authenticator</li>
													<li>LastPass Authenticator</li>
													<li>Duo Authenticator</li>
												</ul>
											</div>
										</td>
										<td class="table-checks mo2fa-column-second"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-second"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"><i class="fa fa-check"></i></td>
									</tr>

									<tr class="table-row">
										<td class="mo2fa-column-first">Security Questions</td>
										<td class="table-checks mo2fa-column-second"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-second"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"><i class="fa fa-check"></i></td>
									</tr>
									<tr class="table-row">
										<td class="mo2fa-column-first">Email Verification</td>
										<td class="table-checks mo2fa-column-second"><i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-second"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
									</tr>
									<tr class="table-row">
										<td class="mo2fa-column-first">OTP Over Email</td>
										<td class="table-checks mo2fa-column-second"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-second"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
									</tr>
									<tr class="table-row">
										<td class="mo2fa-column-first">OTP Over SMS</td>
										<td class="table-checks mo2fa-column-second"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-second"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
										<td class="table-checks mo2fa-column-third"> <i class="fa fa-check"></i></td>
									</tr>
									<tr class="table-row">
										<td>
											<div class="plugin-data" id="plugin-data" onclick="showData(this)">
												<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>&nbsp;&nbsp;miniOrange Authenticator
											</div>
											<div id="plugin-features" class="plugin-features-class" style="display: none;">
												<ul>
													<li>Soft Token Code</li>
													<li>QR Code Authentication</li>
													<li>Push Notifications</li>
												</ul>
											</div>
										</td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-check"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
									</tr>
									<tr class="table-row">
										<td>Yubikey (Hardware Token)</td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-check"></i></td>
									</tr>
									<tr class="table-row">
										<td>OTP Over Whatsapp (Add-on)</td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-check"></i></td>
										<td class="table-checks"> <i class="fa fa-check"></i></td>
									</tr>
									<tr class="table-row">
										<td>OTP Over Telegram</td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-times"></i></td>
										<td class="table-checks"> <i class="fa fa-check"></i></td>
										<td class="table-checks"> <i class="fa fa-check"></i></td>
										<td class="table-checks"> <i class="fa fa-check"></i></td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
					<tr class="table-row">
						<td>
							<div> &nbsp;&nbsp;Passwordless Login </div>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
					</tr>
					<tr class="table-row">
						<td>
							<div> &nbsp;&nbsp;White Labelling </div>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check">
						</td>
						<td class="table-checks">
							<i class="fa fa-check">
						</td>
						<td class="table-checks">
							<i class="fa fa-check">
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
					</tr>
					<tr class="table-row">
						<td>
							<div> &nbsp;&nbsp;Custom SMS Gateway </div>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
					</tr>
					<tr class="table-row">
						<td>
							<div class="plugin-data" id="plugin-data" onclick="showData(this)">
								<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>&nbsp;&nbsp;Backup Login Method
							</div>
							<div id="plugin-features" class="plugin-features-class" style="display: none;">
								<ul>
									<li>Security Questions(KBA)</li>
									<li>OTP Over Email</li>
									<li>Backup Codes</li>
								</ul>
							</div>
						</td>

						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
					</tr>
					<tr class="table-row">
						<td colspan="6">
							<div class="plugin-data" id="plugin-data" onclick="showData(this)">
								<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>&nbsp;&nbsp;Add ons
							</div>
							<div id="plugin-features" class="plugin-features-class" style="display: none;">
								<table class="add-on-table">
									<tbody>
										<tr class="table-row mo2fa-table-width">
											<td class="table-row mo2fa-table-heading"></td>
											<td class="table-row mo2fa-table-heading-one"></td>
											<td class="table-row mo2fa-table-heading-one"></td>
											<td class="table-row mo2fa-table-heading-two"></td>
											<td class="table-row mo2fa-table-heading-two"></td>
											<td class="table-row mo2fa-table-heading-two"></td>
										</tr>
										<tr class="table-row mo2fa-table-width">
											<td class="mo2fa-table-heading-add-ons">
												<div class="plugin-data" id="plugin-data" onclick="showData(this)">
													<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>&nbsp;&nbsp;Remember Device Add-on
												</div>
												<div id="plugin-features" class="plugin-features-class" style="display: none;">
													<small> You can save your device using the Remember device addon and you will get a two-factor authentication prompt to check your identity if you try to login from different devices.</small>
												</div>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row" style="background-color:white;">
											<td class="mo2fa-table-heading-add-ons">
												<div class="plugin-data" id="plugin-data" onclick="showData(this)">
													<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>&nbsp;&nbsp;Personalization Add-on
												</div>
												<div id="plugin-features" class="plugin-features-class" style="display: none;">
													<small> You'll get many more customization options in Personalization, such as
														custom Email and SMS Template, Custom Login Popup, Custom Security Questions, and many more.</small>
												</div>
											</td>
											<td class="table-checks mo2fa-table-heading-one">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks mo2fa-table-heading-one">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks mo2fa-table-heading-two">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks mo2fa-table-heading-two">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks mo2fa-table-heading-two">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row" style="background-color:white;">
											<td class="mo2fa-table-heading-add-ons">
												<div class="plugin-data" id="plugin-data" onclick="showData(this)">
													<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>&nbsp;&nbsp;Short Codes Add-on
												</div>
												<div id="plugin-features" class="plugin-features-class" style="display: none;">
													<small>Shortcode Add-ons mostly include Allow 2FA shortcode (you can use this this to add 2FA on any page),
														Reconfigure 2FA add-on (you can use this add-on to reconfigure your 2FA if you have lost your 2FA verification ability), remember device shortcode.
													</small>
												</div>
												<div>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row" style="background-color:white;">
											<td class="mo2fa-table-heading-add-ons">Session Management</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row" style="background-color:white;">
											<td class="mo2fa-table-heading-add-ons">Page Restriction Add-On</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
					<tr class="table-row">
						<td colspan="6">
							<div class="plugin-data" id="plugin-data" onclick="showData(this)">
								<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>&nbsp;&nbsp;Advance WordPress Login Settings
							</div>
							<div id="plugin-features" class="plugin-features-class" style="display: none;">
								<table class="add-on-table">
									<tbody class="add-on-table">
										<tr class="table-row">
											<th class="table-row mo2fa-table-heading"></th>
											<th class="table-row mo2fa-table-heading-one"></th>
											<th class="table-row mo2fa-table-heading-one"></th>
											<th class="table-row mo2fa-table-heading-two"></th>
											<th class="table-row mo2fa-table-heading-two"></th>
											<th class="table-row mo2fa-table-heading-two"></th>
										</tr>
										<tr class="table-row">
											<td class="mo2fa-table-heading-add-ons">Force Two Factor for Users</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row">
											<td>Role Based and User Based Authentication settings</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row">
											<td>Email Verififcation During Two-Factor Setup</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row">
											<td>Inline Registration (2FA Setup After First Login)</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row">
											<td>Mobile Support</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row">
											<td>Privacy Policy Settings</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
										<tr class="table-row">
											<td>XML-RPC</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-times"></i>
											</td>
											<td class="table-checks">
												<i class="fa fa-check"></i>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
					<tr class="table-row mo2fa-table-width">
						<td>
							<div class="plugin-data" id="plugin-data" onclick="showData(this)">
								<i class="fa fa-plus-circle table-plus-icon" aria-hidden="true" style="display:contents"></i>&nbsp;&nbsp;Advance Security Features
							</div>
							<div id="plugin-features" class="plugin-features-class" style="display: none;">

								<ul>
									<li>Brute Force Protection</li>

									<li>IP Blocking</li>

									<li>Monitoring</li>

									<li>File Protection</li>

									<li>Country Blocking</li>

									<li>HTACCESS Level Blocking</li>

									<li>Browser Blocking</li>

									<li>Block Global Blacklisted Email Domains</li>

									<li>DB Backup</li>
								</ul>
							</div>
						</td>

						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
					</tr>
					<tr class="table-row">
						<td>
							<div> &nbsp;&nbsp;Multi-Site Support </div>
						</td>
						<td class="table-checks">
							<i class="fa fa-times"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							Upto 3 subsites
						</td>
						<td class="table-checks">
							Upto 3 subsites
						</td>
						<td class="table-checks">
							Upto 3 subsites
						</td>
					</tr>
					<tr class="table-row">
						<td>
							<div> &nbsp;&nbsp;Language Translation Support </div>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
						<td class="table-checks">
							<i class="fa fa-check"></i>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>


<div class="mo2f_table_layout mo2fa-font-text-table" style="width: 90%;margin-left:3%">
	<div>
		<h2><?php esc_html_e( 'Steps to upgrade to the Premium Plan :', 'miniorange-2-factor-authentication' ); ?></h2>
		<ol class="mo2f_licensing_plans_ol">
			<li>
				<?php
				printf(
				/* Translators: %s: bold tags */
					esc_html( __( 'Click on %1$1sUpgrade Now%2$2s button of your preferred plan above.', 'miniorange-2-factor-authentication' ) ),
					'<b>',
					'</b>'
				);
				?>
				</li>
				<li><?php esc_html_e( ' You will be redirected to the miniOrange Console. Enter your miniOrange username and password, after which you will be redirected to the payment page.', 'miniorange-2-factor-authentication' ); ?></li>
				<li><?php esc_html_e( 'Select the number of users/sites you wish to upgrade for, and any add-ons if you wish to purchase, and make the payment.', 'miniorange-2-factor-authentication' ); ?></li>
				<li>
					<?php
					printf(
					/* Translators: %s: bold tags */
						esc_html( __( 'After making the payment, you can find the respective %1$1splugins%2$2s to download from the %3$3sLicense%4$4s tab in the left navigation bar of the miniOrange Console.', 'miniorange-2-factor-authentication' ) ),
						'<b>',
						'</b>',
						'<b>',
						'</b>'
					);
					?>
				</li>
				<li>
					<?php
					printf(
					/* Translators: %s: bold tags */
						esc_html( __( 'Download the paid plugin from the %1$1sReleases and Downloads%2$2s tab through miniOrange Console .', 'miniorange-2-factor-authentication' ) ),
						'<b>',
						'</b>'
					);
					?>
				</li>
				<li>
					<?php
					printf(
					/* Translators: %s: bold tags */
						esc_html( __( 'Deactivate and delete the free plugin from %1$1sWordPress dashboard%2$2s and install the paid plugin downloaded.', 'miniorange-2-factor-authentication' ) ),
						'<b>',
						'</b>'
					);
					?>
				</li>
				<li><?php esc_html_e( 'Login to the paid plugin with the miniOrange account you used to make the payment, after this your users will be able to set up 2FA.', 'miniorange-2-factor-authentication' ); ?></li>
			</ol>
		</div>
		<hr>
		<div>
			<h2><?php esc_html_e( 'Note :', 'miniorange-2-factor-authentication' ); ?></h2>
			<ol class="mo2f_licensing_plans_ol">
				<li><?php esc_html_e( 'The plugin works with many of the default custom login forms (like Woocommerce/Theme My Login/Login With Ajax/User Pro/Elementor), however if you face any issues with your custom login form, contact us and we will help you with it.', 'miniorange-2-factor-authentication' ); ?></li>
				<li>
					<?php
					printf(
					/* Translators: %s: bold tags */
						esc_html( __( 'The %1$1slicense key %2$2sis required to activate the premium Plugins. You will have to login with the miniOrange Account you used to make the purchase then enter license key to activate plugin.', 'miniorange-2-factor-authentication' ) ),
						'<b>',
						'</b>'
					);
					?>
				</li>
		</ol>
	</div>
	<hr>
	<br>
	<div>
		<?php
		printf(
			/* Translators: %s: bold tags */
			esc_html( __( '%1$1sRefund Policy : %2$2sAt miniOrange, we want to ensure you are 100%% happy with your purchase. If the premium plugin you purchased is not working as advertised and you\'ve attempted to resolve any issues with our support team, which couldn\'t get resolved then we will refund the whole amount within 10 days of the purchase. ', 'miniorange-2-factor-authentication' ) ),
			'<b class="mo2fa_note">',
			'</b>'
		);
		?>
	</div>
	<br>
	<hr>
	<br>
	<div>
		<?php
		printf(
			esc_html(
				/* Translators: %s: bold tags */
				__(
					'%1$1sSMS Charges :%2$2sIf you wish to choose OTP Over SMS/OTP Over SMS and Email as your authentication method,
	SMS transaction prices & SMS delivery charges apply and they depend on country. SMS validity is for lifetime.',
					'miniorange-2-factor-authentication'
				)
			),
			'<b class="mo2fa_note">',
			'</b>'
		);
		?>
	</div>
	<br>
	<hr>
	<br>
	<div>
		<?php
				printf(
					esc_html(
						/* Translators: %s: bold tags */
						__(
							'%1$sMultisite : %2$sFor your first license 3 subsites will be activated automatically on the same domain. And if you wish to use it for more please contact support ',
							'miniorange-2-factor-authentication'
						)
					),
					'<b class="mo2fa_note">',
					'</b>'
				);
				?>
	</div>
	<br>
	<hr>
	<br>
	<div>
		<?php
				printf(
					esc_html(
						/* Translators: %s: bold tags and links*/
						__(
							'%1$sEnd User License Agreement : %2$2s %3$3sClick Here%4$4s to read our End User License Agreement.',
							'miniorange-2-factor-authentication'
						)
					),
					'<b class="mo2fa_note">',
					'</b>',
					'<a href="https://plugins.miniorange.com/end-user-license-agreement" target="blank">',
					'</a>'
				);
				?>
	</div>
	<br>
	<hr>
	<br>
	<div>
		<?php
		printf(
			esc_html(
				/* Translators: %s: bold tags and links*/
				__(
					'%1$sContact Us : %2$sIf you have any doubts regarding the licensing plans, you can mail us at %3$sinfo@xecurify.com%4$s or submit a query using the support form.',
					'miniorange-2-factor-authentication'
				)
			),
			'<b class="mo2fa_note">',
			'</b>',
			'<a href="mailto:info@xecurify.com"><i>',
			'</i></a>'
		);
		?>
	</div>
</div>
<div id="mo2f_payment_option" class="mo2f_table_layout mo2fa-supported-payment-method" style="width: 90%;margin-left:3%">
	<div>
		<h3>Supported Payment Methods</h3>
		<hr>
		<div class="mo_2fa_container">
			<div class="mo_2fa_card-deck">
				<div class="mo_2fa_card mo_2fa_animation">
					<div class="mo_2fa_Card-header">
						<?php
						echo '<img src="' . esc_url( dirname( plugin_dir_url( __FILE__ ) ) ) . '/includes/images/card.png" class="mo2fa_card">';
						?>
					</div>
					<hr class="mo2fa_hr">
					<div class="mo_2fa_card-body">
						<p class="mo2fa_payment_p">If payment is done through Credit Card/Intenational debit card, the license would be created automatically once payment is completed. </p>
						<p class="mo2fa_payment_p"><i><b>For guide
							<?php echo '<a href=' . esc_url( MoWpnsConstants::FAQ_PAYMENT_URL ) . ' target="blank">Click Here.</a>'; ?></b></i></p>
						</div>
					</div>
					<div class="mo_2fa_card mo_2fa_animation">
						<div class="mo_2fa_Card-header">
							<?php
							echo '<img src="' . esc_url( dirname( plugin_dir_url( __FILE__ ) ) ) . '/includes/images/paypal.png" class="mo2fa_card">';
							?>
					</div>
					<hr class="mo2fa_hr">
					<div class="mo_2fa_card-body">
						<?php echo '<p class="mo2fa_payment_p">Use the following PayPal id for payment via PayPal.</p><p><i><b style="color:#1261d8"><a href="mailto:' . esc_html( MoWpnsConstants::SUPPORT_EMAIL ) . '">info@xecurify.com</a></b></i>'; ?>
					</div>
				</div>
				<div class="mo_2fa_card mo_2fa_animation">
					<div class="mo_2fa_Card-header">
						<?php
						echo '<img src="' . esc_url( dirname( plugin_dir_url( __FILE__ ) ) ) . '/includes/images/bank-transfer.png" class="mo2fa_card mo2fa_bank_transfer">';
						?>

					</div>
					<hr class="mo2fa_hr">
					<div class="mo_2fa_card-body">
						<?php echo '<p class="mo2fa_payment_p">If you want to use Bank Transfer for payment then contact us at <i><b style="color:#1261d8"><a href="mailto:' . esc_html( MoWpnsConstants::SUPPORT_EMAIL ) . '">info@xecurify.com</a></b></i> so that we can provide you bank details. </i></p>'; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="mo_2fa_mo-supportnote">
			<p class="mo2fa_payment_p"><b>Note :</b> Once you have paid through PayPal/Bank Transfer, please inform us at <i><b style="color:#1261d8"><a href="mailto:<?php echo esc_html( MoWpnsConstants::SUPPORT_EMAIL ); ?>">info@xecurify.com</a></b></i>, so that we can confirm and update your License.</p>
		</div>
	</div>
</div>
<form class="mo2f_display_none_forms" id="mo2fa_loginform" action="<?php echo esc_url( MO_HOST_NAME . '/moas/login' ); ?>" target="_blank" method="post">
	<input type="email" name="username" value="<?php echo esc_url( get_option( 'mo2f_email' ) ); ?>" />
	<input type="text" name="redirectUrl" value="<?php echo esc_url( MO_HOST_NAME . '/moas/initializepayment' ); ?>" />
	<input type="text" name="requestOrigin" id="requestOrigin" />
</form>

<form class="mo2f_display_none_forms" id="mo2fa_register_to_upgrade_form" method="post">
	<input type="hidden" name="requestOrigin" />
	<input type="hidden" name="mo2fa_register_to_upgrade_nonce" value="<?php echo esc_attr( wp_create_nonce( 'miniorange-2-factor-user-reg-to-upgrade-nonce' ) ); ?>" />
</form>


<script type="text/javascript">
	function mo2f_upgradeform(planType, planname) {
		jQuery('#requestOrigin').val(planType);
		jQuery('#mo2fa_loginform').submit();
		var nonce = '<?php echo esc_js( wp_create_nonce( 'LoginSecurityNonce' ) ); ?>';
		var data = {
			'action': 'wpns_login_security',
			'wpns_loginsecurity_ajax': 'update_plan',
			'planname': planname,
			'planType': planType,
			'nonce'    :nonce
		}
		jQuery.post(ajaxurl, data, function(response) {});
	}

	function mo2f_register_and_upgradeform(planType, planname) {
		jQuery('#requestOrigin').val(planType);
		jQuery('input[name="requestOrigin"]').val(planType);
		jQuery('#mo2fa_register_to_upgrade_form').submit();
		var nonce = '<?php echo esc_js( wp_create_nonce( 'LoginSecurityNonce' ) ); ?>';
		var data = {
			'action': 'wpns_login_security',
			'wpns_loginsecurity_ajax': 'wpns_all_plans',
			'planname': planname,
			'planType': planType,
			'nonce'   :nonce
		}
		jQuery.post(ajaxurl, data, function(response) {});
	}


</script>
