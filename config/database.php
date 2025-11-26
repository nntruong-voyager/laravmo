<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Module-specific Database Connections
        |--------------------------------------------------------------------------
        |
        | Each module can have its own database connection. This allows for
        | database boundary separation, making it easier to extract modules
        | into microservices later. By default, modules use the main connection,
        | but can be configured to use separate databases.
        |
        */

        'users' => [
            'driver' => 'mysql',
            'url' => env('USERS_DB_URL'),
            'host' => env('USERS_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('USERS_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('USERS_DB_DATABASE', env('DB_DATABASE', 'laravmo_users')),
            'username' => env('USERS_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('USERS_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('USERS_DB_SOCKET', env('DB_SOCKET', '')),
            'charset' => env('USERS_DB_CHARSET', 'utf8mb4'),
            'collation' => env('USERS_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        'orders' => [
            'driver' => 'mysql',
            'url' => env('ORDERS_DB_URL'),
            'host' => env('ORDERS_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('ORDERS_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('ORDERS_DB_DATABASE', env('DB_DATABASE', 'laravmo_orders')),
            'username' => env('ORDERS_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('ORDERS_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('ORDERS_DB_SOCKET', env('DB_SOCKET', '')),
            'charset' => env('ORDERS_DB_CHARSET', 'utf8mb4'),
            'collation' => env('ORDERS_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        'payments' => [
            'driver' => 'mysql',
            'url' => env('PAYMENTS_DB_URL'),
            'host' => env('PAYMENTS_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('PAYMENTS_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('PAYMENTS_DB_DATABASE', env('DB_DATABASE', 'laravmo_payments')),
            'username' => env('PAYMENTS_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('PAYMENTS_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('PAYMENTS_DB_SOCKET', env('DB_SOCKET', '')),
            'charset' => env('PAYMENTS_DB_CHARSET', 'utf8mb4'),
            'collation' => env('PAYMENTS_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        'inventory' => [
            'driver' => 'mysql',
            'url' => env('INVENTORY_DB_URL'),
            'host' => env('INVENTORY_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('INVENTORY_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('INVENTORY_DB_DATABASE', env('DB_DATABASE', 'laravmo_inventory')),
            'username' => env('INVENTORY_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('INVENTORY_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('INVENTORY_DB_SOCKET', env('DB_SOCKET', '')),
            'charset' => env('INVENTORY_DB_CHARSET', 'utf8mb4'),
            'collation' => env('INVENTORY_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
