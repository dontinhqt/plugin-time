<?php
/*
Plugin Name: Timer
Plugin URI: http://wordpress-dev.me
Description: Hmmm
Version: 1.0
Author: tinhnd
Author URI: http://wordpress-dev.me
*/

$timerOption = get_option('timer-setting-option');
define('TIMER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TIMER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TIMER_TABLE_LOCK', 'lock');

define('TIMER_ALLOWED_NUMBER_OF_ATTEMPTS', $timerOption['allowed-number-attempts'] ?? 3);
define('TIMER_TIME_ROMOVE_LOCK_IP', $timerOption['time-remove-lock-ip'] ?? 15); // mins
define('TIMER_EXPIRE_PASSWORD_BY_DATE', 'expired_date');
define('TIMER_EXPIRE_PASSWORD_BY_COOKIE', 'cookie');
define('TIMER_CHECK_TYPE_EXPIRE_PASSWORD', $timerOption['check-type-expire-password'] ?? 'expired_date'); // mins
define('TIMER_WPP_PASSWORD_COOKIE_EXPIRED', (!empty($ppwp = json_decode(get_option(PPW_Constants::GENERAL_OPTIONS))) && !empty($ppwp->wpp_password_cookie_expired)) ? $ppwp->wpp_password_cookie_expired : null);

require_once(TIMER_PLUGIN_DIR . 'includes/helper.php');
require_once(TIMER_PLUGIN_DIR . 'includes/classTimerDb.php');
require_once(TIMER_PLUGIN_DIR . 'includes/classTimerLock.php');
require_once(TIMER_PLUGIN_DIR . 'includes/classTimerSetting.php');
require_once(TIMER_PLUGIN_DIR . 'includes/classTimer.php');

register_activation_hook(__FILE__, ['Timer', 'pluginActivation']);
register_deactivation_hook(__FILE__, ['Timer', 'pluginDeactivation']);

Timer::run();


function load_script_user() {
    wp_enqueue_script('jquery');
    wp_register_script('timer_js',  TIMER_PLUGIN_URL . 'scripts/function.js', );
    wp_localize_script('timer_js', 'jsTimer', [
        'cookieName' => PPW_Pro_Constants::GOLD_PASS_COOKIE . get_the_ID() . COOKIEHASH,
        'checkTypeExpirePassword' => TIMER_CHECK_TYPE_EXPIRE_PASSWORD,
        'wppPasswordCookieExpired' => TIMER_WPP_PASSWORD_COOKIE_EXPIRED,
    ]);
    wp_enqueue_script( 'timer_js', TIMER_PLUGIN_URL . 'scripts/function.js');

    wp_register_style('timer_css',  TIMER_PLUGIN_URL . 'scripts/style.css');
    wp_enqueue_style('timer_css');
}
add_action('wp_enqueue_scripts', 'load_script_user');

add_action('ppw_check_password_is_valid',  ['TimerLock', 'handleWhenUserEnterPass'], 500, 3);
add_filter('the_content', ['TimerLock', 'handleWhenShowPage']);

//add_filter('ppwp_pro_check_valid_password', 'check_lock', 500, 2);
