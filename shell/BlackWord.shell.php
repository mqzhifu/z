<?php
//生成数据结构字典-WORD 格式
class BlackWord {
	function __construct($c){
		$this->commands = $c;
	}

	public function run($attr){
		set_time_limit(0);

		
//		require_once PLUGIN.'PHPWord.php';
//		$PHPWord = new PHPWord();
//		echo $PHPWord->readDocument('./black_word.doc');

		$data = file("black.txt");
		if(!$data)
			exit('err no data');


		$db = getDb('local_test');


		$type = 11;
		$sub_type = 1;


		$i = 1;
		$j = 1;
		foreach($data as $k=>$v){
			echo "row:".$i."\n";
		    if(!$v)
				continue;

//			$d = iconv();
//			$row = explode("、",$v);
			$row = explode(",",$v);

			if(!$row)
				exit(" err explode fialed");

			foreach($row as $k2=>$v2){
				echo "cnt:".$j;

				$v2 = str_replace(array("\r\n", "\r", "\n","\t"," "), "", $v2);
				if(!$v2){
					echo " null\n";
					continue;
				}
//				$sql = "INSERT INTO black_word (`name`,TYPE,sub_type)    VALUES('$v2',1,1)";
				$insert = array('name'=>$v2,'type'=>$type,'sub_type'=>$sub_type);
				$rs = $db->add($insert,'black_word');


				echo "ok.".$rs."\n";

				$j++;
			}
			$i++;
		}
	}
}







