<?php
$arr = array(
    'buff'=>array('title'=>'玩家技能','field'=>array('name'=>'名称','time'=>'持续时间，以秒为单位','attackspeedp'=>'攻击速度加成','攻击加成'=>'描述','inmoneyp'=>'死亡金币收益加成','infoodp'=>'死亡粮草收益加成','inexpp'=>'死亡经验收益加成')),
    'tower'=>array('title'=>'英雄塔','field'=>array('id'=>'唯一ID标识符，连表用','level'=>'等级','name'=>'名称','price'=>'购买价格','pricepower'=>'价格加成系数','demnum'=>'达到本级别，需要小塔的数量','demtower'=>'达到本级别，需要玩家level',
        'demlevel'=>'达到本级别，需要玩家level','demfood'=>'达到本级别，需要玩家粮草数量','skill'=>'技能ID','resname'=>'资源名称','iconname'=>'头像资源名称')),
    'skill'=>array('title'=>'技能','field'=>array('id'=>'唯一ID标识符，连表用','name'=>'名称','time'=>'持续时间，以秒为单位','speedpower'=>'速度减持系数','probability'=>'触发概率','attackpower'=>'攻击加成','attacknum'=>'攻击次数 加成','des'=>'描述')),
    'home'=>array('title'=>'孵化器','field'=>array('level'=>'等级','exp'=>'当前等级达成经验值','attackpower'=>'攻击加成','des'=>'描述')),
    'monster'=>array('title'=>'怪物','field'=>array('id'=>'唯一ID标识符，连表用','level'=>'等级','name'=>'怪物名称','resname'=>'资源名称','type'=>'1为小怪2为bose')),

);

return $GLOBALS['jsonindex'] = $arr;