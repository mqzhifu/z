<?php
$config=file('/tools/mysql.ini');
if(!$config)
	exit('/tools/mysq.ini is null');

if( $argc <= 1)	
	exit("argc at least 1");

$type = array(
			'web'=>array('ip_index'=>101,'db'=>'rctailor'),
			'dev'=>array('ip_index'=>102,'db'=>'rctailor_dev'),
);
	
if(!in_array($argv[1],array( 'web' , 'dev') ) )
	exit("pleace input <web> or <dev>");


$config_info = array();
foreach($config as $k=>$v){
	$info = explode(" ",$v);
	if($info[0] == $type[$argv[1]]['ip_index']){
		$config_info=$info;
		break;
	}

}
if(!$config_info)
	exit("config_info is null...");


$host = $config_info[1];
$user = $config_info[3];
$port = $config_info[2];
$db = $type[$argv[1]]['db'];
$ps = str_replace("\n","",$config_info[4]);
var_dump($ps);
$file = "/tools/publish_mysql.sql";
if(!file_exists($file))
	exit('publish_mysql.sql not exists');

$query_sql = file($file);
if(!$query_sql)
	exit("no sql query...");

$conn = mysql_connect($host . ":" . $port ,$user,$ps);
mysql_select_db($db,$conn);
if(!$conn)
	exit(mysql_error());
mysql_select_db($db,$conn);


foreach($query_sql as $k=>$v){
	echo $v."\n";
	mysql_query($v);
}

echo "clear sql.txt\n";
fopen("/tools/publish_mysql.sql","w");
echo "end";
