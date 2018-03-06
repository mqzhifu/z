<?php
class openCtrl {
    public function __construct(){

        $this->wechat = WechatLib::get_instance();
    }


    public function index(){
        $this->wechat->valid();

        $receive_info = $this->wechat->getRev()->getRevData();
        //内容
        $content = "";
        if(isset($receive_info['Content']) && $receive_info['Content'])
            $content = $receive_info['Content'];
        //事件
        $event = "";
        if(isset($receive_info['Event']) && $receive_info['Event'])
            $event = $receive_info['Event'];
        //菜单KEY
        $event_key = "";
        if(isset($receive_info['EventKey']) && $receive_info['EventKey'])
            $event_key = $receive_info['EventKey'];
        //多媒体ID，如：图片、文件、语音等，从微信下载的时候需要传给对方的
        $MediaId = "";
        if(isset($receive_info['MediaId']) && $receive_info['MediaId'])
            $MediaId = $receive_info['MediaId'];
        //微信-消息ID
        $msgid = "";
        if(isset($receive_info['MsgId']) && $receive_info['MsgId'])
            $msgid = $receive_info['MsgId'];
        //图片地址
        $PicUrl = "";
        if(isset($receive_info['PicUrl']) && $receive_info['PicUrl'])
            $PicUrl = $receive_info['PicUrl'];
        //语音消息扩展名
        $Format = "";
        if(isset($receive_info['Format']) && $receive_info['Format'])
            $Format = $receive_info['Format'];
        //日志
        $data = array(
            'type'=>$receive_info['MsgType'],
            'a_time'=>time(),
            'content'=>$content,
            'openid'=>$receive_info['FromUserName'],
            'event'=>$event,
            'event_key'=>$event_key,
        );
        $this->wechat->receive_db_id = wxReceiveLogModel::db()->add($data);
        //接收的类型
        $type = $this->wechat->getRev()->getRevType();

        switch($type) {
            case WechatLib::MSGTYPE_TEXT://处理文字
                $this->processPreSess($receive_info['FromUserName'],$type,$content);
                break;
            case WechatLib::MSGTYPE_EVENT://处理事件
                $event = $this->wechat->getRev()->getRevEvent();
                $formName = $this->wechat->getRev()->getRevFrom();

                if($event == 'LOCATION'){
                    //用户点击会话，微信会自动推送用户的GSP坐标
                    $this->proLocation();
                }elseif( WechatLib::EVENT_MENU_CLICK == $event['event']){
                    $this->wechat->text('感谢您点击菜单')->reply();
                    //菜单点击
//                    if( 'schedule' == $event['key'] || 'service' == $event['key']){
//                        $this->server();
//                    }
                }elseif( WechatLib::EVENT_SUBSCRIBE == $event['event']){
                    //关注用户[
                    $this->PRO_SUBSCRIBE($receive_info['FromUserName']);
                }elseif(WechatLib::EVENT_UNSUBSCRIBE == $event['event']){
                    //用户取消关注操作
                    $data = array('status'=>2,'up_time'=>time());
                    wxUserModel::db()->update($data,"openid = '{$formName}' limit 1 ");
                }elseif(WechatLib::EVENT_SEND_MASS == $event['event']){
                    //群发完成回调
//                    $massResult = $this->wechat->getRev()->getRevResult();
//                    $save['msg_id'] = $massResult['MsgID'];
//                    $save['status'] = $massResult['Status'];
//                    $save['totalcount'] = $massResult['TotalCount'];
//                    $save['filtercount'] = $massResult['FilterCount'];
//                    $save['sentcount'] = $massResult['SentCount'];
//                    $save['errorcount'] = $massResult['ErrorCount'];
//                    $massInfo = Table_Wx_Mass::inst()->autoClearCache()->field('id')->where("msg_id='{$save['msg_id']}'")->order("id desc")->limit(0,1)->select();
//                    $id = $massInfo[0]['id'];
//                    $save['updatetime'] = time();
//                    Table_Wx_Mass::inst()->addData($save)->edit($id);
//                    //群发短信请求日志
//                    $masslog['addtime'] = time();
//                    $masslog['status'] = $massResult['Status'];
//                    $masslog['msg_id'] =  $save['msg_id'];
//                    $masslog['mass_id'] = $id;
//                    Table_Wx_Mass_Log::inst()->addData($masslog)->add();
                }
                break;
            case WechatLib::MSGTYPE_IMAGE:
                //用户发送的图片
                $rs = $this->wechat->getMedia($MediaId);
                $up_rs = hashFiles(IMG_UPLOAD."/wxpic",$rs);

                $this->processPreSess($receive_info['FromUserName'],$type,"/www/upload/wxpic/".$up_rs,$MediaId,$msgid);
                break;
            case WechatLib::MSGTYPE_LOCATION://共享位置
                //用户主动发送位置
                $this->proMap();
                break;
            case WechatLib::MSGTYPE_LINK:
                break;
            case WechatLib::MSGTYPE_MUSIC:
                break;
            case WechatLib::MSGTYPE_NEWS:
                break;
            case WechatLib::MSGTYPE_VOICE:
                //用户发送的语音
                $rs = $this->wechat->getMedia($MediaId);
                $up_rs = hashFiles(IMG_UPLOAD."/voice",$rs,$Format);

                $src = IMG_UPLOAD."/voice/".$up_rs;
                $tar = IMG_UPLOAD."/voice/".substr($up_rs,0,strlen($up_rs)-3)."mp3";
                exec("ffmpeg -i $src $tar");

                $this->processPreSess($receive_info['FromUserName'],$type,"/www/upload/voice/".substr($up_rs,0,strlen($up_rs)-3).'mp3',$MediaId,$msgid);
                break;
            case WechatLib::MSGTYPE_VIDEO:
                break;
            default:
                $this->wechat->text("help info")->reply();
        }
        exit;
    }



