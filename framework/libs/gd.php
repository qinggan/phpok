<?php
#=====================================================================
#	Filename: class/gd.php
#	Note	: 缩略/水印图生成，使用缩放法
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2008-10-13
#=====================================================================
class gd_lib
{
	# 原文件
	var $filename;
	#[是否使用GD生成水印或缩略图]
	var $isgd = true;
	var $quality = 80;#[图片质量]
	#[新图片的宽度和高度]
	var $width = 0;
	var $height = 0;
	#[补白处理]
	#[border：边框的颜色]
	#[bgcolor：背景色，不要带#号，仅支持16进制]
	var $border = "";
	var $bgcolor = "";
	#[版权处理]
	#[mark：版权图片，如果使用图片版权，请在这里设置]
	#[position：版权放置的位置，默认是bottom-right]
	#[transparence：透明度，默认是80]
	var $mark = "";
	var $position = "bottom-right";
	var $transparence = 80;
	#[可用于整个图片要调用变量]
	var $filepath = "";
	var $imginfo;#[图片数据]
	#[是否使用裁剪法来生成缩略图]
	var $iscut = 0;
	#[如果水印图文件已经存在，是否覆盖]
	var $isrecover = true;

	function __construct($isgd=1)
	{
		$this->isgd = $isgd && function_exists("imagecreate") ? true : false;
	}

	#[参数设置]
	function Set($var,$val="")
	{
		$this->$var = $val;
	}

	function filename($file)
	{
		$this->filename = $file;
	}

	function isgd($isgd=true)
	{
		$this->isgd = $isgd ? true : false;
	}

	#[设置补白操作]
	function Filler($bgcolor="FFFFFF")
	{
		if($bgcolor && strlen($bgcolor) == 6) $this->bgcolor = $bgcolor;
	}

	#[设置版权]
	function CopyRight($mark="qinggan.jpg",$position="bottom-right",$transparence=80)
	{
		$this->mark = $mark && file_exists($mark) ? $mark : "";
		$this->position = $this->_check_position($position);
		$this->transparence = $transparence;
	}

	#[设置新图片的宽度和高度值]
	function SetWH($width="",$height="")
	{
		if(!$this->filename) return false;
		$imginfo = $this->GetImgInfo($this->filename);
		if(!$width && !$height)
		{
			$width = $imginfo["width"];
			$height = $imginfo["height"];
			$this->iscut = false;
		}
		elseif(!$width && $height)
		{
			$width =  ( $height * $imginfo["width"] ) / $imginfo["height"];
			$this->iscut = false;
		}
		elseif($width && !$height)
		{
			$height = ($width * $imginfo["height"]) / $imginfo["width"];
			$this->iscut = false;
		}
		$this->width = $width;
		$this->height = $height;
	}

	#[设置是否使用裁剪法来生成缩略图]
	function SetCut($iscut=0)
	{
		$this->iscut = $iscut;
	}

	#[判断是否写入版权]
	function iscopyright($iscopyright=true)
	{
		$this->iscopyright = $iscopyright ? true : false;
	}

	#[判断是否覆盖新图片]
	function isrecover($isrecover=true)
	{
		$this->isrecover = $isrecover ? true : false;
	}

	#

