<?php
class wxmenuCtrl extends BaseCtrl{

    function index(){





        $this->setTitle('test');

        $this->display("index.html");
    }

    function pushmenu(){
        $id = _g("bind");
        if($id)
            $bindInfo = WxAppBindModel::db()->getById($id);
        else
            $bindInfo = WxAppBindModel::db()->getRow();

        if(!$bindInfo)
            exit('no bind wx app info.');

        $skey = "";
        if($bindInfo['is_secret'] && isset($bindInfo['skey']) && $bindInfo['skey'] )
            $skey = $bindInfo['skey'];

        $options = array(
            'token'=>$bindInfo['token'], //填写你设定的key
            'encodingaeskey'=>$skey, //填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'=>$bindInfo['appid'],
            'appsecret'=>$bindInfo['appsecret'],
        );
        $this->wechat = new WechatLib($options);

        $data = array(
            'button'=>
                array(
                    array('type'=>'view','name'=>'我的','url'=>'http://www.baidu.com'),
                    array('type'=>'click','name'=>'日程','key'=>'schedule'),
                    array('name'=>'服务','sub_button'=>array(
                                                array('type'=>'click','name'=>'咖啡','key'=>'coffee'),
                                                array('type'=>'click','name'=>'鲜花','key'=>'flower')
                                    )
                    ),

                )
        );

//        $rs = json_encode($data,true);
//        $rs = str_replace("[",'',$rs);
//        $rs = str_replace("]",'',$rs);
        $rs = $this->wechat->createMenu($data);

        var_dump($rs);exit;
    }


    function loginuser(){
        $ps = _g("password");
        $uname = _g("username");
        $verify = _g("verify");
        if(!$ps)
            out_err("false",501,'ajax');

        if(!$uname)
            out_err("false",502,'ajax');

        if(!$verify)
            out_err("false",503,'ajax');

        $code = $this->_sess->getImgCode();
        if(strtolower($verify) != strtolower($code))
            out_err("false",504,'ajax');

        $islogin = $this->_acl->adminLogin($uname,$ps);
        if($islogin){
            $uid = $this->_sess->getValue('id');
            $str = "登陆：".$uname;
            admin_db_log_writer($str,$uid,'login');
            out_err("true",200,'ajax');
        }else{
            out_err("false",505,'ajax');
        }
    }


    function login(){

        if(_g("opt")){


        }

        $this->addCss('/assets/global/google/font.css');
        $this->addCss('/assets/global/plugins/font-awesome/css/font-awesome.min.css');
        $this->addCss('/assets/global/plugins/simple-line-icons/simple-line-icons.min.css');
        $this->addCss('/assets/global/plugins/bootstrap/css/bootstrap.min.css');
        $this->addCss('/assets/global/plugins/uniform/css/uniform.default.css');
        $this->addCss('/assets/global/plugins/select2/select2.css');


        $this->addCss('/assets/admin/pages/css/login-soft.css');

        $this->addCss('/assets/global/css/components-md.css');
        $this->addCss('/assets/global/css/plugins-md.css');

        $this->addCss('/assets/admin/layout/css/layout.css');
        $this->addCss('/assets/admin/layout/css/themes/default.css');
        $this->addCss('/assets/admin/layout/css/custom.css');


//        $this->addJs('/assets/global/plugins/respond.min.js');
//        $this->addJs('/assets/global/plugins/excanvas.min.js');
        $this->addJs('/assets/global/plugins/jquery.min.js');
        $this->addJs('/assets/global/plugins/jquery-migrate.min.js');
        $this->addJs('/assets/global/plugins/bootstrap/js/bootstrap.min.js');


        $this->addJs('/assets/global/plugins/jquery.blockui.min.js');
        $this->addJs('/assets/global/plugins/uniform/jquery.uniform.min.js');
        $this->addJs('/assets/global/plugins/jquery.cokie.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/backstretch/jquery.backstretch.min.js');
        $this->addJs('/assets/global/plugins/select2/select2.min.js');
        $this->addJs('/assets/global/scripts/metronic.js');
        $this->addJs('/assets/admin/layout/scripts/layout.js');
        $this->addJs('/assets/admin/layout/scripts/demo.js');
        $this->addJs('/assets/admin/pages/scripts/login-soft.js');

        $css = $this->initCss();
        $js = $this->initJS();

        $DOMAIN_URL = DOMAIN_URL;

        $html = $this->_st->compile("login.html");
        include $html;
    }

    function verifyImg(){
        $lib = get_instance_of("ImageAuthCodeLib");
        $lib->showImg();

        $this->_sess->setImgCode($lib->code);
    }


    function logout(){
        $this->_sess->none();
        Jump("/");
    }

    function upps(){

        if(_g("opt")){
            $old_ps = _g("old_ps");
            if(!$old_ps){
                exit("原密码不能为空");
            }

            $ps = _g("ps");
            if(!$ps){
                exit("新密码不能为空");
            }

            $ps_sure = _g("ps_sure");
            if(!$ps_sure){
                exit("确认密码不能为空");
            }

            if($ps_sure != $ps){
                exit("两次密码不一致");
            }

            if(strlen($ps)<6)
                exit("新密码至少6个字符");

            $uid = $this->_sess->getValue('id');

            $user = Admin_UserModel::db()->getRow(" id = $uid");
            if($user['ps'] != md5($old_ps) ){
                exit('原始密码错误');
            }


            $str = "修改密码:新($ps),旧($old_ps)";
            admin_db_log_writer($str,$uid,'up_ps');

            Admin_UserModel::db()->update(array('ps'=>md5($ps) )," id = $uid limit 1 ");
            $this->_sess->none();
            echo "<script>alert('新密码设置成功，请您重新登陆');location.href='/';</script>";
//            jump("/");
        }

        $const = ConstModel::db()->getRow(" id = 1");
        $this->assign("content",$const['content']);
        $this->assign("status",$const['status']);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("www/upps_hook.html");

        $this->display("www/upps.html");
    }

}