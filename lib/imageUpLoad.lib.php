<?php
//图片上传
class ImageUpLoadLib{
	//文件大小：2MB
    public $fileSize = 2;
    public $fileType = array('pjpeg','gif','gif','bmp','png','jpeg','jpg','x-png');
    //文件上传路径
    public $path = IMG_UPLOAD;
    //是否开始HASH随机文件名
    public $hash = true;
    public $postInputName = null;

    function __construct(){

    }
	//开始上传一个文件
    //$path:文件上传路径
    //$fileType：文件类型|文件扩展名
    //$postNames:input name
    function upLoadOneFile($postInputName,$path = null,$fileType = null ){

        if($path){
            if(!is_dir($path))
                return out_pc(8112);
            $this->path = $path;
        }else{
            if(!is_dir($this->path))
                return out_pc(8112);
        }

        if($fileType)
            $this->fileType = $fileType;

        if(!$postInputName){
        	return out_pc(8017);
        }

        if(!isset($_FILES[$postInputName]))
            return out_pc(8018,'$_FILES['.$postInputName .'] null notice: enctype="multipart/form-data"');

        $mark = file_mode_info($this->path);
        if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'){
            if( $mark < 7){
                return out_pc(8113);
            }
        }else{
            if( $mark != 777){
                return out_pc(8113);
            }
        }

        if(arrKeyIssetAndExist($_FILES[$postInputName],'error')){
			return out_pc(8118);
        }


//        $this->postInputName = $postInputName;
        $fileName = $postInputName;
        if( $_FILES[$fileName]['size']  > $this->fileSize  * 1024 * 1024)
            return out_pc(8114);

        //判断文件类型(扩展)，共3步，1：文件名、2：PHP自带的函数、3：打开二进制文件


		//1判断文件名
        $fileType = get_file_ext($_FILES[$fileName]["name"]);
        //转小写
        $fileType = strtolower($fileType);
        if(!in_array($fileType, $this->fileType))
            return out_pc(8115);

        //2PHP自带的函数
        $fileType = explode('/', $_FILES[$fileName]["type"]);
        $fileType[1] = strtolower($fileType[1]);
        if(!in_array($fileType[1], $this->fileType)){
            return out_pc(8115);
        }

        if($fileType[1] == 'pjpeg' || $fileType[1] == 'jpeg'){
            $fileType[1] = 'jpg';
        }
        if($fileType[1] == 'x-png' || $fileType[1] == 'png'){
            $fileType[1] = 'png';
        }

        //这个验证就比较关键了，防止用户上传恶意文件~
		//但实际上黑客还是能攻击，但至少能防一些低级的攻击者
        $type = $this->getFileType($_FILES[$fileName]["tmp_name"]);
		if(!in_array($type,$this->fileType)){
            return out_pc(8116);
		}

        if($this->hash){
        	//年月日-小时分秒+4位随机数
            $createFileName = date("YmdHis")."_" .rand(1000,9999);
            //获取 HASH 文件夹目录
            $hashDir = $this->mkdirHash();
            $fileDirName = $hashDir . "/" . $createFileName ."." . $fileType[1];
        }else{
            $fileDirName = $_FILES[$fileName]['name'];
        }

        $finalFileDirName =  $this->path . "/" .  $fileDirName;
        //真正-开始-将用户上传的临时文件，转移至目录
        $rs = move_uploaded_file($_FILES[$fileName]["tmp_name"],$finalFileDirName);
        if(!$rs){
            return out_pc(8017);
        }

        return $fileDirName;
    }
	//上传多文件
	function upLoad(){
		foreach($this->postNames as $k=>$fileName ){
			$this->upLoadFile($fileName);
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
		//打开文件
		$file = fopen($filename, "rb");
        //读前两个字节
		$bin = fread($file, 2);
		fclose($file);
		//二进制转十进制
		$strInfo = @unpack("C2chars", $bin);
		//连接两个字符
		$typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
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
	}

}

