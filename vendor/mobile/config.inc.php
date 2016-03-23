<?php
/**
 * 手机验证程序
 * @file mobile/config.inc.php
 * @author phpok.com <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @version 5.0.0
 * @date 2016年01月22日
 */
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
$config['include'] = 'Mobile_Detect.php';
$config['class'] = 'Mobile_Detect';
$config['auto'] = '';
?>