	#[根据提供图片生成新图片]
	#[source：源图必须含有路径]
	#[newpic：新图只能使用源图的路径]
	#[width：新图片的宽度]
	#[height：新图片的高度]
	function Create($source="",$newpic="")
	{
		if(!$this->isgd) return false;
		if(!file_exists($source)) return false;
		#[判断文件是否合法，不合法直接跳出]
		$img_info_source = $this->GetImgInfo($source);
		if(!in_array($img_info_source["ext"],array("jpg","gif","png")))
		{
			return false;
		}
		$this->filepath = substr($source,0,-(strlen(basename($source))));# 文件目录
		if($newpic) $newpic = str_replace(array(".jpg",".gif",".png",".jpeg"),"",$newpic);
		$newpic = $this->_cc_picname($newpic);
		$this->imginfo = $img_info_source;
		#[判断新文件名是否存在，存在即再重命名一下]
		$recover = $this->isrecover;
		if(file_exists($this->filepath."/".$newpic.".".$this->imginfo["ext"]) && !$recover)
		{
			$newpic .= "_".substr(md5(rand(0,9999)),9,16);
		}
		# 判断图片的生成方式，裁剪法或是缩放法
		if($this->iscut)
		{
			$getPicWH = $this->_cutimg();
		}
		else
		{
			$getPicWH = $this->_get_newpicWH();
		}
		if(!$getPicWH) return false;
		#[计算整个新图片的宽度和高度]
		$allpicheight = $this->height;
		$allpicwidth = $this->width;
		#[开始画图]
		return $this->_create_img($source,$newpic,$allpicwidth,$allpicheight,$getPicWH);
	}

	#[获取图片的相关数据]
	function GetImgInfo($picture="")
	{
		if(!$picture || !file_exists($picture)) return false;
		$infos = getimagesize($picture);
		$info["width"] = $infos[0];
		$info["height"] = $infos[1];
		$info["type"] = $infos[2];
		#[强制设置图片的类型]
		$info["ext"] = $infos[2] == 1 ? "gif" : ($infos[2] == 2 ? "jpg" : "png");
		#[设置图片的名称]
		$info["name"] = substr(basename($picture),0,strrpos(basename($picture),"."));
		return $info;
	}

	#[判断设置的位置是否正确]
	function _check_position($position)
	{
		if(!$position) return "bottom-right";
		$position = strtolower($position);
		$l = "top-left,top-middle,top-right,middle-left,middle-middle,middle-right,";
		$l.= "bottom-left,bottom-middle,bottom-right";
		$list = explode(",",$l);
		if(in_array($position,$list))
		{
			return $position;
		}
		else
		{
			return "bottom-right";
		}
	}

	#[判断或创建一个新的图片名称]
	function _cc_picname($name="",$length=10)
	{
		$length = intval($length);
		if($length<2)
		{
			$length = 2;
		}
		$newname = true;#[检测名称是否合法，不合法时将自动重新命名]
		if($name)
		{
			$newname = false;#[改变状态，设为不重命名]
			$name = strtolower($name);#[全部变成小写的]
			$w = "abcdefghijklmnopqrstuvwxyz_0123456789-";
			$length = strlen($name);
			if($length<1)
			{
				$newname = true;
			}
			else
			{
				for($i=0;$i<$length;$i++)
				{
					if(strpos($w,$name[$i]) === false)
					{
						$newname = true;#[检测到这一步，说明新文件名中带有不合法的字符，将重新命名]
					}
				}
			}
		}
		if($newname || !$name)
		{
			$string = md5(rand(0,9999)."-".rand(0,9999)."-".rand(0,9999));
			$name = substr($string,rand(0,(32-$length)),10);
		}
		return $name;
	}

