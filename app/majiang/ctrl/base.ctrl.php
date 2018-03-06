<?php
class BaseCtrl{
    public $_st = null;
    public $_js = array();
    public $_css = array();
    public $_title = '';
    public $_hook_js = '';
    public $_assign = array();
    public $_adminid = "";

    function __construct(){
        $this->_st = getAppSmarty();
        $this->_acl = get_instance_of('AclLib');
        $this->_sess = get_instance_of('SessionLib');

        $this->_majiang = new majiangLib();

//        if(AC != 'login' && AC !='sms' && AC !='logout'  && AC != 'loginuser' && AC != 'verifyImg' && AC != 'verify'){
//            if(!$this->_acl->isLogin()){
//                jump(DOMAIN_URL."/?ac=login");
//            }
//        }

//        $this->_adminid = $this->_sess->getValue('id');

//        $this->init_css_ds();
//        $this->assign("uname",$this->_sess->getValue('uname'));

    }

    function addJs($dir_file){
        if(!in_array($dir_file,$this->_js)){
            $this->_js[] = $dir_file;
        }
    }

    function addHookJS($js){
        $this->_hook_js = $js;
    }

    function addCss($dir_file){
        if(!in_array($dir_file,$this->_css)){
            $this->_css[] = $dir_file;
        }
    }

    function setTitle($title){
        $this->_title = $title;
    }

    function initCss(){
        $css = "";
        if($this->_css){
            foreach($this->_css as $k=>$v){
                $css .= '<link href="/www/'.$v.'" rel="stylesheet" type="text/css"/>';
            }
        }
        return $css;
    }

    function initJS(){
        $js = "";
        if($this->_js){
            foreach($this->_js as $k=>$v){
                $js .= '<script src="/www/'.$v.'" type="text/javascript"></script>';
            }
        }
        return $js;
    }

    function assign($k,$v){
        $this->_assign[$k] = $v;
    }

    function init_css_ds(){
        $this->addCss('/assets/global/google/font.css');


        $this->addCss('/assets/global/plugins/font-awesome/css/font-awesome.min.css');
        $this->addCss('/assets/global/plugins/simple-line-icons/simple-line-icons.min.css');
        $this->addCss('/assets/global/plugins/bootstrap/css/bootstrap.min.css');
        $this->addCss('/assets/global/plugins/uniform/css/uniform.default.css');
        $this->addCss('/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css');
        $this->addCss('/assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css');
        $this->addCss('/assets/global/plugins/fullcalendar/fullcalendar.min.css');
        $this->addCss('/assets/global/plugins/jqvmap/jqvmap/jqvmap.css');
        $this->addCss('/assets/admin/pages/css/tasks.css" rel="stylesheet');
        $this->addCss('/assets/global/css/components.css');
        $this->addCss('/assets/global/css/plugins.css');
        $this->addCss('/assets/admin/layout/css/layout.css');
        $this->addCss('/assets/admin/layout/css/themes/darkblue.css');
        $this->addCss('/assets/admin/layout/css/custom.css');

        $this->addJs('/assets/global/plugins/respond.min.js');
        $this->addJs('/assets/global/plugins/excanvas.min.js');

        $this->addJs('/assets/global/plugins/jquery-migrate.min.js');
        $this->addJs('/assets/global/plugins/jquery-ui/jquery-ui.min.js');
        $this->addJs('/assets/global/plugins/bootstrap/js/bootstrap.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js');
        $this->addJs('/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js');
        $this->addJs('/assets/global/plugins/jquery.blockui.min.js');
        $this->addJs('/assets/global/plugins/jquery.cokie.min.js');
        $this->addJs('/assets/global/plugins/uniform/jquery.uniform.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/jquery.vmap.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.russia.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.world.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.europe.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.germany.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.usa.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/data/jquery.vmap.sampledata.js');
        $this->addJs('/assets/global/plugins/flot/jquery.flot.min.js');
        $this->addJs('/assets/global/plugins/flot/jquery.flot.resize.min.js');
        $this->addJs('/assets/global/plugins/flot/jquery.flot.categories.min.js');
        $this->addJs('/assets/global/plugins/jquery.pulsate.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-daterangepicker/moment.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.js');
        $this->addJs('/assets/global/plugins/fullcalendar/fullcalendar.min.js');
        $this->addJs('/assets/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js');
        $this->addJs('/assets/global/plugins/jquery.sparkline.min.js');
        $this->addJs('/assets/global/scripts/metronic.js');
        $this->addJs('/assets/admin/layout/scripts/layout.js');
        $this->addJs('/assets/admin/layout/scripts/quick-sidebar.js');
        $this->addJs('/assets/admin/layout/scripts/demo.js');
        $this->addJs('/assets/admin/pages/scripts/index.js');
        $this->addJs('/assets/admin/pages/scripts/tasks.js');
    }


    function display($file){
        $ac = AC;
        $ctrl = CTRL;
        $css = $this->initCss();
        $js = $this->initJS();

        if($this->_assign){
            foreach($this->_assign as $k=>$v){
                $$k = $v;
            }
        }


//        $header_html = $this->_st->compile("layout/header.html");
        $index_html = $this->_st->compile($file);
//        $footer_html = $this->_st->compile("layout/footer.html");


        $hook_js = $this->_hook_js;

//        include $header_html;
        include $index_html;
//        include $footer_html;
        exit;

    }
}