<?php
class Callback extends BaseCtrl implements CallbackInf{
    function wechatLogin(){

    }

    function qqLogin(){

    }

    function getOauthToken($appId,$timestamp,$authentication){
        $c = new Oauth();
        $c->getToken($appId,$timestamp,$authentication);
    }
}