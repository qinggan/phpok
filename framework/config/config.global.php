<?php
/***********************************************************
	Filename: {phpok}config/config.global.php
	Note	: 全站全局参数
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月3日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

$config["debug"] = false; //启用调试
$config["gzip"] = true;//启用压缩
$config['develop'] = false;//开发状态
$config["ctrl_id"] = "c";//取得控制器的ID
$config["func_id"] = "f";//取得应用方法的ID
$config["admin_file"] = "admin.php";//后台入口
$config["www_file"] = "index.php";//网站首页，这里一般不修改，除非首页要另外制作，才改名
$config["api_file"] = "api.php";//API接口
$config["is_vcode"] = true;//显示验证码
$config["psize"] = 30;//每页显示数量
$config["pageid"] = "pageid";//分页ID
$config["timezone"] = "Asia/Shanghai";//时区调节，仅限PHP5以上支持
$config["timetuning"] = "0";//时间调节
$config['token_id'] = 'token';//参数Token，后续将全面支持非$_SESSION模式，使用token替代参数传递，与第三方同步如果出现变量冲突，可在此调节
$config['api_remote_sql'] = false; //启用SQL远程执行，建议禁用

//保留词，在前端，存在这些变量时，直接走ctrl_id模式，而不走id模式
$config["reserved"]  = "cart,content,download,login,logout,open,order";
$config['reserved'] .= ",payment,plugin,post,project,register,search";
$config['reserved'] .= ",ueditor,upload,usercp,user,ajax,js,inp,tag,comment,index";

//管理员配置信息
$config['admin']["is_login"] = false; //会员登录验证
$config['admin']["is_admin"] = true; //管理员登录验证

//前端配置信息，当生成网址中包含这些信息时
$config['www']['is_login'] = false;
$config['www']['is_admin'] = false;

//API专用配置信息
$config['api']['is_login'] = false;
$config['api']['is_admin'] = false;

//手机端配置
$config['mobile']['autocheck'] = true; //自动检测手机端，启用后，检测出手机端将读取手机端网页
$config['mobile']['status'] = true; //手机端开始，此项不开启的话，将不使用手机
$config['mobile']['default'] = false; //默认为手机版，为方便开发人员调式，设置为默认后，在网页上也会展示手机版
$config['mobile']['includejs'] = "jquery.touchslide.js"; //手机版自动加载的JS
$config['mobile']['excludejs'] = "jquery.superslide.js"; //手机版要去除加载的JS
//PC端JS的加载
$config['pc']['includejs'] = '';
$config['pc']['excludejs'] = '';

//PHPOK公共JS加载类
//jQuery表单插件，支持ajaxSubmit提交
$config['autoload_js']  = "jquery.md5.js,jquery.phpok.js,global.js,jquery.form.min.js,jquery.json.min.js";


# SESSION存储方式
$config["engine"]["session"]["file"] = "default";
$config["engine"]["session"]["id"] = "PHPSESSION";
$config["engine"]["session"]["timeout"] = 3600;
$config["engine"]["session"]["path"] = ROOT."data/session/";
//当SESSION存储方式为数据库时，执行此配置
$config["engine"]["session"]["table"] = "session";
$config['engine']['session']['auto_methods'] = "auto_start:db";

/**
 * 缓存引挈配置
 * @参数 debug 是否启用调试模式
 * @参数 file 缓存类型，目前支持的有：default memcache redis
 * @参数 status 是否启用缓存 false不使用 true使用（启用后如果系统连不上缓存，会变成false）
 * @参数 timeout 缓存过期时间
 * @参数 folder 缓存文件目录，仅限为 default 时有效
 * @参数 server 缓存服务器，仅在使用 memcache redis 时有效
 * @参数 port 缓存服务器使用的端口，仅在使用 mecache redis 时有效
 * @参数 prefix 缓存Key前缀，防止生成的Key重复
 * @更新时间 2016年07月19日
**/
//缓存默认配置
$config['engine']['cache']['debug'] = false;
$config["engine"]["cache"]["file"] = "default";
$config["engine"]["cache"]["status"] = true;
$config["engine"]["cache"]["timeout"] = 3600;
$config["engine"]["cache"]["folder"] = ROOT."data/cache/";
$config["engine"]["cache"]["server"] = "127.0.0.1";
$config["engine"]["cache"]["port"] = "6379";
$config["engine"]["cache"]["prefix"] = "phpok_";

//Nginx对SERVER_NAME支持不好，如果您使用Nginx，且使用多站点，建议您改成：HTTP_HOST
$config['get_domain_method'] = 'SERVER_NAME';

//SEO优化分割线
$config['seo']['line'] = ' - ';
//SEO优化模式
//title，即传过来的值
//seo，即内置的SEO标题
//sitename，即网站名称
$config['seo']['format'] = '{title}-{sitename}-{seo}';

//订单状态设定
$config['order']['price'] = 'product,shipping,fee,discount';

//针对收藏夹里图片获取
$config['fav']['thumb_id'] = 'thumb';
$config['fav']['note_id'] = 'content';

//购物车里的图片来自字段
$config['cart']['thumb_id'] = 'thumb';
$config['cart']['gd_id'] = '';


//针对JSONP的操作
/**
 * JSONP的参数，getid，表示可以通过get或post得到的callback信息
 * 为空使用default方法
**/
$config['jsonp']['getid'] = 'callback';
$config['jsonp']['default'] = 'callback';