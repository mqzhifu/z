<?php
class indexCtrl extends BaseCtrl{

    function index(){





        $this->setTitle('test');

        $this->display("index.html");
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
//        $this->addJs('/assets/global/plugins/jquery.min.js');
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
//var_dump($js);exit;
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
        $data = array('up_time'=>time(),'is_online'=>0);
        adminUserModel::db()->update($data," id = ".$this->_adminid. "  limit 1");
        Jump("/admin");
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