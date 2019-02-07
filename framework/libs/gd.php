<?php
/**
 * 缩略/水印图生成
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年10月17日
**/

class gd_lib
{
	/**
	 * 要处理的图片地址，支持网站根目录相对地址和绝对地址
	**/
	private $filename;

	/**
	 * 是否启用GD
	**/
	private $isgd = true;

	/**
	 * JPG图片质量，仅限JPG格式有效
	**/
	private $quality = 80;

	/**
	 * 图片宽度，为0表示自适应
	**/
	private $width = 0;

	/**
	 * 图片高度，为0表示自适应
	**/
	private $height = 0;

	/**
	 * 边框的颜色
	**/
	private $border = "";

	/**
	 * 背景色，不要带#号，仅支持16进制
	**/
	private $bgcolor = "";

	/**
	 * 版权图片，如果使用图片版权，请在这里设置
	**/
	private $mark = "";

	/**
	 * 版权放置的位置，默认是bottom-right，支持：top-left/top-middle/top-right/middle-left/middle-middle/middle-right/bottom-left/bottom-middle/bottom-right
	**/
	private $position = "bottom-right";

	/**
	 * 透明度，适用于水印透明度
	**/
	private $transparence = 80;
	#[可用于整个图片要调用变量]

	/**
	 * 文件路径
	**/
	private $filepath = "";

	/**
	 * 图片数据
	**/
	private $imginfo;

	/**
	 * 是否使用裁剪法
	**/
	private $iscut = 0;

	/**
	 * 如果水印图文件已经存在，是否覆盖
	**/
	public $isrecover = true;

	public function __construct($isgd=1)
	{
		if(!$isgd || !function_exists('imagecreate')){
			$this->isgd = false;
		}
		@ini_set('memory_limit','256M');
	}

	/**
	 * 参数设置
	 * @参数 $var 变量名
	 * @参数 $val 变量值
	**/
	public function Set($var,$val="")
	{
		if($var){
			return false;
		}
		if($val != ''){
			$this->$var = $val;
		}
		return $this->$var;
	}

	/**
	 * 文件名，含路径，支持网站根目录相对路径及绝对路径
	 * @参数 $file
	 * @返回 字符串
	**/
	public function filename($file='')
	{
		if($file){
			$this->filename = $file;
		}
		return $this->filename;
	}

	/**
	 * 检查或配置gd支持
	 * @参数 $isgd 是否支持gd 为0或false表示不支持，其他支持 
	 * @返回 
	 * @更新时间 
	**/
	public function isgd($isgd=true)
	{
		if($isgd){
			$this->isgd = true;
			if(!function_exists('imagecreate')){
				$this->isgd = false;
			}
		}else{
			$this->isgd = false;
		}
		return $this->isgd;
	}

	/**
	 * 设置补白操作
	 * @参数 $bgcolor 16位颜色代码值，不支持#
	**/
	public function Filler($bgcolor="FFFFFF")
	{
		if(!$bgcolor){
			return $this->bgcolor;
		}
		$bgcolor = trim($bgcolor);
		if(substr($bgcolor,0,1) == '#'){
			$bgcolor = substr($bgcolor,1);
		}
		if($bgcolor && strlen($bgcolor) == 6){
			$this->bgcolor = $bgcolor;
		}
		return $this->bgcolor;
	}

	/**
	 * 设置版权
	 * @参数 $mark 版权图片
	 * @参数 $position 版权位置
	 * @参数 $transparence 透明度
	**/
	public function CopyRight($mark="",$position="bottom-right",$transparence=80)
	{
		$this->mark = ($mark && file_exists($mark)) ? $mark : "";
		$this->position = $this->_check_position($position);
		$this->transparence = $transparence;
		return true;
	}

	#[]
	/**
	 * 设置新图片的宽度和高度值
	 * @参数 $width 图片宽度，为0表示自适应
	 * @参数 $height 图片高度，为0表示自适应
	 * @返回 true/false
	**/
	public function SetWH($width=0,$height=0)
	{
		if(!$this->filename){
			return false;
		}
		$imginfo = $this->GetImgInfo($this->filename);
		if(!$width && !$height){
			$width = $imginfo["width"];
			$height = $imginfo["height"];
			$this->iscut = false;
		}elseif(!$width && $height && $imginfo['height']){
			$width =  ( $height * $imginfo["width"] ) / $imginfo["height"];
			$this->iscut = false;
		}elseif($width && !$height && $imginfo['width']){
			$height = ($width * $imginfo["height"]) / $imginfo["width"];
			$this->iscut = false;
		}
		$this->width = $width;
		$this->height = $height;
		return true;
	}

	#[]
	/**
	 * 设置是否使用裁剪法来生成缩略图
	 * @参数 $iscut 为0或false表示使用缩放法，其他值表示使用裁剪法
	 * @返回 true/false
	**/
	public function SetCut($iscut=0)
	{
		$this->iscut = $iscut;
		return $this->iscut;
	}

