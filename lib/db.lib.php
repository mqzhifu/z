<?php
class DbLib{
	public $mConfigArr = array('host','port','type','user','pwd','db_name');
	public $mLastAddId;//插入一条记录后，信息
	public $error;//错误信息
	public $mDb= null;//数据库连接资源
	public $mQueryStr;//存放每一次执行的SQL
	public $mQueryId;//mysql_query 返回值
	public $mTransaction = 0;//是否为事务，如果是则出现错误后是要抛出异常的
	public $mMasterSlave = MASTER_SLAVE;//是否开启DB主丛模式
	public $mMysqli = 0;
	//读取数据库配置信息
    public function __construct($config='',$table = '',$pk = ''){
        if( $config ){
			//验证DB配置信息
            $config = $this->authConfig($config);
            $this->config = $config;
        }
        if($table)
            $this->table = $table;

        if($pk)
            $this->pk = $pk;
    }



    //静态化
    static function getDbStatic($config='',$table = '',$pk = ''){
    	return new self($config,$table,$pk);
    }
    //获取表名
    function getTable($table = ''){
    	if($table)
    		return $table;
    	
    	if($this->table)
    		return $this->table;
    	
    	stop('table is null....','DB');
    	
    }
    //获取表主键
    function getPrimary($primary = ''){
    	if($primary)
    		return $primary;
    	 
    	if($this->pk)
    		return $this->pk;
    	 
    	stop('primary is null....','DB');
    	 
    }
    //初始化数据库连接,这里是一个小优化，只有真正执行SQL语句时，才去连接数据库
    protected function initConnect() {
    	if ( !$this->mDb ) $this->mDb = $this->connect();
    }
	//万恶的7，必须得用MYSQL-i
	function adapter(){
		var_dump(PHP_VERSION);exit;
	}

