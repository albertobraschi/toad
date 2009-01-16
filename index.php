<?php

define('FROG_ROOT', dirname(__FILE__));
define('CORE_ROOT', FROG_ROOT . '/frog');
define('APP_PATH',  CORE_ROOT . '/app');

require FROG_ROOT . '/config.php';

define('BASE_URL',  URL_PUBLIC . (USE_MOD_REWRITE ? '': '?'));

require CORE_ROOT . '/Framework.php';

/* If you have installed frog and see this line, you can comment it or delete it. */
if (!defined('DEBUG')) { 
    header('Location: ' . URL_PUBLIC . 'install/'); 
    exit(); 
}

/* TODO: Get rid off DoLite. No need to support hosts without PDO. */
if (USE_PDO) {
    $__FROG_CONN__ = new PDO(DB_DSN, DB_USER, DB_PASS);
    if ($__FROG_CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') {
        $__FROG_CONN__->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);        
    }
} else {
    require_once CORE_ROOT . '/libraries/DoLite.php';
    $__FROG_CONN__ = new DoLite(DB_DSN, DB_USER, DB_PASS);
}

$__FROG_CONN__->exec("set names 'utf8'");


Record::connection($__FROG_CONN__);

/* Run everything. */
require APP_PATH . '/main.php';
