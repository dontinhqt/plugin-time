<?php
/*
Plugin Name: Timer
Plugin URI: http://wordpress-dev.me
Description: Hmmm
Version: 1.0
Author: tinhnd
Author URI: http://wordpress-dev.me
*/

define('TIMER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TIMER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TIMER_TABLE_LOCK', 'lock');

$timerOption = get_option('timer-setting-option');
define('TIMER_ALLOWED_NUMBER_OF_ATTEMPTS', $timerOption['allowed-number-attempts'] ?? 3);
define('TIMER_TIME_ROMOVE_LOCK_IP', $timerOption['time-remove-lock-ip'] ?? 15); // mins

require_once(TIMER_PLUGIN_DIR . 'includes/helper.php');
require_once(TIMER_PLUGIN_DIR . 'includes/classTimerDb.php');
require_once(TIMER_PLUGIN_DIR . 'includes/classTimerLock.php');
require_once(TIMER_PLUGIN_DIR . 'includes/classTimerSetting.php');

require_once(TIMER_PLUGIN_DIR . 'includes/classTimer.php');


register_activation_hook(__FILE__, ['Timer', 'pluginActivation']);
register_deactivation_hook(__FILE__, ['Timer', 'pluginDeactivation']);

// ----------
Timer::run();
// ----------


function load_script_admin() {
    wp_register_style('bootstrap-css','https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css');
    wp_enqueue_style( 'bootstrap-css' );
}
add_action('admin_enqueue_scripts', 'load_script_admin');


function load_script_user() {
    wp_enqueue_script('jquery');
    wp_register_script('timer_js',  TIMER_PLUGIN_URL . 'scripts/function.js', );
    wp_localize_script('timer_js', 'jsTimer', array(
        'cookieName' => PPW_Pro_Constants::GOLD_PASS_COOKIE . get_the_ID() . COOKIEHASH
    ));
    wp_enqueue_script( 'timer_js', TIMER_PLUGIN_URL . 'scripts/function.js');

    wp_register_style('timer_css',  TIMER_PLUGIN_URL . 'scripts/style.css');
    wp_enqueue_style('timer_css');
}
add_action('wp_enqueue_scripts', 'load_script_user');


add_action( 'ppw_check_password_is_valid',  ['TimerLock', 'handleLockUserWhenEnterPass'], 500, 3);

add_filter( 'the_content', ['TimerLock', 'handleUnLockUserWhenShowPage']);

//add_filter('ppwp_pro_check_valid_password', 'check_lock', 500, 2);
//
//function check_lock($checkPassword, $passwordInput){
////    dd($checkPassword);
//    return $checkPassword;
//}