    function processPreSess($openid,$type,$content,$madia_id = "",$msgid = ""){
        //先查找是否已有建立了会话
        $openid = $this->wechat->getRev()->getRevFrom();

        $ScheduleLib = new ScheduleLib($openid);
        $assign_rs = $ScheduleLib->assign();


        $sess = serverSessionModel::db()->getRow(" openid = '{$openid}' and status !=4  ");
        if(!$sess)
            $this->proText( '会话建立失败....' );

        $data = array(
            'type'=>'text',
            'content'=>$content,
            'a_time'=>time(),
            'openid'=>$openid,
            'sid'=>$sess['id'],
            'cate'=>'in',
            'err'=>0,
            'wx_msg_id'=>$msgid,
            'wx_media_id'=>$madia_id,
            'type'=>$type,
        );

        if(isset($sess['admin_id']) && $sess['admin_id'])
            $data['admin_id'] = $sess['admin_id'];

        sessMsgModel::db()->add($data);

        $up_data = array('receive_num'=>array(1),'up_time'=>time());
        serverSessionModel::db()->update($up_data," id = {$sess['id']} limit 1 ");


        $redis = new RedisPHPLib();
        $queue_key = getMsgRedisKey($sess['admin_id'],$sess['id']);

        $redis_content = time()."##".$content."##".$sess['openid']."##".$type;
        $redis->rpush($queue_key,$redis_content);

        $this->proText( $assign_rs['msg'] );
        exit;
    }


    function PRO_SUBSCRIBE($openid){
        //用户关注操作
        $openList = wxUserModel::db()->getRow("openid = '{$openid}'");
        $save['openid'] = $openid;
        $userinfo = $this->wechat->getUserInfo($save['openid']);
        $save['headimgurl'] = $userinfo['headimgurl'];
        $save['nickname'] = $userinfo['nickname'];
        $save['country'] = $userinfo['country'];
        $save['province'] = $userinfo['province'];
        $save['city'] = $userinfo['city'];
        $save['sex'] = $userinfo['sex'];
        $save['status'] = 1 ;
        if($openList){
            //编辑
            $save['up_time'] = time();
            wxUserModel::db()->update($save,"openid = '{$openid}' limit 1 ");
        }else{
            $save['up_time'] = time();
            $save['a_time'] = time();
            wxUserModel::db()->add($save);
        }


    }

