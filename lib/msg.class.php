<?
//站内信-个人
class Table_msg extends Table {
    public $_table = "msg" ;
    public $_primarykey = "id";

    public static $_static = false;

    // 1用户之间私信; 2商家群发; 3系统私信; 4用户组; 5系统群发; 6系统部分发; 7指定标签发送. 
    public static $_TYPE_P2P = 1; // person to person
    public static $_TYPE_S2S = 2; // system to sellers
    public static $_TYPE_S2P = 3; // system to person    (seller)
    public static $_TYPE_S2G = 4; // system to group
    public static $_TYPE_S2A = 5; // system to all
    public static $_TYPE_S2X = 6; // system to x
    public static $_TYPE_S2T = 7; // system to tag
    public static $_TYPE_NOTIFY = 8; //system to person  (member) 

    public static function inst() {
        if(false == self::$_static) {
            self::$_static = new self();
        }
        return self::$_static;
    }
    //redis-持久化-部分群发
    function addOneByPart($msg_text_id,$uid ){
        $msg_text = Table_msg_group::inst()->getInfo($msg_text_id);
        if($msg_text){
            $data = array(
                'send_uid'=>$msg_text['send_uid'],
                'to_uid'=>$uid,
                'title'=>$msg_text['title'],
                'type'=>$msg_text['type'],
                'content_id'=>$msg_text_id,
                'add_time'=>time(),
//                'is_read'=>1,
            );

            Table_msg::inst()->addData($data)->add();
            $id = Table_msg::inst()->insertId();
            if($id){
                $key = usr_msg_part_key($uid);
                Sys_Redis::inst()->sRemove($key,$msg_text_id);
            }
        }
        return out(200,"ok",0,'pc');
    }

    /**
     * 为普通用户发一条系统通知
     */
    function addOneByMemberPoint($to_uid, $title, $content, $uid = '99999')
    {
    	$to_uid = intval($to_uid);
    	if (!$to_uid)
    		return out_err('invalid param to_uid '.$to_uid) ;

    	$data = array(
    			'content'=>$content,
    			'type'=>self::$_TYPE_NOTIFY,
    			'uid'=>$uid,
    	);
    	Table_msg_text::inst()->addData($data)->add();
    	$content_id = Table_msg_text::inst()->insertId();
    	if(!$content_id)
    		return out(505,"add msg_text in db is failed...",1,'pc');

    	$data = array(
    			'send_uid'=>$uid,
    			'to_uid'=>$to_uid,
    			'title'=>$title,
    			'type'=>self::$_TYPE_NOTIFY,
    			'content_id'=>$content_id,
    			'add_time'=>time(),
    			'is_read'=>0,
    			'platform_key'=>'',
    	);

    	$msg_id = Table_msg::inst()->addData($data)->add();
    	if(!$msg_id)
    		return out(505,"add msg in db is failed...",1);

    	$msg_id =   Table_msg::inst()->insertId();

    	$user_msg_unread_key = usr_msg_member_unread_key(  $to_uid );
    	$num = Sys_Redis::inst()->get($user_msg_unread_key);
    	if(!$num)
    		$num = 0;

    	Sys_Redis::inst()->set($user_msg_unread_key,++$num);

    	return out(200,$msg_id,0,'pc');
    }

