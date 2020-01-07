<?php
/**
 * 图形验证码类，使用PNG透明图片
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年1月3日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class vcode_lib
{
	private $width = 76;
	private $height = 24;
	private $count = 4;
	private $word = '';
	private $btype = '_bg_line';
	private $font = '';
	private $root = '';
	
	public function __construct()
	{
		$this->root = str_replace("\\","/",dirname(__FILE__))."/../../";
		$this->width = 76;
		$this->height = 24;
		$this->count = 4;
		$rand = mt_rand(0,1);
		$this->btype = $rand ? '_bg_line' : '_bg_imagesetpixel';
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
		if($count){
			$this->count = $count;
		}
		return $this->count;
	}

	public function font($font='')
	{
		if(!$font){
			return $this->font;
		}
		if(file_exists($font)){
			$this->font = $font;
		}else{
			$this->font = '';
		}
		return $this->font;
	}

	public function word($count='')
	{
		if($count){
			$this->count($count);
		}
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
		$rand = rand(0,1);
		if($rand){
			$this->_bg_line($aimg,$color_id);
		}else{
			$this->_bg_imagesetpixel($aimg,$color_id);
		}
		if($this->font && function_exists('imagettftext')){
			$next = 7;
			for($i=0;$i<$this->count;$i++){
				$angle = rand(-30,30);
				$rndtxt = $this->word[$i];
				$size = rand(12,18);
				imagettftext($aimg, $size, $angle, $next, ($this->height - 5), $color_id, $this->font, $rndtxt);
				$next += $size;
			}
		}else{
			$bx = round($this->width/$this->count);
			$hy = round($this->height/2) - 5;
			for($i=0;$i<$this->count;$i++){
				$rndtxt = $this->word[$i];
				$rndx=mt_rand(1,5);
				$rndy=mt_rand(1,4);
				$leftx = $i<1 ? 5 : $i*$bx+rand(1,5);
				imagestring($aimg,5,$leftx,$hy,$rndtxt,$color_id);
			}
		}		
		header("Pragma:no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate"); 
		header("Content-type: image/png");
		imagepng($aimg);
		imagedestroy($aimg);
		exit;
	}

	private function _bg_line($aimg,$color_id)
	{
		$max_x = $this->width - 1;
		$max_y = $this->height - 1;
		for($i=0;$i<5;$i++){
			$x1 = rand(1,intval($max_x/2));
			$y1 = rand(1,intval($max_y/2));
			$x2 = rand(intval($max_x/2),$max_x);
			$y2 = rand(intval($max_y/2),$max_y);
			for($t=0;$t<2;$t++){
				imageline($aimg,$x1+$t, $y1+$t,$x2+$t, $y2+$t,$color_id);
			}
	    }

	}

	private function _bg_imagesetpixel($aimg,$color_id=0)
	{
		$pxsum = 36 * $this->count;
		$max_x = $this->width-1;
		$max_y = $this->height - 1;
		for($i=0;$i<$pxsum;$i++){
			imagesetpixel($aimg,mt_rand(1,$max_x),mt_rand(1,$max_y),$color_id);
		}
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