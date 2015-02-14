<?php
/***********************************************************
	Filename: install/index.php
	Note	: 安装升级包
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月7日
***********************************************************/
define("PHPOK_SET",true);
//定义根目录
define("INSTALL_DIR",str_replace("\\","/",dirname(__FILE__))."/");
define("ROOT",INSTALL_DIR."../");
include(ROOT."framework/engine/db.php");
include_once(INSTALL_DIR."global.php");

if(is_file(ROOT."data/install.lock"))
{
	error("您已安装过PHPOK4，要重新安装请删除data/install.lock文件");
}

if(!is_file(ROOT."config.php"))
{
	error("配置文件：config.php 不存在，请检查！");
}

if(!G("step"))
{
	include(INSTALL_DIR."tpl/index.php");
	exit;
}
elseif(G("step") == 'check')
{
	//附件上传检测
	$rs = array();
	$rs['upload'] = get_cfg_var('upload_max_filesize');
	$rs['mysql'] = (function_exists('mysql_connect') || function_exists('mysqli_connect')) ? true : false;
	if($rs['mysql'] && function_exists('mysql_get_server_info'))
	{
		$rs['mysql_client'] = @mysql_get_client_info();
		$rs['mysql_server'] = @mysql_get_server_info();
	}
	//磁盘空间
	$rs['space'] = disk_free_space(ROOT) > (50 * 1024 * 1024) ? '50M+' : '通过';
	$rs['curl'] = func_check('curl_close');
	$rs['session'] = func_check("session_start");
	$rs['xml'] = func_check("xml_set_object");
	$rs['gd'] = func_check("gd_info");
	$rs['zlib'] = func_check("gzclose");
	//判断可写目录
	//创建文件
	$tmpfile = time().".txt";
	touch(ROOT."data/".$tmpfile);
	$rs["data_write"] = file_exists(ROOT."data/".$tmpfile) ? '通过' : '<span class="col_red">不通过</span>';
	touch(ROOT."data/cache/".$tmpfile);
	$rs["cache_write"] = file_exists(ROOT."data/cache/".$tmpfile) ? '通过' : '<span class="col_red">不通过</span>';
	touch(ROOT."data/session/".$tmpfile);
	$rs["session_write"] = file_exists(ROOT."data/session/".$tmpfile) ? '通过' : '<span class="col_red">不通过</span>';
	touch(ROOT."data/tpl_admin/".$tmpfile);
	$rs["admin_write"] = file_exists(ROOT."data/tpl_admin/".$tmpfile) ? '通过' : '<span class="col_red">不通过</span>';
	touch(ROOT."data/tpl_www/".$tmpfile);
	$rs["www_write"] = file_exists(ROOT."data/tpl_www/".$tmpfile) ? '通过' : '<span class="col_red">不通过</span>';
	touch(ROOT."res/".$tmpfile);
	$rs["res_write"] = file_exists(ROOT."res/".$tmpfile) ? '通过' : '<span class="col_red">不通过</span>';
	//删除附件
	unlink(ROOT."data/".$tmpfile);
	unlink(ROOT."data/cache/".$tmpfile);
	unlink(ROOT."data/session/".$tmpfile);
	unlink(ROOT."data/tpl_admin/".$tmpfile);
	unlink(ROOT."data/tpl_www/".$tmpfile);
	unlink(ROOT."res/".$tmpfile);
	//
	include(INSTALL_DIR."tpl/check.php");
}
elseif(G("step") == "config")
{
	//配置数据库链接
	include(ROOT."config.php");
	$dbconfig = $config['db'];
	unset($config);
	//支持的数据库类型
	$is_mysql = function_exists("mysql_close") ? true : false;
	$is_mysqli = function_exists("mysqli_close") ? true : false;

	$site = root_url();
	$site['title'] = "PHPOK企业站";
	include(INSTALL_DIR."tpl/config.php");
}
elseif(G("step") == "saveconfig")
{
	if(!isset($_POST['host']))
	{
		error("访问异常，请重新安装！","index.php?_noCache=0.".rand(10000,99999),'error');
	}
	$file = G("file");
	if(!$file) $file = "mysql";
	$dbconfig = array("file"=>$file);
	$dbconfig['host'] = G("host",false);
	$dbconfig['port'] = G("port",false);
	$dbconfig['user'] = G("user",false);
	$dbconfig['pass'] = G("pass",false);
	$dbconfig['data'] = G("data",false);
	$dbconfig['prefix'] = G("prefix",false);
	if(!$dbconfig['user'])
	{
		error("请填写数据库账号","index.php?step=config&_noCache=".rand(1000,9999),"error",3);
	}
	if(!$dbconfig['data'])
	{
		error("数据库名称不能为空","index.php?step=config&_noCache=".rand(1000,9999),"error",3);
	}
	if(!$dbconfig['prefix'])
	{
		$dbconfig['prefix'] = 'qinggan_';
	}
	if(!$dbconfig['host'])
	{
		$dbconfig['host'] = 'localhost';
	}
	if(!$dbconfig['port'])
	{
		$dbconfig['port'] = '3306';
	}
	//测试数据库链接是否正则
	$cls_sql_file = ROOT."framework/engine/db/".$file.".php";
	if(!is_file($cls_sql_file))
	{
		error("数据库类文件：".$cls_sql_file."不存在！","index.php?step=config&_noCache=".rand(1000,9999),"error",3);
	}
	include($cls_sql_file);
	$db_name = "db_".$file;
	$db = new $db_name($dbconfig);
	//判断数据库连接正确与否
	if(!$db->conn_status())
	{
		error("数据库连接失败，请检查您的填写信息","index.php?step=config&_noCache=".rand(1000,9999),"error",3);
	}
	$admin = array();
	$admin['user'] = G("admin_user",false);
	if(!$admin['user'])
	{
		error("管理员账号不能为空","index.php?step=config&_noCache=".rand(1000,9999),"error",3);
	}
	$admin['email'] = G("admin_email",false);
	if(!$admin['email'])
	{
		$admin['email'] = 'admin@admin.com';
	}
	$newpass = G("admin_newpass",false);
	$chkpass = G("admin_chkpass",false);
	if(!$newpass || !$chkpass)
	{
		error("管理员密码不能为空","index.php?step=config&_noCache=".rand(1000,9999),"error",3);
	}
	if($newpass != $chkpass)
	{
		error("两次输入的管理员密码不一致","index.php?step=config&_noCache=".rand(1000,9999),"error",3);
	}
	$admin['pass'] = $newpass;
	//存储配置信息
	$content = file_get_contents(ROOT."config.php");
	//查找替换
	$content = preg_replace('/\$config\["db"\]\["file"\]\s*=\s*[\'|"][a-zA-Z0-9\-\_]*[\'|"];/isU','$config["db"]["file"] = "'.$dbconfig['file'].'";',$content);
	$content = preg_replace('/\$config\["db"\]\["host"\]\s*=\s*[\'|"][a-zA-Z0-9\-\_]*[\'|"];/isU','$config["db"]["host"] = "'.$dbconfig['host'].'";',$content);
	$content = preg_replace('/\$config\["db"\]\["port"\]\s*=\s*[\'|"][a-zA-Z0-9\-\_]*[\'|"];/isU','$config["db"]["port"] = "'.$dbconfig['port'].'";',$content);
	$content = preg_replace('/\$config\["db"\]\["user"\]\s*=\s*[\'|"][a-zA-Z0-9\-\_]*[\'|"];/isU','$config["db"]["user"] = "'.$dbconfig['user'].'";',$content);
	$content = preg_replace('/\$config\["db"\]\["pass"\]\s*=\s*[\'|"][a-zA-Z0-9\-\_]*[\'|"];/isU','$config["db"]["pass"] = "'.$dbconfig['pass'].'";',$content);
	$content = preg_replace('/\$config\["db"\]\["data"\]\s*=\s*[\'|"][a-zA-Z0-9\-\_]*[\'|"];/isU','$config["db"]["data"] = "'.$dbconfig['data'].'";',$content);
	$content = preg_replace('/\$config\["db"\]\["prefix"\]\s*=\s*[\'|"][a-zA-Z0-9\-\_]*[\'|"];/isU','$config["db"]["prefix"] = "'.$dbconfig['prefix'].'";',$content);
	file_put_contents(ROOT."config.php",$content);
	//存储管理员信息
	include(ROOT."framework/libs/file.php");
	$file = new file_lib();
	$admin['title'] = G('title',false);
	$admin['domain'] = G('domain',false);
	$admin['dir'] = G('dir',false);
	$file->vi($admin,ROOT."data/install.lock.php",'adminer');
	include(INSTALL_DIR."tpl/saveconfig.php");
}
elseif(G("step") == "ajax_importsql")
{
	$db = connect_db("ajax");
	if(!file_exists(INSTALL_DIR."phpok.sql"))
	{
		exit("缺少SQL文件：install/phpok.sql");
	}
	$sql = file_get_contents(INSTALL_DIR."phpok.sql");
	if($db->prefix != "qinggan_")
	{
		$sql = str_replace("qinggan_",$db->prefix,$sql);
	}
	//执行安装
	format_sql($sql);
	exit('ok');
}
elseif(G("step") == "ajax_initdata")
{
	$db = connect_db("ajax");
	$truncate_array = array('address','adm','adm_popedom','cart','cart_product','log','order','order_product');
	$truncate_array[] = 'order_address';
	$truncate_array[] = 'payment';
	$truncate_array[] = 'plugins';
	$truncate_array[] = 'reply';
	$truncate_array[] = 'session';
	$truncate_array[] = 'tag';
	$truncate_array[] = 'tag_list';
	$truncate_array[] = 'temp';
	$truncate_array[] = 'site';
	$truncate_array[] = 'site_domain';
	foreach($truncate_array as $key=>$value)
	{
		$sql = "TRUNCATE ".$db->prefix.$value;
		$db->query($sql);
	}
	//清空回复
	$sql = "UPDATE ".$db->prefix."list SET replydate=0";
	$db->query($sql);
	$file = ROOT."data/install.lock.php";
	if(file_exists($file))
	{
		include($file);
		$sql = "INSERT INTO ".$db->prefix."site_domain(id,site_id,domain) VALUES(1,1,'".$adminer['domain']."')";
		$db->query($sql);
		$data = array('domain_id'=>1,'title'=>$adminer['title'],'dir'=>$adminer['dir']);
		$data['status'] = 1;
		$data['content'] = '网站建设中';
		$data['is_default'] = 1;
		$data['tpl_id'] = 1;
		$data['url_type'] = 'default';
		$data['logo'] = 'res/201409/01/27a6e141c3d265ae.jpg';
		$data['register_status'] = 1;
		$data['login_status'] = 1;
		$data['register_close'] = '暂停注册';
		$data['login_close'] = '暂停登录';
		$data['email_charset'] = 'utf-8';
		$data['seo_title'] = '企业建站就找PHPOK，更专业！';
		$data['seo_keywords'] = '企业建站,PHPOK企业站,网站建设';
		$data['seo_desc'] = '高效的企业网站建设系统，可实现高定制化的企业网站电商系统，实现企业网站到电子商务企业网站。';
		$data['biz_sn'] = 'prefix[P]-year-month-date-number';
		$data['currency_id'] = 1;
		$data['upload_guest'] = 0;
		$data['upload_user'] = 1;
		$db->insert_array($data,'site');
	}
	//添加插件
	$data = array('id'=>'identifier','title'=>'标识串自动生成工具','author'=>'phpok.com','version'=>'1.0');
	$data['status'] = 1;
	$data['note'] = '实现名称转拼音，英语的功能';
	$tmp = array('is_youdao'=>0,'keyfrom'=>'','keyid'=>'','is_pingyin'=>1,'is_py'=>1);
	$data['param'] = serialize($tmp);
	$db->insert_array($data,'plugins');
	exit('ok');
}
elseif(G("step") == "ajax_iadmin")
{
	$db = connect_db("ajax");
	$file = ROOT."data/install.lock.php";
	if(file_exists($file))
	{
		include($file);
		$pass_sub = rand(11,99);
		$pass = md5($adminer['pass'].$pass_sub).":".$pass_sub;
		$data = array('account'=>$adminer['user'],'pass'=>$pass,'email'=>$adminer['email']);
		$data['status'] = 1;
		$data['if_system'] = 1;
		$db->insert_array($data,'adm');
	}
	exit('ok');
}
elseif(G("step") == "ajax_clearcache")
{
	unlink(ROOT."data/install.lock.php");
	exit('ok');
}
elseif(G("step") == "ajax_endok")
{
	touch(ROOT."data/install.lock");
	exit('ok');
}

?>