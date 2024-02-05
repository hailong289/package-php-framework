<?php
define('URL_PATH', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/');
define('CONNECT_REDIS', false); // coming soon
define('DEBUG_LOG', true);
define('LANGUAGE', 'vi');
define('TIMEZONE', 'Asia/Ho_Chi_Minh');

// connection db
define('DB_ENVIRONMENT', 'default'); // environment
define('DB_CONNECTION', 'mysql'); // chỉ hỗ trợ mysql, pgsql
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'blog');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('REDIS_CONNECTION', 'redis');
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', '6379');
define('REDIS_PASSWORD', null);
// end db