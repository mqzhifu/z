<?php
// * 发送短信
class SmsLib  {
    public $rule = null;
    //真的发送，走第3方接口
    function realSend($mobile, $content) {
        $sms = $GLOBALS['main']['sms'];
        $data = array(
            'from' => 'liaozhan',
            'mobile' => $mobile,
            'appid' => $sms['appid'],
            'content' => $content
        );
        ksort($data);
        $json = json_encode($data);
        $sign = md5("msg.send{$json}{$sms['sign']}");
        $urlencodeJson = urlencode($json);
        $param = array(
            'name' => 'msg.send',
            'data' => $urlencodeJson,
            'sign' => $sign
        );

        $rs = CurlLib::send($sms['url'],2,$param);
        if($rs['code'] != 200){
            return $rs;
        }

        $res = json_decode($rs['msg'], true);
        if (is_array($res) && isset($res['code']) && $res['code'] == 0) {
            return out_pc(200,$res);
        }else{
            return out_pc(5009,$res);
        }
    }


}
