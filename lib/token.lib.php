<?php
class TokenLib {
    static function create($uid){
        $secret = $GLOBALS['main']['tokenSecret'];

        return self::crypt($uid, $secret, 'encode');
    }

    static function getUid($str){
        $secret = $GLOBALS['main']['tokenSecret'];

        return intval(self::crypt($str, $secret, 'decode'));
    }

    //operation ＝ decode 解密  key 私钥
    static function crypt($string, $key = '', $operation = 'encode') {
//        $expire = 30 * 24 * 60 * 60;
        if ($operation == 'decode') {
            return self::decrypt($string, $key);
        } else {
            return self::encrypt($string, $key);
        }
    }

    static function encrypt($data, $key, $expire = 0) {
        $expire = sprintf('%010d', $expire ? $expire + time() : 0);
        $key = md5($key);
        $data = base64_encode($expire . $data);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l)
                $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
        }
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
    }

    static function decrypt($data, $key) {
        $key = md5($key);
        $data = str_replace(array('-', '_'), array('+', '/'), $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data = base64_decode($data);

        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l)
                $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        $data = base64_decode($str);
        $expire = substr($data, 0, 10);
        if ($expire > 0 && $expire < time()) {
            return '';
        }
        $data = substr($data, 10);
        return $data;
    }
}