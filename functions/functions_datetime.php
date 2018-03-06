<?php
//取得<周>相关信息
function get_week_info($gdate = ""){
    if(!$gdate) $gdate = date("Y-m-d");
    $w = date("w", strtotime($gdate));//取得一周的第几天,星期天开始0-6
    $dn = $w - 1;
// 	if(!$dn)$dn = 6;
    //本周开始日期
    $st = date("Y-m-d", strtotime("$gdate -".$dn." days"));
    //本周结束日期
    $en = date("Y-m-d", strtotime("$st +6 days"));
    //上周开始日期
    $last_st = date('Y-m-d',strtotime("$st - 7 days"));
    //上周结束日期
    $last_en = date('Y-m-d',strtotime("$st - 1 days"));
    return array('start_date'=>$st,'end_date'=> $en,'last_start_date'=> $last_st,'last_end_date'=>$last_en);//返回开始和结束日期
}
//获取一个月的最后一天
function get_month_last_day($year,$month){
    if(substr($month,0,1) === 0)
        $month = substr($month,1,1);

    $rs = 0;
    switch ($month){
        case 1:
            $rs = 31;break;
        case 2:
            if($year % 4 == 0)
                $rs = 28;
            else
                $rs = 29;
            break;
        case 3:
            $rs = 31;break;
        case 4:
            $rs = 30;break;
        case 5:
            $rs = 31;break;
        case 6:
            $rs = 30;break;
        case 7:
            $rs = 31;break;
        case 8:
            $rs = 31;break;
        case 9:
            $rs = 30;break;
        case 10:
            $rs = 31;break;
        case 11:
            $rs = 30;break;
        case 12:
            $rs = 31;break;
        default:

    }

    return $rs;
}

function date_week($unixtime)
{
    $_week = date('N', $unixtime);
    $week = '星期日';
    switch ($_week) {
        case 1:
            $week = '星期一';
            break;
        case 2:
            $week = '星期二';
            break;
        case 3:
            $week = '星期三';
            break;
        case 4:
            $week = '星期四';
            break;
        case 5:
            $week = '星期五';
            break;
        case 6:
            $week = '星期六';
            break;
        default:
            $week = '星期日';
            break;
    }

    return $week;
}


function get24HourOption(){
    $time_between = "<option '全天均可'>全天均可</option>";
    for($i=8;$i<=20;$i++){
        $start = $i;
        if($i < 10)
            $start = "0".$i;

        $end = $i + 1;
        if($end < 10)
            $end = "0".$end;

        $str =$start.":00 - ".$end . ":00";
        $time_between .= "<option value='$str'>$str</option>";
    }
    return $time_between;
}

//格式化当前时间
function TimeFormat($time=0){
    $curtime=time();
    $diff = $curtime-$time;
    $str = '';
    if($diff<60){
        $str='刚刚';
    }elseif($diff<3600){
        $str=intval($diff/60).'分钟前';
    }elseif($diff<86400){
        $str=intval($diff/3600).'小时前';
    }
    /*elseif($diff>=86400 && date("Y",$curtime)==date("Y",$curtime)){
        $str=date("m月d日 H:i",$time);
    }*/
    else{
        $str=date("Y-m-d H:i",$time);
    }
    return $str;
}

