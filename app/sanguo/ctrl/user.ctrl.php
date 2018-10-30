<?php

class UserCtrl extends BaseCtrl implements UserInf {

    function pushUserInfo($nickname = null,$gender =null,$avatarUrl = null,$country = null,$province = null,$city = null){
        $data = [];

        if($gender){
            $data['gender'] = $gender;
        }

        if($avatarUrl){
            $data['avatar'] = urldecode($avatarUrl);
        }

        if($nickname){
            $data['name'] = $nickname;
        }

        $area= AreaLib::getByIp();

        if($country){
            $data['country'] = $country;
        }else{
            $data['country'] =$area['country'];
        }

        if($province){
            $data['province'] = $province;
        }else{
            $data['province'] =$area['country'];
        }

        if($city){
            $data['city'] = $city;
        }else{
            $data['city'] =$area['city'];
        }

        $data['IP'] =$area['IP'];


        UserModel::db()->setNames('utf8mb4');
        UserModel::db()->upById($this->uid,$data);

        out_ajax(200,"ok");
    }

    function getOne(){
        out_ajax($this->uinfo);
    }
}