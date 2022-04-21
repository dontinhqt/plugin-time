<?php

class PPWP_SEC_SETTING {
    protected static $instance;
    protected static $option;

    protected function __construct() {}

    protected function __clone() {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PPWP_SEC_SETTING();
        }
        return self::$instance;
    }

    public static function run()
    {
        $instance = self::getInstance();
        self::$option = get_option('ppwp-sec-setting');

        add_action('admin_enqueue_scripts', function($hook) {
            if (!strpos($hook, 'timer-setting')) {
                return;
            }
            wp_register_style('bootstrap-css','https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css');
            wp_enqueue_style( 'bootstrap-css' );
        });

        add_action('admin_menu', function () use ($instance) {
            add_menu_page('Timer Setting', 'Timer Config', 'manage_options', 'timer-setting-url', [$instance, 'timerCreateSettingPage']);

            add_submenu_page( 'timer-setting-url', 'IP Block', 'IP Block', 'manage_options', 'timer-setting-block', [$instance, 'timerListLockIpPage']);
        });

        add_action('admin_init', [$instance, 'timerSetupSetting']);


        return $instance;
    }

    public static function timerCreateSettingPage()
    {
        require(PPWP_SEC_PLUGIN_DIR . 'views/setting.php');
    }
    public static function timerListLockIpPage()
    {
        require(PPWP_SEC_PLUGIN_DIR . 'views/listlock.php');
    }

    public static function timerSetupSetting()
    {
        register_setting('timerSettingGroup', 'ppwp-sec-setting', [self::$instance, 'timerSaveDataSetting']);

        add_settings_section('timerSettingLock', '', function () {
            echo '<h1>Setting block</h1>';
        }, 'timer-setting-url');

        add_settings_field('timerAllowedNumberAttempts', 'Allowed number attempts', function () {
            printf('<input name="ppwp-sec-setting[allowed-number-attempts]" type="number" step="1" min="0" id="allowed-number-attempts" value="%d" class="small-text">', isset(self::$option['allowed-number-attempts']) ? esc_attr(self::$option['allowed-number-attempts']) : 3);
        }, 'timer-setting-url', 'timerSettingLock');

        add_settings_field('timerRemoveLockIp', 'Time remove lock IP', function () {
            printf('<input name="ppwp-sec-setting[time-remove-lock-ip]" type="number" step="1" min="1" id="time-remove-lock-ip" value="%d" class="small-text">', isset(self::$option['time-remove-lock-ip']) ? esc_attr(self::$option['time-remove-lock-ip']) : 15);
        }, 'timer-setting-url', 'timerSettingLock');

        add_settings_section('timerSettingExpiredPassword', '', function () {
            echo '<h1>Setting expired date</h1>';
        }, 'timer-setting-url');

        add_settings_field('timerCheckExpirePassword', 'Check expire password', function () {
            $selected = isset(self::$option['check-type-expire-password']) ? esc_attr(self::$option['check-type-expire-password']) : 'expired_date';
            echo '<select name="ppwp-sec-setting[check-type-expire-password]" id="check-type-expire-password">
                <option value="cookie" ' . ($selected == "cookie" ? "selected" : "") . '>Cookie Expiration Time Setting</option>
                <option value="expired_date" ' . ($selected == "expired_date" ? "selected" : "") . '>Expired date</option>
            </select>';
        }, 'timer-setting-url', 'timerSettingExpiredPassword');
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
        $newInput['check-type-expire-password'] = $input['check-type-expire-password'];
        return $newInput;
    }
}