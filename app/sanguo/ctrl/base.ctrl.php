<?php
class BaseCtrl implements BaseInf{
    public $uid = 0;
    public $uinfo = null;
    public $userLib = null;
    public $userReqCntMax = 1000;
    public $userReqCntTime = 300;

    public $IPReqCntMax = 1000;
    public $IPReqCntTime = 600;


    function initConfig(){
        //所有错误码
        include_once APP_CONFIG.DS."code.php";
        include_once APP_CONFIG.DS."main.php";
        include_once APP_CONFIG.DS."rediskey.php";

        include_once 'sanguoadmin'.DS."jsonindex.php";


        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['jsonTotal']['key'],null,'sanguoadmin');
        $json = RedisPHPLib::get($key,true);
        $GLOBALS['json'] = $json;

    }

    function __construct(){
        $this->initConfig();

        //记录请求日志
        $id = AccesslogModel::addReq();
        define("ACCESS_ID",$id);

        $this->userLib = new UserLib();

        $tokenRs = $this->initUserLoginInfoByToken();
        if($tokenRs['code'] != 200){
            out_ajax($tokenRs);
        }
//        define("LOGIN_UID",$this->uid);
//
//        if($this->uinfo['id']){
//            $rs = $this->checkUserBlackList($this->uid);
//            if($rs){
//                out_ajax(6004);
//            }
//        }
//
//        $rs = $this->checkIPBlackList();
//        if($rs){
//            out_ajax(5003);
//        }
//
//        $rs = $this->checkAPIRequestCnt();
//        if(!$rs){
//            out_ajax(5003);
//        }
//
//        $check = $this->loginAPIExcept();
//        if(!$check){
//            if(!$this->uinfo){
//                out_ajax(5001);
//            }
//        }

    }

    //有些接口，必须是登陆后，才能访问~有些不需要
    function loginAPIExcept(){
        $arr = $GLOBALS['main']['loginAPIExcept'];

        foreach($arr as $k=>$v){
            if($v[0] == CTRL && $v[1] == AC){
                return 1;
            }
        }

        return 0;
    }


    //判断登陆，初始化用户信息
    function initUserLoginInfoByToken(){
        $realUid = 0;

        $token = _g('token');
        if(!$token)
            return out_pc(200,'no token');

        $uid = TokenLib::getUid($token);
        if(!$uid){
            return out_pc(8109);
        }

        if($uid){
            //防止黑客伪造非整形UID,这样后面所有程度在读取的时候，都会错
            $uid = (int)$uid;
            if(!$uid || $uid < 0 ){
                return out_pc(8105);
            }
            $realUid = substr($uid,1);
            $this->uinfo = $this->userLib->getUinfoById($realUid);
            if(!$this->uinfo){//TOKEN解出的UID 不在DB中
                return out_pc(1002);
            }
        }

        $this->uid = $realUid;

        return out_pc(200);
    }
    //用户是否在黑名单中
    function checkUserBlackList($uid ){
        $rs = UserBlackModel::isBlack($uid);
        if(!$rs){
            return false;
        }
        return true;
    }
    //用户访问IP是否在黑名单中
    function checkIPBlackList(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['blackip']['key'],get_client_ip());
        $expireTime = RedisPHPLib::get($key);
        if(!$expireTime){
            return false;
        }

        return true;
    }
    //检查API请求次数，防止被攻击
    function checkAPIRequestCnt(){
        if($this->uinfo){
            //已登陆用户，针对UID 进行限制
            $cnt = AccesslogModel::getUserReqCntByTime($this->uinfo['id'],$this->userReqCntTime);
            if($cnt && $cnt > $this->userReqCntMax){
                UserBlackModel::add($this->uinfo['id'],1);
                return false;
            }
        }

        $IP = get_client_ip();
        $cnt = AccesslogModel::getIPReqCntByTime($IP,$this->IPReqCntTime);
        if($cnt > $this->IPReqCntMax){
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['blackip']['key'],get_client_ip());
            RedisPHPLib::set($key,1,time()+$GLOBALS['rediskey']['blackip']['expire']);
            return false;
        }

        return true;
    }

    //检查加密KEY，主要是给APP用，把所有参数MD5一下，检验安全
//    function checkRequestKey(){
//        $key = _g("key");
//        if(!$key)
//            return 5002;
//        if($key == md5(APP_NAME)){
//            return 5004;
//        }
//
//        return 1;
//    }
}