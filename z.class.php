<?php

//路径中的返斜杠:/
define ('DS', "/");
//项目目录
define ('APP_DIR', BASE_DIR .DS .'app' .DS. APP_NAME);

//常量检查
Z::checkConst();

if(!is_dir(APP_DIR))
	exit('APP_DIR not dir:'.APP_DIR);


//===========控制器==================
defined('C_EXT') or define('C_EXT', '.ctrl.php');//文件的后缀
defined('C_DIR_NAME') or define('C_DIR_NAME', 'ctrl');//文件夹名
defined('C_CLASS') or define('C_CLASS', 'Ctrl');//文件的类名后缀
//===========控制器==================
//===========模型层==================
defined('M_EXT') or define('M_EXT', '.model.php');
defined('M_DIR_NAME') or define('M_DIR_NAME', 'model');
defined('M_CLASS') or define('M_CLASS', 'Model');
//===========模型层==================
//===========类库==================
defined('LIB_DIR_NAME') or define('LIB_DIR_NAME','lib');
defined('LIB_EXT') or define('LIB_EXT','.lib.php');
defined('LIB_CLASS') or define('LIB_CLASS','Lib');
//===========类库==================
defined('MYSQL_DEBUG') or define('MYSQL_DEBUG',1);
//加载语言包
defined('LANG') or define('LANG','CN');
//调试模式,0:关闭,1:全开，2半开
defined('DEBUG') or define('DEBUG',1);

//默认30秒为超时
defined('TIME_LIMIT') or define('TIME_LIMIT',30);
set_time_limit(TIME_LIMIT);
//是否开启DB主丛模式
defined('MASTER_SLAVE') or define('MASTER_SLAVE', 0);
//数据库字符集
defined('DB_CHARSET') or define('DB_CHARSET', 'utf8');
//时区
defined('TIME_ZONE') or define('TIME_ZONE', 'Asia/shanghai' ) ;
ini_set("date.timezone",TIME_ZONE);
//定义默认的  控制器名称  与  事件名称
defined('DEF_CTRL') or define('DEF_CTRL','index');
defined('DEF_AC') or define('DEF_AC','index');
//公共类与公共配置
set_include_path(get_include_path() . PATH_SEPARATOR . BASE_DIR .DS.LIB_DIR_NAME);
set_include_path(get_include_path() . PATH_SEPARATOR . BASE_DIR .DS. 'config');
set_include_path(get_include_path() . PATH_SEPARATOR . BASE_DIR .DS. 'functions');

defined('PLUGIN') or define(  'PLUGIN',BASE_DIR . '/plugins/');
set_include_path(get_include_path() . PATH_SEPARATOR . APP_DIR .DS.LIB_DIR_NAME);

//初始化 控制器 模型层 目录
set_include_path(get_include_path() .PATH_SEPARATOR. APP_DIR .DS .C_DIR_NAME);
set_include_path(get_include_path() .PATH_SEPARATOR. APP_DIR . DS .M_DIR_NAME);
//总日志目录
defined('LOG_PATH') or define('LOG_PATH', BASE_DIR.DS."log");//文件的后缀

define("CONFIG_DIR",BASE_DIR.DS."config/");


//项目配置目录
define("APP_CONFIG",CONFIG_DIR.DS.APP_NAME.DS);
//框架版本
define('VERSION','1.0');

//初始分有为2个部分，1公共部分，专属部分
//专属部分：1 WB端，2指令行端，3 API调用
class Z{

	public static $_ACCESS_TYPE = "";

