<?php
class sessmsgCtrl extends BaseCtrl
{

    function index()
    {
        $this->setTitle('test');


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


        $status_desc = serverSessionModel::$_status_desc;
        $this->assign("status_desc",$status_desc);

        $this->addHookJS("sess_msg_hook.html");
        $this->display("sess_msg.html");

    }


    function getWhere(){
        return 1;
    }

    function getlist()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = sessMsgModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords) {
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
            $order = $sort[$order_column] . " " . $order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if (999999 == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $data = sessMsgModel::db()->getAll(" $where order by $order limit $iDisplayStart,$end");

            foreach ($data as $k => $v) {

                $bnt = "";

                $uname = getUnameByOid($v['openid']);
                $admin_name = '';
                if (isset($v['admin_id']) && $v['admin_id']) {
                    $admin = adminUserModel::db()->getById($v['admin_id']);
                    $admin_name = $admin['nickname'];
                }

                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="' . $v['id'] . '">',
                    $v['id'],
                    $uname,
                    $admin_name,
                    $v['content'],
                    $v['sid'],
                    date("Y-m-d H:i:s", $v['a_time']),
                    $bnt,
//                    '<a href="#" class="btn btn-xs default blue-hoki upstatus" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 更改状态</a>',
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