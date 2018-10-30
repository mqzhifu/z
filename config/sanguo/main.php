<?php
$main = array(
    'sms'=>array(
        'url' => 'http://task.egret.com/task/push',
        'appid' => 'fdf18ca',
        'sign' => '5f33cfdf18caf644dff6d7c75a14d07a'
    ),
    'email'=>array(
//        'smtpHost' => "smtp.qq.com",
        'smtpHost' => 'smtp.exmail.qq.com',
        'name' => '白鹭游戏中心',
        'username' => "open@egret.com",
        "password" => "1@system",
        'fromEmail'=>'open@egret.com',
    ),
    //地图格子总数
    'map_block_total'=>12,
    //用于日志表 标识
    'typeKey'=>array(
        1=>'buyTower',//购买防御塔
        2=>'upgradeBoss',//升级BOSS
        3=>'beatMonster',//打怪
        4=>'task',//任务领取
        5=>'upgradeBase',//升级基地

    ),
    'loginAPIExcept'=>        $arr = array(
        array("login","WXGame",),
        array(   'index','apilist',),
        array(   'index','apitest',),
        array(   'index','getCodeDesc',),



    ),

    'tokenKey'=>'e65178de9e5543a1f3cffd00345da58f',
    'tokenSecret'=>'e65178de9e5543a1f3cffd00345da58f',
);

$GLOBALS['main'] = $main;