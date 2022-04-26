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
        register_rest_route('ppwp-sec', '/list-block', [
            'methods' => 'GET',
            'callback' => [self::$instance, 'list_block'],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ]);

        register_rest_route( 'ppwp-sec', '/get-by-id/(?P<id>\d+)', [
            'methods'  => 'GET',
            'callback' => [self::$instance, 'get_by_id'],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ]);

        register_rest_route( 'ppwp-sec', '/update-block', [
            'methods'  => 'POST',
            'callback' => [self::$instance, 'update_block'],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ]);

        register_rest_route( 'ppwp-sec', '/delete-block', [
            'methods'  => 'POST',
            'callback' => [self::$instance, 'delete_block'],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ]);
    }

    public function list_block($data) {
        return PPWP_SEC_DB::getDataPagination(PPWP_SEC_TABLE_LOCK, empty($data['post_per_page']) ? 10 : $data['post_per_page'], empty($data['cpage']) ? 1 : $data['cpage']);
    }

    public function get_by_id($data) {
        return PPWP_SEC_DB::get(PPWP_SEC_TABLE_LOCK, "id = " .$data['id'], 'ARRAY_A');
    }

    public function update_block($data) {
        PPWP_SEC_DB::update(
            PPWP_SEC_TABLE_LOCK,
            [
                'attempt' => $data['attempt'],
                'blocked' => !empty($data['blocked']) ? 1 : 0
            ],
            ['id' => $data['id']]
        );
        return true;
    }

    public function delete_block($data) {
        if (!empty($data['delete_all'])) {
            return PPWP_SEC_DB::truncate(PPWP_SEC_TABLE_LOCK);
        }
        return PPWP_SEC_DB::delete(PPWP_SEC_TABLE_LOCK, ["id" => $data['id']]);

    }
}