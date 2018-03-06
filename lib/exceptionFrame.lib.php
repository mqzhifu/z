<?php
//自己的异常处理器
class ExceptionFrameLib extends Exception {
	
	public function __construct($message,$code=0,$extra=false) {
		parent::__construct($message,$code);
	}
	
	static public function appError($errno, $errstr, $errfile, $errline) {
		//这里主要
		//:mysql_connect(): The mysql extension is deprecated and will be removed in the future: 
		//use mysqli or PDO instead
		if(8192 == $errno)
			return 0;
		if(z::$_ACCESS_TYPE == 'SHELL'){
			$s = "\n";
			$e = "error info:".$errstr .$s;
			$e .= "file:".$errfile .$s;
			$e .= "line:".$errline .$s;
		}else{
			$green = " class='Green'";
			$Blue = " class='Blue'";
			$Red = " class='Red'";
			
			
			$e = "<p $green>error no.:".$errno."</p>";
			$e .= "<p $green>info:".$errstr."</p>";
			$e .= "<p $Blue>file:".$errfile ."</p>";
			$e .= "<p $Red>line:".$errline ."</p>";
		}
		$trace = debug_backtrace();
		self::halt($e,$trace,'SYSTEM');
	}

	static public function errInfo($trace){
// 		var_dump($trace);exit;
// 		unset($trace[0]);//0元素跟踪的是：appError函数
		$traceInfo = '';
		if(z::$_ACCESS_TYPE == 'SHELL'){
			$s = "\n";
// 			if(isset($trace[0]['file']))
// 				$traceInfo .= "file:".$trace[0]['file'] .$s;
			
// 			if(isset($trace[0]['function']))
// 				$traceInfo .= "function:".$trace[0]['function'] .$s;;
				
// 			if(isset($trace[0]['line']))
// 				$traceInfo .= 'line:'.$trace[0]['line'] ." ".$s;
				
				
			$time = date('y-m-d H:i:m');
			$traceInfo .= '[' . $time . '] ';
			foreach ($trace as $t) {
				if(isset($t['line']))
					$traceInfo .=  ' (' . $t['line'] . ') ';
			
				if(isset($t['file']))
					$traceInfo .= $t['file'] . " ";
			
				if(isset($t['class']))
					$traceInfo .= $t['class'] . " ";
			
				if(isset($t['type']))
					$traceInfo .= $t['type'] . " ";
			
				if(isset($t['function']))
					$traceInfo .= $t['function'] . " ";
					
				// 					$traceInfo .= implode(',', $t['args']);
				$traceInfo .=')'.$s;
			}
		}else{
			$img = '<td valign="top"  class="iconfont"><img src="'.STATIC_URL .'/common_img/licon.png"/></td>';
			$td = '<td valign="top" class="message">';
			$td_e = '<td valign="top" class="message"></td>';
// 			if(isset($trace[0]['file']))
// 				$traceInfo .= "<tr>$img{$td}file:".$trace[0]['file'] ."</td></tr>" ;
			
// 			if(isset($trace[0]['function']))
// 				$traceInfo .= "<tr>$img{$td}function:".$trace[0]['function'] ."</td></tr>" ;
			
// 			if(isset($trace[0]['line']))
// 				$traceInfo .= "<tr>$img{$td}line:".$trace[0]['line'] ."</td></tr>" ;
			
// 			$time = date('y-m-d H:i:m');
// 			$traceInfo .= '[' . $time . '] ';
			foreach ($trace as $t) {
				$traceInfo .= "<tr>$img{$td}";
				if(isset($t['line']))
					$traceInfo .=   $t['line'].'</td>'.$td ;
					
				if(isset($t['file']))
					$traceInfo .= $t['file'] . " ";
					
				if(isset($t['class']))
					$traceInfo .= $t['class'] . " ";
					
				if(isset($t['type']))
					$traceInfo .= $t['type'] . " ";
					
				if(isset($t['function']))
					$traceInfo .= $t['function'] . " ";
				
				$str = "";
				if(isset($t['args']) && is_array($t['args']) && $t['args'] ){
					foreach($t['args'] as $k=>$v){
						if(is_array($v)){
							
							
						}elseif(is_object($v)){	
						}else{
							$str .= $v;
						}
						
					}
// 					$traceInfo .= "(". $v2 .")";
				}
				// 					
				$traceInfo .= "</td>$td_e</tr>";
			}
		}
		return $traceInfo;
	}

	static function halt($error , $trace,$module = '' ,$back_url = '') {
		$traceInfo = self::errInfo($trace);
		if('SHELL' == z::$_ACCESS_TYPE){
			var_dump($error . $error);exit;
			
		}
		if(strpos($error,"action_log")!== false){
			echo "loop....";var_dump($error);	
			exit;
		}
		if(strpos($error,"DB_config") !== false){
			echo "loop....";var_dump($error);
			exit;
		}

		LogLib::errorWrite($error,$module);

		$title = "错误提示";
		$e = $error ;
		
		$tplt = getAppSmarty();
		$tplt->checkErrorFile();

		$STATIC_URL = STATIC_URL;


		include $tplt->compile('error.html');
		exit;
	}
}
