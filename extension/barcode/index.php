<?php
/**
 * 条码生成器
 * @package phpok\extension\barcode
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月11日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class barcode_lib extends _init_lib
{
	private $font;
	public function __construct()
	{
		parent::__construct();
		require_once 'phar://' . $this->dir_extension . 'barcode/barcode.phar';
		require_once('phar://barcode.phar/BCGFont.php');
		require_once('phar://barcode.phar/BCGColor.php');
		require_once('phar://barcode.phar/BCGDrawing.php'); 
		require_once('phar://barcode.phar/BCGcode39.barcode.php');
	}

	public function font($font='',$size=18)
	{
		if($font){
			$this->font = new BCGFont($font,$size);
		}
		return $this->font;
	}

	public function create($txt='',$filename='')
	{
		if($font){
			$this->font($font,$size);
		}
		$color_black = new BCGColor(0, 0, 0);
		$color_white = new BCGColor(255, 255, 255); 

		$code = new BCGcode39();
		$code->setScale(2); // Resolution
		$code->setThickness(30); // Thickness
		$code->setForegroundColor($color_black); // Color of bars
		$code->setBackgroundColor($color_white); // Color of spaces
		$code->setFont($this->font); // Font (or 0)
		$code->parse($txt); // Text
		$drawing = new BCGDrawing($filename, $color_white);
		$drawing->setBarcode($code);
		$drawing->draw();
		$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
	}
}
