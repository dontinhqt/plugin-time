<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Timer
{
    protected static $instance;
    protected function __construct() {}
    protected function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Timer();
        }
        return self::$instance;
    }

    public static function run() {
        $instance = self::getInstance();
        TimerDb::run();
        TimerSetting::run();

        return $instance;
    }

    public static function pluginActivation() {
        TimerDb::createTable(TIMER_TABLE_LOCK);
        if (!get_option('timer-setting-option')) {
            add_option('timer-setting-option', [
                'allowed-number-attempts' => 3,
                'time-remove-lock-ip' => 15, //mins
                'check-type-expire-password' => 'expired_date'
            ]);
        }
    }

    public static function pluginDeactivation() {
        TimerDb::dropTable(TIMER_TABLE_LOCK);
        delete_option('timer-setting-option');
    }
}