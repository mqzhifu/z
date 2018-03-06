<?php
//所有跟数组-字符串 相关操作的


//二维数组，根据第二维下标取最大值或最小值
function getArraysort($array,$field=0,$sort="max"){

    if(!is_array($array)){
        return 0;
    }

    $rel=$array[0][$field];
    foreach($array as $value){
        if($sort=="max"){
            $rel = $rel>$value[$field] ? $rel : $value[$field];
        }else{
            $rel = $rel<$value[$field] ? $rel : $value[$field];
        }
    }
    return $rel;
}
//字符串截取，多余的字符用...填充
function str_cut($str,$max,$symbol = '...'){
    $char = 'utf-8';
    $length = mb_strlen($str,$char);
    if($length < $max)
        return $str;

    $rs = mb_substr($str,0,$max,$char) . $symbol;
    return $rs;
}
/* 快速排序：一个一维数组  */
function quick_sort($array){
    $len = count($array);
    if($len <= 1)
        return $array;

    $left_array = array();
    $right_array = array();
    $key = $array[0];
    for($i=1; $i<$len; $i++){
        if($key < $array[$i]){
            $right_array[] = $array[$i];
        }else{
            $left_array[]=$array[$i];
        }
        $first_array = array_merge($left_array, array($key), $right_array);
    }
    return $first_array;
}
//二维数组中，某一个数组中，某一项中，最大的值
function getArrMax($arr , $f){
    $tmp = 0;
    foreach($arr as $k=>$v){
        if($v[$f] > $tmp)$tmp = $v[$f];
    }
    return $tmp;
}
//判断某一个值，是否在数据中
function arr_in_arr($arr,$key,$node){
    $f = 0;
    foreach($arr as $k=>$v){
        if($node  == $v[$key]){
            $f = 1;
            break;
        }
    }

    return $f;
}
//获取一个字符串长度，汉字算2个
//UTF8 汉字是占3个字符，但是有些需求，要求汉字占2个字符
function length ($string){
    $length = strlen($string);
    $n = 0;
    $noc = 0;
    while($n < strlen($string)) {

        $t = ord($string[$n]);
        if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $tn = 1; $n++; $noc++;
        } elseif(194 <= $t && $t <= 223) {
            $tn = 2; $n += 2; $noc += 2;
        } elseif(224 <= $t && $t <= 239) {
            $tn = 3; $n += 3; $noc += 2;
        } elseif(240 <= $t && $t <= 247) {
            $tn = 4; $n += 4; $noc += 2;
        } elseif(248 <= $t && $t <= 251) {
            $tn = 5; $n += 5; $noc += 2;
        } elseif($t == 252 || $t == 253) {
            $tn = 6; $n += 6; $noc += 2;
        } else {
            $n++;
        }

        if($noc >= $length) {
            break;
        }

    }
    return $noc;
}

function _lang($key,$variable_name = '', $variable_content = ''){
    if(isset(  $GLOBALS['LANG'][$key] ) &&  $GLOBALS['LANG'][$key]){
        if($variable_name && $variable_content){
            return str_replace($variable_name, $variable_content, $GLOBALS['LANG'][$key]);
        }else{
            return $GLOBALS['LANG'][$key] ;
        }
    }

    return "";
}

