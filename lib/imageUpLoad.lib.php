<?php
/* 
$_FILES["file"]["error"]:
UPLOAD_ERR_OK,0:没有错误发生，文件上传成功
UPLOAD_ERR_INI_SIZE,1:上传的文件超过了 php.ini中upload_max_filesize(默认情况为2M) 选项限制的值
UPLOAD_ERR_FORM_SIZE,2:上传文件的大小超过了 HTML表单中MAX_FILE_SIZE选项指定的值
UPLOAD_ERR_PARTIAL,3:文件只有部分被上传
UPLOAD_ERR_NO_FILE,4:没有文件被上传
5: 传文件大小为0
*/

class ImageUpLoadLib{
	public $fileSize = 2;
	public $fileType = array('pjpeg','gif','gif','bmp','png','jpeg','jpg','x-png');
	public $path = IMG_UPLOAD;
	public $upFileTotal;
	public $upSucc ;
	public $upFail;
	public $info = array('upSucc'=>0,'upFail'=>0);
	public $original ;//不随机生成文件名，而是用原文件名
	//$postNames:array('post_name1','post_name2','post_name3');
	function __construct($postNames,$path = null,$fileType = null ,$original = null){
		if($path){
			if(!is_dir($path))
				exit('$path is error');
				$this->path = $path;
		}
		if($fileType)
			$this->fileType = $fileType;
		if(!$postNames || !is_array($postNames)){
			exit('错误，初始化图片类($postNames)');
		}
// 		$mark = file_mode_info($this->path);
// 		if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'){
// 			if( $mark < 7){
// 				echo "目录无权限";
// 			}
// 		}else{
// 			if( $mark != 777){
// 				echo "目录无权限";
// 			}
// 		}
		
		$this->postNames = $postNames;
	}
	
	function getUpName(){
		$rs =array();
		if(!$_FILES)
			return false;
		
		foreach($_FILES as $k=>$v){
			if($v['tmp_name'] && $v['name'] )
				$rs[] = $v['name'];
		}
		
		return $rs;
	}
	
	static function getUpNames(){
		$rs =array();
		if(!$_FILES)
			return false;
		
		foreach($_FILES as $k=>$v){
			if($v['tmp_name'] && $v['name'] )
				$rs[] = $k;
		}
		
		return $rs;
	}
	
	function upLoad(){
		foreach($this->postNames as $k=>$fileName ){
			$this->upLoadFile($fileName);
		}
		
	}
	
	function upLoadFile($fileName){
		if(!isset($_FILES[$fileName]))
			exit('$_FILES['.$fileName .'] null notice: enctype="multipart/form-data"');
		
		if( $_FILES[$fileName]['size']  > $this->fileSize  * 1024 * 1024){
			$this->info['upFail']++;
			return $this->info[$fileName]['error']  = '图片大于2MB';
		}
 		$fileType = get_file_ext($_FILES[$fileName]["name"]);
		$fileType = strtolower($fileType);
 		if(!in_array($fileType, $this->fileType)){
 			$this->info['upFail']++;
			return $this->info[$fileName]['error']  = '$_FILES[$fileName]["tmp_name"]';
 		}
		//验证扩展名，分为2部分
 		$fileType = explode('/', $_FILES[$fileName]["type"]);
		$fileType[1] = strtolower($fileType[1]);
 		if(!in_array($fileType[1], $this->fileType)){
 			$this->info['upFail']++;
			return $this->info[$fileName]['error']  = '$_FILES[$fileName]["type"] ';
 		}

 		if($fileType[1] == 'pjpeg' || $fileType[1] == 'jpeg'){
 			$fileType[1] = 'jpg';
 		}
 		if($fileType[1] == 'x-png' || $fileType[1] == 'png'){
 			$fileType[1] = 'png';
 		}
 		
		if(!$this->original){
			$createFileName = date("YmdHis")."_" .uniqid(rand());
			$hashDir = $this->mkdirHash();
			$fileHashName = $hashDir . "/" . $createFileName ."." . $fileType[1];
		}else{
			$fileHashName = $_FILES[$fileName]['name'];
		}
			
		$fileDirName =  $this->path . "/" .  $fileHashName;
		$tmp = "/" . $fileDirName;
		$this->info[$fileName]['uploadFileName'] = $fileHashName;
		$rs = move_uploaded_file($_FILES[$fileName]["tmp_name"],$fileDirName);
		if( $rs ){ //把临时文件移动到规定的路径下
			$this->info[$fileName]['method']  = 1;
			$this->info['upSucc']++;
		}else{
			$this->info[$fileName]['method']  = 1;
			$this->info['upFail']++;
			$this->info[$fileName]['error'] = $_FILES[$fileName]['error'];
		}
	}
	
	function mkdirHash(){
		$dirName = date("Ymd");
		$dir = $this->path . "/" . $dirName;
		if(!is_dir($dir)){
			mkdir( $dir );
		}
		
		return $dirName;
	}
	
	function getFileType($filename){
		$file = fopen($filename, "rb");
		$bin = fread($file, 2); //只读2字节
		fclose($file);
		$strInfo = @unpack("C2chars", $bin);
		$typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
		$fileType = '';
		switch ($typeCode){
			case 7790:
				$fileType = 'exe';
				break;
			case 7784:
				$fileType = 'midi';
				break;
			case 8297:
				$fileType = 'rar';
				break;
			case 8075:
				$fileType = 'zip';
				break;
			case 255216:
				$fileType = 'jpg';
				break;
			case 7173:
				$fileType = 'gif';
				break;
			case 6677:
				$fileType = 'bmp';
				break;
			case 13780:
				$fileType = 'png';
				break;
			default:
				$fileType = 'unknown: '.$typeCode;
		}
	
		//Fix
		if ($strInfo['chars1']=='-1' AND $strInfo['chars2']=='-40' ) return 'jpg';
		if ($strInfo['chars1']=='-119' AND $strInfo['chars2']=='80' ) return 'png';
	
		return $fileType;
		
// 		if(in_array($attach['ext'], array('jpg', 'jpeg', 'gif', 'png', 'swf', 'bmp')) && function_exists('getimagesize') && !@getimagesize($target))
// 		{
// 			unlink($target);
// 			upload_error('post_attachment_ext_notallowed', $attacharray);
// 		}
	}

}
?>
