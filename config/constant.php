<?php
define('URL_PATH', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/');
define('CONNECT_REDIS', false); // coming soon
define('DEBUG_LOG', true);
define('LANGUAGE', 'vi');