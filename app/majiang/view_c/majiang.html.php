<html>

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>

<body>

<table>

    <tr>
        <td></td>
        <td><span id="dealer_top"></span><span id="dir_top"></span><span id="top_record">玩家3</span></td>
        <td></td>
    </tr>

    <tr>
        <td><span id="dealer_left"></span><span id="dir_left"></span><span id="left_record">玩家2</span></td>
        <td>废牌区</td>
        <td><span id="dealer_right"></span><span id="dir_right"></span><span id="right_record">玩家4</span></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <a href="javascript:void(0);" id="ready_el" style="display: none" onclick="ready()">报名</a>
            <a href="javascript:void(0);" id="ready_cancel_el" style="display: none" onclick="unready()">取消报名</a>
            <a href="javascript:void(0);" id="dice_el" style="display: none" >等待庄家打骰子</a>
            <a href="javascript:void(0);" id="already_el" style="display: none"  >准备中，等待其它玩家准备</a>
        </td>
        <td></td>
    </tr>


    <tr>
        <td></td>
        <td> <span id="dealer_bottom"></span><span id="dir_bottom"></span> <span id="self_record">玩家1</span></td>
        <td></td>
    </tr>


</table>



</body>


<!--<a href="#" onclick="ready(1)">A准备</a>-->
<!--<a href="#" onclick="ready(2)">B准备</a>-->
<!--<a href="#" onclick="ready(3)">C准备</a>-->
<!--<a href="#" onclick="ready(4)">D准备</a>-->



<!--<br/>-->
<!--<a href="#" onclick="getDealer(1)">获取庄家UID_BY_ROOMID=1</a>-->
<!--<br/>-->

<!--<a href="#" onclick="takeDealer(1,1)">打骰子 uid=1,groupid=1</a>-->

<!--<br/>-->
<!--<a href="#" onclick="recover(1)">初始化牌局 uid=1</a>-->




<script src="/www/assets/global/plugins/jquery.min.js" type="text/javascript" ></script>

<script>
    var uid = <?php if($uid){ ?><?php echo $uid; ?><?php }else{ ?>0<?php } ?>;

    function filterUid(uid){
        if(!uid || uid == 0 || typeof(uid) =="undefined" || isNaN(uid) )
            return false;

        var re = /^[1-9]+$/ ;
        return re.test(uid)

    }
</script>

<script>

    init();

//=============接收服务端 PUSH============

    //大家都准备后，同步4家-信息进入打骰子信息
    function rycDice(){

    }
    //打骰子结束后，同步4家-进入发牌阶段
    function rycGameStart(){

    }
    //同步4家-，该谁抓牌
    function rycGetOneRecord(){

    }
    //用户打一张牌后，另外3家，可以吃碰的，S同步到C端，等待响应
    function rycRecordChangeRespond(){

    }
    //接收另外三家 吃碰胡 等信息
    function rycRecordChangeRs(){

    }



