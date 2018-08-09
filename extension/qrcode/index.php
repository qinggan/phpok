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
		$errorCorrectionLevel = 'L';
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
}
