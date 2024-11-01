<?php
namespace WpGmap\install;

use WpGmap\core\Settings;

class Installer
{
    private $wpdb;

    public function __construct()
    {
        $this->wpdb = Settings::db();
    }

    public function getTables()
    {
        return Settings::getTableNames();
    }

    public function install()
    {
        $tables = $this->getTables();

        if($this->wpdb->get_var("show tables like '".$tables->settings."'") != $tables->settings) {
            $sql = $this->getSqlQueries($tables);

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    private function getSqlQueries($tables)
    {
        $sql = [];

        $sql[] = "CREATE TABLE `".$tables->data."` (
            `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `data` longtext NOT NULL,
            `rawdata` longtext NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $sql[] = "CREATE TABLE `".$tables->settings."` (
            `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `data` longtext NOT NULL,
            `is_default` tinyint(1) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        return $sql;
    }
}