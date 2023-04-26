<?php
/**
 * This file is controller for views/twofa/two-fa-shortcode.php.
 *
 * @package miniorange-2-factor-authentication/controllers/twofa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Including the file for frontend.
 */

global $mo_wpns_utility, $mo2f_dir_name, $current_user;
require $mo2f_dir_name . 'views' . DIRECTORY_SEPARATOR . 'twofa' . DIRECTORY_SEPARATOR . 'two-fa-unlimitted-user.php';
