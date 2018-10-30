<?php
define("RUN_ENV","CLI");

define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) );
define('APP_NAME', 'sanguo');

include BASE_DIR.'/z.class.php';

Z::init();
Z::runConsoleApp();