	/**
	 * 判断是否写入版权
	 * @参数 $iscopyright 为0或false表示不写入版权，其他值表示写入版权
	 * @返回 true/false
	**/
	public function iscopyright($iscopyright=true)
	{
		$this->iscopyright = $iscopyright ? true : false;
		return $this->iscopyright;
	}

	/**
	 * 判断是否覆盖新图片
	 * @参数 $isrecover 为0或false表示不复盖原图，其他表示覆盖
	 * @返回 
	 * @更新时间 
	**/
	public function isrecover($isrecover=true)
	{
		$this->isrecover = $isrecover ? true : false;
		return $this->isrecover;
	}

	/**
	 * 根据提供图片生成新图片
	 * @参数 $source 源图必须含有路径
	 * @参数 $newpic 新图名称
	 * @参数 $folder 新图片自定义的地址，留空使用源图的地址
	 * @更新时间 2019年1月20日
	**/
	public function Create($source="",$newpic="",$folder='')
	{
		if(!$this->isgd){
			return false;
		}
		if(!file_exists($source)){
			return false;
		}
		$img_info_source = $this->GetImgInfo($source);
		if(!in_array($img_info_source["ext"],array("jpg","gif","png"))){
			return false;
		}
		$this->filepath = substr($source,0,-(strlen(basename($source))));# 文件目录
		if($folder){
			$this->filepath = $folder;
		}
		if($newpic){
			$newpic = str_replace(array(".jpg",".gif",".png",".jpeg"),"",$newpic);
		}
		$newpic = $this->_cc_picname($newpic);
		$this->imginfo = $img_info_source;
		if(file_exists($this->filepath."/".$newpic.".".$this->imginfo["ext"]) && !$this->isrecover){
			$newpic .= "_".substr(md5(rand(0,9999)),9,16);
		}
		if($this->iscut){
			$getPicWH = $this->_cutimg();
		}else{
			$getPicWH = $this->_get_newpicWH();
		}
		if(!$getPicWH){
			return false;
		}
		$allpicheight = $this->height;
		$allpicwidth = $this->width;
		return $this->_create_img($source,$newpic,$allpicwidth,$allpicheight,$getPicWH);
	}

	/**
	 * 获取图片的相关数据，修正微信图片识别格式不正确问题
	 * @参数 $picture 图片地址
	 * @返回 
	 * @更新时间 2019年2月6日
	**/
	public function GetImgInfo($picture="")
	{
		if(!$picture || !file_exists($picture)){
			return false;
		}
		$tmp = strtolower(basename($picture));
		$ext = substr($tmp,-3);
		$infos = getimagesize($picture);
		$info["width"] = $infos[0];
		$info["height"] = $infos[1];
		$info["type"] = $infos[2];
		$info["ext"] = $infos[2] == 1 ? "gif" : ($infos[2] == 2 ? "jpg" : "png");
		if($ext && in_array($ext,array('jpg','gif','png'))){
			$info['ext'] = $ext;
		}
		$info["name"] = substr(basename($picture),0,strrpos(basename($picture),"."));
		return $info;
	}


	/**
	 * 判断设置的位置是否正确
	 * @参数 $position 位置
	**/
	private function _check_position($position = '')
	{
		if(!$position){
			return "bottom-right";
		}
		$position = strtolower($position);
		$l = "top-left,top-middle,top-right,middle-left,middle-middle,middle-right,";
		$l.= "bottom-left,bottom-middle,bottom-right";
		$list = explode(",",$l);
		if(in_array($position,$list)){
			return $position;
		}else{
			return "bottom-right";
		}
	}

	/**
	 * 判断或创建一个新的图片名称
	 * @参数 $name 图片名称，仅限字母，数字，下划线及中划线，其他名称暂时不支持，并且字母统一小写
	 * @参数 $length 名称长度
	**/
	private function _cc_picname($name="",$length=10)
	{
		$length = intval($length);
		if($length<2){
			$length = 2;
		}
		$newname = true;
		if($name){
			$newname = false;
			$name = strtolower($name);
			$w = "abcdefghijklmnopqrstuvwxyz_0123456789-";
			$length = strlen($name);
			if($length<1){
				$newname = true;
			}else{
				for($i=0;$i<$length;$i++){
					if(strpos($w,$name[$i]) === false){
						$newname = true;
					}
				}
			}
		}
		if($newname || !$name){
			$string = md5(rand(0,9999)."-".rand(0,9999)."-".rand(0,9999));
			$name = substr($string,rand(0,(32-$length)),10);
		}
		return $name;
	}

