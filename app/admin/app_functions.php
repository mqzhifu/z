<?php
function getMsgRedisKey($service_id,$sess_id){
    return "msg_".$service_id."_".$sess_id;
}

function getDataListTableWhere(){
    $where = 1;
    $openid = _g("openid");
    $sex = _g("sex");
    $status = _g("status");

    $nickname = _g('nickname');
    $nickname_byoid = _g('nickname_byoid');

    $content = _g('content');

    $is_online = _g('is_online');

    $uname = _g('uname');

    $from = _g("from");
    $to = _g("to");

    $trigger_time_from = _g("trigger_time_from");
    $trigger_time_to = _g("trigger_time_to");


    $uptime_from = _g("uptime_from");
    $uptime_to = _g("uptime_to");






    if($openid)
        $where .=" and openid = '$openid' ";

    if($sex)
        $where .=" and sex = '$sex' ";

    if($status)
        $where .=" and status = '$status' ";

    if($nickname)
        $where .=" and nickname = '$nickname' ";

    if($nickname_byoid){
        $user = wxUserModel::db()->getRow(" nickname='$nickname_byoid'");
        if(!$user){
            $where .= " and 0 ";
        }else{
            $where .=  " and openid = '{$user['openid']}' ";
        }
    }

    if($content)
        $where .= " and content like '%$content%'";

    if($from)
        $where .=" and a_time >=  ".strtotime($from);

    if($to)
        $where .=" and a_time <= ".strtotime($to);

    if($trigger_time_from)
        $where .=" and trigger_time_from >=  ".strtotime($trigger_time_from);

    if($trigger_time_to)
        $where .=" and trigger_time_to <= ".strtotime($trigger_time_to);

    if($uptime_from)
        $where .=" and up_time >=  ".strtotime($uptime_from);

    if($uptime_to)
        $where .=" and up_time <= ".strtotime($uptime_to);



    if($is_online)
        $where .=" and is_online = '$is_online' ";


    if($uname)
        $where .=" and uname = '$uname' ";














    return $where;
}