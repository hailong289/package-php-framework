<?php
define('URL_PATH', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/');

define('CONNECT_REDIS', false);

define('DEBUG_LOG', false);