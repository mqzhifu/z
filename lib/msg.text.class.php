<?
//站内信 - 文本内容
class Table_msg_text extends Table {
    public $_table = "msg_text" ;
    public $_primarykey = "id";

    public static $_static = false;

    public static function inst() {
        if(false == self::$_static) {
            self::$_static = new self();
        }
        return self::$_static;
    }
}