<?php

class PPWP_SEC_Activator {

    public static function activate() {
        PPWP_SEC_DB::createTable(PPWP_SEC_TABLE_LOCK);
        if (!get_option('ppwp-sec-setting')) {
            add_option('ppwp-sec-setting', [
                'allowed-number-attempts' => 3,
                'time-remove-lock-ip' => 15, //mins
                'check-type-expire-password' => 'expired_date'
            ]);
        }
        self::check_before_activate_plugin();
    }

    private static function check_before_activate_plugin() {
        require_once PPWP_SEC_PLUGIN_DIR . 'services/class-service-plugin-compability.php';
        $message = PPWP_SEC_Service_Plugin_Compatibility::get_message_before_activate();
        if ( $message ) {
            wp_die( $message );
        }
    }
}
