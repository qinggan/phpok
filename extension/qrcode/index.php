<?php
/**
 * 二维码生成库
 * @package phpok\extension
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月06日
**/

class qrcode_lib extends _init_lib
{
	private $logo = '';
	private $font = '';
	private $fontsize = 16;
	public function __construct()
	{
		parent::__construct();
		require_once 'phar://' . $this->dir_extension . 'qrcode/phpqrcode.phar';
		if(file_exists($this->dir_data.'font/airbus_special.ttf')){
			$this->font = $this->dir_data.'font/airbus_special.ttf';
		}
	}

	public function logo($filename='')
	{
		if($filename && file_exists($filename)){
			$this->logo = $filename;
		}
		return $this->logo;
	}

	public function fontsize($size='')
	{
		if($size && is_numeric($size)){
			$this->fontsize = $size;
		}
		return $this->fontsize;
	}

	public function font($font='')
	{
		if($font && file_exists($this->dir_data.'font/'.$font)){
			$this->font = $this->dir_data.'font/'.$font;
		}
		return $this->font;
	}

	public function png($data)
	{
		QRcode::png($data,false,'L',10,2);
	}

	public function create($string='',$filename='',$extinfo='')
	{
		if(!$string || !$filename){
			return false;
		}
		$tmpfile = $this->dir_cache.md5($filename).'.png';
		$errorCorrectionLevel = 'Q';
		$matrixPointSize = 10;
		QRcode::png($string, $tmpfile, $errorCorrectionLevel, $matrixPointSize, 2);
		if(!file_exists($tmpfile)){
			return false;
		}
		$QR = imagecreatefromstring(file_get_contents($tmpfile));
		$QR_width = imagesx($QR);
		$QR_height = imagesy($QR);
		if($this->logo){
			$logo = imagecreatefromstring(file_get_contents($this->logo));
			$logo_width = imagesx($logo);
			$logo_height = imagesy($logo);
			$logo_qr_width = $QR_width / 5;
			$scale = $logo_width / $logo_qr_width;
			$logo_qr_height = $logo_height / $scale;
			$from_width = ($QR_width - $logo_qr_width) / 2;
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
		}
		if(!$extinfo){
			imagepng($QR,$filename);
			@unlink($tmpfile);
			return true;
		}
		@unlink($tmpfile);
		imagepng($QR,$tmpfile);
		unset($QR);
		$QR = imagecreate($QR_width,($QR_height + 30));
		$white = imagecolorallocate($QR, 255, 255, 255);
		$black = imagecolorallocate($QR,0,0,0);
		imagefilledrectangle($QR, 0, 0,$QR_width,($QR_height + 30), $white);
		
		$qrimg = imagecreatefromstring(file_get_contents($tmpfile));
		imagecopyresampled($QR,$qrimg,0,0,0,0,$QR_width,$QR_height,$QR_width,$QR_height);
		if($this->font && function_exists('imagettftext')){
			imagettftext($QR, $this->fontsize, 0, 21, ($QR_height+10), $black, $this->font, $extinfo);
		}else{
			imagestring($QR,5,21,($QR_height + 10),$extinfo,$black);
		}
		imagepng($QR,$filename);
		//@unlink($tmpfile);
		return true;
	}

