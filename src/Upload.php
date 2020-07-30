<?php
namespace hcgrzh\upload;
class Upload{
	protected static $upfileerror=[];//文件错误提示
	/**
     * 默认上传配置
     * @var array
     */
    private static $config = [
        'mimes'         =>  [], //允许上传的文件MiMe类型
        'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
        'exts'          =>  ['jpg','jpeg','png','downloading','gif','ico','bmp'], //允许上传的文件后缀
		'extsImg'      	=>  ['jpg','jpeg','png','downloading','gif','ico','bmp'], //当对图片进行最大宽度和最大高度验证时使用
        'rootPath'      => 	'./uploads/', //保存根路径  无效
        'savePath'		=>'',//保存路径
        'pathFormat'	=>2,//1:默认格式2:Y/m/d格式3:Y_m_d
        'maxwidth'		=>'',//最大宽带
        'maxheight'		=>'',//最大高度
        'isdel'			=>1,//是否删除同名文件
        'uppath'		=>'',//固定到上传路径
    ];
    //上传参数配置
	public static function setconfig($config){
		foreach($config as $k=>$v){
			self::$config[$v[0]]=$v[1];
		}
	}
	/**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public static function getError(){
        return self::$upfileerror;
    }
    //单张上传
  	public static function upload_one($file){
		$files[]=$file;
		return self::uploads($files);
	}
	//多张上传一个上传按钮上传多张 input name=file[] 这种情况
	public static function upload_more($files){
		$filearr=self::filesarr($files);
		return self::uploads($filearr);
	}
	//上传目录路径
	private static function dir_pathFormat(){
		$uppath='';
		if(!empty(self::$config['uppath'])){//固定到上传路径
			$rootpath=self::$config['uppath'];
		}else{// 默认设置 和 生成
			/* 检测上传根目录 */
			$rootpath=self::$config['rootPath'];
	     }
		if(!is_dir($rootpath)){
			if(!mkdir($rootpath,0777,true)){
				self::$upfileerror="上传文件目录".$rootpath."创建失败";
				return false;
			}
		}
		//生成目录结构
	    if(self::$config['pathFormat']==1){
			$savePath=self::$config['savePath'].'/';
		}else if(self::$config['pathFormat']==2){
			$savePath=self::$config['savePath'].'/'.date('Y').'/'.date('m')."/".date('d')."/";
		}else if(self::$config['pathFormat']==3){
			$savePath=self::$config['savePath'].'/'.date('Y_m_d').'/';
		}
		chmod($rootpath,0777);
		$uppath=$rootpath.$savePath;
		if(!is_dir($uppath)){
			if(!mkdir($uppath,0777,true)){
				self::$upfileerror="上传文件目录".$uppath."创建失败";
				return false;
			}
		}
		return $uppath;
	}
	/**
	 * 判断服务器上传文件大小设置 返回服务器上传单位mb
	 * @param  integer $dec [description]
	 * @return [type]       [description]
	 */
	public static function get_upload_max_filesize_byte(){
    	$max_size=ini_get('upload_max_filesize');  
    	preg_match('/(^[0-9\.]+)(\w+)/',$max_size,$info);  
	    $size=$info[1];
	    $suffix=strtoupper($info[2]); 
	    $a = array_flip(array("B", "KB", "MB", "GB", "TB", "PB"));  
	    $b = array_flip(array("B", "K", "M", "G", "T", "P"));
	 	if(!isset($a[$suffix])){
	 		$pos=$b[$suffix];
	 	}else{
	 		$pos=$a[$suffix];
	 	}
	    $powvalue=pow(1024,$pos);
	    $bsize=$size*$powvalue;
	   	return $bsize;
	} 
	public static function error_des($error){
		$msg='';
		if($error==1){
			$bsize=self::get_upload_max_filesize_byte();
			$bsize=$bsize/(1024*1024);
			$msg='文件允许上传最大值:'.$bsize."MB";
		}elseif($error==2){
			$msg='上传文件的大小超过了 MAX_FILE_SIZE 选项指定的值';
		}elseif($error==3){
			$msg='文件只有部分被上传。';
		}elseif($error==4){
			$msg='没有文件被上传。';
		}elseif($error==6){
			$msg='找不到临时文件夹';
		}elseif($error==7){
			$msg='文件写入失败';
		}else{
			$msg='未知错误';
		}
		return $msg;
	}
    /**
     * 上传文件
     * @param 文件信息数组 $files ，通常是 $_FILES数组
     */
    public static function uploads($files=''){
        if('' === $files){
            $files  =   $_FILES;
        }
        if(empty($files)){
            self::$upfileerror[] = '没有上传的文件！';
            return false;
        }
        $info=array();
		foreach($files as $key=>$file){
			if(empty($file['name'])){
				self::$upfileerror[]='未知错误';
				continue;
			}
			if($file['error']!=0){
				self::$upfileerror[] =$file['name'].self::error_des($file['error']);
				continue;
			}
			if (!is_uploaded_file($file['tmp_name'])) {
            	self::$upfileerror[]=$file['name'].",不是通过HTTP、POST 上传的";
            	continue;
        	}
        	if(self::$config['maxSize']!=0){
				if($file['size']>self::$config['maxSize']*1024*1024){
					self::$upfileerror[]=$file['name'].",上传文件不超过".self::$config['maxSize']."MB";
					continue;
				}
			}
			/* 检查文件Mime类型 */
        	//FLASH上传的文件获取到的mime类型都为application/octet-stream
	        if (!empty(self::$config['mimes'])){
	        	if(!in_array($file['type'],self::$config['mimes'])){
					self::$upfileerror[]= $file['name'].',上传文件MIME类型不允许！';
	            	continue;
				}
	        }
	         $file['exts']= pathinfo($file['name'], PATHINFO_EXTENSION);
	         $file['exts']=strtolower($file['exts']);
	        //文件后缀
	        if (!empty(self::$config['exts'])){
	        	if(!in_array($file['exts'],self::$config['exts'])){
	        		$exts=implode(',',self::$config['exts']);
					self::$upfileerror[] = $file['name'].',只允许上传'.$exts."的文件";
	            	continue;
				}
	        }
	        //获取上传目录
	    	$path=self::dir_pathFormat();
      		if($path===false){
				self::$upfileerror[]= $file['name'].',上传文件目录设置失败！';
				return false;
			}
			$microtime=str_replace('.','',microtime(true));
			$filename=$path.$microtime.mt_rand(0,10000000000).mt_rand(0,10000000000).'.'.$file['exts'];
	       /* 不覆盖同名文件 */
        	if (is_file($filename) && self::$config['isdel']==false) {
           		self::$upfileerror[] = $file['name'].',存在同名文件' . $file['name'];
            	continue;
        	}
	        /* 移动文件 */
        	if (!move_uploaded_file($file['tmp_name'],mb_convert_encoding($filename,'gbk','utf-8'))){
            	self::$upfileerror[] =$file['name'].',文件上传保存错误！';
           	 	continue;
        	}
        	//判断图片是否有效
        	if(in_array($file['exts'],self::$config['extsImg'])){
	        	$filename_arr = getimagesize($filename);
	        	if($filename_arr===false){
	        		self::$upfileerror[] = $file['name'].'图片无效';
	        		if(is_file($filename)){
	        			unlink($filename);
	        		}
	        		continue;
	        	}
	        }
        	if(self::$config['maxwidth']!=''){
				$filename_arr = getimagesize($filename);
				$filename_w= $filename_arr[0];
				if($filename_w>self::$config['maxwidth']){
					if(is_file($filename)){
        				unlink($filename);
        			}
					self::$upfileerror[]=$file['name'].',上传图片最大宽带不能大于'.self::$config['maxwidth'];
					continue;
				}
			}
			if(self::$config['maxheight']!=''){
				$filename_arr = getimagesize($filename);
				$filename_w= $filename_arr[1];
				if($filename_w>self::$config['maxheight']){
					if(is_file($filename)){
        				unlink($filename);
        			}
					self::$upfileerror[]=$file['name'].',上传图片最大高度不能大于'.self::$config['maxheight'];
					continue;
				}
			}
			$info[$key]['filename']=$filename;
			unset($file['tmp_name']);
        	$info[$key]=array_merge($info[$key],$file);
		}
        return $info;
    }
   	/**
   	 * 一个上传按钮上传多张 input name=file[] 这种情况
   	 * @param  [type] $files [description] $files 值为 $_FILES[input['name']]
   	 * @return [type]        [description]
   	 */
    private static function filesarr($files){
    	$arr=array();
    	if(empty($files)){self::$upfileerror[]='请选择上传文件'; return false;}
    	$count=count($files['name']);
    	for($i=0;$i<$count;$i++){
    		$arr[$i]['name']=$files['name'][$i];
    		$arr[$i]['type']=$files['type'][$i];
    		$arr[$i]['tmp_name']=$files['tmp_name'][$i];
    		$arr[$i]['error']=$files['error'][$i];
    		$arr[$i]['size']=$files['size'][$i];
    	}
    	return $arr;
    }
}
?>
