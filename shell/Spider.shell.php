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

        $domain = "http://bj.uban.com/";

        for($i=1;$i<=20;$i++){
            $url = " http://bj.uban.com/searchlist/$i/#list-result";
            $this->catch_url($domain);
        }




    }

    function catch_url($url){
        $url_content = get_url_content($url);


        $search = "/<div class=\"office-list-item clearfix\">.*?<\/div>/si";
        preg_match_all($search,$url_content,$xx);

        var_dump($xx);exit;

    }


//<div class="office-list-item clearfix">
//<a href="/detail-98.html" class="db pr" target="_blank">
//<div class="jfl pr">
//<img data-original="http://img1.static.uban.com/bc3275e2-9d84-11e5-9444-00163e00571b.JPG-wh480x320" src="http://img1.static.uban.com/bc3275e2-9d84-11e5-9444-00163e00571b.JPG-wh480x320" width="270" height="180" alt="半岛科技园" style="display: inline;">
//</div>
//<div class="price-box text-right">
//<span class="db text-gray6"><em class="font26 font-num fb text-pink-app">4</em> 元/<span class="font-num">m²</span>·天</span>
//<span class="db text-gray9 font12 mt10">均价</span>
//</div>
//<dl class="office-building-cont pr clearfix">
//<dt class="mb25 clearfix">
//<b class="font20 text-black fl">半岛科技园</b>
//</dt>
//<dd>
//<i class="sem-icon item-address"></i>[浦东-张江] 上海浦东新区达尔文路88号(近高科中路)</dd>
//
//<dd>
//<i class="sem-icon item-area"></i>可租面积  <span class="text-black fb">64-1688</span><span class="font-num"> m²</span>, 待租办公室&nbsp;<span class="font-num text-black fb">84</span>&nbsp;套</dd>
//<dd>
//<span><i class="sem-icon item-see"></i>近7天有 <b class="hover">15</b> 位用户咨询过</span>
//</dd>
//<dd class="last-fix-bottom">
//<div class="jfl building-tag">
//<span>距张江高科站982米</span>
//<span>创意园区</span>
//</div>
//</dd>
//</dl>
//</a>
//<p class="isfavorite cur-pointer" data-favorite="0" data-id="98" }="">关注</p>
//									<p class="contrast cur-pointer" data-office="98">加入对比</p>
//							</div>


    function get_a_href($html,$url){




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