	//连接数据库
    public function connect($config='') {
//		$v = substr(PHP_VERSION,0,1);
		//初始化配置信息
    	if($config){
    		$config = $this->authConfig($config);
    		$this->config = $config;
    	}else if(isset($this->config)){
    		$config = $this->config;
    	}else{
    		$config = $GLOBALS['db_config'][DEF_DB_CONN];
    	}

    	$host = $config['host'].($config['port']?":{$config['port']}":'');
    	//是否为长连接
//        if(!defined('MYSQL_PCONNECT')){
        	$func = 'mysql_connect';
			$this->mDb = mysqli_connect( $host, $config['user'], $config['pwd']);
//    	}else{
//    		$func = 'mysql_pconnect';
//    		$this->mDb = mysqli_pconnect( $host, $config['user'], $config['pwd']);
//    	}
        if ( !$this->mDb ) 
            stop("connect db error:". mysqli_error($this->mDb) . $func ." [connect db error]",'DB');
        if(!mysqli_select_db( $this->mDb,$config['db_name']))
        	stop("connect db error:". mysqli_error($this->mDb) ." [select_db error]",'DB');
		//设置字符集
        mysqli_query($this->mDb,"SET NAMES '".DB_CHARSET."'");
        return $this->mDb;
    }
	//执行查询 返回数据集
    public function query($sql) {
        $this->initConnect();
        $this->mQueryStr = $sql;
        //释放前次的查询结果
        if ( $this->mQueryId ) $this->free();
        N('db_query',1);//记录共执行了多少条SQL
        // 记录开始执行时间
        G('queryStartTime');
        $this->mQueryId = mysqli_query($this->mDb,$sql);
        $this->debug();//SQL日志
        if ( false === $this->mQueryId ) {
            $err  = $this->error();
            if($this->mTransaction){//这个位置是事务处理时，需要抛出异常
           		throw new Exception($err);
            }else{
            	stop($this->mQueryStr."  ".$err);
            }
        } else {
            return $this->getDbAll();
        }
    }
	//仅允许执行：update,delete~,返回addId upId
    public function execute($sql) {
    	$this->authExecute($sql);
        $this->initConnect();
        $this->mQueryStr = $sql;
        //释放前次的查询结果
        if ( $this->mQueryId ) $this->free();
        N('db_write',1);
        // 记录开始执行时间
        G('queryStartTime');
        $result =   mysqli_query( $this->mDb,$sql) ;
        $this->debug();
        if ( false === $result) {
           $err = $this->error();
           if($this->mTransaction){
           		throw new Exception($err);
            }else{
            	stop($err . $sql);
            }
        } else {
            $this->mNumRows = mysqli_affected_rows($this->mDb);
            $this->mLastAddId = mysqli_insert_id($this->mDb);
            return $this->mNumRows;
        }
    }
    //添加一条数据
    public function add( $data , $table = '' , $cache = null) {
    	$table = $this->getTable($table);
    	// 写入数据到数据库
    	$values  =  $fields    = array();
    	foreach ($data as $key=>$val){
    		$values[] =  $this->parseValue($val);
    		$fields[] =  $this->parseKey($key);
    	}
    	
    	$sql   =  'INSERT INTO `'.$table.'` ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
    	$result = $this->execute($sql);
    	if(false !== $result ) {
    		$insertId   =   $this->getLastInsID();
    		if($insertId) {
    			return $insertId;
    		}
    	}

        if($cache)
            $cache->delTableCache();

    	return $result;
    }
    //添加多条数据
    public function addAll($datas,$table = ''){
    	$table = $this->getTable($table);
    	// 写入数据到数据库
    	if(!is_array($datas[0])) return false;
        $fields = array_keys($datas[0]);
        array_walk($fields, array($this, 'parseKey'));
        $values  =  array();
        foreach ($datas as $data){
            $value   =  array();
            foreach ($data as $key=>$val){
                $val   =  $this->parseValue($val);
                if(is_scalar($val)) { // 过滤非标量数据
                    $value[]   =  $val;
                }
            }
            $values[]    = '('.implode(',', $value).')';
        }
        $sql   =  'INSERT INTO '.$table.' ('.implode(',', $fields).') VALUES '.implode(',',$values);
        $result = $this->execute($sql);
    	if(false !== $result ) {
    		$insertId   =   $this->getLastInsID();
    		if($insertId) {
    			return $insertId;
    		}
    	}
    	return $result;
    }
    //根据 IDS 删除表记录
    function delByIds($ids ,$table ='',$fieldName = ''){
    	$table = $this->getTable($table);
    	$fieldName = $this->getPrimary($fieldName);
    	
    	$limit = count(explode(",",$ids));
    	$sql = "delete from `$table` where `$fieldName` in ( $ids ) limit $limit";
    	return $this->execute($sql);
    }
    //根据 IDS 删除表记录
    function delById($id ,$table ='',$fieldName = ''){
    	$table = $this->getTable($table);
    	$fieldName = $this->getPrimary($fieldName);
    	 
    	$sql = "delete from `$table` where `$fieldName` =  $id  limit 1";
    	return $this->execute($sql);
    }

	function delete($where ,$table =''){
		$table = $this->getTable($table);

		$sql = "delete from `$table` where $where";
		return $this->execute($sql);
	}


