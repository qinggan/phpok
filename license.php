<?php
/**
 * PHPOK企业站系统，使用PHP语言及MySQL数据库编写的企业网站建设系统，基于LGPL协议开源授权
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 */

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
* 授权方式，支持MIT，PBIZ，CBIZ，三种模式，PBIZ，表示个人商业授权，CBIZ表示企业商业授权
*/
define("LICENSE","MIT");

/**
* 授权时间
*/
define("LICENSE_DATE","2013-11-29");

/**
* 授权的域名，注意必须以.开始，仅支持国际域名，二级域名享有国际域名授权
*/
define("LICENSE_SITE",".phpok.com");

/**
* 授权码，16位或32位的授权码，要求全部大写
*/
define("LICENSE_CODE","FD42ABF3940BF0BC0DF22DD2B038ADDF");

/**
* 授权者称呼，企业授权填写公司名称，个人授权填写姓名
*/
define("LICENSE_NAME","phpok.com");

/**
* 显示开发者信息，即Powered by信息
*/
define("LICENSE_POWERED",true);