    //点到点时，添加用这个
    //type,1:用户之间，3系统对用户
    function addOneByPoint($uid,$to_uid = 0 ,$title,$content,$type ,$platform_key= ""){
        $data = array(
            'content'=>$content,
            'type'=>$type,
            'uid'=>$uid,
        );
        Table_msg_text::inst()->addData($data)->add();
        $content_id = Table_msg_text::inst()->insertId();
        if(!$content_id)
            return out(505,"add msg_text in db is failed...",1,'pc');

        $data = array(
            'send_uid'=>$uid,
            'to_uid'=>$uid,
            'title'=>$title,
            'type'=>$type,
            'content_id'=>$content_id,
            'add_time'=>time(),
            'is_read'=>0,
            'platform_key'=>$platform_key,
        );

        if($to_uid)
            $data['to_uid'] = $to_uid;

        $msg_id = Table_msg::inst()->addData($data)->add();
        if(!$msg_id)
            return out(505,"add msg in db is failed...",1);

        $msg_id =   Table_msg::inst()->insertId();

        $user_msg_unread_key = usr_msg_unread_key(  $to_uid );
        $num = Sys_Redis::inst()->get($user_msg_unread_key);
        if(!$num)
            $num = 0;

        Sys_Redis::inst()->set($user_msg_unread_key,++$num);

        return out(200,$msg_id,0,'pc');
    }

    function delOneById($id,$type){
        $msg = Table_msg::inst()->noCache()->getInfo($id);
        if($msg){
//            if($msg['type'] != 1)
//                return out(610,"仅支持用户点条类型删除",1,'pc');

            if($type == 1)
                $data = array("to_del"=>1);
            else
                $data = array("send_del"=>1);

            if(!$msg['is_read'] && $type==1 ){
            	if (5==$msg['type']){
	                $unread_num = Sys_Redis::inst()->get(usr_msg_unread_key($msg['to_uid']));
	                if($unread_num){
	                    $unread_num = $unread_num - 1;
	                    if($unread_num <= 0)
	                        $unread_num = 0;
	                }else
	                    $unread_num = 0;
	                Sys_Redis::inst()->set(usr_msg_unread_key($msg['to_uid']),$unread_num);
            	}elseif (6==$msg['type']){
            		$unread_num = Sys_Redis::inst()->get(usr_msg_part_unread($msg['to_uid']));
            		if($unread_num){
            			$unread_num = $unread_num - 1;
            			if($unread_num <= 0)
            				$unread_num = 0;
            		}else
            			$unread_num = 0;

            		Sys_Redis::inst()->set(usr_msg_part_unread($msg['to_uid']),$unread_num);
            	}elseif ( 4 == $msg['type'] && !empty($msg['to_gid']) ){
	            	// lixin@20151125
	            	$__key = sys_user_group_msg_unread($msg['to_gid'], $msg['to_uid']) ; //
	            	$unread_num = Sys_Redis::inst()->get( $__key );
	            	if($unread_num){
	            		$unread_num = $unread_num - 1;
	            		if($unread_num <= 0)
	            			$unread_num = 0;
	            	}else
	            		$unread_num = 0;

	            	Sys_Redis::inst()->set($__key,$unread_num);
	            }
            }

            Table_msg::inst()->addData($data)->edit($id);

            return out(200,'ok',0,'pc');
        }
        return out(611,"ID not in db",1,'pc');
    }

    /**
     * 获取未读消息数
     * @param unknown $uid
     * @param number $type<br>
     * 0:所有<br>
     * 1用户之间私信、系统私信<br>
     * 2商家群发、用户组、系统群发、系统部分发<br>
     * 3用户组发<br>
     * 4用户标签发送<br>
     * @return int 未读消息数
     */
    function getUserUnreadNum($uid,$type = 0){
        $unread_num = 0;
        if(!$type){
            /*
        	$point_unread = Sys_Redis::inst()->get(usr_msg_unread_key(  $uid ));
            if(!$point_unread){
                if($point_unread === false){
                    $num =  Table_msg::inst()->noCache()->where("  to_uid = $uid and ( type = 1 or type = 3 ) and to_del = 0 and is_read = 0 ")->selectCount();
                    if($num){
                        Sys_Redis::inst()->set(usr_msg_unread_key(  $uid ),$num);
                        $point_unread = $num;
                    }else
                        $point_unread = 0;
                }else
                    $point_unread = 0;
            }
            */

            $point_unread = $this->getUserPointUnreadNum($uid);
            $part_unread = Table_msg_group::inst()->getPartUnread($uid);
            $group_msg_unread = Table_msg_group::inst()->getGroupUnread($uid);
            $ugroup_msg_unread = Table_msg_group::inst()->getSysUserGroupUnread($uid);

            $unread_num = $point_unread + $part_unread + $group_msg_unread + $ugroup_msg_unread;
        }elseif($type == 1){
        }elseif($type == 2){
        }elseif($type == 3){
        	$ugroup_msg_unread = Table_msg_group::inst()->getSysUserGroupUnread($uid);
            $unread_num = $ugroup_msg_unread ;
        }elseif($type == 4){
        	$point_unread = $this->getMemberPointUnreadNum($uid);
        	$tag_unread = Table_msg_tagtext::inst()->getSysUserTagUnread($uid);
        	//$unread_num = Table_msg_tagtext::inst()->getSysUserTagUnread($uid);
        	$unread_num = $point_unread + $tag_unread ;
        }
        return $unread_num;
    }