    /*
     * @param $text 信息
     * 返回文本
     */
    function proText($text = ''){
        $curTime = time();//微信发送请求时间
//        $info = mb_substr($text,-2);
        if(!$text){
            exit;
        }
//            $text = "抱歉，没搜索到数据";
//        $surround = array('酒店','小吃','餐馆','咖啡店','KTV','银行','酒吧','超市','便利店','烧烤','川菜','粤菜','好吃');//周边
//        if(!strcasecmp($info,'TV')){
//            $info = "KTV";
//        }elseif(!strcasecmp($info,'啡店')){
//            $info = "咖啡店";
//        }elseif(!strcasecmp($info,'利店')){
//            $info = "便利店";
//        }
//        if($info == '天气'){
//            //天气预报
//            $this->textWeather($text);
//        }elseif($info == '汇率'){
//            //汇率转换
//            $this->textRate($text);
//        }elseif(strstr($text,"语")){
//            //在线翻译
//            $this->textTranslation($text);
//        }elseif(in_array($info,$surround)){
//            //查找周边
//            $this->textSurround($text,$info);
//        }else{

//            $all_keyword = Table_Wx_Keyword::inst()->autoClearCache()->where(" 1 ")->order(" sort desc ")->select();
//            if( $all_keyword ){
//                $reply_info = null;
//                foreach($all_keyword as $k=>$v){
//                    if($v['search'] == 1){
//                        if(   $text == $v['keyword']){
//                            $reply_info = $v;
//                            break;
//                        }
//                    }elseif($v['search'] == 2){
//                        //模糊查找
//                        if( strripos( $text, $v['keyword']) !== false ){
//                            $reply_info = $v;
//                            break;
//                        }
//                    }
//                }
//
//                if(!$reply_info)
//                    return $this->wechat->text($error)->reply();
//
//                $this->proTextReply($reply_info,$curTime,"reply");
//            }else{
                $this->wechat->text($text)->reply();
//            }


//        }
    }

    /*
     * 汇率转换
     * @param $text 信息
     */
    function textRate($text){
        $error = "抱歉，没搜索到数据";
        $seach =  mb_substr($text,0,2);
        $mark = Table_Wx_Currency::inst()->autoClearCache()->field('mark,name')->where("name like '{$seach}%'")->select();
        if($mark && !empty($mark)){
            $fromMark = $mark[0]['mark'];
            $fromName = $mark[0]['name'];
            $len = mb_strlen($fromName);
            $to = mb_substr($text,$len,-2);
            $toArr = Table_Wx_Currency::inst()->autoClearCache()->field('mark')->where("name = '{$to}'")->select();
            $toMark = $toArr[0]['mark'];
            $result = $this->wechat->queryRate($fromMark,$toMark);
            if($result['now']){
                $str = $fromName."对".$to."的汇率：".$result['now'];
                $this->wechat->text($str)->reply();
            }else{
                $this->wechat->text($error)->reply();
            }

        }else{
            $this->wechat->text($error)->reply();
        }

    }

    /*
     * 天气预报
     * @param $text文本信息
     */
    function textWeather($text){
        $error = "抱歉，没搜索到数据";
        $openid = $this->wechat->getRev()->getRevFrom();
        $queryweather = $this->wechat->querySemantic($openid,$text,"weahter",0,0,"北京");//天气的语义
        $city = $queryweather['semantic']['details']['location']['loc_ori'];
        if($city){
            $result = $this->wechat->queryWeather($city);
            $weather = $result["results"][0];
            $data = $weather["weather_data"][0];
            $str = "城市：".$city."\n"."天气：".$data['weather']."\n"."气温：".$data['temperature']."\n"."风力：".$data['wind'];
            $this->wechat->text($str)->reply();
        }else{
            $this->wechat->text($error)->reply();
        }

    }

    /*
     *在线翻译
     * $text 文本信息
     */
    function textTranslation($text){
        $error = "抱歉，没搜索到数据";
        $lanArr = explode("语",$text);
        $name = $lanArr[0]."语";
        $content = $lanArr[1];//翻译内容
        $language = Table_Wx_Language::inst()->autoClearCache()->field("mark")->where("name='{$name}'")->select();
        $to = $language[0]['mark'];
        if($content){
            $result = $this->wechat->queryTranslation($to,$content);
            $str = $result['trans_result'][0]['dst'];
            $this->wechat->text($str)->reply();
        }else{
            $this->wechat->text($error)->reply();
        }

    }

