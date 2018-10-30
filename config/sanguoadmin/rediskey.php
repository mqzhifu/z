<?php
$key = array(
    'userinfo'=>array('key'=>'uinfo','expire'=>0 ),
    'blackip'=>array('key'=>'blackip','expire'=>600),
    'oauthtoken'=>array('key'=>'oauthtoken','expire'=>6000),
    'verifierimgcode'=>array('key'=>'verifierimgcode','expire'=>6000),
    'upPScode'=>array('key'=>'upPScode','expire'=>600),
    'jsonTotal'=>array('key'=>'json_total','expire'=>0 ),
);
$GLOBALS['rediskey'] = $key;