    public function getMemberPointUnreadNum( $uid ) {
    	$point_unread = Sys_Redis::inst()->get(usr_msg_member_unread_key(  $uid ));
    	if(!$point_unread){
    		if($point_unread === false){
    			$num =  Table_msg::inst()->noCache()->where("  to_uid = $uid and type = ".Table_msg::$_TYPE_NOTIFY." and to_del = 0 and is_read = 0 ")->selectCount();
    			if($num){
    				Sys_Redis::inst()->set(usr_msg_member_unread_key(  $uid ),$num);
    				$point_unread = $num;
    			}else
    				$point_unread = 0;
    		}else
    			$point_unread = 0;
    	}

    	return $point_unread;
    }

	/**
	 * 获取用户私信未读数  type 1/3  用户私信/系统私信
	 * @param int $uid
	 */
    public function getUserPointUnreadNum( $uid ) {
    	$point_unread = Sys_Redis::inst()->get(usr_msg_unread_key(  $uid ));
    	if(!$point_unread){
    		if($point_unread === false){
    			$num =  Table_msg::inst()->noCache()->where("  to_uid = $uid and ( type = 1 or type = 3 ) and to_del = 0 and is_read = 0 ")->selectCount();
    			if($num){
    				Sys_Redis::inst()->set(usr_msg_unread_key(  $uid ),$num);
    				$point_unread = $num;
    			}else
    				$point_unread = 0;
    		}else
    			$point_unread = 0;
    	}

    	return $point_unread;
    }

    //用户已读/已持久化MYSQL-系统群发集合
    function getMsgGroup($uid){
        $key = usr_group_msg_key($uid);
        $sys_msg_redis = Sys_Redis::inst()->sMembers($key);
        if(!$sys_msg_redis){
            $sys_msg_text = Table_msg::inst()->autoClearCache()->where(" type = 5 and to_uid = $uid ")->order(" id desc")->select();
            if($sys_msg_text){
                $rs = array();
                foreach($sys_msg_text as $k=>$v ){
                    Sys_Redis::inst()->sAdd($key,$v['content_id']);
                    $rs[] = $v['content_id'];
                }

                $sys_msg_redis = $rs;
            }
        }

        return $sys_msg_redis;
    }

    /**
     * 用户已读/已持久化MYSQL-系统分标签发集合
     * @param unknown $tid
     * @param unknown $uid
     */
    public function getMsgUserTag($tid, $uid){
    	$key = sys_user_tag_msg_key($tid, $uid);
    	$sys_msg_redis = Sys_Redis::inst()->sMembers($key);
    	if(!$sys_msg_redis){
    		$sys_msg_text = Table_msg::inst()->noCache()->where(" type = 7 and to_tid = {$tid} and to_uid = $uid ")->order(" id desc")->select();
    		if($sys_msg_text){
    			$rs = array();
    			foreach($sys_msg_text as $k=>$v ){
    				Sys_Redis::inst()->sAdd($key,$v['content_id']);
    				$rs[] = $v['content_id'];
    			}

    			$sys_msg_redis = $rs;
    		}
    	}

    	return $sys_msg_redis;
    }

