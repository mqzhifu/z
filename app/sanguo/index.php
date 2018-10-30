<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) );
define('APP_NAME', 'sanguo');
//运行方式：WEB CLI
define("RUN_ENV","WEB");

//当前环境 本地 开发 线上
define("ENV","local");

//默认连接数据库
define('DEF_DB_CONN', 'sanguo');
//默认redis连接 库
define('DEF_REDIS_CONN', 'sanguo');

include BASE_DIR.'/z.class.php';

z::init();
z::runWebApp();

