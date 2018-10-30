<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) );
define('APP_NAME', 'sanguoadmin');

define('DEF_DB_CONN', 'local_sanguo');

define('DOMAIN', 'local.sanguo.admin.com');

define('DOMAIN_URL', "http://" .DOMAIN);

define("RUN_ENV","WEB");

include BASE_DIR.'/z.class.php';


z::init();
z::runWebApp();