	#[根据已提供的信息计算出新图的相关参数]
	function _get_newpicWH()
	{
		$info = ($this->imginfo["width"] && $this->imginfo["height"])  ? $this->imginfo : false;
		if(!$info) return false;
		if($this->width > $info["width"] && $this->height > $info["height"])
		{
			$array["width"] = $info["width"];
			$array["tempx"] = $info["width"];
			$array["tempy"] = $info["height"];
			$array["height"] = $info["height"];
		}
		else
		{
			#[判断原图和新图的宽高比的大小比例，以确定使用哪个为基准]
			$rate_width = $info["width"]/$this->width;
			$rate_height = $info["height"]/$this->height;
			if($rate_width>$rate_height)
			{
				$array["width"] = $this->width;
				$array["height"] = round(($this->width*$info["height"])/$info["width"]);
			}
			else
			{
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

	#[将十六进制转成RGB格式]
	function _to_rgb($color="")
	{
		if(!$color) return false;
		if(strlen($color) != 6) return false;
		$color = strtolower($color);
		$array["red"] = hexdec(substr($color,0,2));
		$array["green"] = hexdec(substr($color,2,2));
		$array["blue"] = hexdec(substr($color,4,2));
		return $array;
	}

	function _create_img($source,$newpic,$width,$height,$getpicWH)
	{
		$truecolor = function_exists("imagecreatetruecolor") ? true : false;
		$img_create = $truecolor ? "imagecreatetruecolor" : "imagecreate";
		$img = $img_create($width,$height);
		//ImageAlphaBlending($img,true);
		#[颜色转成十进制]
		$bg = $this->_to_rgb($this->bgcolor);
		$bg["red"] = $bg["red"] ? $bg["red"] : 0;
		$bg["green"] = $bg["green"] ? $bg["green"] : 0;
		$bg["blue"] = $bg["blue"] ? $bg["blue"] : 0;
		//实现Png格式图片透明
		if($this->imginfo["ext"] == 'png'){
			$bgfill = imagecolorallocatealpha($img,$bg["red"],$bg["green"],$bg["blue"],127);
		} else {
			$bgfill = imagecolorallocate($img,$bg["red"],$bg["green"],$bg["blue"]);
		}
		#[填充背景色]
		imagefill($img,0,0,$bgfill);
		#[被框架的图片的位置]
		$picX = ($width-$getpicWH["width"])/2;
		$picY = ($height-$getpicWH["height"])/2;
		#[采集原图样本信息]
		$tmpImg = $this->_get_imgfrom($source);
		if(!$tmpImg) return false;
		$img_create = $truecolor ? "imagecopyresampled" : "imagecopyresized";
		$img_create($img,$tmpImg,$picX,$picY,$getpicWH["srcx"],$getpicWH["srcy"],$getpicWH["width"],$getpicWH["height"],$getpicWH["tempx"],$getpicWH["tempy"]);
		if($truecolor){
			imagesavealpha($img,true);
		}
		#[判断是否要求写入版权图片]
		if($this->mark)
		{
			$npicImg = $this->_get_imgfrom($this->mark);
			$npicInfo = $this->GetImgInfo($this->mark);
			$getPosition = $this->_set_position($npicInfo,$width,$height);
			if($npicInfo["type"] == 3)
			{
				imagecopy($img,$npicImg,$getPosition["x"],$getPosition["y"],0,0,$npicInfo["width"],$npicInfo["height"]);
			}
			else
			{
				imagecopymerge($img,$npicImg,$getPosition["x"],$getPosition["y"],0,0,$npicInfo["width"],$npicInfo["height"],$this->transparence);
			}
		}
		#[如果新图片存在，则删除新图片]
		$newpicfile = $this->filepath.$newpic.".".$this->imginfo["ext"];
		if(file_exists($newpicfile))
		{
			@unlink($newpicfile);
		}
		#[写入数据]
		$this->_write_imgto($img,$newpicfile,$this->imginfo["type"]);
		imagedestroy($tmpImg);
		imagedestroy($img);
		#[销毁版权的图片]
		if($npicImg)
		{
			imagedestroy($npicImg);
		}
		return basename($newpicfile);#[返回生成的图片名称]
	}

	#[获取图片数据流信息]
	function _get_imgfrom($pic)
	{
		$info = $this->GetImgInfo($pic);
		if($info["type"] == 1 && function_exists("imagecreatefromgif"))
		{
			$img = imagecreatefromgif($pic);
			ImageAlphaBlending($img,true);
		}
		elseif($info["type"] == 2 && function_exists("imagecreatefromjpeg"))
		{
			$img = imagecreatefromjpeg($pic);
			ImageAlphaBlending($img,true);
		}
		elseif($info["type"] == 3 && function_exists("imagecreatefrompng"))
		{
			$img = imagecreatefrompng($pic);
			ImageAlphaBlending($img,true);
		}
		else
		{
			$img = "";
		}
		return $img;
	}

	function _write_imgto($temp_image,$newfile,$info_type)
	{
		if($info_type == 1)
		{
			imagegif($temp_image,$newfile);
		}
		elseif($info_type == 2)
		{
			//imagesavealpha($temp_image,true);
			imagejpeg($temp_image,$newfile,$this->quality);
		}
		elseif($info_type == 3)
		{
			imagepng($temp_image,$newfile);
		}
		else
		{
			#[如果不存在这些条件，那将文件改为png的缩略图]
			$newfile = $newfile.".png";
			if(file_exists($newfile))
			{
				unlink($newfile);
			}
			imagepng($temp_image,$newfile);
		}
	}

	function _set_position($npicInfo,$width,$height)
	{
		if(!$npicInfo) return array("x"=>0,"y"=>0);
		$x = $this->border ? 1 : 0;
		$y = $this->border ? 1 : 0;
		if($this->position == "top-left")
		{
			$x = $this->border ? 1 : 0;
			$y = $this->border ? 1 : 0;
		}
		elseif($this->position == "top-middle")
		{
			if($npicInfo["width"] < $width)
			{
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}
		elseif($this->position == "top-right")
		{
			if($npicInfo["width"] < $width)
			{
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}
		elseif($this->position == "middle-left")
		{
			if($npicInfo["height"] < $height)
			{
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
		}
		elseif($this->position == "middle-middle")
		{
			if($npicInfo["height"] < $height)
			{
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width)
			{
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}
		elseif($this->position == "middle-right")
		{
			if($npicInfo["height"] < $height)
			{
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width)
			{
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}
		elseif($this->position == "bottom-left")
		{
			if($npicInfo["height"] < $height)
			{
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
		}
		elseif($this->position == "bottom-middle")
		{
			if($npicInfo["height"] < $height)
			{
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width)
			{
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}
		else
		{
			if($npicInfo["height"] < $height)
			{
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width)
			{
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}
		return array("x"=>$x,"y"=>$y);
	}

	#[使用裁剪法根据已提供的信息计算出新图的相关参数]
	function _cutimg()
	{
		$width = $this->width;
		$height = $this->height;
		$info_width = $this->imginfo["width"];
		$info_height = $this->imginfo["height"];
		$info["width"] = $info_width ? $info_width : 1;
		$info["height"] = $info_height ? $info_height : 1;
		//判断是高度优先还是宽度优先
		//原图比例
		$info_rate = $info["width"]/$info["height"];
		//新生成图片的比例
		$new_rate = $width/$height;
		//如果原图比例大于新图比例，则新图以高度优先进行缩放，再进行裁剪
		if($info_rate > $new_rate)
		{
			$tempx = $info["height"] * $new_rate;
			$tempy = $info["height"];
			$srcx = ($info["width"] - $tempx) / 2;
			$srcy = 0;
		}
		else
		{
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

	#[后台使用到的缩略图]
	function thumb($filename,$id)
	{
		if(!$filename || !$id) return false;
		$this->filename($filename);
		$this->SetCut(true);
		$this->Filler("FFFFFF");
		$this->SetWH(200,200);
		$newfile = "_".$id;
		return $this->Create($filename,$newfile);
	}

	# res，图片资源信息
	# rs，GD配置信息
	function gd($filename,$fileid,$rs)
	{
		if(!$filename || !$fileid || !$rs) return false;
		$this->filename($filename);
		$this->Filler($rs["bgcolor"]);
		if($rs["width"] && $rs["height"] && $rs["cut_type"])
		{
			$this->SetCut(true);
		}
		else
		{
			$this->SetCut(false);
		}
		$this->SetWH($rs["width"],$rs["height"]);
		# 水印及透明度
		$this->CopyRight($rs["mark_picture"],$rs["mark_position"],$rs["trans"]);
		# 图片质量
		if($rs["quality"]) $this->quality = $rs["quality"];
		$newfile = $rs["identifier"]."_".$fileid;
		return $this->Create($filename,$newfile);
	}
}
?>