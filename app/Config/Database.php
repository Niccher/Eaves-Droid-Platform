<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations
     * and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to
     * use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => 'db_eaves_droid',
        'password'     => 'R6rQhag3qcWQ2Yi',
        'database'     => 'db_eaves_droid',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8',
        'DBCollat'     => 'utf8_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
    ];

    /**
     * This database connection is used when
     * running PHPUnit database tests.
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => 'utf8_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        // 1. Check CodeIgniter env() helper overrides
        $this->default['hostname'] = env('database.default.hostname', $this->default['hostname'] ?? 'localhost');
        $this->default['username'] = env('database.default.username', $this->default['username'] ?? '');
        $this->default['password'] = env('database.default.password', $this->default['password'] ?? '');
        $this->default['database'] = env('database.default.database', $this->default['database'] ?? '');
        $this->default['DBDriver'] = env('database.default.DBDriver', $this->default['DBDriver'] ?? 'MySQLi');

        // 2. Direct Railway / Cloud environment variable overrides
        if ($host = getenv('MYSQLHOST') ?: getenv('DB_HOST')) {
            $this->default['hostname'] = $host;
        }
        if ($user = getenv('MYSQLUSER') ?: getenv('DB_USER')) {
            $this->default['username'] = $user;
        }
        if ($pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS')) {
            $this->default['password'] = $pass;
        }
        if ($db = getenv('MYSQLDATABASE') ?: getenv('DB_NAME')) {
            $this->default['database'] = $db;
        }
        if ($port = getenv('MYSQLPORT') ?: getenv('DB_PORT')) {
            $this->default['port'] = (int) $port;
        }

        // Ensure that we always set the database group to 'tests' if testing
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