//把一个数组中的KEY值，转换成一个数组
function key_turn_arr($arr){
    $rs = array();
    foreach($arr as $k=>$v){
        $rs[] = $k;
    }
    return $rs;
}
//将一个数组中的某个键值，组成成一个字符串：1,3,2,2,2
function split_arr($arr,$keyName = ''){
    $str = "";
    if(!$keyName){
        foreach($arr as $k=>$v){
            $str .= $v . ",";
        }
    }else{
        foreach($arr as $k=>$v){
            $str .= $v[$keyName] . ",";
        }
    }
    return substr($str, 0 , strlen($str) - 1);
}
//将一个数组中的某个键值，组成成一个字符串：1,3,2,2,2
function split_arr_one($arr){
    $rs = "";
    foreach($arr as $k=>$v){
        $rs .= $v . ",";
    }
    return substr($rs, 0 , strlen($rs) - 1);
}
// 1 2 3 4 ,如果从3开始计算，结果是：3 4 1 2
function middle_sort_arr($arr,$keyword){
    $middle = 0;
    foreach($arr as $k=>$v){
        if($k+1 == $keyword)
            $middle = $k;
    }

    $rs = array();
    for($i=$middle;$i < count($arr) ;$i++){
        $rs[] = $arr[$i];
    }

    for($i =0;$i<$middle;$i++){
        $rs[] = $arr[$i];
    }

    return $rs;
}
//递归 遍历 数组 并组成 字符串
function arr_foreach_str ($arr)
{
    static $rs ;
    if (!is_array ($arr))
    {
        return false;
    }

    foreach ($arr as $key => $val )
    {
        if (is_array ($val))
        {
            arr_foreach ($val);
        }
        else
        {
            $rs .= $key.":". $val.' ';
        }
    }
}
//将一个数组中的某个键值，组成成一个字符串：1,3,2,2,2
function split_arr_sql($arr,$keyName = ''){
    $str = "";
    if(!$keyName){
        foreach($arr as $k=>$v){
            $str .= "'" . $v . "',";
        }
    }else{
        foreach($arr as $k=>$v){
            $str .= "'" .$v[$keyName] . "',";
        }
    }
    return substr($str, 0 , strlen($str) - 1);
}
//array('name'=>'lisi',id=1)转换成    array(id=>'lisi')
function two_turn_one($arr,$key,$val){
    $rs = array();
    foreach($arr as $k=>$v){
        $rs[$v[$key]] = $v[$val];
    }

    return $rs;
}

function two_trun_one_arr($arr,$key){
    $rs = array();
    foreach($arr as $k=>$v){
        $rs[] = $v[$key];
    }
    return $rs;
}

//将array('t'=>222,'t2'=>33)转成数字键值(0=>222,1=>33)
function somearr_to_onearr($somearr){
    $rs = array();
    foreach($somearr as $k=>$v){
        $rs[] = $v;
    }

    return $rs;
}


//将字节转换成MB GB KB
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

//判断长度
function slen($value,$max,$min = 1,$unicode = 'utf8'){
    $str = mb_strlen($value , $unicode);
    if( $str > $max || $str < $min){
        return 0;
    }

    return 1;
}


function char_decode($str) {
    $str = strip_tags($str);
    $find = "&iexcl;,&copy;,&laquo;,&not,&reg;,&para;,&middot;,&cedil;,&raquo;,&frac12;,&frac34;,&iquest;,&Acirc;,&Atilde;,&Auml;,&Aring;,&AElig;,&Ccedil;,&Euml;,&Icirc;,&Iuml;,&Ntilde;,&Ocirc;,&Otilde;,&Ouml;,&Oslash;,&Ucirc;,&szlig;,&atilde;,&auml;,&aring;,&aelig;,&ccedil;,&euml;,&icirc;,&iuml;,&ntilde;,&ocirc;,&otilde;,&ouml;,&oslash;,&ucirc;,&yuml;,&fnof;,&sigmaf;,&thetasym;,&upsih;,&piv;,&bull;,&frasl;,&trade;,&harr;,&part;,&minus;,&cong;,&lceil;,&rceil;,&lfloor;,&rfloor;,&lang;,&rang;,&loz;,&spades;,&clubs;,&hearts;,&diams;,&OElig;,&oelig;,&Scaron;,&scaron;,&Yuml;,&circ;,&tilde;,&sbquo;,&bdquo;,&dagger;,&Dagger;,&lsaquo;,&rsaquo;,&euro;,&#63;,&#161;,&#169;,&#171;,&#172;,&#174;,&#182;,&#183;,&#184;,&#187;,&#189;,&#190;,&#191;,&#194;,&#195;,&#196;,&#197;,&#198;,&#199;,&#203;,&#206;,&#207;,&#209;,&#212;,&#213;,&#214;,&#216;,&#219;,&#223;,&#227;,&#228;,&#229;,&#230;,&#231;,&#235;,&#238;,&#239;,&#241;,&#244;,&#245;,&#246;,&#248;,&#251;,&#255;,&eacute;";
    $find .= ",&hellip;,&#39;";
    $rplace = "¡,©,«,¬,®,¶,·,¸,»,½,¾,¿,Â,Ã,Ä,Å,Æ,Ç,Ë,Î,Ï,Ñ,Ô,Õ,Ö,Ø,Û,ß,ã,ä,å,æ,ç,ë,î,ï,ñ,ô,õ,ö,ø,û,ÿ,ƒ,ς,?,?,?,•,⁄,™,↔,∂,−,∝,?,?,?,?,?,?,◊,♠,♣,♥,♦,Œ,œ,Š,š,Ÿ,ˆ,˜,‚,„,†,‡,‹,›,€,?,¡,©,«,¬,®,¶,·,¸,»,½,¾,¿,Â,Ã,Ä,Å,Æ,Ç,Ë,Î,Ï,Ñ,Ô,Õ,Ö,Ø,Û,ß,ã,ä,å,æ,ç,ë,î,ï,ñ,ô,õ,ö,ø,û,ÿ,é";
    $rplace .= ",…,t'";
    $find = explode(",",$find);
    $rplace = explode(",",$rplace);
    $str = str_replace($find,$rplace,$str);
    $str = str_replace(array("&#160;", "&nbsp;", "&ldquo;", "&rdquo;", "'", "#", "\t", "&mdash;", "&ndash;", "&rsquo;"), array(" ", " ", "“", "”", "''", "", "", "—", "–", "’"), $str);
    return $str;
}
function isUtf8($pstr){
    if (preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$pstr) == true
        || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$pstr) == true
        || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$pstr) == true){
        return true;
    }else{
        return false;
    }
}