    //数据库更新
    public function update($data , $where , $table = '' ,$cache = null ) {
    	$table = $this->getTable($table);
    	
    	$data = $this->parseSet($data);
    	$sql = "update `" . $table . "`" .  $data . " where " . $where;

        if($cache)
            $cache->delTableCache();

    	return $this->execute($sql);
    }
    //数据库更新
    public function upById($id,$data,$table = '') {
    	$table = $this->getTable();
    	$pk = $this->getPrimary();
    	$where = " `$pk` = $id limit 1";
    	
    	$data = $this->parseSet($data);
    	$sql = "update `" . $table . "`" .  $data . " where " . $where;
    	return $this->execute($sql);
    }
    //更新数据时，格式化数据
    function parseSet($data) {
    	foreach ($data as $key=>$val){
    		if(is_scalar($val)){ // 过滤非标量数据
    			$value   =  $this->parseValue($val);
    			$set[]    =  "`".$key.'`='.$value;
    		}else{
    			$value   =  $this->parseValue($val[0]);
    			$set[]    =  "`".$key.'`='."`$key` + " .$value;
    		}
    	}
    	return ' SET '.implode(',',$set);
    }
    //给值添加单引号，防止SQL注入,数字是不需要添加的，字符串需要添加
    function parseValue($value) {
    	if(is_string($value)) {
    		$value = '\''.$this->escapeString($value).'\'';
    	}elseif(is_null($value)){
    		$value   =  'null';
    	}
    	return $value;
    }
    //字段和表名处理添加:<`>
    protected function parseKey(&$key) {
    	$key   =  trim($key);
    	if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
    		$key = '`'.$key.'`';
    	}
    	return $key;
    }
    //释放查询资源
    public function free() {
    	mysqli_free_result($this->mQueryId);
    	$this->mQueryId = null;
    }
    //  SQL指令安全过滤
    public function escapeString($str) {
    	if ( !$this->mDb ) $this->mDb = $this->connect();
    	return mysqli_real_escape_string($this->mDb,$str);
    }
	//debug
    protected function debug() {
    	global $db_sql_cnt;
    	if ( MYSQL_DEBUG  ) {// 记录操作结束时间
    		G('queryEndTime');
    		$db_sql_cnt[] =  $this->mQueryStr.' [ RunTime:'.G('queryStartTime','queryEndTime',6).'s ]';
//     		Log::record($this->mQueryStr.' [ RunTime:'.G('queryStartTime','queryEndTime',6).'s ]',Log::SQL);
    	}
    }
    //验证数据库配置文件信息
    function authConfig($config){
    	if(!is_array($config)){
    		if(is_array($this->config)) $config = $this->config;
    		else stop('db_config format error','DB');
    	}
    	foreach($this->mConfigArr as $k=>$v){
    		if( !$config[$v] ) stop('db_config para error','DB');
    	}
    	 
    	return $config;
    }
    //验证execute方法，只允许执行update 和 delete 且   包含 limit 且 不能操作大于100行
    function authExecute($str){
    	$str = trim($str);
    	$tmp_str = strtolower(substr($str, 0 ,6));
    	if('insert' == $tmp_str)return 1;
    	if($tmp_str != 'delete' && $tmp_str != 'update'){
    		echo $str;
    		stop('execute func method: <update> or <delete>','DB');
    	}
    	 
    	$location = strpos($str,'limit');
    	if( $location === false){
    		echo $str;
    		stop('execute func update or delete : required <limit>','DB');
    	}
    	 
    	if(strpos($str,'where') === false){
    		echo $str;
    		stop('execute func update or delete : required <where>','DB');
    	}
    	 
//    	$tmp_str2 = trim(substr($str, $location + 6 ));
//    	if($tmp_str2 > 100){
//    		echo $str;
//    		stop('execute func update or delete : limit numbers < 100','DB');
//    	}
    }
    
//事务处理--------------------------------------------------    
    function begin(){
    	$this->mDb->mTransaction = 1;
    	$this->querySql('SET AUTOCOMMIT=0');
    	$this->querySql('BEGIN');
    }
    function rollback(){
    	$this->querySql('rollback');
    }
    
    function commit(){
    	$this->querySql('commit');
    	$this->querySql('SET AUTOCOMMIT=1');
    	$this->mDb->mTransaction = 0;
    }
    
    //此个功能是仅仅给执行SQL不需要返回值的情况
    function querySql($sql){
    	N('db_query',1);//记录共执行了多少条SQL
    	G('queryStartTime');// 记录开始执行时间
    	$this->initConnect();
    	$rs = mysqli_query($this->mDb,$sql);
    	$this->debug();//SQL日志
    	return $rs;
    }
//事务处理--------------------------------------------------
    
