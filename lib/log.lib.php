<?php
//系统日志
class LogLib {

    //基方法
	static function writeHash($content,$dir,$hashType = 'day'){
        $date = "[".date("Y-m-d H:i:s").']';
        $content =$date. " [pid:".getmypid(). "]  ".$content;

	    $file = date("Y-m-d") . ".log";
	    $path_file = LOG_PATH.DS.APP_NAME.DS.$dir.DS.$file;
//	    var_dump($path_file);
        $fd = fopen($path_file,"a+");
        fwrite($fd,$content);
    }
    //系统日志
    static function systemWriteFileHash($module ,$title,$content =null ) {
        $path =  "system".DS.$module;
        if(!$title && !$content){
            return -1;
        }

        $str = $title;
        if($content){
            $str .= json_encode($str);
        }

        LogLib::writeHash($str,$path);

    }

    //访问日志
    static function accessWrite(){
        self::writeFileHash();
//        self::addDb();
    }

    static function writeFileHash(){
        $path =  "access";
        $str = SCRIPT_NAME . json_encode($_REQUEST);
        LogLib::writeHash($str,$path);
    }

    static function addDb(){

    }


}