    /*
     * 查找周边
     * @param $text 文本
     * @param $info 关键字
     */
    function textSurround($text,$info){
        $error = "抱歉，没搜索到数据";
        $infoLen =  mb_strlen($info);
        $address = mb_substr($text,0,mb_strlen($text)-$infoLen);
        //得到要搜索地方的经纬度
        $url = "http://api.map.baidu.com/geocoder?";
        $akey = "ECe3698802b9bf4457f0e01b544eb6aa";
        $data['address'] = $address;
        $data['output'] = "json";
        $data['src'] = $akey;
        $hresult = $this->http_post($url,$data);
        if($hresult  && !empty($hresult)){
            $json = json_decode($hresult, true);
            $lng = $json['result']['location']['lng'];//经度
            $lat = $json['result']['location']['lat'];//纬度
            //搜索周边
            $surl = "http://api.map.baidu.com/place/search?";
            $sdata['radius'] = 1000; //$query检索半径
            $sdata['src'] = $akey;//yourCompanyName|yourAppName
            $sdata['query'] = $info;
            $sdata['location'] = $lat.",".$lng;//地图经纬度

            $sdata['output'] = "json";
            $sresult = $this->http_post($surl,$sdata);
            if($sresult){
                $surround = json_decode($sresult, true);
                $str = "";
                $arr[0]['Title'] = "搜索\"".$text."\"";
                $arr[0]['Description'] = "";
                $arr[0]['PicUrl'] = STATIC_URL."/photo/0/49/QU1dXg/80x80";
                $arr[0]['Url'] = "";
                $count = count($surround['results']);
                if($count > 6){
                    $len = 6;
                }else{
                    $len = $count;
                }
                for($i = 0 ; $i < $len ; $i++){
                    $str =  $surround['results'][$i]['name']."\n".$surround['results'][$i]['address']."\n".$surround['results'][$i]['telephone'];
                    $arr[$i+1]['Title'] =  $str;
                    $arr[$i+1]['Description'] = "";
                    $arr[$i+1]['PicUrl'] = "";
                    $arr[$i+1]['Url'] = $surround['results'][$i]['detail_url'];
                }
                $this->wechat->news($arr)->reply();
            }else{
                $this->wechat->text($error)->reply();
            }
        }else{
            $this->wechat->text($error)->reply();
        }
    }

    /*
     * @param $keyword_desc 信息
     * @param $time 微信请求时间
     * @param $type 请求来源类型
     * 微信回复信息推送
     */
    function proTextReply($keyword_desc,$time,$type){
        $error = "抱歉，没搜索到数据";
        if($keyword_desc['type'] == 1){//普通文字
            $this->wechat->text($keyword_desc['content'])->reply();
        }elseif($keyword_desc['type'] == 2){//多图文
            //返回多图文
            $message = Table_Wx_Multimedia_Message::inst()->autoClearCache()->field('title,cover,content,url')->where("mult_id = '{$keyword_desc['mult_id']}'")->select();
            $info = array();
            foreach((array)$message as $mk => $mv){
                $info[$mk]['Title'] = $mv['title'];
                $info[$mk]['Description'] = $mv['content'];
                $info[$mk]['PicUrl'] = getphotourl($mv['cover']);
                $info[$mk]['Url'] = $mv['url'];
            }
            $this->wechat->news($info)->reply();
        }elseif($keyword_desc['type'] == 3){
            //图片
            $multInfo = Table_Wx_Multimedia::inst()->autoClearCache()->field('media_id')->where("id = '{$keyword_desc['mult_id']}'")->select();
            $this->wechat->image($multInfo[0]['media_id'])->reply();
        }else{
            $this->wechat->text($error)->reply();
        }
        //微信推送信息日志
        $klog['a_time'] = $time;
        if($type == "menu"){
            $klog['menu_id'] = $keyword_desc['id'];
            $klog['push_type'] = 2;
        }elseif($type == "reply"){
            $klog['reply_id'] = $keyword_desc['id'];
            $klog['push_type'] = 1;
        }
        $klog['replytime'] = time();
        $klog['type'] = $keyword_desc['type'];
        Table_Wx_Keyword_Log::inst()->addData($klog)->add();
        exit;
    }


