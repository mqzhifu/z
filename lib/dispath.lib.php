<?php
//前端路由器
class DispathLib{
    //开户反射路由
    public $reflection = 1;
	function __construct(){
		
	}
	function authDispath(){

//		$app = AppModel::db()->getRow("en_title = '" .APP_NAME ."'");
//		if(!$app)
//			stop('应用不存在');


		if(URL_REWRITE_ON){
			//这里还需要考虑一下
		}else{
			$ctrl = _g(PARA_CTRL,'ctrl',0);
			$ac = _g(PARA_AC,'ac',0);
		}

//		if(!$app['status'])
//			stop('应用未开放');
		
		if(!$ctrl)
			if(defined('DEF_CTRL'))
				$ctrl = DEF_CTRL;
			else
                ExceptionFrameLib::throwErr('ctrl参数为空','G_PARA');
		
		if(!$ac)
			if(defined('DEF_AC'))
				$ac = DEF_AC;
			else
                ExceptionFrameLib::throwErr('ac参数为空','G_PARA');

		$dir =  APP_DIR .DS. C_DIR_NAME . DS ;
		$ctrl_file = strtolower($dir . $ctrl .C_EXT);
		if( !file_exists($ctrl_file))
            ExceptionFrameLib::throwErr('ctrl文件不存在:'.$ctrl_file,'FILE');


		include_once $ctrl_file;
		if ( !class_exists($ctrl.C_CLASS))
            ExceptionFrameLib::throwErr('ctrl类不存在:'.$ctrl.C_CLASS,'FILE');
		if(! method_exists($ctrl.C_CLASS,$ac))
            ExceptionFrameLib::throwErr('ac方法不存在:'.$ac,'FILE');


// 		$app_ctrl_info = $AppClass->authCtrl($appid,$ctrl,$ac);

// 		define('APP_ID',$appid);
		define("CTRL", $ctrl);
		define("AC", $ac);
		
// 		$this->appid = $appid;
		$this->ctrl = $ctrl;
		$this->ac = $ac;
		
	}
	
	function action(){
        $ac = $this->ac;
        $ctrl = $this->ctrl .C_CLASS;

	    if($this->reflection){
//	        $c = new ReflectionParameter (array($ctrl,$ac),1);
//            $x = $c->getType();
//            $x->getName();

            $info = new ReflectionMethod($ctrl,$ac);
            $p = $info->getParameters();

            $para = [];
            foreach($p as $k=>$v){
                $key = ($v->getName());
//                var_dump($key);
                $para[$v->getName()] = _g($key);

            }

            $class = new $ctrl();

            $reflection = new ReflectionClass($ctrl);
            $me = $reflection->getMethod($ac);
            $me->invokeArgs($class,$para);
        }else{
            $ctrlClass = get_instance_of($ctrl);
    		$ctrlClass->$ac();
        }
	}
	
	function rewrite($ctrl,$ac){
		
	}
}
