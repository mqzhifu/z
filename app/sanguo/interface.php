<?php
//基类
interface BaseInf{
    function initUserLoginInfoByToken();//初始化用户登陆信息BY-token
    function checkUserBlackList($uid);//判断用户是否在黑名单中|用户ID
    function checkIPBlackList();//判断请求IP 是否在黑名单中
    function checkAPIRequestCnt();//checkAPIRequestCnt
    function loginAPIExcept();//判断请求接口是否需要登陆
}
//用户
interface UserInf{
    function getOne();//获取一个用户信息
    function pushUserInfo($nickname = null,$gender =null,$avatarUrl = null ,$country = null,$province = null,$city = null);//推送用户信息|昵称##性别##头像##国家##省##市
}
//默认
interface IndexInf{
    function index();//默认页，没啥用
}
//登陆
interface LoginInf{
    function WXGame($code);//微信登陆|微信CODE换取OPENID
}
//任务
interface TaskUserInf{
    function getList();//获取用户当天-任务列表
    function getReward($taskId);//用户领取一个任务的奖励|任务ID
}
//银行
interface Bank{
    function addGoldcoin($num,$type,$isShare = 0,$memo = '');//增加金币|数量##分类##是否为共享##备注
    function lessGoldcoin($num,$type,$isShare = 0,$memo = '');//减少金币|数量##分类##是否为共享##备注
    function addMagic($num,$type,$isShare = 0,$memo = '');//增加魔法值|数量##分类##是否为共享##备注
    function lessMagic($num,$type,$isShare = 0,$memo = '');//减少魔法值|数量##分类##是否为共享##备注
    function addSunflower($num,$type,$isShare = 0,$memo = '');//增加向日葵|数量##分类##是否为共享##备注
    function lessSunflower($num,$type,$isShare = 0,$memo = '');//减少向日葵|数量##分类##是否为共享##备注
}
//游戏
interface GameInf{
    function bossUpgrade();//boss升级
    function baseUpgrade();//基地升级
    function mergeTower($srcTowerId,$targetTowerId,$srcMapId,$targetMapId);//合并塔
    function baseAddExp($num,$type,$isShare = 0,$memo = '');//基地加血|数量##分类##是否为共享##备注
    function baseLessExp($num,$type,$isShare = 0,$memo = '');//基地减少血|数量##分类##是否为共享##备注
    function useAngry($num,$type,$isShare = 0,$memo = '');//增加愤怒值|数量##分类##是否为共享##备注
    function addAngry($num,$type,$isShare = 0,$memo = '');//减少愤怒值|数量##分类##是否为共享##备注
    function getMapInfo();//获取地图信息
    function setMapInfo($mapInfo);//设置地址信息
}
//礼包
interface GiftBag{
    function getOne($giftBagId);//取得一个礼包|礼包ID
    function getReward($giftBagId);//领取一个礼包|礼包ID
}
//第3方回调
interface CallbackInf{
    function getInfo();//此处主要是接微信用，暂时不考虑
}

