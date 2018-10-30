<?php
class IndexCtrl extends BaseCtrl implements IndexInf {

    function index(){
        echo "welcome!";
        exit;
    }

    function apilist(){
        $list = ApiconfigModel::db()->getAll(1);
        foreach($list as $k=>$v){
            if($v['is_login']==1){
                $list[$k]['is_login'] = '是';
            }else{
                $list[$k]['is_login'] = '否';
            }
            $info = ApiparaModel::db()->getAll(" api_config_id = ".$v['id']);
            foreach($info as $k2=>$v2){
                if($v2['is_must'] == 1){
                    $info[$k2]['is_must'] = '必填';
                }else{
                    $info[$k2]['is_must'] = '选填';
                }
            }

            $list[$k]['para'] = $info;
        }
        $st = getAppSmarty();
        $index_html = $st->compile("test.html");
        include $index_html;



    }

    function apitest($apiId){
        $token = "sqemqH94otiGqXrafaWtng";
        $uid = TokenLib::getUid($token);
        $info = $this->userLib->getUinfoById($uid);

        $api = ApiconfigModel::db()->getById($apiId);
        $para = ApiparaModel::db()->getAll(" api_config_id = ".$apiId);
        foreach($para as $k=>$v){
            if($v['is_must'] == 1){
                $para[$k]['is_must'] = '是';
            }else{
                $para[$k]['is_must'] = '否';
            }

            $para[$k]['default'] = "";
            if($v['name'] == 'uniqueCode'){
                $para[$k]['default'] = '123';
            }
        }
        //需要登陆的接口
        if($api['is_login'] == 1){
            $para[] =array("name"=>'token','is_must'=>'必填','default'=>$token,'info'=>$info,'title'=>'token');
        }

        $st = getAppSmarty();
        $index_html = $st->compile("apitest.html");
        include $index_html;
    }

    function getCodeDesc(){
        $code = $GLOBALS['code'];
        $st = getAppSmarty();
        $index_html = $st->compile("code.html");
        include $index_html;
    }
}