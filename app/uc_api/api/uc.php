<?php
define('UC_CONNECT', 'mysql');
define('UC_DBHOST', '127.0.0.1');
define('UC_DBUSER', 'root');
define('UC_DBPW', 'root');
define('UC_DBNAME', 'dz');
define('UC_DBCHARSET', 'utf8');
define('UC_DBTABLEPRE', '`dz`.ucenter_');
define('UC_DBCONNECT', '0');
define('UC_KEY', 'lechang');
define('UC_API', 'http://bbs.flyhigh.com.cn/uc_server');
define('UC_CHARSET', 'utf-8');
define('UC_IP', '');
define('UC_APPID', '2');
define('UC_PPP', '20');

// $fd = fopen("/tmp/bbs.txt", "w+");

include './uc_client/client.php';
ini_set("display_errors", 1);
$code = @$_GET['code'];
parse_str(uc_authcode($code, 'DECODE', UC_KEY), $get);
// $str = "";
// foreach($_REQUEST as $k=>$v){
// 	$str .= $k."=".$v."\n";
// }
// foreach($get as $k=>$v){
// 	$str .= $k."=".$v."\n";
// }
// fwrite($fd, $str);

// fwrite($fd, $str);

mysql_connect(UC_DBHOST,UC_DBUSER,UC_DBPW);


if('synlogout' == $get['action']){
	$sql = "delete from `lechang`.`session` where uid  = ".$_REQUEST['uid'];
// 	fwrite($fd, $sql);
	mysql_query($sql);
// 	$rs = mysql_error();
// 	fwrite($fd, '222');
}elseif('synlogin' == $get['action']){
	$sql = "delete from `lechang`.`session` where uid  = ".$_REQUEST['uid'];
	mysql_query($sql);
	
// 	include 'lib/session.lib.php';
// 	include 'lib/sessionDb.lib.php';
	session_start();
	$userInfo = array('uname'=>$get['username'],'uid'=>$get['uid']);
	$t = time() + 60 * 60 * 3;
// 	$sess = new SessionLib();
// 	$sess->setSession($userInfo);
	$data = "USER|".serialize($userInfo);
	$sql = "INSERT INTO `lechang`.`session` (`session_id`, `uid`, `uname`, `add_time`, `expire`, `data`, `user_type`) 
	VALUES ('".session_id(). "', ".$get['uid']." ,'".$get['username']."', ".time().",  ".$t."  , '".$data."', 'USER')";
	mysql_query($sql);
	
}

// fwrite($fd, $str);


echo 1;
exit;
