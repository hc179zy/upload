<?php
namespace hcgrzh\upload;
class UploadExt{
	public static $upimgerror='';
	public static $imgzipdir="imgzip";//压缩目录
	public static $imgwaterdir="water";//水印目录
	public static $imgthumbdir="thumb";//缩略图目录
	/**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public static function getError(){
        return self::$upimgerror;
    }
	/**
	 * @param $img_old="Hydrangeas_1449219544.jpg"
	 * @param $thumb_img="tpl";//缩放图地址不要/
	 * @param $size1=50;
	 * @param $size2=50;
	 * @param bool|true $fix_size
	 * @return string 返回缩微图地址
	 */
	public static function thumb($img_old,$size1,$size2){

		//$img_old原图,$thumb_img 新图位置 缩略图放置目录 
		 //设置缩略图存放目录
		$thumb_path=dirname($img_old);
		$thumb_path=rtrim($thumb_path,'/'.self::$imgzipdir);
		$thumb_path=rtrim($thumb_path,'/'.self::$imgwaterdir);
	    $thumb_path=$thumb_path."/".self::$imgthumbdir."/";
	    if(!is_dir($thumb_path)){
			if(!mkdir($thumb_path,0777,true)){
				self::$upimgerror="上传文件目录".$thumb_path."创建失败";
				return false;
			}
		}
	   	$img_old=mb_convert_encoding($img_old,'gbk','utf-8');
	    //获取文件目录所在
		$img_path=basename($img_old);
	    $thumb_img=$thumb_path.$img_path;
	    //获取旧图的 宽度 和 高度
	    $srcInfo = getimagesize($img_old);
	    if($srcInfo===false){
	    	self::$upimgerror=$img_old."图片无效,无法缩略";
			return false;
	    }
	    $src_width = $srcInfo[0];
	    $src_height = $srcInfo[1];
	    if($size1>0 && $size2>0){//$size图片缩放大小固定宽高
	    	if($size1>$src_width && $size2>$src_height){
	    		$dst_width=$src_width;
	    		$dst_height=$src_height;
	    	}else{
	    		$dst_width=$size1;
	    		$dst_height=$size2;
	    	}
	    }else if($size1>0){
	    	if($size1>$src_width){
	    		$dst_width=$src_width;
	    	}else{
	    		$dst_width=$size1;
	    	}
	    	$dst_height=ceil(($dst_width/$src_width)*$src_height);
	    }else if($size2>0){
	    	if($size2>$src_height){
	    		$dst_height=$src_height;
	    	}else{
	    		$dst_height=$size2;
	    	}
	    	$dst_width=ceil(($dst_height/$src_height)*$src_width);
	    }
	    //为新图创建图像：
	    $dst_img = @imagecreatetruecolor($dst_width,$dst_height);
	    //更改背景为白色
	    $background = imagecolorallocate($dst_img, 255, 255, 255);   
     	imagefill($dst_img,0,0,$background);

	    //载入旧图：
	     switch ($srcInfo[2])
	    {
	        case 1:
	            $src_img =imagecreatefromgif($img_old);
	          
	            break;
	        case 2:
	            $src_img =imagecreatefromjpeg($img_old);
	          
	            break;
	        case 3:
	            $src_img =imagecreatefrompng($img_old);
	           
	            break;
	        default:
	            self::$upimgerror="不支持的图片文件类型";
	            return false;
	    }

	   
	    //判断原图太小 用原图
	    if($dst_height>$src_height or $dst_width>$src_width){
	        $dst_width=$src_width;
	        $dst_height=$src_height;
	    }
	    // 缩放图片：
	    if (function_exists('imagecopyresampled')) {
	        @imagecopyresampled($dst_img,$src_img,0,0,0,0,$dst_width,$dst_height,$src_width,$src_height);
	    }else{
	        @imagecopyresized($dst_img,$src_img,0,0,0,0,$dst_width,$dst_height,$src_width,$src_height);
	    }

	     switch ($srcInfo[2])
	    {
	        case 1:
	            imagegif($dst_img, $thumb_img );
	            break;
	        case 2:
	            imagejpeg($dst_img, $thumb_img );
	            break;
	        case 3:
	            imagepng($dst_img, $thumb_img );
	            break;
	        default:
	            self::$upimgerror="不支持的图片文件类型";
	            return false;
	    }
	    //把图像资源输出到指定的路径
	    @imagedestroy($dst_img);
	    @imagedestroy($src_img);
	    $thumb_img=mb_convert_encoding($thumb_img,'utf-8','gbk');
	    return $thumb_img;
	}
 	/**
	 * 图片水印
	 * $imgSrc :原图
	 * $markImg:水印图片
	 * $markImg:位置放置
	 * $water_path:水印地址放置 无需要/
	 * return string 水印文件地址
	 */
	public static function imgWater($imgSrc,$markImg,$markPos,$pha=100){
		$imgSrc=mb_convert_encoding($imgSrc,'gbk','utf-8');
	    //获取扩展名
	    $ext=pathinfo($imgSrc,PATHINFO_EXTENSION);
	    $srcInfo = @getimagesize($imgSrc);
	    if($srcInfo===false){
	    	self::$upimgerror=$img_old."图片无效,无法水印";
			return false;
	    }
	    $srcImg_w    = $srcInfo[0];
	    $srcImg_h    = $srcInfo[1];
	    
	    $newext='';//新的扩展名
	    switch ($srcInfo[2])
	    {
	        case 1:
	            $srcim =imagecreatefromgif($imgSrc);
	            $newext="gif";
	            break;
	        case 2:
	            $srcim =imagecreatefromjpeg($imgSrc);
	            $newext="jpg";
	            break;
	        case 3:
	            $srcim =imagecreatefrompng($imgSrc);
	            $newext="png";
	            break;
	        default:
	            self::$upimgerror="不支持的图片文件类型";
	            return false;
	    }
	    $water_path_dir=dirname($imgSrc);
	  	$water_path_arr=explode('/', $water_path_dir);
	  	foreach($water_path_arr as $k=>$v){//去掉可能是缩量图和压缩图目录
	  		if($v==self::$imgzipdir || $v==self::$imgthumbdir || $v==self::$imgwaterdir){
	  			unset($water_path_arr[$k]);
	  		}
	  	}
	  	$water_path=implode('/',$water_path_arr);
	    $water_path=rtrim($water_path,'/')."/".self::$imgwaterdir."/";
	    if(!is_dir($water_path)){
			if(!mkdir($water_path,0777,true)){
				self::$upimgerror="上传文件目录".$water_path."创建失败";
				return false;
			}
		}
		$img_path=basename($imgSrc,$ext);
	    //图片url
	    $img_water_url=$img_water=$water_path.$img_path.$newext;
        if(!file_exists($markImg) || empty($markImg)){
            self::$upimgerror='水印文件不存在';
            return false;
        }
        $markImgInfo = @getimagesize($markImg);
        $markImg_w    = $markImgInfo[0];
        $markImg_h    = $markImgInfo[1];
        if($srcImg_w < $markImg_w || $srcImg_h < $markImg_h)
        {
            self::$upimgerror='水印文件比源图片文件都还大';
            return false;
        }
        switch ($markImgInfo[2])
        {
            case 1:
                $markim =imagecreatefromgif($markImg);
                break;
            case 2:
                $markim =imagecreatefromjpeg($markImg);
                break;
            case 3:
                $markim =imagecreatefrompng($markImg);
                break;
            default:
                self::$upimgerror="不支持的水印图片文件类型";
                return false;
        }
        $logow = $markImg_w;
        $logoh = $markImg_h;
	    switch($markPos)
	    {
	        case 1:
	            $x = 5;
	            $y = 5;
	            break;
	        case 2:
	            $x = ($srcImg_w - $logow) / 2;
	            $y = 5;
	            break;
	        case 3:
	            $x = $srcImg_w - $logow - 5;
	            $y = 5;
	            break;
	        case 4:
	            $x = 5;
	            $y = ($srcImg_h - $logoh) / 2;
	            break;
	        case 5:
	            $x = ($srcImg_w - $logow) / 2;
	            $y = ($srcImg_h - $logoh) / 2;
	            break;
	        case 6:
	            $x = $srcImg_w - $logow - 5;
	            $y = ($srcImg_h - $logoh) / 2;
	            break;
	        case 7:
	            $x = 5;
	            $y = $srcImg_h - $logoh - 5;
	            break;
	        case 8:
	            $x = ($srcImg_w - $logow) / 2;
	            $y = $srcImg_h - $logoh - 5;
	            break;
	        case 9:
	            $x = $srcImg_w - $logow;
	            $y = $srcImg_h - $logoh;
	            break;
	        default:
	            $x = rand ( 0, ($srcImg_w - $logow) );
	            $y = rand ( 0, ($srcImg_h - $logoh) );
	    }

	    $dst_img = @imagecreatetruecolor($srcImg_w, $srcImg_h);
	     //更改背景为白色
	    $background = imagecolorallocate($dst_img, 255, 255, 255);   
     	imagefill($dst_img,0,0,$background);
     	
		//imagecopymerge($dst_img, $srcim, 0, 0, 0, 0, $srcImg_w, $srcImg_h,$pha);
	   	imagecopy($dst_img, $srcim, 0, 0, 0, 0, $srcImg_w, $srcImg_h);
	    //imagecopymerge($dst_img, $markim, $x, $y, 0, 0, $logow, $logoh,$pha);
	    imagecopy($dst_img, $markim, $x, $y, 0, 0, $logow, $logoh);
	    imagedestroy($markim);
	    

	    switch ($srcInfo[2])
	    {
	        case 1:
	            imagegif($dst_img, $img_water );
	            break;
	        case 2:
	            imagejpeg($dst_img, $img_water );
	            break;
	        case 3:
	            imagepng($dst_img, $img_water );
	            break;
	        default:
	            echo ("不支持的水印图片文件类型");
	            return false;
	    }

	    imagedestroy($dst_img);
	    imagedestroy($srcim);
	     $img_water_url=mb_convert_encoding($img_water_url,'utf-8','gbk');
	    return $img_water_url;
	}
	/**
	 * 添加中文水印
	 *$imgSrc="tpl/Desert.jpg";
	 *$fontType="simhei.ttf";
	 *$markText="登康建辅的看法都快乐康复";
	 *$TextColor=("#2099DD");
	 *$markPos=0;
	 *$water_path=tpl';
	 *$fontSize:字体大小
	 *$pha 水印 1-100,(注:输入100的话,会很清晰;输入1的话,会很透明,即水印图不明显)
	 * return 水印地址
	 */
	public static function textWater($imgSrc,$markText,$TextColor,$markPos,$fontSize=16,$fontType,$pha=100){
		$imgSrc=mb_convert_encoding($imgSrc,'gbk','utf-8');
	    //原文件获取扩展名
	    $ext=pathinfo($imgSrc,PATHINFO_EXTENSION);

	    $srcInfo = @getimagesize($imgSrc);
	    if($srcInfo===false){
	    	self::$upimgerror=$img_old."图片无效,无法水印";
			return false;
	    }
	    $srcImg_w    = $srcInfo[0];
	    $srcImg_h    = $srcInfo[1];
	    $markText = mb_convert_encoding($markText, "html-entities","utf-8" );
	    $newext='';
	    //新的扩展名称
	    switch($srcInfo[2]){
	        case 1:
	            $srcim =imagecreatefromgif($imgSrc);
	            $newext="gif";
	            break;
	        case 2:
	            $srcim =imagecreatefromjpeg($imgSrc);
	            $newext="jpg";
	            break;
	        case 3:
	            $srcim =imagecreatefrompng($imgSrc);
	            $newext="png";
	            break;
	        default:
	            self::$upimgerror='不支持的图片文件类型';
	            return false;
	    }
	    $water_path_dir=dirname($imgSrc);
	  	$water_path_arr=explode('/', $water_path_dir);
		foreach($water_path_arr as $k=>$v){//去掉可能是缩量图和压缩图目录
	  		if($v==self::$imgzipdir || $v==self::$imgthumbdir || $v==self::$imgwaterdir){
	  			unset($water_path_arr[$k]);
	  		}
	  	}
	  	$water_path=implode('/',$water_path_arr);
	    $water_path=rtrim($water_path,'/')."/".self::$imgwaterdir."/";
	    if(!is_dir($water_path)){
			if(!mkdir($water_path,0777,true)){
				self::$upimgerror="上传文件目录".$water_path."创建失败";
				return false;
			}
		}
	    $img_path=basename($imgSrc,$ext);
	    //图片url
	    $markedfilename_url=$markedfilename=$water_path.$img_path.$newext;
        if(!empty($markText)){
            if(!file_exists($fontType))
            {
                self::$upimgerror='字体文件不存在';
                return false;
            }
        }else {
            self::$upimgerror='没有水印文字';
            return false;
        }
        //此函数返回一个含有8个单元的数组表示文本外框的四个角，索引值含义：0代表左下角 X 位置，1代表坐下角 Y 位置，
        //2代表右下角 X 位置，3代表右下角 Y 位置，4代表右上角 X 位置，5代表右上角 Y 位置，6代表左上角 X 位置，7代表左上角 Y 位置
        $box = @imagettfbbox($fontSize, 0, $fontType,$markText);
        //var_dump($box);exit;
        $logow = max($box[2], $box[4]) - min($box[0], $box[6]);
        $logoh = max($box[1], $box[3]) - min($box[5], $box[7]);
	    
	    switch($markPos)
	    {
	        case 1:
	            $x = 5;
	            $y = $fontSize;
	            break;
	        case 2:
	            $x = ($srcImg_w - $logow) / 2;
	            $y = $fontSize;
	            break;
	        case 3:
	            $x = $srcImg_w - $logow - 5;
	            $y = $fontSize;
	            break;
	        case 4:
	            $x = $fontSize;
	            $y = ($srcImg_h - $logoh) / 2;
	            break;
	        case 5:
	            $x = ($srcImg_w - $logow) / 2;
	            $y = ($srcImg_h - $logoh) / 2;
	            break;
	        case 6:
	            $x = $srcImg_w - $logow - 5;
	            $y = ($srcImg_h - $logoh) / 2;
	            break;
	        case 7:
	            $x = $fontSize;
	            $y = $srcImg_h - $logoh - 5;
	            break;
	        case 8:
	            $x = ($srcImg_w - $logow) / 2;
	            $y = $srcImg_h - $logoh - 5;
	            break;
	        case 9:
	            $x = $srcImg_w - $logow;

	            $y = $srcImg_h - $logoh + 15;
	       
	            break;
	        default:
	            $x = rand ( 0, ($srcImg_w - $logow) );
	            $y = rand ( 0, ($srcImg_h - $logoh) );
	    }

	    $dst_img = @imagecreatetruecolor($srcImg_w, $srcImg_h);
	     //更改背景为白色
	    $background = imagecolorallocate($dst_img, 255, 255, 255);    
     	imagefill($dst_img,0,0,$background);

	    imagecopy ( $dst_img, $srcim, 0, 0, 0, 0, $srcImg_w, $srcImg_h);

        if($TextColor[0]=='#'){
            $TextColor= substr($TextColor,1);
        }
        if(strlen($TextColor)==6){
            list($r,$g,$b)=array(
                $TextColor[0].$TextColor[1],
                $TextColor[2].$TextColor[3],
                $TextColor[4].$TextColor[5],
            );
        }elseif(strlen($TextColor)==3){
            list ($r,$g,$b)=array(
                $TextColor[0].$TextColor[0],
                $TextColor[1].$TextColor[1],
                $TextColor[2].$TextColor[2]
            );
        }else{
            return false;
        }
        $r = hexdec ( $r );
        $g = hexdec ( $g );
        $b = hexdec ( $b );
        /**rgb  end**/
        $color = imagecolorallocatealpha($dst_img,intval($r), intval($g), intval($b),$pha);  
       // $color = imagecolorallocate($dst_img, intval($r), intval($g), intval($b));
        imagettftext($dst_img, $fontSize, 0, $x, $y, $color, $fontType,$markText);


	    switch ($srcInfo[2])
	    {
	        case 1:
	            imagegif($dst_img,$markedfilename);
	            break;
	        case 2:
	            imagejpeg($dst_img,$markedfilename);
	            break;
	        case 3:
	            imagepng($dst_img,$markedfilename);
	            break;
	        default:
	            self::$upimgerror="不支持的水印图片文件类型";
	            return false;
	    }
	    imagedestroy($dst_img);
	    imagedestroy($srcim);
	     $markedfilename_url=mb_convert_encoding($markedfilename_url,'utf-8','gbk');
	    return $markedfilename_url;
	}
	//删除源文件
	public static function del($old_img){
		$old_img=iconv('UTF-8','gbk',$old_img);
		if(!file_exists($old_img)){
			self::$upimgerror="不存在文件".$old_img;
			return false;
		}else{
			unlink($old_img);
			return true;
		}
	}
}
?>