    /**
     * 用户已读/已持久化MYSQL-系统分组发集合
     * @author lixin
     */
    function getMsgUserGroup($gid, $uid){
    	$key = sys_user_group_msg_key($gid, $uid);
    	$sys_msg_redis = Sys_Redis::inst()->sMembers($key);
    	if(!$sys_msg_redis){
    		$sys_msg_text = Table_msg::inst()->noCache()->where(" type = 5 and to_gid = {$gid} and to_uid = $uid ")->order(" id desc")->select();
    		if($sys_msg_text){
    			$rs = array();
    			foreach($sys_msg_text as $k=>$v ){
    				Sys_Redis::inst()->sAdd($key,$v['content_id']);
    				$rs[] = $v['content_id'];
    			}

    			$sys_msg_redis = $rs;
    		}
    	}

    	return $sys_msg_redis;
    }

    function getList($uid,$page = 1,$length,$type,$cate){
        if(!$uid)
            return 0;

        if(!$length)
            $length = 10;

        $where = " 1=1 ";
        Table_msg_group::inst()->writeOnRead($uid);

        if($cate){
            $where .= " and `type` IN ( {$cate} ) ";
        }
        if($type){
            if($type == 1){
                $where .= " and to_del = 0 and to_uid = $uid";
            }else{
                $where .= " and send_del = 0 and send_uid = $uid";
            }
        }else{
            $where .= " and to_del = 0 and send_del = 0 and ( to_uid = $uid or send_uid = $uid ) ";
        }


        $list = Table_Msg::inst();
        $list = $list->autoClearCache();
        $list = $list->pageSize($length);
        $list = $list->page($page);

        $list = $list->where($where);
        $list = $list->order("`add_time` DESC");
        $list = $list->selectPage();




        $rs  = array('pagecount'=>0,'datacount'=>0,'list'=>array());
        if($list['res']){
            if(  $page > $list['pagedata']['pagecount']   ){
                $rs  = array(
                    'pagecount'=>(int)$list['pagedata']['pagecount'],
                    'datacount'=> (int)$list['pagedata']['datacount'],
                    'list'=>array(),
                );
            }else{
                foreach($list['res'] as $k=>$v ){
                    if($type == 1)
                        $user = Table_user::inst()->getInfo($v['send_uid']);
                    else
                        $user = Table_user::inst()->getInfo($v['to_uid']);

                    if($user){
                        $list['res'][$k]['nickname'] = $user['nikename'];
                        $list['res'][$k]['avatar'] = getuseravart($user['id']);
                    }


                    $content = Table_msg::inst()->getMsgContent($v['content_id'],$v['type']);
                    $list['res'][$k]['content'] = $content['content'];
                }
                $rs  = array(
                    'pagecount'=>(int)$list['pagedata']['pagecount'],
                    'datacount'=>(int) $list['pagedata']['datacount'],
                    'list'=>$list['res'],
                );
            }

        }

        return $rs;

    }


    function getMsgContent($id,$type){
        if($type == self::$_TYPE_P2P || $type == self::$_TYPE_S2P || $type == self::$_TYPE_NOTIFY)
            $content = Table_msg_text::inst()->getInfo($id);
        else if ($type == Table_msg::$_TYPE_S2T)
        	$content = Table_msg_tagtext::inst()->getInfo($id);
        else
            $content = Table_msg_group::inst()->getInfo($id);

        return $content;
    }

