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
define('UC_API', 'http://bbslc.huaphp.com/uc_server');
define('UC_CHARSET', 'utf-8');
define('UC_IP', '');
define('UC_APPID', '2');
define('UC_PPP', '20');

include './api/uc_client/client.php';
function uc_login($uname,$ps){
	$uinfo = uc_user_login($uname, $ps);
	$rs = uc_user_synlogin($uinfo[0]);
// 	var_dump($uinfo);exit;
// 	$s = array();
// 	$ge = preg_match_all("/src=\"(\S)*\"/",$rs,$s);
// 	$js1 = substr($s[0][0],5);
// 	$js1 = substr($js1,0,strlen($js1)-1);
	
// 	$js2 = substr($s[0][1],5);
// 	$js2 = substr($js1,0,strlen($js1)-1);
// 	echo $js1 ."<br/>";echo $js2;
// 	$rs = client_get($js1,"http://lechang.huaphp.com");
// 	var_dump($rs);exit;
// 	$rs = client_get($js2);
// 	exit;
	$uinfo['js'] = $rs;
	return $uinfo;
}

function uc_logout(){
	return uc_user_synlogout();
}


function client_get($url, $referer = '')
{
	$info = null;
	$ch = curl_init($url);
	$options = array(
			CURLOPT_RETURNTRANSFER => true,         // return web page
			CURLOPT_HEADER         => false,        // don't return headers
			CURLOPT_FOLLOWLOCATION => true,         // follow redirects
			CURLOPT_ENCODING       => "",           // handle all encodings
			CURLOPT_USERAGENT      => "",     // who am i
			CURLOPT_AUTOREFERER    => true,         // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
			CURLOPT_TIMEOUT        => 120,          // timeout on response
			CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
			CURLOPT_REFERER        =>$referer,
	);
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}