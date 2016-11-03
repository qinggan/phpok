<?php
/**
 * PHPOK企业站系统，使用PHP语言及MySQL数据库编写的企业网站建设系统，基于LGPL协议开源授权
 * @package phpok
 * @author phpok.com
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 */


if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

/**
 * 连接数据库配置
 * @参数 file 字符串，支持 mysqli mysql 和 pdo_mysql，PHP大于5.3版的用户建议使用mysqli或pdo_mysql
 * @参数 host 字符串，数据库服务器，本地请填写localhost或127.0.0.1
 * @参数 port 字符串或数字均可，指数据库服务器的端口号，默认是3306
 * @参数 user 字符串，连接数据库的账号
 * @参数 pass 字符串，连接数据库的密码
 * @参数 data 字符串，数据库名称
 * @参数 prefix 字符串，数据表前缀，实现同一个数据库安装不同版本程序，默认使用 qinggan_
 * @参数 socket 字符串，使用通道连接（即不走网卡，Mysql在Linux下一般是/tmp/mysql.sock，建议有独立主机的用户使用）
 * @参数 debug 布尔值，启用调试后，配合系统的debug为true时，会打印出整个页面执行的SQL语句
 * @更新时间 2016年06月05日
**/
$config["db"]["file"] = "mysqli";
$config["db"]["host"] = "127.0.0.1";
$config["db"]["port"] = "3306";
$config["db"]["user"] = "root";
$config["db"]["pass"] = "root";
$config["db"]["data"] = "phpok";
$config["db"]["prefix"] = "qinggan_";
$config['db']['socket'] = '';
$config['db']['debug'] = false;

/**
 * 手机端参数配置
 * @参数 autocheck 值可选true或false，为true时表示自动检测手机端，启用后，检测出手机端将读取手机端网页
 * @参数 status 值可选true或false，手机端开始，此项为false时不使用手机端
 * @参数 default 值可选true或false，值为true时，在PC端也是打开手机站风格，便于开发人员调试。正式使用时请关闭
 * @更新时间 
**/
$config['mobile']['autocheck'] = true;
$config['mobile']['status'] = true;
$config['mobile']['default'] = false;

/**
 * 开发调试参数本配置
 * @参数 develop 值可选true或false，开发模式，正常运行的网站请设为false，可防止CRSF注入
 * @参数 debug 值可选true或false，启用调试模式后，将不支持zend opcache，在正式投入使用时，请改为false
 * @返回 
 * @更新时间 
**/
$config['develop'] = true;
$config['debug'] = true;