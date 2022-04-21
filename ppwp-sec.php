<?php
/*
Plugin Name: ppwp-sec
Plugin URI: http://wordpress-dev.me
Description: ppwp sec
Version: 1.0
Author: tinhnd
Author URI: http://wordpress-dev.me
*/

if (!defined('WPINC')) {
    die;
}

define('PPWP_SEC_VERSION', '1.0' );
define('PPWP_SEC_PLUGIN_NAME', 'PPWP Password Sec' );
define('PPWP_SEC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PPWP_SEC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PPWP_SEC_TABLE_LOCK', 'lock');

define('PPWP_SEC_ALLOWED_NUMBER_OF_ATTEMPTS', 3);
define('PPWP_SEC_TIME_REMOVE_LOCK_IP', 15); // mins
define('PPWP_SEC_CHECK_TYPE_EXPIRE_PASSWORD', 'expired_date');
define('PPWP_SEC_EXPIRE_PASSWORD_BY_DATE', 'expired_date');
define('PPWP_SEC_EXPIRE_PASSWORD_BY_COOKIE', 'cookie');
if (defined( 'PPW_PRO_VERSION' )) {
    define('PPWP_SEC_WPP_PASSWORD_COOKIE_EXPIRED', (!empty($ppwp = json_decode(get_option(PPW_Constants::GENERAL_OPTIONS))) && !empty($ppwp->wpp_password_cookie_expired)) ? $ppwp->wpp_password_cookie_expired : null);
}

require_once(PPWP_SEC_PLUGIN_DIR . 'includes/helper.php');
require_once(PPWP_SEC_PLUGIN_DIR . 'includes/class-ppwp-sec-db.php');
require_once(PPWP_SEC_PLUGIN_DIR . 'includes/class-ppwp-sec-lock.php');
require_once(PPWP_SEC_PLUGIN_DIR . 'includes/class-ppwp-sec-setting.php');
require_once(PPWP_SEC_PLUGIN_DIR . 'includes/class-ppwp-sec.php');

function activate_ppwp_sec() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-ppwp-sec-activator.php';
    PPWP_SEC_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_ppwp_sec');
register_deactivation_hook(__FILE__, ['PPWP_SEC', 'pluginDeactivation']);

PPWP_SEC::run();

function load_script_user() {
    $ppwp_sec_setting = get_option('ppwp-sec-setting');
    wp_enqueue_script('jquery');
    wp_register_script('ppwp_sec_js',  PPWP_SEC_PLUGIN_URL . 'scripts/function.js', );
    wp_localize_script('ppwp_sec_js', 'jsTimer', [
        'cookieName' => PPW_Pro_Constants::GOLD_PASS_COOKIE . get_the_ID() . COOKIEHASH,
        'checkTypeExpirePassword' => $ppwp_sec_setting['check-type-expire-password'],
        'wppPasswordCookieExpired' => PPWP_SEC_WPP_PASSWORD_COOKIE_EXPIRED,
    ]);
    wp_enqueue_script( 'ppwp_sec_js', PPWP_SEC_PLUGIN_URL . 'scripts/function.js');

    wp_register_style('ppwp_sec_css',  PPWP_SEC_PLUGIN_URL . 'scripts/style.css');
    wp_enqueue_style('ppwp_sec_css');
}
add_action('wp_enqueue_scripts', 'load_script_user');

add_action('ppw_check_password_is_valid',  ['PPWP_SEC_LOCK', 'handleWhenUserEnterPass'], 500, 3);
add_filter('the_content', ['PPWP_SEC_LOCK', 'handleWhenShowPage']);

//add_filter('ppwp_pro_check_valid_password', 'check_lock', 500, 2);
