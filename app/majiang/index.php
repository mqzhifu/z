<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) );
define('APP_NAME', 'majiang');

define('DEF_DB_CONN', 'majiang');
define('DOMAIN', 'majiang.com');

define('DOMAIN_URL', "http://" .DOMAIN. "/".APP_NAME);

include BASE_DIR.'/z.class.php';

z::init();
z::runWebApp();

