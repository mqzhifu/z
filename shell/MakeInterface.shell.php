<?php
class MakeInterface{
	function __construct($c){
		$this->commands = $c;
	}
	
	public function run($attr){
		if(!isset($attr['db_key']))
			exit('db_key=xxx');
		
		if(!isset($GLOBALS['db_config']))
			exit('db_config is null');
		
		if(!isset($GLOBALS['db_config'][$attr['db_key']]))
			exit('db_key not in db_config');
		
		if(!isset($attr['file']))
			exit('file=xxx');
		
		if(!file_exists($attr['file']))
			exit('file not exist');
			
		$table = 'app_ctrl';
		$db = getDb($attr['db_key']);
		$sql = "truncate table " . $table;
		$db->querySql($sql);
		
		$data = array(
			'app_id'=>1,
			'ac'=>'',
			'ctrl'=>'',
			'cn_title'=>'',
			'access_type'=>'USER',
			'status'=>1,
			'mid'=>0,
			'add_time'=>date("Y-m-d H:i:s"),
		);
		
		$file = file($attr['file']);
		if($file){
			$ctrl = "";
			foreach($file as $k=>$v){
				$c = strpos($v, 'interface');
				if( $c !== false){
					$l = strpos($v, '{');
					$s = $l -  9;
					$ctrl = trim(substr($v, 9,$s));
				}else{
					
					$a = strpos($v, 'function');
					if($a !== false){
						$l = strpos($v, '(');
						$s = $l -  10;
						$ac = trim(substr($v, 10,$s));
						echo "ctrl:".$ctrl ." ac:". $ac ."\n";
						$data['ctrl'] = $ctrl;
						$data['ac'] = $ac;
						$db->add($data,$table);
					}
				}
			}
		}
	}
}
