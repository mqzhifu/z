<?php

// * 操作redis
class Mod_Tools_sms extends Mod_Tools {

    static $prefix = null;
    static $redis = null;
    protected function run() {

    }



    function send($mobile,$rule_id,$replace_content = null,$uid = 0){
        $check = $this->check($mobile,$rule_id);
        if($check['code'] != 200){
            return $check;
        }
        $rule = MooController::get('Obj_Mobile_rule')->getById($rule_id);

        $content = $rule['content'];
        if(!$content){
            return $this->err("content is null",40001);
        }

        $authCode = '';
        $ruleCodeTure = [
            1,
        ];

        if($replace_content){
            if (is_array($replace_content)) {
                foreach($replace_content as $k => $v){
                    if (in_array($rule_id, $ruleCodeTure)) {
                        $authCode .= $v;
                    }
                    $content = str_replace($k,$v,$content);
                }
            }else{
                if (in_array($replace_content, $ruleCodeTure)) {
                    $authCode = $replace_content;
                }
                $content = str_replace('XXX',$replace_content,$content);
            }
        }

        $res = MooController::get('Mod_Tools_Send')->sms($mobile, $content);
        if($res){
            $status = 1;
        }else{
            $status = 2;
        }
        $data = array(
            'mobile_rule_id' => $rule_id,
            'uid' => $uid,
            'content' => $content,
            'status' => $status,
            'a_time' => time(),
            'mobile' => $mobile,
            'IP' => MooController::get('Mod_Tools_getAreaInfo')->realIp(),
            'errinfo' => null,
            'type' => 1,
        );

        MooController::get('Obj_Mobile_log')->addOne($data);

        MooController::get('Obj_Mobile_verify')->handleInfo(['status' => 2],"rule_id = '{$rule_id}' AND status = 0");

        $sql = [
            'uid' => $uid,
            'code' => $authCode,
            'fail_time' => time() + 3600,
            'a_time' =>  time(),
            'status' => 0,
            'rule_id' => $rule_id,
            'mobile' => $mobile,
        ];

        MooController::get('Obj_Mobile_verify')->handleInfo($sql);

        if($status == 1){
            return $this->ok();
        }else{
            return $this->err('运营商发送失败',40004);
        }

    }

}
