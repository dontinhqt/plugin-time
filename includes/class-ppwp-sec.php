<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

class PPWP_SEC
{
    protected static $instance;
    protected function __construct() {}
    protected function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new PPWP_SEC();
        }
        return self::$instance;
    }

    public static function run() {
        $instance = self::getInstance();
        PPWP_SEC_DB::run();
        PPWP_SEC_SETTING::run();

        PPWP_SEC_API::run();

        return $instance;
    }


    public static function pluginDeactivation() {
        PPWP_SEC_DB::dropTable(PPWP_SEC_TABLE_LOCK);
        delete_option('ppwp-sec-setting');
    }
}