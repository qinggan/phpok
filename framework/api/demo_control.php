<?php
/*****************************************************************************************
	文件： {phpok}/api/demo_control.php
	备注： 测试用的
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月9日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class demo_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$id = 'www.Phpok.com';
		$token = $this->lib('token')->encode($id);
		$decode = $this->lib('token')->decode($token);
		$this->_header();
		echo '加密：'.$token;
		echo '<br />';
		
		echo '解密：'.$decode;
		echo '<br />';
		$idcard = "35052519830312051X";
		$chk = $this->lib('common')->idcard_check($idcard);
		echo '身份证验证：'.($chk ? '通过' : '不通过');
		$this->_footer();
	}

	function _header()
	{
		echo '<!DOCTYPE html>'."\n";
		echo '<html>'."\n";
		echo '<head>'."\n";
		echo '	<meta charset="utf-8" />'."\n";
		echo '	<meta http-equiv="cache-control" content="no-cache" />'."\n";
		echo '	<meta http-equiv="expires" content="Mon, 26 Jul 1997 05:00:00 GMT" />'."\n";
		echo '	<meta http-equiv="expires" content="0">'."\n";
		echo '	<title>测试</title>'."\n";
		echo '</head>'."\n";
		echo '<body>'."\n";
	}

	function _footer()
	{
		echo "\n".'</body>'."\n";
		echo '</html>';
		exit;
	}
}

?>