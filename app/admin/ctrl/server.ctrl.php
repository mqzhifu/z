<?php
class serverCtrl extends BaseCtrl
{

    function index(){

        if(_g("getlist")){
            $this->getList();
        }



        $this->addCss('/assets/global/plugins/select2/select2.css');
        $this->addCss('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css');
        $this->addCss('/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css');
//        $this->addCss('/assets/admin/layout4/css/themes/light.css');
        $this->addCss('/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');




        $this->addJs('/assets/global/plugins/select2/select2.min.js');
        $this->addJs('/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js');
        $this->addJs('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js');
//        $this->addJs('/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js');

        $this->addJs('/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js');

        $this->addJs('/assets/global/scripts/datatable.js');
        $this->addJs('/assets/global/plugins/bootbox/bootbox.min.js');

        $this->addJs('/js/jquery.validate.min.js');
        $this->addJs('/js/additional-methods_cn.js');

        $this->addJs('/js/jquery.form.js');

        $this->addJs('/js/pop_bootbox.js');
        $this->addJs('/js/pop_ajax.js');


        $status_desc = serverModel::$_is_online;
        $this->assign("status_desc",$status_desc);

        $this->addHookJS("server_hook.html");
        $this->display("server.html");

    }

    function upstatus(){
        $html = $this->_st->compile("schedule_upstatus.html");
        $html = file_get_contents($html);
        echo_json($html);
    }

    function add(){
        if(_g('doings')){
            $ps = _g("ps");
            $ps_sure = _g("ps_sure");
            $nickname = _g("nickname");
            $avatar = _g("avatar");
            $uname = _g("uname");


            if(!$ps)
                echo_json("密码不能为空",'500');

            if(!$ps_sure)
                echo_json("确认-密码不能为空",'501');

            if(!$nickname)
                echo_json("昵称不能为空",'502');


            if($ps != $ps_sure)
                echo_json("两次密码不一致",'502');

            if(!$uname)
                echo_json("用户名不能为空",'503');



//            var_dump($_FILES);
//            exit;

            $c = new ImageUpLoadLib(array('avatar'),IMG_UPLOAD."/admin_avatar");

            $c->upLoad();
            $avatar = "/www/upload/admin_avatar/".$c->info['avatar']['uploadFileName'];
            $data = array(
                'uname'=>$uname,
                'nickname'=>$nickname,
                'ps'=>md5($ps),
                'avatar'=>$avatar,
                'a_time'=>time(),
                'up_time'=>time(),
            );

            adminUserModel::db()->add($data);

            echo_json("ok",200);
        }else{

//            $p_type = productTypeModel::db()->getAll();
//            $p_type_option = "";
//            foreach($p_type as $k=>$v){
//                $p_type_option .= "<option value='{$v['id']}'>{$v['title']}</option>";
//            }


            $html = $this->_st->compile("server_add.html");
            $html = file_get_contents($html);
//            $html = str_replace("#p_type_option#",$p_type_option,$html);
            echo_json($html);
        }




    }

    function getWhere(){
        return 1;
    }

    function getlist(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = getDataListTableWhere();

        $cnt = adminUserModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                'id',
                'id',
                '',
                '',
                '',
                '',
                'add_time',
            );
            $order = $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $data = adminUserModel::db()->getAll(" $where order by $order limit $iDisplayStart,$end");

            $arr = orderModel::$_status_desc;
            foreach($data as $k=>$v){
                if($v['is_online'] == 1){
                    $is_online = "是";
                }else{
                    $is_online = "否";
                }

//                $status = "异常";
//                if(in_array($v['status'], array_flip($arr)))
//                    $status = $arr[$v['status']];



                $avatart = getAdminAvatarid($v['id']);

                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['servicing_sess_num'],
                    $v['nickname'],
                    $is_online,
                    $v['uname'],
                    $v['wating_sess_num'],
                    $v['close_sess_num'],
                    "<img src='{$avatart}' width='50' height='50' />",

                    '',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }
}
