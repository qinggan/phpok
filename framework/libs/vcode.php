<?php
/***********************************************************
	Filename: {phpok}/libs/vcode.php
	Note	: 通用图形验证码类，支持干扰线
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
	//要生成的验证码
	private $word = '';
	private $font = 'data/font/airbus_special.ttf';
	
	function __construct()
	{
		$this->width = 76;
		$this->height = 24;
		$this->count = 4;
	}

	function width($width=76)
	{
		$this->width = $width;
	}

	function height($height=24)
	{
		$this->height = $height;
	}

	function count($count=4)
	{
		$this->count = $count;
	}

	function font($fontfile)
	{
		$this->font = $fontfile;
	}

	function word()
	{
		$txt = "345679ACDEFGHJKLMNPQRTUVWXY";
		$length = strlen($txt);
		$thetxt = '';
		for($i=0;$i<$this->count;$i++)
		{
			$thetxt .= $txt[rand(0,$length-1)];
		}
		$this->word = $thetxt;
		return $thetxt;
	}
	
	//这里仅限数字
	function create()
	{
		//清空所有缓存
		ob_end_clean();
		$aimg = imagecreate($this->width,$this->height);
		imagefilledrectangle($aimg, 0, 0, $this->width, $this->height, imagecolorallocate($aimg, 255, 255, 255));
		//$color = $this->color();
		$color1 = rand(1,120);
		$color2 = rand(1,120);
		$color3 = rand(1,120);
		$color_id = imagecolorallocate($aimg,$color1, $color2, $color3);
		$color_id_line = imagecolorallocate($aimg, ($color1+3), ($color2+5), ($color3+3));
		$next = 7;
		for($i=0;$i<$this->count;$i++)
		{
			$angle = rand(-10,10);
			$rndtxt = $this->word[$i];
			$size = rand(12,18);
			imagettftext($aimg, $size, $angle, $next, ($this->height - 5), $color_id, $this->font, $rndtxt);
			$next += $size;
		}
		//画曲线
		//imageline($aimg,0,0,$this->width,$this->height,$color_id);
		//imageline($aimg,0,1,$this->width-1,$this->height,$color_id);
		//$this->lines($aimg,$color_id_line,8);
		//增加同色系干扰素
		$pxsum = 108;
		for($i=0;$i<$pxsum;$i++)
		{
			imagesetpixel($aimg,mt_rand(1,$this->width-1),mt_rand(1,$this->height-1),$color_id);
		}
		header("Pragma:no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate"); 
		header("Content-type: image/png");
		imagepng($aimg);
		imagedestroy($aimg);
		exit;
	}

	//随机颜色
	function color()
	{
		$list = array("#0000CC","#000066","#000000","#3300CC","#330066","#660000","#006633","#990033","#990066","#336633");
		$total = count($list);
		$color = rand(0,($total-1));
		$color = $list[$color];
		return array(hexdec($color[1].$color[2]),hexdec($color[3].$color[4]),hexdec($color[5].$color[6]));
	}


	function _writeCurve($img,$color_id,$st=2)
	{
		$A = mt_rand(1, $this->height/2);                  // 振幅
		$b = mt_rand(-$this->height/4, $this->height/4);   // Y轴方向偏移量
		$f = mt_rand(-$this->height/4, $this->height/4);   // X轴方向偏移量
		$T = mt_rand($this->height*1.5, $this->width*2);  // 周期
		$w = (2* M_PI)/$T;
		$px1 = 0;  // 曲线横坐标起始位置
		$px2 = mt_rand($this->width/2, $this->width * 0.667);  // 曲线横坐标结束位置
		for ($px=$px1; $px<=$px2; $px=$px+ 1.2) {
			if ($w!=0) {
				$py = $A * sin($w*$px + $f)+ $b + $this->height/2;  // y = Asin(ωx+φ) + b
				$i = $st;
				while ($i > 0) {
					imagesetpixel($img, $px + $i, $py + $i, $color_id);
					$i--;
				}
			}
		}

		$A = mt_rand(1, $this->height/2);                  // 振幅
		$f = mt_rand(-$this->height/4, $this->height/4);   // X轴方向偏移量
		$T = mt_rand($this->height*1.5, $this->width*2);  // 周期
		$w = (2* M_PI)/$T;
		$b = $py - $A * sin($w*$px + $f) - $this->height/2;
		$px1 = $px2;
		$px2 = $this->width;
		for ($px=$px1; $px<=$px2; $px=$px+ 1.2) {
			if ($w!=0) {
				$py = $A * sin($w*$px + $f)+ $b + $this->height/2;  // y = Asin(ωx+φ) + b
				$i = $st;
				while ($i > 0) {
					imagesetpixel($img, $px + $i, $py + $i, $color_id);
					$i--;
				}
			}
		}
	}

	//画干扰线
	function lines($img,$color_id,$st=1)
	{
		$this->_writeCurve($img,$color_id,$st);
	}
}
//$vcode = new vcode_lib();
//$vcode->word();
//$vcode->create();
?>