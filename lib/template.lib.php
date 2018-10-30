<?php
class TemplateLib{
    
    private static $instance;
    public $path;

//     static function getInstance($controller="",$action="",$obj=""){
//         if(!isset(self::$instance)&&empty(self::$instance)) {
//             self::$instance = new self($controller,$action,$obj);
//         }
//         return self::$instance;
//     }
    //构造函数
    function __construct(){
       
    }

    function setPath($path){
    	if(!is_dir($path))
    		exit('template dir not exist;');
    	
    	$this->path = $path;
    }
    
    function setCompilePath($path){
    	if(!is_dir($path))
    		exit('template compile dir not exist;');
    	
    	
    	$this->compilePath = $path;
    }
    
	function compile($file) {
		if(!is_dir($this->path))
			exit('template not exist;');
		
		if(!is_dir($this->compilePath))
			exit('template compile dir not exist;');
		
        $templateFile = $this->path . $file;
		if(!file_exists($templateFile)) 
			exit("模板文件" . $templateFile . "不存在");
            
     	$str = file_get_contents($templateFile);
       	$str = preg_replace('|{if (.+)}|isU', '<?php if(\\1){ ?>', $str);
       	$str = preg_replace('|{/if}|isU', '<?php } ?>', $str);
      	$str = preg_replace('|{_(.*)}|isU', '<?php echo \\1; ?>', $str);
       	$str = preg_replace('|{\$lang_(.*)}|isU', '<?php echo $language["\\1"]; ?>', $str);
       	$str = preg_replace('|{\$(.*)}|isU', '<?php echo $\\1; ?>', $str);
        $str = preg_replace('/{CONST\|(.*)}/isU', '<?php echo \\1; ?>', $str);
       	$str = preg_replace('|{else}|isU', '<?php }else{ ?>', $str);
      	$str = preg_replace('|{elseif (.*)}|isU', '<?php }elseif(\\1){ ?>', $str);
       	$str = preg_replace('|{eval (.*)}|isU', '<?php \\1; ?>', $str);
      	$str = preg_replace('|{echo (.*)}|isU', '<?php echo \\1; ?>', $str);
                
       	$str = preg_replace('|<!--{loop (.*) (.*) (.*)}-->|isU', '<?php if(is_array(\\1)){ foreach(\\1 as \\2 => \\3){  ?>', $str);
       	$str = preg_replace('|{for (.*)}|isU', '<?php for (\\1) {  ?>', $str);
      	$str = preg_replace('|{/for}|isU', '<?php } ?>', $str);
      	$str = preg_replace('|{foreach (.*)}|isU', '<?php foreach \\1 {  ?>', $str);
      	$str = preg_replace('|{/foreach}|isU', '<?php } ?>', $str);
      	$str = preg_replace('|<!--{/loop}-->|isU', '<?php }} ?>', $str);
                
       	$str = preg_replace('|\t|isU', '', $str);
                
       	// 载入其他文件
       	$str = preg_replace('|{include (.*)}|isU', '<?php include $this->_st->compile("\\1"); ?>', $str);
       	$str = preg_replace('|{include_php (.*)}|isU', '<?php if(is_file("\\1.php")){ include_once("\\1.php");} ?>', $str);
       	$str = preg_replace('|{include_htm(.*) (.*)}|isU', '<?php if(is_file("'.$this->path.'\\2.htm\\1")){ $str = file_get_contents("'.$this->path.'\\2.htm\\1"); echo $str;} ?>', $str);

		//判断文件中是否包含目录名，如果包含目录，是需要创建文件夹的，不然报错
		if( $l =  strripos($file,DS)){
			$create_tmp_path = substr($file,0,$l);
			$create_tmp_path = $this->compilePath .DS .$create_tmp_path;
			if(!is_dir($create_tmp_path)){
				mkdir($create_tmp_path,0777,true);
			}
		}

		$CompliefileTmp = $this->compilePath .DS. $file.".php";
		$fd = fopen($CompliefileTmp,"w+");
		fwrite($fd,$str);
		return $CompliefileTmp;
	}
	
	function checkErrorFile(){
		$templateFile = $this->path . 'error.html';
		if(!file_exists($templateFile))
			exit("模板文件" . $templateFile . "不存在");
	}
}