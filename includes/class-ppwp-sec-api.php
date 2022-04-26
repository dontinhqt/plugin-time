<?php

class PPWP_SEC_API {

    protected static $instance;

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PPWP_SEC_API();
        }
        return self::$instance;
    }

    public static function run()
    {
        $instance = self::getInstance();
        add_action('rest_api_init', [$instance, 'register_rest_routes'], 10, 2 );
        return $instance;
    }

    public function register_rest_routes()
    {
        register_rest_route('ppwp-sec', '/panigate-list-block', [
            'methods' => 'GET',
            'callback' => [self::$instance, 'panigate_list_block'],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ]);
    }

    public function panigate_list_block($data) {
        return PPWP_SEC_DB::getDataPagination(PPWP_SEC_TABLE_LOCK, empty($data['post_per_page']) ? 10 : $data['post_per_page'], empty($data['cpage']) ? 1 : $data['cpage']);
    }
}