//-------------------各种SELECT-获取操作----get----------------------------------
	function addFormData($ignore = '',$table){
		$fields = $this->getFields('',$table);
		$ignore_arr = array();
		$ignore_arr[] = $this->getPrimary();
		$rs = array();
		foreach($fields as $k=>$v){
			if(in_array($v,$ignore))
				continue;
			
			$rs[$v] = _g($k);
		}
		return $rs;
	}
    function countTableTotal($where  = '',$filed = '*', $table){
    	$sql = "select count($filed) as total from $table where $where ";
    	$rs = $this->getOne( $sql );
    	return $rs;
    }
    function getAllBySQL($sql){
    	$dbRs = $this->query($sql);
    	return $dbRs;
    }
    
    function getRowBySQL($sql ,$cache = null){
        $key = md5 (  $sql );
        if($cache){
            $v = $cache->get($key);
            if($v){
                echo 234;
                return $v;
            }
        }

    	$dbRs = $this->query($sql);

        if($cache){
            $cache->set($key,$cache);
        }
    	
    	if($dbRs)
    		return $dbRs[0];
    }
    
    function getOneBySQL($sql){
    	$dbRs = $this->query($sql);
    	
    	return $dbRs[0]['total'];
    }
    
    function getTableAll($table = ''){
    	if(!$table)
    		$table = $this->table;
    	
    	$sql = "select * from `$table` where 1";
    	$rs = $this->getAllBySQL( $sql );
    	return $rs;
    	
    }
    
    function getOne($where = ' 1 = 1 ',$table = "",$filed = '*' ){
        if(!$table)
            $table = $this->table;

    	$sql = "select $filed as total from $table where $where";
    	$rs = $this->getOneBySQL( $sql );
    	return $rs;
    }
    
    function getAll($where = ' 1 = 1 ',$table = '' ,$filed = '*',$cache = null){
    	if(!$table)
    		$table = $this->table;
    	
    	if(!$filed)
    		$filed = "*";
    	
    	$sql = "select $filed from `$table` where $where";
    	$rs = $this->getAllBySQL( $sql );
    	return $rs;
    }
    
    function getRow($where = ' 1 = 1 ',$table = '' ,$filed = '*',$cache = null){
    	if(!$table)
            $table = $this->table;
    	 
    	if(!$filed)
    		$filed = "*";

    	$sql = "select $filed from `$table` where $where";
    	$rs = $this->getRowBySQL( $sql , $cache );

    	return $rs;
    }
    //根据主键ID(字段名必须 为:id)，获得一条记录
    function getRowById($id = '',$pk = '',$table = '' ,$filed = '*'){
    	$table = $this->getTable($table);
    	$pk_filed = $this->getPrimary($pk);
    	
    	$sql = "select $filed  from `$table` where $pk_filed = $id";
    	$rs = $this->getRowBySQL( $sql );
    	return $rs;
    }
    function getById($id = '',$pk = '',$table = '' ,$filed = '*'){
    	$table = $this->getTable($table);
    	$pk_filed = $this->getPrimary($pk);
    	 
    	$sql = "select $filed  from `$table` where $pk_filed = $id";
    	$rs = $this->getRowBySQL( $sql );
    	return $rs;
    }
    //根据主键ID(字段名必须 为:id)，获得一条记录
	function getRowByIds($ids = '',$table = '' ,$filed = '*'){
    	$table = $this->getTable($table);
    	$id_filed = $this->getPrimary($filed);
    	
    	$sql = "select $filed  from `$table` where $id_filed in ( $ids )";
    	$rs = $this->getRowBySQL( $sql );
    	return $rs;
    }
    //根据主键ID(字段名必须 为:id)，获得一条记录
    function getByIds($ids = '',$table = '' ,$filed = '*'){
    	$table = $this->getTable($table);
    	$id_filed = $this->getPrimary($filed);
    	 
    	$sql = "select $filed  from `$table` where $id_filed in ( $ids )";
    	$rs = $this->getRowBySQL( $sql );
    	return $rs;
    }
    
    
	function getCount( $where = ' 1 = 1 ',$table = ''){
    	if(!$table)
    		$table = $this->table;
    	
    	$sql = "select count(*) as total from $table where $where";
    	$rs = $this->getOneBySQL( $sql );
    	return $rs;
    }
    
    public function getLastInsID() {
    	return $this->mLastAddId;
    }
    //数据库错误信息.记录并显示当前的SQL语句
    public function error() {
    	$this->error = mysqli_error($this->mDb);
    	return $this->error;
    }
    //返回数据集
    private function getDbAll() {
    	$result = array();
    	if($this->mQueryId) {
	    		while($row = mysqli_fetch_assoc($this->mQueryId)){
	    			$result[]   =   $row;
	    		}
    	}
    	return $result;
    }
    
