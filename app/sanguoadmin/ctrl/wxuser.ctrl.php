<?php
class wxuserCtrl extends BaseCtrl
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


        $this->addHookJS("wxuser_hook.html");

        $this->display("wxuser.html");

    }

    function getWhere(){

    }

    function getlist(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = getDataListTableWhere();

        $cnt = wxUserModel::db()->getCount($where);

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

            $data = wxUserModel::db()->getAll(" $where order by $order limit $iDisplayStart,$end");

            foreach($data as $k=>$v){
                $sex = '女';
                if($v['sex'] == 1)
                    $sex = '男';

                $status = '已取消';
                if($v['status'] == 1)
                    $status = '关注中';

                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    "<img src='{$v['headimgurl']}' width='80' height='80' />",
                    $v['openid'],
                    $sex,
                    $v['nickname'],
                    $v['country'],
                    $v['province'],
                    $v['city'],
                    $status,
                    date("Y-m-d H:i:s",$v['a_time']),
                    date("Y-m-d H:i:s",$v['up_time']),
                    '',
//                    '<a href="#" class="btn btn-xs default red delone" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>',
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