    function upUnread($uid,$ids,$del = 0){
        if(!$ids)
            return 0;
        if(!$uid)
            return 0;

        $msg_ids = explode(",",$ids);
//         $num = count($msg_ids);
		$num = 1;
        foreach($msg_ids as $k=>$v ){
            $msg = Table_msg::inst()->getInfo($v);
            if(!$msg)
                continue;
            if(empty($del)){
                if($msg['is_read'])//已读，无须其它处理
                    continue;
                if($msg['send_uid'] == $uid)//发件者查看，是不能算已读状态
                    continue;
            }
            if(1 == $msg['type'] || 3 == $msg['type']){
                $unread_num = Sys_Redis::inst()->get(usr_msg_unread_key($uid));
                if($unread_num){
                    $unread_num = $unread_num - $num;
                    if($unread_num <= 0)
                        $unread_num = 0;
                }else
                    $unread_num = 0;

                Sys_Redis::inst()->set(usr_msg_unread_key($uid),$unread_num);
            }elseif(8 == $msg['type']){
                $unread_num = Sys_Redis::inst()->get(usr_msg_member_unread_key($uid));
                if($unread_num){
                    $unread_num = $unread_num - $num;
                    if($unread_num <= 0)
                        $unread_num = 0;
                }else
                    $unread_num = 0;

                Sys_Redis::inst()->set(usr_msg_member_unread_key($uid),$unread_num);
            }elseif(5 == $msg['type']){
                $unread_num = Sys_Redis::inst()->get(usr_msg_group_unread($uid));
                if($unread_num){
                    $unread_num = $unread_num - $num;
                    if($unread_num <= 0)
                        $unread_num = 0;
                }else
                    $unread_num = 0;

                Sys_Redis::inst()->set(usr_msg_group_unread($uid),$unread_num);
            }elseif( 3  == $msg['type'] || 6  == $msg['type'] ){
                $unread_num = Sys_Redis::inst()->get(usr_msg_part_unread($uid));
                if($unread_num){
                    $unread_num = $unread_num - $num;
                    if($unread_num <= 0)
                        $unread_num = 0;
                }else
                    $unread_num = 0;

                Sys_Redis::inst()->set(usr_msg_part_unread($uid),$unread_num);
            }elseif ( 4 == $msg['type'] && !empty($msg['to_gid']) ){
            	// lixin@20151125
            	$__key = sys_user_group_msg_unread($msg['to_gid'], $uid) ; //
            	$unread_num = Sys_Redis::inst()->get( $__key );
            	if($unread_num){
            		$unread_num = $unread_num - $num;
            		if($unread_num <= 0)
            			$unread_num = 0;
            	}else
            		$unread_num = 0;

            	Sys_Redis::inst()->set($__key,$unread_num);
            }elseif ( 7 == $msg['type'] && !empty($msg['to_tid']) ){
            	// lixin@20151125
            	$__key = sys_user_tag_msg_unread($msg['to_tid'], $uid) ; //
            	$unread_num = Sys_Redis::inst()->get( $__key );
            	if($unread_num){
            		$unread_num = $unread_num - $num;
            		if($unread_num <= 0)
            			$unread_num = 0;
            	}else
            		$unread_num = 0;

            	Sys_Redis::inst()->set($__key,$unread_num);
            }
            if(empty($del)){
                Table_msg::inst()->addData(array('is_read'=>1))->where(" id = $v ")->edit();
            }else{
                Table_msg::inst()->addData(array('to_del'=>1))->where(" id = $v ")->edit();
            }

        }



        return 1;

    }

    function getOneDetail($uid,$id ,$return_type = 'ajax'){
        $msg = Table_msg::inst()->getInfo($id);
        if(!$msg)
            return  out(501,'id 错误，不在DB中',1,'pc');
        if($msg['to_uid'] != $uid && $msg['send_uid'] != $uid)
            return  out(501,'该信息不属于该UID',1,'pc');

        $content = Table_msg::inst()->getMsgContent($msg['content_id'],$msg['type']);
        $msg['content'] = $content['content'];
        return out(200,$msg,0,$return_type);
    }
}
