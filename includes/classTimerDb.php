<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

class TimerDb
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
            self::$instance = new TimerDb();
        }
        return self::$instance;
    }

    public static function run()
    {
        $instance = self::getInstance();
        global $wpdb;
        self::$wpdb = $wpdb;
        self::$table = self::$wpdb->prefix . 'timer_';

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

        $sql = "CREATE TABLE IF NOT EXISTS $table (
			id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			ip varchar(50) NOT NULL,
			pw_code text NOT NULL,
            page_id int(10),
            attempt int(1),
            lock tinyint(1),
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL
		) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function updateTable()
    {
        $table = self::$table;
        $row = self::$wpdb->get_row("SELECT * FROM $table");
        if (!isset($row->message2)) {
            $sql = "ALTER TABLE $table ADD message2 text NOT NULL";
            self::$wpdb->query($sql);
        }

    }

    public static function checkUpdate()
    {
        $version = get_option('shoutboxVersion');
        if ($version && $version != SHOUTBOX_VERSION) {
            QHShoutboxMessage::updateTable();
            add_option('shoutboxVersion', SHOUTBOX_VERSION);
        }
    }

    public static function dropTable()
    {
        $table = self::$table;
        $sql = "DROP TABLE IF EXISTS $table";
        self::$wpdb->query($sql);
    }

    public static function save($message, $user_login = 'Guest')
    {
        self::$wpdb->insert(self::$table, array(
            'user_login' => $user_login,
            'message' => $message,
            'time' => current_time('mysql')
        ));
        return true;
    }

    public static function get($dataType = 'OBJECT')
    {
        $table = self::$table;
        $message = self::$wpdb->get_results("
			SELECT * FROM $table ORDER BY id ASC
		", $dataType);
        return $message;
    }
}