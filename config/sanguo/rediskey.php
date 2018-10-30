<?php
$key = array(
    'towerMap'=>array('key'=>'tower_map','expire'=>0 ),
    'jsonTotal'=>array('key'=>'json_total','expire'=>0 ),
    'userinfo'=>array('key'=>'uinfo','expire'=>0 ),
    'token'=>array('key'=>'token','expire'=> 30 * 24 * 60 * 60),
);
$GLOBALS['rediskey'] = $key;