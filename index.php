<?php
/**
 * PHPOK企业站系统
 *
 * 本程序采用LGPL协议开源授权，具体内容：(http://www.gnu.org/licenses/lgpl.html)
 *
 * 有问题请进官网查阅，我们的官网是：(http://www.phpok.com)
 *
 * @file index.php
 * @author phpok.com <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @version 4.5.x
 * @date 2016年01月17日
 */
/**
 * 定义常量，所有PHP文件仅允许从这里入口
 */
define("PHPOK_SET",true);
/**
 * 定义APP_ID，不同APP_ID调用不同的文件
 */
define("APP_ID","www");

/**
 * 定义根目录，如果此项出错，请将定义改成
 *     define("ROOT","./");
 */
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");

/**
 * 定义框架目录
 */

define("FRAMEWORK",ROOT."framework/");

/**
 * 检测是否已安装，如未安装跳转到安装页面，建议您在安装成功后去除这个判断。
 */
if(!file_exists(ROOT."data/install.lock")){
	header("Location:phpokinstall.php");
	exit;
}

/**
 * 引入初始化文件
 */
require(FRAMEWORK.'init.php');
?>