	static function init(){
        include_once 'functions_sys.php';//公共函数
		include_once 'functions_datetime.php';//公共函数
		include_once 'functions_path_file.php';//公共函数
		include_once 'functions_str_arr.php';//公共函数

		$GLOBALS['start_time'] = microtime(TRUE);//框架开始执行时间-开始时间
		//项目初始化函数
		if ( function_exists('_start_') )
			_start_();


		if(DEBUG){//测试模式-打开出错信息
			ini_set('display_errors', 1);
			if(1 == DEBUG){
				error_reporting(E_ALL);
			}else{
				error_reporting(E_ERROR);
			}
		}else{//生产模式 关闭 错误提示
			ini_set('display_errors', 0);
			error_reporting(0);
		}
		
		spl_autoload_register('autoload');//自动加载类
        register_shutdown_function('shutdown_function');//fatal error

		// 设定错误和异常处理
		set_error_handler(array('ExceptionFrameLib','appError'));
// 		set_exception_handler(array('ExceptionFrameLib','appException'));

		//===========内存信息==================
		define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
		if(MEMORY_LIMIT_ON) $GLOBALS['start_use_mems'] = memory_get_usage();
		$memorylimit = @ini_get('memory_limit');
		if($memorylimit && return_bytes($memorylimit) < 33554432 ) {
			ini_set('memory_limit', '128m');
		}


		include_once APP_CONFIG.DS.ENV."/mysql.php";
        include_once APP_CONFIG.DS.ENV."/redis.php";
        include_once APP_CONFIG.DS.ENV."/domain.php";

		//===========内存信息==================
	}
	//指令行方式运行RUN_ENV
	static function runConsoleApp(){
		defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
		
		set_include_path(get_include_path() . PATH_SEPARATOR .  APP_DIR . DS .'/shell');
		$Cmd = new CmdlineLib();

		$Cmd->addCommands(APP_DIR ."/shell/" );
		$Cmd->runCommand();
	}
	//web方式进行访问
	static function runWebApp(){
        include_once APP_DIR.DS."interface.php";
        self::checkWebConst();

		if(file_exists(APP_DIR.'/app_functions.php'))
			include_once APP_DIR.'/app_functions.php';//项目公共函数


		//****************session***************************

		//前台用户登陆SEESION_KEY
		//defined('SESS_USER_KEY') or define('SESS_USER_KEY','userInfo');
		//后台用户登陆SEESION_KEY
		//defined('SESS_ADMIN_KEY') or define('SESS_ADMIN_KEY','adminuserInfo');

        //是否开始SESSION,默认开户
		defined('IS_SESS') or define('IS_SESS','1');
		//session存储类型
		defined('SESS_TYPE') or define('SESS_TYPE','FILE');
		//session 失效时间
		defined('SESS_EXPIRE') or define('SESS_EXPIRE',60 * 60 * 3);
		if(SESS_TYPE == 'DB'){
			if(ini_get('session.save_handler') != 'user')
				ini_set('session.save_handler', 'user');
		}

		//****************session***************************
		//控制器 参数名称
		defined('PARA_CTRL') or define('PARA_CTRL', 'ctrl');
		//控制器 方法参数名称
		defined('PARA_AC') or define('PARA_AC', 'ac');
		//开启URL地址重写-此功能还没有编写
		defined('URL_REWRITE_ON') or define('URL_REWRITE_ON', 0);
		//图片上传路径
		defined('IMG_UPLOAD') or define('IMG_UPLOAD', BASE_DIR . '/www/upload/'.APP_NAME);

		// 获取请求地址：/project2/point/index.php
		$script_path = _get_script_url();
		$script_file = $script_path . "?" .  $_SERVER['QUERY_STRING'];
		$ctrl = substr($_SERVER['QUERY_STRING'],0,stripos($_SERVER['QUERY_STRING'],'ac') + 3);
		//请求文件的名称
		define('SCRIPT_NAME',$script_file);
		//请求文件+控制器参数值
		define('SCRIPT_CTRL',$script_path . "?" .$ctrl );
		//初始化SESSION
		$Session = new SessionLib();
		//初始化路由
		$Dispath = new DispathLib();
        LogLib::accessWrite();
		$Dispath->authDispath();//路由验证
        try{
            $Dispath->action();
        }catch (Exception $e){
            var_dump($e);exit;
        }

		//getSqlLog();//所有SQL记录日志
		//析构函数
		if ( function_exists('_tp_end') )
			_tp_end();
	}
	static function checkConst(){//初始化的常量值，必埴项检查
        if(!defined('ENV'))exit('常量未定义：ENV');
		if(!defined('BASE_DIR'))exit('常量未定义：BASE_DIR');
        if(!defined('RUN_ENV'))exit('常量未定义：RUN_ENV');
        if(!defined('APP_NAME'))exit('常量未定义：APP_NAME');
        if(!defined('DEF_DB_CONN'))exit('常量未定义：DEF_DB_CONN');
        if(!defined('DEF_REDIS_CONN'))exit('常量未定义：DEF_REDIS_CONN');
	}

    static function checkWebConst(){
        if(!defined('DOMAIN_URL'))exit('常量未定义：DOMAIN_URL');
        if(!defined('STATIC_URL'))exit('常量未定义：STATIC_URL');
    }
}
//self::initLanguageConst();
//语言包常量
//static function initLanguageConst(){
//    $data = ConstModel::db()->getAll();
//    if($data){
//        foreach($data as $k=>$v){
//            $GLOBALS['LANG'][$v['key']] = $v['content'];
//        }
//    }
//}



//ini_set("display_errors",1);


//include_once "global.inc.php";//公共全局变量
//include_once 'db.inc.php';//数据库配置
//self::checkDBConfig();
//static function checkDBConfig(){
//    if(!isset($GLOBALS['db_config'][DEF_DB_CONN]))
//        exit("db key DEF_DB_CONN:".DEF_DB_CONN."不存在");
//}
