<?php
define('URL_PATH', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/');
define('CONNECT_REDIS', false); // coming soon
define('DEBUG_LOG', true);
define('LANGUAGE', 'vi');
define('TIMEZONE', 'Asia/Ho_Chi_Minh');

// connection db
define('DB_CONNECTION', 'mysql'); // chỉ hỗ trợ mysql, pgsql
define('DB_ENVIRONMENT', 'default'); // environment
define('REDIS_CONNECTION', 'redis'); // chỉ hỗ trợ mysql, pgsql
// end db