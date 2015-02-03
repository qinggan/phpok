<?php
/*****************************************************************************************
	文件： plugins/qrcode/api.php
	备注： 二维码图片接口
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月16日 22时49分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class api_qrcode extends phpok_plugin
{
	public $me;
	private $path;
	private $qr_wh = 6;
	private $qr_logo;
	function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->qr_wh = $this->me['param']['wh'] ? $this->me['param']['wh'] : 6;
		$this->qr_logo = $this->me['param']['logo'] ? $this->me['param']['logo'] : '';
		$this->tpl->assign('plugin',$this->me);
	}

	public function index()
	{
		$content = $this->get('content');
		if(strlen($content) >= 2940)
		{
			$content = $this->lib('string')->cut($content,2940);
		}
		$filename = 'data/cache/'.md5($content).'.png';
		if(!file_exists($this->dir_root.$filename))
		{
			include_once($this->dir_root.'plugins/qrcode/phpqrcode.php');
			QRcode::png($content, $filename, QR_ECLEVEL_M, $this->qr_wh,1);
			if($this->qr_logo)
			{
				$qr = imagecreatefromstring(file_get_contents($filename));
				$logo = imagecreatefromstring(file_get_contents($this->qr_logo));
				$qr_width = imagesx($qr);
				$qr_height = imagesy($qr);
				$logo_width = imagesx($logo);
				$logo_height = imagesy($logo);
				$logo_qr_width = $qr_width / 5;
				$scale = $logo_width / $logo_qr_width;
				$logo_qr_height = $logo_height / $scale;
				$from_width = ($qr_width - $logo_qr_width) / 2;
				imagecopyresampled($qr, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
				imagepng($qr,$this->dir_root.$filename);
				imagedestroy($qr);
				imagedestroy($logo);
			}
		}
		header("Pragma:no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate"); 
		header("Content-type: image/png");
		echo file_get_contents($this->dir_root.$filename);
	}
}

?>