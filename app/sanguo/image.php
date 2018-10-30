<?php

/**
 * Description of image
 * 往cdn同步图片
 * @author tyj
 */
class Mod_Tools_Send_image extends Mod_Tools_Send {

    protected function run($fileContent, $dir = '', $toFile = '' , $time_out = 2) {
        $fileContent = base64_encode($fileContent);
        $params = array(
            "data" => $fileContent,
            "dir" => $dir,
            "fileName" => $toFile,
            "token" => md5($fileContent . date("Y-m-d") . "egret")
        );
        
        MooCurl::setData($params, 'POST');
        $rs = MooCurl::call('https://pic.gz.1251278653.clb.myqcloud.com/uploadImg.php',$time_out);
        $rs = json_decode($rs, true);
        return $rs;
    }
}