// xml编码
function xml_encode($data,$encoding='utf-8',$root="think") {
    $xml = "<?xml version=\"1.0\" encoding=\"".$encoding."\"?>\n";
    //$xml.= '<'.$root.'>';
    $xml.= "<rss version=\"2.0\">\n";
    $xml.= "<channel>\n";
    $xml.= data_to_xml($data);
    $xml.= "</channel>\n";
    $xml.= "</rss>";
    //$xml.= '</'.$root.'>';
    return $xml;
}

function data_to_xml($data) {
    if(is_object($data)) {
        $data = get_object_vars($data);
    }
    $xml = '';
    foreach($data as $key=>$val) {
        is_numeric($key) && $key="item";
        $xml.="<$key>\n";
        $xml.=(is_array($val)||is_object($val))?data_to_xml($val):$val;
        $xml.="\n";
        list($key,)=explode(' ',$key);
        $xml.="</$key>\n";
    }
    return $xml;
}

function str_encode($string, $codekey=null){
    $coded = '';
    $keylength = strlen($codekey);

    for($i=0,$n=strlen($string);$i<$n;$i+=$keylength){
        $coded .= substr($string, $i, $keylength) ^ $codekey;
    }
    $coded = str_replace('=', '', base64_encode($coded));

    return $coded;
}

function str_decode($string, $codekey){
    $coded = '';
    $keylength = strlen($codekey);
    $string = base64_decode($string);
    for($i=0,$n=strlen($string);$i<$n;$i+=$keylength){
        $coded .= substr($string, $i, $keylength) ^ $codekey;
    }
    return $coded;
}

/**
 * 阿拉伯数字转汉字数字字符
 * @param $num
 * @return string
 */
function ToChinaseNum($num){
    $char = array("零","一","二","三","四","五","六","七","八","九");
    $dw = array("","十","百","千","万","亿","兆");
    $retval = "";
    $proZero = false;
    for($i = 0;$i < strlen($num);$i++) {
        if($i > 0)    $temp = (int)(($num % pow (10,$i+1)) / pow (10,$i));
        else $temp = (int)($num % pow (10,1));

        if($proZero == true && $temp == 0) continue;

        if($temp == 0) $proZero = true;
        else $proZero = false;

        if($proZero) {
            if($retval == "") continue;
            $retval = $char[$temp].$retval;
        }
        else $retval = $char[$temp].$dw[$i].$retval;
    }
    if($retval == "一十") $retval = "十";
    return $retval;
}