//数据库结构操作---------------------------------------------------------------------------------------------------------------     
    //获取数据库信息
    function getDbInfo($dbName=''){
    	if(!$dbName)
    		$dbName = $this->config['db_name'];
    	 
    	$sql = "select DEFAULT_CHARACTER_SET_NAME,DEFAULT_COLLATION_NAME from information_schema.SCHEMATA where SCHEMA_name = '$dbName' ";//数据库信息
    	$dbInfo = $this->getRow($sql);
    	 
    }
    //取得数据库的所有 表
    function showTablesList($dbName='') {
    	if(!empty($dbName)) {
    		$sql    = 'SHOW TABLES FROM '.$dbName;
    	}else{
    		$sql    = 'SHOW TABLES ';
    	}
    	$result =   $this->query($sql);
    	$info   =   array();
    	foreach ($result as $key => $val) {
    		$info[$key] = current($val);
    	}
    	return $info;
    }
    //取得数据库的所有 表
    public function getTablesList($dbName='') {
    	if(!$dbName)
    		$dbName = $this->config['db_name'];
    	
    	$sql = "select * from information_schema.TABLES where  table_schema = '$dbName'";//列相关信息
    	$rs =  $this->getAll($sql);
    	
//     	$rs[$v]['ENGINE'] = $row['ENGINE'];
//     	$rs[$v]['ENGINE'] = $row['ENGINE'];
    	
    	return $rs;
    }
    //取得数据库字段信息
    public function getFields($dbName = '',$table='') {
    	if(!$dbName)
    		$dbName = $this->config['db_name'];
    	
    	if(!$table)
    		$table = $this->showTablesList();
    	
    	if(!is_array($table))
    		stop('table must array!'.'DB');
    	
    	$rs = array();
    	foreach($table as $k=>$v){
    		$sql = "select COLUMN_NAME,COLUMN_TYPE,COLUMN_DEFAULT,IS_NULLABLE,COLUMN_COMMENT,COLUMN_KEY from information_schema.COLUMNS where table_schema = '$dbName' and table_name = '$v' order by ORDINAL_POSITION";//列相关信息
    		$row =  $this->getAllBySQL($sql);
    		if($row){
    			$rs[$v] = $row;
//     			foreach($row as $k2=>$v2){
//     				if($v['COLUMN_KEY']){
//     					$t = $indexDesc[$v['COLUMN_KEY']];
//     					if($v['EXTRA']){
//     						$t .= ' 自增';
//     					}
//     					$table->addCell()->addText($t);
//     				}
//     			}
    		}
    	}
    	
    	return $rs;
    }
	//获取表的索引信息    
    function getTableIndex($dbName = '',$table = ''){
		if(!$dbName)
    		$dbName = $this->config['db_name'];
    	if(!$table)
    		$table = $this->showTablesList();
    	if(!is_array($table))
    		stop('table must array!'.'DB');

    	$rs = array();
    	foreach($table as $k=>$v){
    		$sql = "select * from `information_schema`.`STATISTICS` where table_schema = '$dbName' and table_name = '$v'";//索引
    		$index = $this->getAllBySQL($sql);
    		if($index){
    			foreach($index as $k2=>$v2){
    				$rs[$v][$v2['COLUMN_NAME']] = $v2['INDEX_NAME'];
    			}
    		}else{
    			$rs[$v] = null;
    		}
    	}
    	
    	return $rs;

    }

    function checkTable($table,$db = ''){
    	$tables = $this->showTablesList($db);
	    if($tables){
	    	$f = 0;
	    	foreach	($tables as $k=>$v){
	    		if($v == $table){
	    			$f=1;
	    			break;
	    		}
	    	}
	    	return $f;
    	}
    }
//数据库结构操作-------------------------------------    
}