    function proLocation(){

        $formName = $this->wechat->getRev()->getRevFrom();
        $result = $this->wechat->getRev()->getRevEventGeo();


        $save['x'] = $result['x'];
        $save['y'] = $result['y'];
        $save['openid'] = $formName;//微信用户openid
        $save['precision'] = $result['precision'];



        $url = "http://api.map.baidu.com/geocoder?location={$save['x']},{$save['y']}&output=xml&key=28bcdd84fae25699606ffad27f8da77b";
        $info = $this->http_get($url);
        $location_info = "";
        if($info){
            $xml = simplexml_load_string($info);
            $json = json_decode(json_encode($xml),true);
            if($json['status'] == 'ok' || $json['status'] == 'OK'){
                $location_info = $json['result']['formatted_address'];
            }
        }

        $save['area_info'] = $location_info;

        $uinfo = wxLocationModel::db()->getRow(" openid = '$formName'");
        if(!$uinfo){
            $save['a_time'] = time();
            wxLocationModel::db()->add($save);
        }else{
            $save['up_time'] = time();
            wxLocationModel::db()->update($save," openid = '$formName' limit 1 ");
        }

        echo "success";
        exit;

    }

    /*
     * 查周边景点
     */
    function  proMap(){
        $formName = $this->wechat->getRev()->getRevFrom();
        $result = $this->wechat->getRev()->getRevGeo();
        $save['x'] = $result['x'];
        $save['y'] = $result['y'];
        $save['openid'] = $formName;//微信用户openid
        $save['a_time'] = time();
        $save['scale'] = $result['scale'];
        $save['label'] = $result['label'];




        $url = "http://api.map.baidu.com/geocoder?location={$save['x']},{$save['y']}&output=xml&key=28bcdd84fae25699606ffad27f8da77b";
        $info = $this->http_get($url);
var_dump($info);exit;

        wxLocationModel::db()->add($save);

        exit;
//        $query = "景点";
//        $url = "http://api.map.baidu.com/place/search?";
//        $akey = "ECe3698802b9bf4457f0e01b544eb6aa";
//        $data['radius'] = 2000; //$query检索半径
//        $data['src'] = $akey;//yourCompanyName|yourAppName
//        $data['query'] = $query;
//        $data['location'] = $save['location_x'].",".$save['location_y'];//地图经纬度
//        $data['output'] = "json";
//        $hresult = $this->http_post($url,$data);
//        if($hresult){
//            $scenery = json_decode($hresult, true);
//            $str = "";
//            $info[0]['Title'] = "查看景点";
//            $info[0]['Description'] = "";
//            $info[0]['PicUrl'] = STATIC_URL."/photo/0/49/QU1dXg/80x80";
//            $info[0]['Url'] = "";
//            $count = count($scenery['results']);
//            if($count > 6){
//                $len = 6;
//            }else{
//                $len = $count;
//            }
//            for($i = 0 ; $i < $len ; $i++){
//                $str =  $scenery['results'][$i]['name']."\n".$scenery['results'][$i]['address']."\n".$scenery['results'][$i]['telephone'];
//                $info[$i+1]['Title'] =  $str;
//                $info[$i+1]['Description'] = "";
//                $info[$i+1]['PicUrl'] = "";
//                $info[$i+1]['Url'] = $scenery['results'][$i]['detail_url'];
//            }
//            $this->wechat->news($info)->reply();
//        }

    }

    function  http_get($url ){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);
        if(intval($status["http_code"])==200){
            return $content;
        }else{
            return false;
        }
    }


    /*
     * post http请求
     * @param $url 请求地址
     * @param $param 参数
     * return array $content 请求内容
     */
    function  http_post($url,$param,$post_file=false){
        $curl = curl_init();
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        $url = $url.$strPOST;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);
        if(intval($status["http_code"])==200){
            return $content;
        }else{
            return false;
        }
    }

}