//=========================

    function init(){
        if(!filterUid(uid))
            return alert('uid 错误');

        $.ajax({
            type: "GET",
            url: "/majiang?ac=init&uid="+uid,
            dataType: "json",
            success: function(data){
                if(data.code !=200)
                    return alert(data.msg);

                var dd = data.msg;
                if(dd.status == -1){
                    //可以-报名
                    $("#ready_el").css('display','block');

                }else if(dd.status == 0){
                    $("#already_el").css('display','block');
                    $("#ready_cancel_el").css('display','block');
                    //已经报名成功，等待其它3家准备
                }else if(dd.status == 1){
                    //已初始化数据，建立了组，等待庄家-打骰子
                    $("#dice_el").css('display','block');
                    if(dd.data.dice_dealer_uid == uid){
                        $("#dice_el").html('打骰子');
                        $("#dice_el").bind('click',function(){
                            takeDealer();
                        });
                    }

                    initDices(dd.data.user_sort,dd.data.dealer_uid);
                    initDirection(dd.data.user_sort,dd.data.user_dir);
                }else if(dd.status == 2){
//                    alert(dd.data.self_record);
                    //进行中

                    //东南西北
                    initDirection(dd.data.user_sort,dd.data.user_dir_desc);
                    //庄家及打骨子数
                    initDices(dd.data.user_sort,dd.data.dealer_uid);
                    //4家手牌
                    initUserRecord(dd.data.user_record,dd.data.user_sort);
                    //4列未抓的牌的情况
                    init4LineRecord();
                    //废弃牌的处理
                    initTrash();
                    //抓牌-打牌-等待
                    initCurrentStatus(uid,dd.data.current_catch_uid,dd.data.current_throw_uid);

                }
            }
        });
    }
    //接收服务端发送的消息，更新当前页面信息
    function receiveSysMsg(){
        var status = 0;
        if(status == 1){
            //其它用户已准备

        }else if(status == 2){
            //4位用户都已准备初始化牌局
        }else if(status == 3){
            //牌已经发放完毕，抓牌-等待
        }else if(status == 4){
            //用户已经打牌
        }else if(status == 5){
            //确认 吃碰胡杠
        }
    }


    function initCurrentStatus(uid,current_uid){
        if(uid == current_uid){//当前用户要抓牌

        }else{

        }

    }
    //初始化用户手里的牌
    function initUserRecord(user_record,user_sort) {
        var html = "";
        html = getUserRecordHtml(user_record[user_sort[0]], 1,0);
        $("#self_record").after(html);

        html = getUserRecordHtml(user_record[user_sort[1]], 0,1);
        $("#left_record").after(html);

        html = getUserRecordHtml(user_record[user_sort[2]], 0,0);
        $("#top_record").after(html);

        html = getUserRecordHtml(user_record[user_sort[3]], 0,1);
        $("#right_record").after(html);

    }
    //初始化 页面中的 4列 牌
    function init4LineRecord(){

    }
    //初始化-东南西北
    function initDirection(data,desc){
        $("#dir_bottom").html(desc[data[0]]);
        $("#self_record").html("玩家" + data[0]);


        $("#dir_left").html(desc[data[1]]);
        $("#left_record").html("玩家" + data[1]);


        $("#dir_top").html(desc[data[2]]);
        $("#top_record").html( "玩家" + data[2]);

        $("#dir_right").html(desc[data[3]]);
        $("#right_record").html("玩家" + data[3]);

    }
    //初始化-骰子
    function initDices(user_sort,dealer_uid){
        var s = 0;
        for(var i=0;i<user_sort.length;i++){
            if(user_sort[i] == dealer_uid){
                s = i;
                break;
            }
        }

        var n = '庄家';
        if(s == 0){
            $("#dealer_bottom").html(n);
        }else if( s == 1){
            $("#dealer_left").html(n);
        }else if( s == 2){
            $("#dealer_top").html(n);
        }else if( s == 3){
            $("#dealer_right").html(n);
        }
    }
    //初始化-手牌-吃差胡杠
    function initUserSelfChangeRecord(){

    }
    //初始化废弃的牌
    function initTrash(){

    }

    //自己的手牌，需要都显示出来，另外三家不能显示（除 ：吃碰明杠），三家要依次按照方向，放牌
    //dir:方向，0横着 1竖着
    function getUserRecordHtml(data,is_self,dir){


        var html = "<table>";

        if(dir == 0){
            html += "<tr>";
            for(var i =0;i<data.length;i++){
                html += "<td>" + data[i].title + "</td>";
            }
            html += "</tr>";
        }else{
            for(var i =0;i<data.length;i++){
                html += "<tr><td>" + data[i].title + "</td></tr>";
            }
        }

        html += "</table>";
//        alert(html);
        return html;
    }

//==================================================
    //报名
    function ready(){
        if(!filterUid(uid))
            return alert('uid 错误');

        $.ajax({
            type: "GET",
            url: "/majiang?ac=ready&uid="+uid,
            dataType: "json",
            success: function(data){
                if(data.code !=200)
                    return alert(data.msg);

                $("#ready_el").css('display','none');
                $("#ready_cancel_el").css('display','block');
                $("#already_el").css('display','block');

            }
        });
    }
    //取消 报名
    function unready(){
        if(!filterUid(uid))
            return alert('uid 错误');

        $.ajax({
            type: "GET",
            url: "/majiang?ac=unready&uid="+uid,
            dataType: "json",
            success: function(data){
                if(data.code !=200)
                    return alert(data.msg);

                $("#ready_el").css('display','block');
                $("#ready_cancel_el").css('display','none');
                $("#already_el").css('display','none');

            }
        });
    }
    //打骰子
    function takeDealer(){
        $.ajax({
            type: "GET",
            url: "/majiang?ac=takeDealer&uid="+uid ,
            dataType: "json",
            success: function(data){
                alert(data.msg);
            }
        });
    }
    //打牌，倒计时
    function recordTimeout(){

    }
    //托管
    function auto(){

    }
    //取消托管
    function autoCancel(){

    }
//###############################渲染####################################
    //鼠标经过 每周牌的效果
    function renderRecordOnmouse(){

    }
    //骰子动的效果
    function renderDice(){

    }
    //弹出 吃杠胡  然后，将手牌 3张为一组
    function renderChange(){

    }
    //打完骰子，分发4个用户牌的效果
    function renderInitSendRecord(){

    }

//###############################渲染####################################
</script>


<script>
    var wsServer = 'ws://39.107.127.244:9502';
    var websocket = new WebSocket(wsServer);
    websocket.onopen = function (evt) {
        msg = websocket.readyState;
        alert(msg);
        websocket.send(uid);
    };

//    function send_server(){
        //向服务器发送数据
//        websocket.send('aaa');
//    }
//    //监听关闭
//        websocket.onclose = function (evt) {
//            console.log("Disconnected");
//        };
//
    //onmessage 监听服务器数据推送
    websocket.onmessage = function (evt) {
        alert('Retrieved data from server: ' + evt.data);
    };
    //监听连接错误信息
    websocket.onerror = function (evt, e) {
        alert('Error occured: ' + evt.data);
    };

//    send_server();

</script>





</html>