	/**
	 * 根据已提供的信息计算出新图的相关参数
	**/
	private function _get_newpicWH()
	{
		$info = ($this->imginfo["width"] && $this->imginfo["height"])  ? $this->imginfo : false;
		if(!$info){
			return false;
		}
		if($this->width > $info["width"] && $this->height > $info["height"]){
			$array["width"] = $info["width"];
			$array["tempx"] = $info["width"];
			$array["tempy"] = $info["height"];
			$array["height"] = $info["height"];
		}else{
			$rate_width = $info["width"]/$this->width;
			$rate_height = $info["height"]/$this->height;
			if($rate_width>$rate_height){
				$array["width"] = $this->width;
				$array["height"] = round(($this->width*$info["height"])/$info["width"]);
			}else{
				$array["height"] = $this->height;
				$array["width"] = round(($info["width"]*$this->height)/$info["height"]);
			}
			$array["tempx"] = $this->imginfo["width"];
			$array["tempy"] = $this->imginfo["height"];
		}
		$array["srcx"] = 0;
		$array["srcy"] = 0;
		return $array;
	}

	/**
	 * 将十六进制转成RGB格式
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	private function _to_rgb($color="")
	{
		if(!$color){
			return false;
		}
		if(strlen($color) != 6){
			return false;
		}
		$color = strtolower($color);
		$array["red"] = hexdec(substr($color,0,2));
		$array["green"] = hexdec(substr($color,2,2));
		$array["blue"] = hexdec(substr($color,4,2));
		return $array;
	}

	/**
	 * 创建一张图片
	 * @参数 $source 原图资源
	 * @参数 $newpic 新图名称
	 * @参数 $width 图片宽度
	 * @参数 $height 图片高度
	 * @参数 $getPicWH 原图宽高
	**/
	private function _create_img($source,$newpic,$width,$height,$getpicWH)
	{
		$truecolor = function_exists("imagecreatetruecolor") ? true : false;
		$img_create = $truecolor ? "imagecreatetruecolor" : "imagecreate";
		$img = $img_create($width,$height);
		$bg = $this->_to_rgb($this->bgcolor);
		$bg["red"] = $bg["red"] ? $bg["red"] : 0;
		$bg["green"] = $bg["green"] ? $bg["green"] : 0;
		$bg["blue"] = $bg["blue"] ? $bg["blue"] : 0;
		if($this->imginfo["ext"] == 'png'){
			$bgfill = imagecolorallocatealpha($img,$bg["red"],$bg["green"],$bg["blue"],127);
		} else {
			$bgfill = imagecolorallocate($img,$bg["red"],$bg["green"],$bg["blue"]);
		}
		imagefill($img,0,0,$bgfill);
		$picX = ($width-$getpicWH["width"])/2;
		$picY = ($height-$getpicWH["height"])/2;
		$tmpImg = $this->_get_imgfrom($source);
		if(!$tmpImg){
			return false;
		}
		$img_create = $truecolor ? "imagecopyresampled" : "imagecopyresized";
		$img_create($img,$tmpImg,$picX,$picY,$getpicWH["srcx"],$getpicWH["srcy"],$getpicWH["width"],$getpicWH["height"],$getpicWH["tempx"],$getpicWH["tempy"]);
		if($truecolor){
			imagesavealpha($img,true);
		}
		if($this->mark){
			$npicImg = $this->_get_imgfrom($this->mark);
			$npicInfo = $this->GetImgInfo($this->mark);
			$getPosition = $this->_set_position($npicInfo,$width,$height);
			if($npicInfo["type"] == 3){
				imagecopy($img,$npicImg,$getPosition["x"],$getPosition["y"],0,0,$npicInfo["width"],$npicInfo["height"]);
			}else{
				imagecopymerge($img,$npicImg,$getPosition["x"],$getPosition["y"],0,0,$npicInfo["width"],$npicInfo["height"],$this->transparence);
			}
		}
		$newpicfile = $this->filepath.$newpic.".".$this->imginfo["ext"];
		if(file_exists($newpicfile)){
			@unlink($newpicfile);
		}
		$this->_write_imgto($img,$newpicfile,$this->imginfo["type"]);
		imagedestroy($tmpImg);
		imagedestroy($img);
		if($npicImg){
			imagedestroy($npicImg);
		}
		return basename($newpicfile);
	}

	/**
	 * 获取图片数据流信息
	 * @参数 $pic 图片信息
	**/
	private function _get_imgfrom($pic)
	{
		$info = $this->GetImgInfo($pic);
		$img = "";
		if($info["type"] == 1 && function_exists("imagecreatefromgif")){
			$img = imagecreatefromgif($pic);
			ImageAlphaBlending($img,true);
		}elseif($info["type"] == 2 && function_exists("imagecreatefromjpeg")){
			$img = imagecreatefromjpeg($pic);
			ImageAlphaBlending($img,true);
		}elseif($info["type"] == 3 && function_exists("imagecreatefrompng")){
			$img = imagecreatefrompng($pic);
			ImageAlphaBlending($img,true);
		}
		return $img;
	}

