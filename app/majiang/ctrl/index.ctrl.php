<?php
class indexCtrl extends BaseCtrl{
    function index(){
        $uid = _g('uid');
        $this->assign('uid',$uid);

        $this->display("majiang.html");
    }


    function chi(){
        $group_id = _g('group_id');
        $uid = _g('uid');
        $from_uid = _g('from_uid');
        $take_record_num= _g('take_record_num');



        $rs = $this->_majiang->userChangeRecord($group_id,$uid,$from_uid,$take_record_num,'chi');
    }

    function peng(){

    }

    function gang(){

    }

    function an_gang(){

    }

    function hu(){

    }

    //用户取消准备
    function unready(){
        LogLib::app(" ctrl unready func: ready ===================\n");

        $uid = _g('uid');
        $rs = $this->_majiang->userCancelReady($uid);

        LogLib::app($rs);

        echo json_encode($rs);
    }

    //用户准备
    function ready(){
        LogLib::app(" ctrl ready func: ready ===================\n");

        $uid = _g('uid');
        $room_id = _g('room_id');

        $rs = $this->_majiang->userReady($uid);

        LogLib::app($rs);

        echo json_encode($rs);
    }
    //生成一个组
    function makeGroup(){
        LogLib::app(" ctrl make_group func: ready ===================\n");


        $rs = $this->_majiang->makeGroup();
        var_dump($rs);exit;
    }
    //获取庄家ID
    function getDealer(){
        $room_id = _g("room_id");
        $uid = _g('uid');

        $rs = $this->_majiang->getDealerInfo($room_id,$uid);
        echo json_encode($rs);
    }
    //打骰子
    function takeDealer(){
        $uid = _g("uid");

        $rs = $this->_majiang->userDice($uid);

        LogLib::app($rs);

        echo json_encode($rs);

    }

//当用户掉线后，重新渲染页面
    function userRecover(){
        $uid = _g("uid");
//        $group_id = _g('group_id');

        $rs = $this->_majiang->userRecover($uid);
        LogLib::app($rs);
        var_dump($rs);exit;
    }

    //用户进入后，初始页面
    function init(){
        $uid = _g("uid");
        $rs = $this->_majiang->userInit($uid);

        echo json_encode($rs);
    }
}