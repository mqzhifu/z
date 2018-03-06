<?php
class sessionCtrl extends BaseCtrl
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

        $this->addHookJS("server_sess_hook.html");
        $this->display("server_sess.html");

    }

    function process(){
        $server = serverModel::db()->getRow(" admin_id = ".$this->_adminid );
        if($server){

        }

        $this->addJs('/js/jquery.form.js');

        $this->display("wechat.html");


    }

    function unprocess(){

    }



    function getMsg(){
        $sid = _g('sid');
        if(!$sid)
            return echo_json("sid 为空",500);

        $sess = serverSessionModel::db()->getById($sid);
        if(!$sess)
            return echo_json("sid err,not in db...",501);


        $list = sessMsgModel::db()->getAll(' sid = '.$sid);
        if($list){
            $redis = new RedisPHPLib();
            $queue_key =getMsgRedisKey($sess['admin_id'],$sid);
            $redis->delete($queue_key);

            $html = "";
            foreach($list as $k=>$v){
                if(isset($sess['admin_id']) && $sess['admin_id']){
                    $v['admin_id'] = $sess['admin_id'];
                }
                $html .= $this->formatMsgRow($v);
            }
        }

        echo_json($html);
    }

    function formatMsgRow($v){
        $cate = $v['cate'];
        $time = date("Y-m-d H:i:s",$v['a_time']);

        $avatar = '';
        if($cate == 'in'){
            $uinfo = wxUserModel::db()->getRow(" openid = '".$v['openid']."'");
            $uname = $v['openid'];
            if($uinfo && isset($uinfo['nickname']) && $uinfo['nickname'])
                $uname = $uinfo['nickname'];

            if($uinfo && isset($uinfo['headimgurl']) && $uinfo['headimgurl'])
                $avatar = $uinfo['headimgurl'];


        }else{
            $avatar = getAdminAvatarid($v['admin_id']);
            $uname = getAdminUnameByid($v['admin_id']);
        }

        $content = "";
        if($v['type'] == 'text'){
            $content = $v['content'];
        }elseif($v['type'] == 'image'){
            $content = "<a href='{$v['content']}' target='_blank' ><img src='{$v['content']}' width='80' height='80' /></a>";
        }elseif($v['type'] == 'voice'){
//                    $content = "<audio src='{$v['content']}'>您的浏览器不支持 audio 标签。</audio>";
            $content = "<a href='{$v['content']}' target='_blank'>语音消息~请点我</a>";
        }

//                var_dump($v['type']);
//                var_dump($content);
//
//                var_dump($v);exit;

        $html = '<li class="'.$cate.'">'.
            '<img class="avatar" alt="" src="'.$avatar.'"/>'.
            '<div class="message">'.
            '<span class="arrow"></span>'.
            '<a href="javascript:;" class="name"> '.$uname.' </a>'.
            '<span class="datetime">at '.$time.' </span>'.
            '<span class="body">'.$content.'</span>'.
            '</div>'.
            '</li>';

        return $html;
    }



    function userlist(){
        $type = _g('type');
        $list = serverSessionModel::db()->getAll(" status = $type and admin_id = ".$this->_adminid . "  order by status");

        $list_status_desc = serverSessionModel::$_status_desc;
        $html = "";
        if($list){
            foreach($list as $k=>$v){
                $num = $k+1;
                $statud_desc = $list_status_desc[$v['status']];


                $uinfo = wxUserModel::db()->getRow(" openid = '".$v['openid']."'");
                $uname = $v['openid'];
                if($uinfo && isset($uinfo['nickname']) && $uinfo['nickname'])
                    $uname = $uinfo['nickname'];

                $html .= "<tr>";
                $html .= "<td>$num</td>";

                if($v['status'] == 3 ){
                    $td = "<a href='javascript:void(0);' onclick='get_msg({$v['id']})'> {$uname}</a>";
                }else{
                    $td = $uname;
                }

                $html .= "<td>$td</td>";
//                $html .= "<td>{$statud_desc}</td>";


                $s = 0;
                $opt = "";
                if($v['status'] == 3){
                    $opt = '关闭';
                    $s = 4;
                    $color = "yellow";
                }elseif($v['status'] == 2){
                    $opt = '接入';
                    $s = 3;
                    $color = "blue";
                }

                if($opt){
                    $opt = '<a href="#" onclick="up_status('.$v['id'].','.$s.');" class="btn btn-xs default '.$color.'" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> '.$opt.'</a>';
                }
                $html .= "<td>$opt</td>";

                if($v['status'] == 3 || $v['status'] == 2){
                    $redis = new RedisPHPLib();
                    $queue_key =getMsgRedisKey($v['admin_id'],$v['id']);

                    $list = $redis->lranges($queue_key,0,-1);
                    if(!$list) {
                        $unread = 0;
                        $css = '';
                    }else {
                        $unread = count($list);
                        $css = "bgcolor='red'";
                    }

                    $html .= "<td $css>$unread</td>";
                }

                $html .= "<tr>";

            }
        }
        $content =  file_get_contents(   $this->_st->compile('wechat_userlist.html')  );
        $content = str_replace('#table#',$html,$content);
        echo_json($content,200);
    }

    function sendPic(){
        $sid = _g('sid');
        if(!$sid)
            return echo_json("sid 为空",501);

        $sess = serverSessionModel::db()->getById($sid);
        if(!$sess)
            return echo_json("sid err,not in db...",502);

        $imgUp = new ImageUpLoadLib(array('up_pic_ipt'),IMG_UPLOAD."/send_wx_pic");
        $imgUp->upLoad();
        $pic_url = "/www/upload/send_wx_pic/".$imgUp->info['up_pic_ipt']['uploadFileName'];

        $this->wechat = WechatLib::get_instance();

        $sys_url = IMG_UPLOAD."/send_wx_pic/".$imgUp->info['up_pic_ipt']['uploadFileName'];
        $data = '@'.$sys_url;
        $rs = $this->wechat->uploadMedia($data,'image');
        if(!$rs){
            $err = "";
            if($this->wechat->errCode){
                $err .= $this->wechat->errCode. " ";
            }

            if($this->wechat->errmsg){
                $err .= $this->wechat->errmsg. " ";
            }

            echo_json('上传到微信端-图片失败('.$err.')....',500);
        }

        $data = array(
            'touser'=>$sess['openid'],
            'msgtype'=>'image',
            'image'=>array('media_id'=>$rs['media_id'])

        );

        $rs = $this->wechat->sendCustomMessage($data);

        if(!$rs){
            $err = "";
            if($this->wechat->errCode){
                $err .= $this->wechat->errCode. " ";
            }

            if($this->wechat->errmsg){
                $err .= $this->wechat->errmsg. " ";
            }

            echo_json('发送微信客服消失失败('.$err.')....',500);
        }



        $data = array(
            'type'=>'image',
            'content'=>$pic_url,
            'a_time'=>time(),
            'openid'=>$sess['openid'],
            'sid'=>$sid,
            'cate'=>'out',
            'wx_media_id'=>$rs['media_id']
        );

        sessMsgModel::db()->add($data);


        $v = array('cate'=>'out','admin_id'=>$sess['admin_id'],'type'=>'image','content'=>$pic_url,'a_time'=>time());
        $msg = $this->formatMsgRow($v);

        echo_json($msg,200);
    }


    function upstatus(){
        $status = _g('status');
        $sid = _g('sid');

        if(!$status)
            return echo_json("status 为空",500);

        if(!$sid)
            return echo_json("sid 为空",501);

        $sess = serverSessionModel::db()->getById($sid);
        if(!$sess)
            return echo_json("sid err,not in db...",502);

        if($sess['status'] == $status)
            return echo_json("status 相同，不需要修改",503);

        //关闭一个会话
        if($status == 4){
            ScheduleLib::inst()->closeSess($sid);
        }elseif($status == 3){
            ScheduleLib::inst()->servicingSess($sid);
        }elseif($status == 1){
//            $up_data = array('servicing_sess_num'=>array(-1),'up_time'=>time());
//            adminUserModel::db()->update($up_data," id = ".$sess['admin_id']. " limit 1");
        }


        echo_json("OK",200);
    }


    function getUserInfo(){
        $openid = _g('openid');
        $user = wxUserModel::db()->getRow(" openid = '$openid'");
        if(!$user)
            echo_json(" openid not in db ...",500);


        $avatar = getAvatarByOid($openid);
        $nickname = getUnameByOid($openid);


        $a_time = date("Y-m-d H:i:s",$user['a_time']);


        $order_num = 0;
        $order_price_total = 0;

        $order = orderModel::db()->getAll(" openid ='$openid' ");
        if($order)
            foreach($order as $k=>$v){
                $order_num++;
                $order_price_total += $v['price'];
            }


        $country = "";
        if( $user['country'] )
            $country = $user['country'];

        $province = "";
        if( $user['province'] )
            $province = $user['province'];


        $city = "";
        if( $user['city'] )
            $city = $user['city'];


        $area_info = "";
        $location = wxLocationModel::db()->getRow("  openid ='$openid' ");
        if($location){
            if(isset($location['area_info']) && $location['area_info']){
                $area_info = $location['area_info'];
            }
        }

        $html = "<tr><td colspan='2'><img src='$avatar'  width=\"50\" height=\"50\" /></td>";
        $html.= "<tr><td>昵称</td><td>$nickname</td>";
        $html.= "<tr><td>国家/省/市</td><td>$country-$province-$city</td>";
        $html.= "<tr><td>关注时间</td><td>$a_time</td>";
        $html.= "<tr><td>手机号</td><td>{$user['phone']}</td>";
        $html.= "<tr><td>地点</td><td>$area_info</td>";
        $html.= "<tr><td>更新时间</td><td></td>";
        $html.= "<tr><td>订单数</td><td>$order_num</td>";
        $html.= "<tr><td>销售额</td><td>$order_price_total</td>";
        $html.= "<tr><td>常用地址</td><td></td>";
        $html.= "<tr><td>用户标签</td><td></td>";


        echo_json($html,200);


    }

    function sendMsg(){
        $sid = _g('sid');
        if(!$sid)
            return echo_json("sid 为空",500);

        $sess = serverSessionModel::db()->getById($sid);
        if(!$sess)
            return echo_json("sid err,not in db...",501);

        if($sess['status'] != 3)
            return echo_json("请先接入..才能聊天",502);

        $content = _g('content');
        if(!$content)
            return echo_json("sid 为空",503);


        $WECHAT = WechatLib::get_instance();
//        $sess['openid'] = '111';
        $data = array(
            'touser'=>$sess['openid'],
            'msgtype'=>'text',
            'text'=>array('content'=>$content,),

        );
        $rs = $WECHAT->sendCustomMessage($data);
        if(!$rs){
            $err = "";
            if($WECHAT->errCode){
                $err .= $WECHAT->errCode. " ";
            }

            if($WECHAT->errMsg){
                $err .= $WECHAT->errMsg. " ";
            }

            $data['err'] = 1;
            $msg = '请求微信接口失败('.$err.')...';
            $code = 600;

            echo_json($msg,$code);
        }

        $data = array(
            'type'=>'text',
            'content'=>$content,
            'a_time'=>time(),
            'openid'=>$sess['openid'],
            'sid'=>$sid,
            'cate'=>'out',
        );

        sessMsgModel::db()->add($data);

        $v = array('cate'=>'out','admin_id'=>$sess['admin_id'],'type'=>'text','content'=>$content,'a_time'=>time());
        $msg = $this->formatMsgRow($v);

        $up_data = array('reply_num'=>array(1));
        serverSessionModel::db()->update($up_data," id = $sid limit 1 ");

        echo_json($msg,200);





    }

    function add(){
//        $this->addCss('/assets/global/plugins/typeahead/typeahead.css');
//        $this->addJs('/assets/admin/pages/scripts/components-form-tools.js');


        if(_g('doings')){
            $title = _g("title");
            $content = _g("content");
            $openid = _g("openid");
            $trigger_time = _g("trigger_time");


            if(!$content)
                echo_json("content is null",'500');

            if(!$openid)
                echo_json("$openid is null",'500');

            if(!$trigger_time)
                echo_json("$trigger_time is null",'500');


            $trigger_time = strtotime($trigger_time);

            $data = array(
                'title'=>$title,
                'content'=>$content,
                'trigger_time'=>$trigger_time,
                'openid'=>$openid,
                'a_time'=>time(),
                'up_time'=>time(),

            );

            scheduleModel::db()->add($data);

            echo_json("ok",200);
        }else{
            $html = $this->_st->compile("order_add.html");
            $html = file_get_contents($html);
            echo_json($html);
        }




    }

    function schedule(){
        $sid = _g('sid');
        if(!$sid)
            return echo_json("sid 为空",500);

        $sess = serverSessionModel::db()->getById($sid);
        if(!$sess)
            return echo_json("sid err,not in db...",501);

        if($sess['status'] == 3)
            return echo_json("进行中的会话不能调度",502);

        if($sess['status'] == 4)
            return echo_json("已关闭的会话不能调度",503);


        if(_g('opt')){

            $id = _g('id');
            if(!$id)
                return echo_json("id 为空",400);

            $info = adminUserModel::db()->getById($id);
            if(!$info)
                return echo_json("id err,not in db...",501);


            $rs = ScheduleLib::inst()->adminAssign($sid,$id);


            echo_json($rs['msg'],$rs['code']);
        }else{

            $list = adminUserModel::db()->getAll();
            $data = "";
            foreach($list as $k=>$v){
                $data .="<tr>";
                $data .="<td><input type='radio' name='admin_ids' value='{$v['id']}' /></td><td>{$v['nickname']}</td><td>{$v['wating_sess_num']}</td><td>{$v['servicing_sess_num']}</td>";
                $data .="</tr>";
            }


            $html = $this->_st->compile("server_schedule.html");
            $html = file_get_contents($html);
            $html = str_replace("#table_data#",$data,$html);
            $html = str_replace("#sid#",$sid,$html);

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

        $where = $this->getWhere();

        $cnt = serverSessionModel::db()->getCount($where);

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

            $data = serverSessionModel::db()->getAll(" $where order by $order limit $iDisplayStart,$end");

            foreach($data as $k=>$v){
                $s = serverSessionModel::$_status_desc;
                $status = $s[$v['status']];
//                $status = "";
//                if(!$v['status'] && $status == 3){
//                    $status = '异常';
//                }elseif($v['status']  == 1){
//                    $status = '未处理';
//                }elseif($v['status']  == 2){
//                    $status = '已提醒';
//                }

                $bnt = "";
                if($v['status'] == 1 || $v['status'] == 2){
                    $bnt =  '<a href="#" class="btn btn-xs default red schedule" data-id="'.$v['id'].'" onclick="schedule('.$v['id'].')"><i class="fa fa-trash-o"></i> 调度</a>';
                }

                $admin_name = '';
                if(isset($v['admin_id']) && $v['admin_id']){
                    $admin = adminUserModel::db()->getById($v['admin_id']);
                    $admin_name = $admin['nickname'];
                }


                $uname = getUnameByOid($v['openid']);

                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $uname,
                    $admin_name,
                    $v['receive_num'],
                    $v['reply_num'],
                    date("Y-m-d H:i:s",$v['a_time']),
                    date("Y-m-d H:i:s",$v['up_time']),
                    $status,
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
    //获取增量消息
    function getIncMsg(){
        $sid = _g('sid');
        if(!$sid)
            return echo_json("sid 为空",500);

        $sess = serverSessionModel::db()->getById($sid);
        if(!$sess)
            return echo_json("sid err,not in db...",501);


        $redis = new RedisPHPLib();
        $queue_key =getMsgRedisKey($sess['admin_id'],$sid);



        $list = $redis->lranges($queue_key,0,-1);
        if(!$list)
            echo_json("",600);

        $redis->delete($queue_key);

        if($list){
            $html = "";
            foreach($list as $k=>$v){
                $v = explode("##",$v);
//                var_dump($v);exit;
                $v['type'] = $v[3];
                $v['openid'] = $v[2];
                $v['content'] = $v[1];
                $v['cate'] = 'in';
                $v['a_time'] = $v[0];


//                var_dump($v);exit;

                $html .= $this->formatMsgRow($v);
            }
            echo_json($html);
        }


    }

}