<?php

class TimerSetting {
    protected static $instance;
    protected static $option;

    protected function __construct() {}

    protected function __clone() {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new TimerSetting();
        }
        return self::$instance;
    }

    public static function run()
    {
        $instance = self::getInstance();
        self::$option = get_option('timer-setting-option');
        add_action('admin_menu', function () use ($instance) {
            add_menu_page('Timer Setting', 'Timer Config', 'manage_options', 'timer-setting-url', [$instance, 'timerCreateSettingPage']);

            add_submenu_page( 'timer-setting-url', 'IP Block', 'IP Block', 'manage_options', 'timer-setting-block', [$instance, 'timerListLockIpPage']);
        });

        add_action('admin_init', [$instance, 'timerSetupSetting']);

        return $instance;
    }

    public static function timerCreateSettingPage()
    {
        require(TIMER_PLUGIN_DIR . 'views/setting.php');
    }
    public static function timerListLockIpPage()
    {
        require(TIMER_PLUGIN_DIR . 'views/listlock.php');
    }

    public static function timerSetupSetting()
    {
        register_setting('timerSettingGroup', 'timer-setting-option', [self::$instance, 'timerSaveDataSetting']);

        add_settings_section('timerSettingLock', 'Tổng quan', function () {
            echo '<h1>Setting block</h1>';
        }, 'timer-setting-url');

        add_settings_field('timerAllowedNumberAttempts', 'Allowed number attempts', function () {
            printf('<input name="timer-setting-option[allowed-number-attempts]" type="number" step="1" min="1" id="allowed-number-attempts" value="%d" class="small-text">', isset(self::$option['allowed-number-attempts']) ? esc_attr(self::$option['allowed-number-attempts']) : 3);
        }, 'timer-setting-url', 'timerSettingLock');

        add_settings_field('timerRemoveLockIp', 'Time remove lock IP', function () {
            printf('<input name="timer-setting-option[time-remove-lock-ip]" type="number" step="1" min="1" id="time-remove-lock-ip" value="%d" class="small-text">', isset(self::$option['time-remove-lock-ip']) ? esc_attr(self::$option['time-remove-lock-ip']) : 15);
        }, 'timer-setting-url', 'timerSettingLock');
    }

    public static function timerSaveDataSetting($input)
    {
        $newInput = [];
        if (isset($input['allowed-number-attempts'])) {
            $newInput['allowed-number-attempts'] = absint($input['allowed-number-attempts']);
        }

        if (isset($input['time-remove-lock-ip'])) {
            $newInput['time-remove-lock-ip'] = absint($input['time-remove-lock-ip']);
        }
        return $newInput;
    }
}