<?php
/***********************************************************
	Filename: {phpok}/libs/vcode.php
	Note	: 图形验证码类，使用PNG透明图片
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月6日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class vcode_lib
{
	private $width = 76;
	private $height = 24;
	private $count = 4;
	private $word = '';
	
	public function __construct()
	{
		$this->width = 76;
		$this->height = 24;
		$this->count = 4;
	}

	public function width($width=76)
	{
		$this->width = $width;
	}

	public function height($height=24)
	{
		$this->height = $height;
	}

	public function count($count=4)
	{
		$this->count = $count;
	}

	public function word()
	{
		$txt = "1234567890";
		$length = strlen($txt);
		$thetxt = '';
		for($i=0;$i<$this->count;$i++){
			$thetxt .= $txt[rand(0,$length-1)];
		}
		$this->word = $thetxt;
		return $thetxt;
	}
	
	//这里仅限数字
	public function create()
	{
		ob_end_clean();
		$aimg = imagecreate($this->width,$this->height);
		$white_color = imagecolorallocate($aimg, 255, 255, 255);
		imagefilledrectangle($aimg, 0, 0, $this->width, $this->height, $white_color);
		imagecolortransparent($aimg,$white_color);
		$color = $this->color();
		$color_id = imagecolorallocate($aimg,$color[0], $color[1], $color[2]);
		$next = 7;
		$bx = round($this->width/$this->count);
		$hy = round($this->height/2) - 5;
		for($i=0;$i<$this->count;$i++){
			$rndtxt = $this->word[$i];
			$rndx=mt_rand(1,5);
			$rndy=mt_rand(1,4);
			$leftx = $i<1 ? 5 : $i*$bx+rand(1,5);
			imagestring($aimg,5,$leftx,$hy,$rndtxt,$color_id);
		}
		$pxsum = 108;
		for($i=0;$i<$pxsum;$i++){
			imagesetpixel($aimg,mt_rand(1,$this->width-1),mt_rand(1,$this->height-1),$color_id);
		}
		header("Pragma:no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate"); 
		header("Content-type: image/png");
		imagepng($aimg);
		imagedestroy($aimg);
		exit;
	}

	private function color()
	{
		$list = array("#0000CC","#000066","#000000","#3300CC","#330066","#660000","#006633","#990033","#990066","#336633");
		$total = count($list);
		$color = rand(0,($total-1));
		$color = $list[$color];
		return array(hexdec($color[1].$color[2]),hexdec($color[3].$color[4]),hexdec($color[5].$color[6]));
	}
}
?>