<?php
/**
 * PHPOK企业站系统，使用PHP语言及MySQL数据库编写的企业网站建设系统，基于LGPL协议开源授权
 * @package phpok
 * @author phpok.com
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
**/

/**
 * 定义常量，所有PHP文件仅允许从这里入口
**/
define("PHPOK_SET",true);

/**
 * 定义**APP_ID**，不同**APP_ID**调用不同的文件
**/
define("APP_ID","admin");

/**
 * 定义应用的根目录，如果程序出程，请将ROOT改为：define("ROOT","./");
**/
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");

/**
 * 定义框架目录
**/
define("FRAMEWORK",ROOT."framework/");

/**
 * 引入初始化文件
**/
require(FRAMEWORK.'init.php');