	/**
	 * 写入图片
	 * @参数 $temp_image 图片资源
	 * @参数 $newfile 图片保存路径
	 * @参数 $info_type 图片类型
	**/
	private function _write_imgto($temp_image,$newfile,$info_type)
	{
		if($info_type == 1){
			imagegif($temp_image,$newfile);
		}elseif($info_type == 2){
			imagejpeg($temp_image,$newfile,$this->quality);
		}elseif($info_type == 3){
			imagepng($temp_image,$newfile);
		}else{
			$newfile = $newfile.".png";
			if(file_exists($newfile)){
				unlink($newfile);
			}
			imagepng($temp_image,$newfile);
		}
	}

	/**
	 * 设置图片的位置
	 * @参数 $npicInfo 图片信息
	 * @参数 $width 宽度
	 * @参数 $height 高度
	 * @返回 
	 * @更新时间 
	**/
	private function _set_position($npicInfo,$width,$height)
	{
		if(!$npicInfo) return array("x"=>0,"y"=>0);
		$x = $this->border ? 1 : 0;
		$y = $this->border ? 1 : 0;
		if($this->position == "top-left"){
			$x = $this->border ? 1 : 0;
			$y = $this->border ? 1 : 0;
		}elseif($this->position == "top-middle"){
			if($npicInfo["width"] < $width){
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "top-right"){
			if($npicInfo["width"] < $width){
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "middle-left"){
			if($npicInfo["height"] < $height){
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "middle-middle"){
			if($npicInfo["height"] < $height){
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width){
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "middle-right"){
			if($npicInfo["height"] < $height){
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width){
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "bottom-left"){
			if($npicInfo["height"] < $height){
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "bottom-middle"){
			if($npicInfo["height"] < $height){
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width){
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}else{
			if($npicInfo["height"] < $height){
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width){
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}
		return array("x"=>$x,"y"=>$y);
	}

	/**
	 * 使用裁剪法根据已提供的信息计算出新图的相关参数
	**/
	private function _cutimg()
	{
		$width = $this->width;
		$height = $this->height;
		if(!$height || !$width){
			return false;
		}
		$info_width = $this->imginfo["width"];
		$info_height = $this->imginfo["height"];
		$info["width"] = $info_width ? $info_width : 1;
		$info["height"] = $info_height ? $info_height : 1;
		$info_rate = $info["width"]/$info["height"];
		$new_rate = $width/$height;
		if($info_rate > $new_rate){
			$tempx = $info["height"] * $new_rate;
			$tempy = $info["height"];
			$srcx = ($info["width"] - $tempx) / 2;
			$srcy = 0;
		}else{
			$tempx = $info["width"];
			$tempy = $info["width"] / $new_rate;
			$srcx = 0;
			$srcy = ($info["height"] - $tempy) / 2;
		}
		$array["height"] = $this->height;
		$array["width"] = $this->width;
		$array["tempx"] = $tempx;
		$array["tempy"] = $tempy;
		$array["srcx"] = $srcx;
		$array["srcy"] = $srcy;
		return $array;
	}

	/**
	 * 后台使用到的缩略图
	 * @参数 $filename 文件名
	 * @参数 $id 文件ID，也是缩略图文件名
	**/
	public function thumb($filename,$id,$width=200,$height=200)
	{
		if(!$filename || !$id){
			return false;
		}
		if(!$width){
			$width = 200;
		}
		if(!$height){
			$height = 200;
		}
		$this->isgd(true);
		$this->filename($filename);
		$this->SetCut(true);
		$this->Filler("FFFFFF");
		$this->SetWH($width,$height);
		$newfile = "_".$id;
		return $this->Create($filename,$newfile);
	}

	/**
	 * 根据实际情况生成各种规格图片
	 * @参数 $filename 文件名
	 * @参数 $fileid 文件ID
	 * @参数 $rs GD配置信息
	**/
	public function gd($filename,$fileid,$rs)
	{
		$this->isgd(true);
		if(!$filename || !$fileid || !$rs){
			return false;
		}
		$this->filename($filename);
		$this->Filler($rs["bgcolor"]);
		if($rs["width"] && $rs["height"] && $rs["cut_type"]){
			$this->SetCut(true);
		}else{
			$this->SetCut(false);
		}
		$this->SetWH($rs["width"],$rs["height"]);
		$this->CopyRight($rs["mark_picture"],$rs["mark_position"],$rs["trans"]);
		if($rs["quality"]){
			$this->quality = $rs["quality"];
		}
		$newfile = $rs["identifier"]."_".$fileid;
		return $this->Create($filename,$newfile);
	}
}