	public function read($file='',$width='')
	{
		if(!$file){
			return false;
		}
		if(!$width){
			include_once($this->dir_extension.'qrcode/lib/QrReader.php');
			$obj = new QrReader($file,'file',false);
			return $obj->text();
		}
		$img_info = $this->_img_info($file);
		if(!$img_info){
			return false;
		}
		
		if($img_info['width'] <= ($width*2) || $info['height'] < ($width*2)){
			include_once($this->dir_extension.'qrcode/lib/QrReader.php');
			$obj = new QrReader($file,'file',false);
			return $obj->text();
		}
		
		//如果图片宽度超过800，将图片宽度调成800
		$tmpfile = $this->dir_cache.md5(time()).'.png';
		if($img_info['width']>800){
			$this->_to800($file,$tmpfile);
			$file = $tmpfile;
			$img_info = $this->_img_info($file);
		}
		//切成小块
		$w_count = intval($img_info['width']/$width);
		$h_count = intval($img_info['height']/$width);
		$list = array();
		for($i=0;$i<$w_count;$i++){
			$t = $i+1;
			if($t==$w_count){
				$w = $img_info['width'] - ($width*$i);
				$x = $width * $i;
			}else{
				$w = $width;
				$x = $width * $i;
			}
			for($j=0;$j<$h_count;$j++){
				$o = $j+1;
				if($o == $h_count){
					$h = $img_info['height'] - ($width * $j);
					$y = $width * $j;
				}else{
					$h = $width;
					$y = $width * $j;
				}
				$list[] = array("width"=>$w,"height"=>$h,'x'=>$x,'y'=>$y);
			}
		}
		if($img_info["type"] == 1 && function_exists("imagecreatefromgif")){
			$img = imagecreatefromgif($file);
		}elseif($img_info["type"] == 2 && function_exists("imagecreatefromjpeg")){
			$img = imagecreatefromjpeg($file);
		}else{
			$img = @imagecreatefrompng($file);
		}
		imagealphablending($img,true);
		$tmplist = array();
		$tmpid = time();
		foreach($list as $key=>$value){
			$tmp = imagecreate($value['width'],$value['height']);
			imagecolorallocate($tmp,255,255,255);
			imagefill($tmp,0,0,$bgfill);
			imagecopyresized($tmp,$img,0,0,$value['x'],$value['y'],$value['width'],$value['height'],$value['width'],$value['height']);
			$tmpfile = $this->dir_cache.$tmpid.'-'.$key.'.'.$img_info['ext'];
			if(file_exists($tmpfile)){
				@unlink($tmpfile);
			}
			if($img_info['type'] == 1){
				imagegif($tmp,$tmpfile);
			}elseif($img_info['type'] == 2){
				imagejpeg($tmp,$tmpfile,100);
			}else{
				imagepng($tmp,$tmpfile);
			}
			imagedestroy($tmp);
			$value['file'] = $tmpfile;
			$list[$key] = $value;
		}
		imagedestroy($img);
		include_once($this->dir_extension.'qrcode/lib/QrReader.php');
		$text = false;
		foreach($list as $key=>$value){
			if($text){
				break;
			}
			$obj = new QrReader($value['file'],'file',false);
			$text = $obj->text();
		}
		return $text;
	}

	private function _to800($file,$newfile)
	{
		$img_info = $this->_img_info($file);
		$width = 800;
		$height = round($width*$img_info['height']/$img_info['width'],2);
		$truecolor = function_exists("imagecreatetruecolor") ? true : false;
		$img_create = $truecolor ? "imagecreatetruecolor" : "imagecreate";
		$img = $img_create($width,$height);
		if($img_info["ext"] == 'png'){
			$bgfill = imagecolorallocatealpha($img,255,255,255,127);
		} else {
			$bgfill = imagecolorallocate($img,255,255,255);
		}
		imagefill($img,0,0,$bgfill);
		if($img_info["type"] == 1 && function_exists("imagecreatefromgif")){
			$tmpImg = imagecreatefromgif($file);
		}elseif($img_info["type"] == 2 && function_exists("imagecreatefromjpeg")){
			$tmpImg = imagecreatefromjpeg($file);
		}else{
			$tmpImg = @imagecreatefrompng($file);
		}
		if(!$tmpImg){
			return false;
		}
		imagealphablending($tmpImg,true);
		$img_create = $truecolor ? "imagecopyresampled" : "imagecopyresized";
		$img_create($img,$tmpImg,0,0,0,0,$width,$height,$img_info["width"],$img_info["height"]);
		if($truecolor){
			imagesavealpha($img,true);
		}
		if(file_exists($newfile)){
			@unlink($newfile);
		}
		if($img_info['type'] == 1){
			imagegif($img,$newfile);
		}elseif($img_info['type'] == 2){
			imagejpeg($img,$newfile,100);
		}else{
			imagepng($img,$newfile);
		}
		imagedestroy($tmpImg);
		imagedestroy($img);
		return true;
	}

	private function _img_info($picture="")
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
		$info["name"] = substr(basename($picture),0,strrpos(basename($picture),"."));
		return $info;
	}
}
