<?php
class Spider{
    public $cnt = 0;
    public $rs_url = array();
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
//        if(!isset($attr['db_name']))
//            exit('db_name=xxx');

        set_time_limit(0);

        $domain = "http://www.uthing.cn/";
        $this->catch_url($domain);
    }

    function catch_url($url){
        if(!FilterLib::regex($url,'url')){
            echo "url error:".$url."\n";
            return 0;
        }

        if(substr($url,0,7) == 'http://'){
            $auth_url = explode("/",$url);
            $auth_url = explode(".",$auth_url[2]);
            if($auth_url[1] != 'uthing' ){
                $this->rs_url[] = $url;
                echo "not local domain:".$url ."\n";
                return 0;
            }
        }

        $this->rs_url[] = $url;
        $url_content = get_url_content($url);
        if(!$url_content)
            return 0;

        $a_collection = $this->get_a_href($url_content,$url);
        if(!$a_collection){
            echo ++$this->cnt." ".$url. "\n";
        }else {
            foreach ($a_collection as $k => $v) {
                $this->catch_url($v);
            }
        }
    }

    function get_a_href($html,$url){
        $search = "/<script[^>]*?>.*?<\/script>/si";
        $html = preg_replace($search,' ',$html);



        preg_match_all('/href="(.*?)"/is',$html,$a_href);
        $a_collection = array();
        if(!$a_href){
            return "";
        }
        foreach($a_href[1] as $k=>$v){
            if(!$v || $v == '/' || $v == '#' || $v == './' || strpos($v,'javascript' ) !== false || strpos($v,'getGrouponListUrl' ) !== false  ){

            }elseif(substr($v,-3) == 'css' || substr($v,-3) == 'ico' || substr($v,-3) == 'gif' || substr($v,-3) == 'apk' ){

            }
            elseif(substr($v,0,7) != 'http://'){
                if(substr($v,0,1) == "#")
                    return 0;


                $a_domain = explode("/",$url);
//                echo $v."\n";
                if(substr($v,0,1) != "/")
                    $tmp  = "http://".$a_domain[2] ."/".$v;
                else {
//                    echo 444 ."\n";
                    $tmp = "http://" . $a_domain[2] . $v;
                }
                $a_collection[] = $tmp;
            }
            else{
                $a_collection[] = $v;
            }
        }

        $final_coll = array();
        foreach($a_collection as $k=>$v){
            if (in_array($v, $this->rs_url))
                continue;
            $this->rs_url[] = $v;

            $final_coll[] = $v;
        }

        return $final_coll;
    }
}



function get_url_content($url){

    $ch=curl_init();

    $useragent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
    //伪造header
    $header=array('Accept-Language: zh-cn','Connection: Keep-Alive','Cache-Control: no-cache');
    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch,CURLOPT_USERAGENT,$useragent);
    curl_setopt($ch,CURLOPT_URL,$url);
    $timeout=10;
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//结果不输出到页面

    $file_contents=curl_exec($ch);
    $info=curl_getinfo($ch);
//    echo $info['http_code'] . "\n";
    if($info['http_code'] == 200){
        if($file_contents === false){
            echo 'Curl error: ' . curl_error($ch);
            exit;
        }
        curl_close($ch);
        return $file_contents;
    }elseif($info['http_code'] == 302 || $info['http_code'] == 301){
        echo ("http code error:".$info['http_code']) . "\n";
        return get_url_content($info['redirect_url']);//302 301 跳转的，需要再次抓取
    }else{
        echo ("http code error:".$info['http_code'])." ".$url." \n";
        return false;
    }
}
