<?php

namespace Util;

use DB\SQL;

class MySql
{
    /**
     * @var MySql
     */
    private static $instance;

    /**
     * @var SQL
     */
    private $db;

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function query($query, $params) {
        $this->isConnected();

        return $this->db->exec(
            $query,
            $params
        );
    }

    public function isConnected()
    {
        if (!$this->db) {
            $f3 = \Base::instance();

            $host = $f3->get('mysql.host');
            $port = $f3->get('mysql.port');
            $dbName = $f3->get('mysql.database');
            $username = $f3->get('mysql.user');
            $password = $f3->get('mysql.password');

            $this->db = new \DB\SQL(
                "mysql:host={$host};port={$port};dbname={$dbName}",
                $username,
                $password
            );
        }

        return $this->db;
    }
}