<?php
//用户基类~ 注册   等
class UserLib{
    public $redisCacheUser = 1;
    function register($name,$ps,$type = 0){
        if(!$name){
            return out_pc(8009);
        }

        if(!$ps){
            return out_pc(8010);
        }


        if(!$type){
            $type = $this->calcTypeByUname($name);
        }else{
            if(!UserModel::keyInRegType($type)){
                return out_pc(8103);
            }
        }
        if(!FilterLib::regex($ps,'md5')){
            return out_pc(8102);
        }

        if($type == UserModel::$_type_cellphone){
            $uniq = $this->isCellphoneUnique($name);
        }elseif($type == UserModel::$_type_email){
            $uniq = $this->isEmailUnique($name);
        }elseif($type == UserModel::$_type_name){
            $uniq = $this->isNameUnique($name);
        }

        if(!$uniq){
            return out_pc(6003);
        }

        //这里要再加密一次，防止MD5 碰撞
        $ps = md5($ps);

        $data = array(
            "ps"=>$ps,
            "a_time"=>time(),
            "name"=>$name,
            "type"=>$type,
        );

        if($type == UserModel::$_type_cellphone){
            $data['cellphone'] = $name;
        }

        $area= AreaLib::getByIp();
        $data['province'] =$area['province'];
        $data['city'] =$area['city'];
        $data['country'] =$area['country'];
        $data['IP'] =$area['IP'];


        $id = UserModel::db()->add($data);
        return out_pc(200,$id);

    }
    //手机 邮箱  用户名 登陆
    function login($name,$ps,$type = 0){
        if(!$name){
            return out_pc(8009);
        }

        if(!$ps){
            return out_pc(8010);
        }

        if(!FilterLib::regex($ps,'md5')){
            return out_pc(8102);
        }

        if(!$type){
            $type = $this->calcTypeByUname($name);
        }else{
            if(!UserModel::keyInRegType($type)){
                return out_pc(8103);
            }

            if($type == UserModel::$_type_cellphone){
                $rs = FilterLib::regex($name,'phone');
                if(!$rs){
                    return out_pc(8100);
                }
            }elseif($type == UserModel::$_type_email){
                $rs = FilterLib::regex($name,'email');
                if(!$rs){
                    return out_pc(8101);
                }
            }
        }

        if($type == UserModel::$_type_cellphone){
            $where = " cellphone = '$name' ";
        }elseif($type == UserModel::$_type_email){
            $where = " email = '$name' ";
        }if($type == UserModel::$_type_name){
            $where = " name = '$name' ";
        }

        $user = UserModel::db()->getRow($where);
        if(!$user){
            return out_pc(1006);
        }
        if($user['ps'] != $ps){
            return out_pc(8111);
        }

        $token = TokenLib::create($user['id']);
        $id  = LoginModel::add($user['id'],1);
        return out_pc(200,$token);
    }

    function thirdLogin($uid,$userinfo,$platform){
        return UserModel::db()->getRow("platform = '$platform' and openid = '$uid'");
    }

    function thirdReg($uid,$userinfo,$platform){
//        $data = array(
//            'platform'=>,'openid'=>,'name'=>,'base_level=>','base_exp'=>,'magic'=>,'sunflower'=>,'angry'=>,'boss_level'=>,'a_time'=>,''=>,
//        );
//
//        return $data;
    }

    function getUinfoById($uid){
        if($this->redisCacheUser){
            $uinfo = $this->getUserCache($uid);
            if($uinfo){
                return $uinfo;
            }
        }

        $user = UserModel::db()->getById($uid);
        if(!$user){
            return false;
        }

        if($this->redisCacheUser){
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid);
            $rs = RedisPHPLib::getServerConnFD()->hmset($key,$user);
            var_dump($rs);exit;
        }

        return $user;
    }
    //目前只允许修改：性别、生日 、学生、学历、教育、技能
    function upUserInfo($uid,$sex = null,$birthday = null,$school = null,$education = null,$skill = null,$country = null,$province = null,$city = null,$addr = null,$company = null,$telphone = null,$fax = null){
        $data = array();
        if($sex){
            if(UserModel::keyInSex($sex)){
                out_pc();
            }
            $data['sex'] = $sex;
        }

        if($birthday){
            if(!FilterLib::regex($birthday,'dateformat')){
                out_pc();
            }
            $data['birthday'] =$birthday;
        }

        if($school){
            $data['school'] = $school;
        }

        if($education){
            $data['education'] = $education;
        }

        if($skill){
            $data['skill'] = $skill;
        }

        if($country){
            $data['country'] =$country;
        }

        if($province){
            $data['province'] = $province;
        }

        if($city){
            $data['city'] = $city;
        }

        if($addr){
            $data['addr'] = $addr;
        }

        if($company){
            $data['company'] = $company;
        }

        if($telphone){
            $data['telphone'] = $telphone;
        }

        if($fax){
            $data['fax'] = $fax;
        }

        if($data){
            return out_pc(8119);
        }

        UserModel::db()->upById($uid,$data);
        if($this->redisCacheUser){
            RedisPHPLib::hmset();
        }

        return out_pc(200);
    }
    function isCellphoneUnique($cellphone){
        $rs = UserModel::db()->getRow(" cellphone = '$cellphone'");
        if($rs){
            return false;
        }
        return true;
    }

    function isEmailUnique($email){
        $rs = UserModel::db()->getRow(" email = '$email'");
        if($rs){
            return false;
        }
        return true;
    }

    function isNameUnique($uname){
        $rs = UserModel::db()->getRow(" name = '$uname)'");
        if($rs){
            return false;
        }
        return true;
    }

    function getUserCache($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid);
        return RedisPHPLib::getServerConnFD()->hGetAll($key);
    }

    function upCacheUinfoByField($uid,$fields){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid);
        $rs = RedisPHPLib::getServerConnFD()->hmset($key,$fields);
        return $rs;
    }

    function calcTypeByUname($name){
        $rs = FilterLib::regex($name,'phone');
        if($rs){
            $type = 1;
        }elseif(FilterLib::regex($name,'email')){
            $type = 4;
        }else{
            $type = 5;
        }
        return $type;
    }
}