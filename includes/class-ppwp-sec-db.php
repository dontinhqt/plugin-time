<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

class PPWP_SEC_DB
{
    protected static $instance;
    protected static $wpdb;
    protected static $option;
    protected static $table;

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PPWP_SEC_DB();
        }
        return self::$instance;
    }

    public static function run()
    {
        $instance = self::getInstance();
        global $wpdb;
        self::$wpdb = $wpdb;
        self::$table = self::$wpdb->prefix . 'ppwp_sec_';

        return $instance;
    }

    public static function createTable($tableName)
    {
        $table = self::$table . $tableName;
        $charset_collate = '';
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        if ($tableName === PPWP_SEC_TABLE_LOCK) {
            $sql = "CREATE TABLE IF NOT EXISTS $table (
                id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ip varchar(50) NOT NULL,
                pw_code text NOT NULL,
                page_id int(10),
                attempt int(1),
                blocked tinyint(1) DEFAULT 0,
                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL
		    ) $charset_collate;";
            dbDelta($sql);
        }
    }

    public static function dropTable($tableName)
    {
        $table = self::$table . $tableName;
        self::$wpdb->query("DROP TABLE IF EXISTS $table");
    }

    public static function insert($tableName, $data)
    {
        return self::$wpdb->insert(self::$table . $tableName, $data);
    }

    public static function get($tableName, $where, $dataType = 'OBJECT')
    {
        $table = self::$table . $tableName;
        $sql = "SELECT * FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        return self::$wpdb->get_row($sql, $dataType);
    }

    public static function update($tableName, $data, $where)
    {
        $table = self::$table . $tableName;
        return self::$wpdb->update($table, $data, $where);
    }

    public static function delete($tableName, $data)
    {
        $table = self::$table . $tableName;
        return self::$wpdb->delete($table, $data);
    }

    public static function getDataPagination($tableName, $postPerPage = 10, $page = 1, $output = "ARRAY_A")
    {
        $table = self::$table . $tableName;
        $offset = ($page * $postPerPage) - $postPerPage;
        return [
            'total' => self::$wpdb->get_var("SELECT COUNT(id) FROM (SELECT (id) FROM $table) AS a"),
            'results' => self::$wpdb->get_results("SELECT * FROM $table LIMIT $postPerPage OFFSET $offset", $output)
        ];
    }
}