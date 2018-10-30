<?php
class LoginCtrl extends BaseCtrl implements LoginInf{

    function WXGame($code){
//        var_dump($code);

        $host = "https://api.weixin.qq.com/sns/jscode2session?";
        $appid = "wx0ba9b27128bdf7c5";
        $secret = "002daef284c70cae91dfc44419162122";

        $url = $host."appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";


        $rs = CurlLib::send($url,1,null,1);
        if($rs['code'] == 200){
//            $rs['msg'] = "{\"session_key\":\"Sd2cStc5Atqp2c8SIshlKA==\",\"openid\":\"oNa4Q5YO9lJJpKs0M11ogIRYr-iY\"}";
            $json = json_decode($rs['msg'],'true');
            if(arrKeyIssetAndExist($json,'errcode')){
                out_ajax($json['errcode'],$json['errmsg']);
            }
        }else{
            echo json_encode($rs);exit;
        }


        $user = UserModel::db()->getRow(" type ='".UserModel::$_type_wechat."' and openid = '{$json['openid']}' " );
        if($user){
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['toekn']['key'],$user['id']);
            $token = RedisPHPLib::get($key);
            if(!$token){
                $token = TokenLib::create(UserModel::$_type_wechat.$user['id']);
                RedisPHPLib::set($key,$token,$GLOBALS['rediskey']['toekn']['expire']);
            }

            out_ajax(200,array('token'=>$token));
        }

        $data = array(
            'openid'=> $json['openid'],
            'wx_session_key'=>$json['session_key'],
            'a_time'=>time(),
            'type'=>UserModel::$_type_wechat,
            'base_level'=>1,
            'base_exp'=>1,
            'boss_level'=>1,
        );


        $uid = UserModel::db()->add($data);
        $token = TokenLib::create(UserModel::$_type_wechat.$user['id']);
        RedisPHPLib::set($key,$token,$GLOBALS['rediskey']['toekn']['expire']);


        include_once CONFIG_DIR."/sanguoadmin/apiversion.php";

        out_ajax(200,array('token'=>$token,'version'=>$GLOBALS['apiVersion']));

    }
}