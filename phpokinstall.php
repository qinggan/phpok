<?php
/**
 * PHPOK企业站系统，使用PHP语言及MySQL数据库编写的企业网站建设系统，基于LGPL协议开源授权
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年9月2日
**/

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
define('PHPOK_SET',true);
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");
define('DIR_CONFIG',ROOT.'_config/');
define('DIR_CACHE',ROOT.'_cache/');
define('DIR_DATA',ROOT.'_data/');
define('DATA',ROOT.'_data/');
if(function_exists("date_default_timezone_set")){
	date_default_timezone_set('Asia/Shanghai');
}
if(file_exists(ROOT.'version.php')){
	include(ROOT.'version.php');
}
if(!defined('VERSION')){
	define('VERSION','4.x');
}
function error($tips="",$url="",$time=2)
{
	echo '<!DOCTYPE html>'."\n";
	echo '<html>'."\n";
	echo '<head>'."\n";
	echo '<meta charset="utf-8" />'."\n";
	echo '<meta http-equiv="expires" content="wed, 26 feb 1997 08:21:57 gmt">'."\n";
	echo '<title>友情提示</title>'."\n";
	echo '<style type="text/css">body{font-size:12px;font-family:"Microsoft Yahei","宋体","Tahoma","Arial"}.body{margin:100px auto auto auto;width:550px;border:1px solid #8F8F8F;}.red{color:red;}.body .tips{height:70px;}.body .tips .title{margin-left:70px;font-weight:bold;height:20px;padding-top:15px;}.body .tips .note{margin-left:70px;height:30px;}.body .tips .txt{margin-left:70px;padding-top:10px;line-height:50px;font-weight:bold;}</style>'."\n";
	echo '</head>'."\n";
	echo '<body>'."\n";
	echo '<div class="body"><div class="tips">';
	if($url){
		echo '<div class="title">'.$tips.'</div>';
		echo '<div class="note"><a href="'.$url.'">系统将在 <span class="red">'.$time.'秒</span> 后跳转，手动跳转请点这里</div>';
	}else{
		echo '<div class="txt">'.$tips.'</div>';
	}
	echo '</div></div>';
	if($url){
		echo '<script type="text/javascript>"'."\n";
		echo 'function refresh()'."\n";
		echo '{'."\n\t";
		echo 'window.location.href = "'.$url.'";'."\n";
		echo '}'."\n";
		echo 'window.setTimeout("refresh()",'.($time*1000).');'."\n";
		echo '</script>'."\n";
	}
	echo '</body>'."\n</html>";
	exit;
}

if(!function_exists('P_Lang')){
	function P_Lang($info,$replace=''){
		if($replace && is_array($replace)){
			foreach($replace as $key=>$value){
				$info = str_replace(array('{'.$key.'}','['.$key.']'),$value,$info);
			}
		}
		return $info;
	}
}

function root_url()
{
	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$port = $_SERVER["SERVER_PORT"];
	//$myurl = $http_type.$_SERVER["SERVER_NAME"];
	$myurl = $_SERVER["SERVER_NAME"];
	if($port != "80" && $port != "443"){
		$myurl .= ":".$port;
	}
	$site = array("domain"=>$myurl);
	$docu = $_SERVER["PHP_SELF"];
	$array = explode("/",$docu);
	$count = count($array);
	$dir = "";
	if($count>1){
		foreach($array AS $key=>$value){
			$value = trim($value);
			if($value && ($key+1) < $count){
				$dir .= "/".$value;
			}
		}
	}
	$dir .= "/";
	$dir = str_replace(array("//","install/"),array("/",""),$dir);
	$site["dir"] = $dir;
	$site['url'] = $http_type.$myurl.$dir;
	return $site;
}

function format_sql($sql)
{
	global $db;
	$sql = str_replace("\r","\n",$sql);
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			$db->query($query);
		}
	}
}

function checkdir_rw(&$status,$dir='')
{
	if(substr($dir,-1) != '/'){
		$dir .= "/";
	}
	$tmpfile = time().".txt";
	$file = $dir.$tmpfile;
	touch($dir.$tmpfile);
	$dir_info = '<span class="darkblue">读写</span>';
	if(!file_exists($file)){
		$dir_info = '<span class="red">不可写</span>';
		$status = false;
	}else{
		if(!is_readable($file)){
			$dir_info = '<span class="red">不可读</span>';
			$status = false;
		}
		unlink($file);
	}
	$showDir = str_replace(ROOT,'',$dir);
	$info = '<tr><td class="lft">目录：'.$showDir.'</td><td>读写</td><td>'.$dir_info.'</td></tr>';
	echo $info;
}

function rand_string($length=10)
{
	$first = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$letter_length = strlen($first)-1;
	$rand_str = $first[rand(0,$letter_length)];
	//取得以下随机数
	$a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz@%^#&!';
	$maxlength = strlen($a)-1;
	for($i=0;$i<($length-1);++$i){
		$rand_str .= $a[rand(0,$maxlength)];
	}
	return $rand_str;
}

class install
{
	public function head($num=1)
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
		echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
		echo '<head>'."\n";
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
		echo '<title>PHPOK企业站系统安装</title>'."\n";
		$this->cssinfo();
		$this->jsinfo();
		echo '</head>'."\n";
		echo '<body>'."\n";
		echo '<div class="header">';
		echo '<div class="logo">PHPOK <span>'.VERSION.'</span></div>';
		echo '<div class="step"><div class="line"></div><ul class="step_num">';
		$current = array('1'=>'','2'=>'','3'=>'','4'=>'','5'=>'','6'=>'');
		if($num){
			$current[$num] = 'current';
		}
		echo '<li class="'.$current[1].'"><span class="num">1</span><p class="name">检测安装环境</p></li>';
		echo '<li class="'.$current[2].'"><span class="num">2</span><p class="name">可写检测</p></li>';
		echo '<li class="'.$current[3].'"><span class="num">3</span><p class="name">阅读安装协议</p></li>';
		echo '<li class="'.$current[4].'"><span class="num">4</span><p class="name">配置资料</p></li>';
		echo '<li class="'.$current[5].'"><span class="num">5</span><p class="name">开发安装</p></li>';
		echo '<li class="'.$current[6].'"><span class="num">6</span><p class="name">完成安装</p></li>';
		echo '</ul></div></div>';
	}

	public function foot()
	{
		echo '<div class="footer">Powered By phpok.com 版权所有 &copy; 2005-'.date("Y").', All right reserved.</div>'."\n";
		echo '</body>'."\n";
		echo '</html>';
		exit;
	}

	public function env_check()
	{
		echo '<div class="tips_box"><div class="tips_title">提示消息</div>';
		echo '<div class="tips_txt">';
		echo '<p>本系统采用PHP+MySQL编写，这里对系统环境进行简单测试，请保证下列文件或目录可读写(Unix和Linux服务器请设置以下文件夹的属性为777，文件属性为666）：星号 <span style="color:red">*</span> 表示任意值</p></div></div>';
		$status = true;
		$obj = PDO::ATTR_DRIVER_NAME;
		if(function_exists('mysqli_connect') || (class_exists("pdo") && extension_loaded('pdo_mysql'))){
			$mysql_status = '<span class="darkblue">支持</span>';
		}else{
			$mysql_status = '<span class="red">不支持</span>';
			$status = false;
		}
		echo '<table width="980" border="0" cellspacing="0" cellpadding="0" class="tablebox">';
		echo '<tr class="head_bg"><td>&nbsp;</td><td>PHPOK最低要求</td><td>PHPOK最佳配置</td><td>当前环境检测</td></tr>';
		echo '<tr><td class="lft">PHP版本</td><td>5.6.x</td><td>7.1.x</td><td>'.PHP_VERSION.'</td></tr>';
		echo '<tr><td class="lft">附件上传</td><td>2M+</td><td>10M+</td><td>'.get_cfg_var('upload_max_filesize').'</td></tr>';
		echo '<tr><td class="lft">MYSQL支持</td><td>5.1.x</td><td>5.5.x</td><td>'.$mysql_status.'</td></tr>';
		$curl = $this->func_check('curl_close');
		if(!$curl['status']){
			$status = false;
		}
		echo '<tr><td class="lft">PHP组件：Curl库</td><td>支持</td><td>支持</td><td>'.$curl['info'].'</td></tr>';
		$session = $this->func_check('session_start');
		if(!$session['status']){
			$status = false;
		}
		echo '<tr><td class="lft">PHP组件：Session</td><td>支持</td><td>支持</td><td>'.$session['info'].'</td></tr>';
		$xml = $this->func_check('simplexml_load_file');
		if(!$xml['status']){
			$status = false;
		}
		echo '<tr><td class="lft">PHP组件：SimpleXML</td><td>支持</td><td>支持</td><td>'.$xml['info'].'</td></tr>';
		$json = $this->func_check('json_encode');
		if(!$json['status']){
			$status = false;
		}
		echo '<tr><td class="lft">PHP组件：JSON</td><td>支持</td><td>支持</td><td>'.$json['info'].'</td></tr>';
		$gd = $this->func_check('gd_info');
		if(!$gd['status']){
			$status = false;
		}
		echo '<tr><td class="lft">PHP组件：GD库</td><td>支持</td><td>支持</td><td>'.$gd['info'].'</td></tr>';
		$zlib = $this->func_check('gzclose');
		if(!$zlib['status']){
			$status = false;
		}
		echo '<tr><td class="lft">PHP组件：Zlib压缩</td><td>支持</td><td>支持</td><td>'.$zlib['info'].'</td></tr>';
		//检测根目录是否可写
		$tmpfile = time().".txt";
		touch(ROOT.$tmpfile);
		$dir_info = '<span class="darkblue">可读写</span>';
		if(!file_exists(ROOT.$tmpfile)){
			$dir_info = '<span class="red">不可写</span>';
			$status = false;
		}else{
			unlink(ROOT.$tmpfile);
		}
		echo '<tr><td class="lft">网站根目录</td><td>可读写</td><td>可读写</td><td>'.$dir_info.'</td></tr>';
		echo '</table>';
		echo '<div class="btn_wrap">';
		if(!$status){
			echo '<div style="line-height:33px;color:red;text-align:center;">检测不通过，不能执行安装</div>';
		}else{
			echo '<input name="" type="button" class="next_btn" value="下一步" onclick="window.location.href=\'?step=checkdir\'" />';
		}
		echo '<div class="cl"></div></div>';
	}

	public function check_dir()
	{
		$status = true;
		echo '<div class="tips_box"><div class="tips_title">提示消息</div>';
		echo '<div class="tips_txt">';
		echo '<p>检测环境目录是否可写</p></div></div>';
		echo '<table width="980" border="0" cellspacing="0" cellpadding="0" class="tablebox">';
		echo '<tr class="head_bg"><td>目录或文件</td><td>要求</td><td>当前环境检测</td></tr>';
		$tmpfile = time().".txt";
		touch(ROOT.$tmpfile);
		$dir_info = '<span class="darkblue">读写</span>';
		if(!file_exists(ROOT.$tmpfile)){
			$dir_info = '<span class="red">不可写</span>';
			$status = false;
		}else{
			if(!is_readable(ROOT.$tmpfile)){
				$dir_info = '<span class="red">不可读</span>';
				$status = false;
			}
			unlink(ROOT.$tmpfile);
		}
		echo '<tr><td class="lft">网站根目录</td><td>读写</td><td>'.$dir_info.'</td></tr>';
		if(is_writable(DIR_CONFIG.'db.ini.php')){
			$info = '<span class="darkblue">正常</span>';
			if(!is_readable(DIR_CONFIG.'db.ini.php')){
				$info = '<span class="red">不可读</span>';
				$status = false;
			}
		}else{
			$info = '<span class="red">异常，无法写入</span>';
			$status = false;
		}
		echo '<tr><td class="lft">文件：_config/db.ini.php</td><td>写入</td><td>'.$info.'</td></tr>';
		checkdir_rw($status,ROOT.'_cache/');
		checkdir_rw($status, DIR_DATA);
		checkdir_rw($status, DIR_DATA.'crontab/');
		checkdir_rw($status, DIR_DATA.'design/');
		checkdir_rw($status, DIR_DATA.'json/');
		checkdir_rw($status, DIR_DATA.'log/');
		checkdir_rw($status, DIR_DATA.'session/');
		checkdir_rw($status, DIR_DATA.'tpl_admin/');
		checkdir_rw($status, DIR_DATA.'tpl_www/');
		checkdir_rw($status, DIR_DATA.'update/');
		checkdir_rw($status, DIR_DATA.'xml/');
		checkdir_rw($status, DIR_DATA.'xml/fields/');
		checkdir_rw($status, DIR_DATA.'zip/');
		checkdir_rw($status, ROOT.'res/');
		echo '</table>';
		if($status){
			echo '<div class="btn_wrap">';
			echo '<input name="" type="button" class="next_btn" value="下一步" onclick="window.location.href=\'?step=readme\'" />';
			echo '<input name="" type="button" class="prev_btn" value="上一步" onclick="window.location.href=\'?\'" />';
			echo '<div class="cl"></div></div>';
		}else{
			echo '<div style="line-height:33px;color:red;text-align:center;height:50px;">检测不通过，不能执行安装</div>';
		}
	}

	public function readme()
	{
		if(!file_exists(ROOT.'LICENSE')){
			error('请保留 LICENSE 文件');
		}
		echo '<div class="agreement">';
		echo '<div class="txt_box">';
		$content = file_get_contents('LICENSE');
		echo '<pre>'.$content."</pre>";
		echo '</div>';
		echo '</div>';
		echo '<script type="text/javascript">'."\n";
		echo 'function submit_next()'."\n";
		echo '{'."\n\t";
		echo 'if(!$("#agree").is(":checked")){'."\n\t\t";
		echo '$.dialog.tips("请勾选同意本站安装协议").lock();'."\n\t\t";
		echo 'return false;'."\n\t\t";
		echo '}'."\n\t";
		echo 'window.location.href = "?step=install";'."\n";
		echo '}'."\n";
		echo '</script>';
		echo '<div class="btn_wrap">';
		echo '<label class="choose_check"><input id="agree" type="checkbox" />我已阅读并接受该服务条款</label>';
		echo '<input name="" type="button" class="next_btn" value="下一步" onclick="submit_next()" />';
		echo '<input name="" type="button" class="prev_btn" value="上一步" onclick="window.location.href=\'?step=checkdir\'" />';
		echo '<div class="cl"></div></div>';
	}

	public function config()
	{
		$config = array();
		$config['db'] = parse_ini_string(file_get_contents(DIR_CONFIG.'db.ini.php'));
		$dbconfig = $config['db'];
		unset($config);
		$site = root_url();
		$site['title'] = "PHPOK企业站";
		$optlist = '<select name="file" id="file" style="padding:5px;border:1px solid #cacaca;">';
		if(function_exists("mysqli_close")){
			$optlist .= '<option value="mysqli">使用 MySQLi 连接数据库</option>';
		}
		if(class_exists('pdo') && extension_loaded('pdo_mysql')){
			$optlist .= '<option value="pdo_mysql">使用 PDO-MySQL 连接数据库</option>';
		}
		$optlist .= '</select>';
		echo <<<EOT
<div class="tips_box">
	<div class="tips_title">提示消息</div>
	<div class="tips_txt">
   	  <p>填写网站安装要用到的配置，如数据库信息等，请仔细填写</p>
    </div>    
</div>
<script type="text/javascript">
function submit_next()
{
	var chk = check_connect(true);
	if(!chk){
		return false;
	}
	var domain = jQuery("#domain").val();
	if(!domain){
		jQuery.dialog.tips('域名不能为空').lock();
		return false;
	}
	var user = jQuery('#admin_user').val();
	if(!user){
		jQuery.dialog.tips('管理员账号不能为空').lock();
		return false;
	}
	var email = jQuery('#admin_email').val();
	if(!email){
		jQuery.dialog.tips('管理员邮箱不能为空').lock();
		return false;
	}
	var newpass = jQuery('#admin_newpass').val();
	if(!newpass){
		jQuery.dialog.tips('管理员密码不能为空').lock();
		return false;
	}
	var chkpass = jQuery('#admin_chkpass').val();
	if(!chkpass){
		jQuery.dialog.tips('管理员确认密码不能为空').lock();
		return false;
	}
	if(newpass != chkpass){
		jQuery.dialog.tips('两次输入的管理员密码不一致').lock();
		return false;
	}
	return true;
}
function check_connect(isin)
{
	var url = "?step=checkdb";
	var file = jQuery("#file").val();
	url += "&file="+file;
	var host = jQuery("#host").val();
	if(!host){
		jQuery("#host").val('127.0.0.1');
		host = '127.0.0.1';
	}
	url += "&host="+encodeURIComponent(host);
	var port = jQuery("#port").val();
	if(!port){
		jQuery("#port").val('3306');
	}
	url += "&port="+encodeURIComponent(port);
	var user = jQuery("#user").val();
	if(!user){
		jQuery.dialog.tips("请填写数据库账号！").lock();
		return false;
	}
	url += "&user="+encodeURIComponent(user);
	var pass = $("#pass").val();
	var chkpass = $("#chkpass").val();
	if(pass){
		if(!chkpass){
			jQuery.dialog.tips('请再次输入数据库密码').lock();
			return false;
		}
		if(pass != chkpass){
			jQuery.dialog.tips('两次输入的数据库密码不一致').lock();
			return false;
		}
	}else{
		var q = confirm("请填写数据库密码，如果您确定为空，请按确定");
		if(q == '0'){
			return false;
		}
	}
	url += "&pass="+encodeURIComponent(pass)+"&chkpass="+encodeURIComponent(chkpass);
	var data = jQuery("#data").val();
	if(!data){
		jQuery.dialog.tips("请填写您的数据库名称，不能为空").lock();
		return false;
	}
	url += "&data="+encodeURIComponent(data);
	var info = $.ajax({'url':url,'dataType':'html','cache':false,'async':false}).responseText;
	var rs = $.parseJSON(info);
	if(!rs.status){
		jQuery.dialog.tips(rs.info).lock();
		return false;
	}
	if(!isin || isin == 'undefined'){
		jQuery.dialog.alert('测试连接数据库通过',true,'succeed');
	}
	return true;
}
</script>
<div class="tips_box">
	<div class="tips_title">配置数据库连接</div>
	<form method="post" action="?step=save" onsubmit="return submit_next()">
	<div class="input_box">
		<ul>
			<li><span class="l_name">数据库引挈：</span>
				$optlist
			</li>
    		<li>
	    		<span class="l_name">数据库服务器：</span>
	    		<input  name="host" id="host" type="text" class="infor_input col_red" value="{$dbconfig['host']}" />
	    		<p class="tips_p">MySQL数据库服务器地址，一般为127.0.0.1</p>
	    	</li>
        	<li><span class="l_name">数据库端口：</span>
				<input type="text" class="infor_input col_red" name="port" id="port" value="{$dbconfig['port']}" />
				<p class="tips_p">MySQL默认端口为3306，请根据您实际情况调整</p>
			</li>
        	<li><span class="l_name">数据库用户名：</span>
				<input type="text" class="infor_input" name="user" id="user" value="{$dbconfig['user']}" />
				<p class="tips_p">MySQL数据库用户名</p>
			</li>
			<li><span class="l_name">数据库密码：</span>
				<input type="password" class="infor_input" name="pass" id="pass" value="" />
				<p class="tips_p">MySQL数据库密码</p>
			</li>
			<li><span class="l_name">数据库确认密码：</span>
				<input type="password" class="infor_input" name="chkpass" id="chkpass" value="" />
				<p class="tips_p">请再填写一次数据库密码</p>
			</li>
			<li><span class="l_name">数据库名：</span>
				<input type="text" class="infor_input" name="data" id="data" value="{$dbconfig['data']}" />
				<p class="tips_p">数据库名称<span class="col_red">（数据库必须存在，不存在请先创建！）</span></p>
			</li>
			<li><span class="l_name">表名前缀：</span>
				<input type="text" class="infor_input" name="prefix" id="prefix" value="{$dbconfig['prefix']}" />
				<p class="tips_p">同一数据库安装多个应用时可改变前缀</p>
			</li>
			<li><span class="l_name">&nbsp;</span>
				<input name="" type="button" class="next_btn" value="检测数据库配置是否正确" onclick="check_connect()" style="float:none;" />
			</li>
        </ul>
    </div>   
</div>
<div class="tips_box">
	<div class="tips_title">站点信息设置</div>
	<div class="input_box">
		<ul>			
			<li><span class="l_name">网站域名：</span>
				<input type="text" class="infor_input" name="domain" id="domain" value="{$site['domain']}" />
				<p class="tips_p">设置网站绑定的域名，不能有/和http://</p>
			</li>
			<li><span class="l_name">安装目录：</span>
				<input type="text" class="infor_input" name="dir" id="dir" value="{$site['dir']}" />
				<p class="tips_p">根目录请设为/</p>
			</li>
			<input type="hidden" name="demo" value="1"/>
        </ul>
    </div>
</div>
<div class="tips_box">
	<div class="tips_title">管理员设置</div>
	<div class="input_box">
		<ul>
			<li><span class="l_name">管理员账号：</span>
				<input type="text" class="infor_input" name="admin_user" id="admin_user" value="admin" />
				<p class="tips_p">中、英文均可使用</p>
			</li>
			<li><span class="l_name">管理员Email地址：</span>
				<input type="text" class="infor_input" name="admin_email" id="admin_email" />
				<p class="tips_p">E-mail请一定填写正确有效的地址</p>
			</li>
			<li><span class="l_name">管理员密码：</span>
				<input type="password" class="infor_input" name="admin_newpass" id="admin_newpass" />
				<p class="tips_p">密码长度不能小于6位，且区分大小写</p>
			</li>
			<li><span class="l_name">确认密码：</span>
				<input type="password" class="infor_input" name="admin_chkpass" id="admin_chkpass" />
				<p class="tips_p">请重复输入一次密码，确认</p>
			</li>
        </ul>
    </div>   
</div>
		
EOT;
		echo '<div class="btn_wrap">';
		echo '<input name="" type="submit" class="next_btn" value="下一步" />';
		echo '<input name="" type="button" class="prev_btn" value="上一步" onclick="window.location.href=\'?step=readme\'" />';
		echo '<div class="cl"></div></div>';
		echo '</form>';
	}

	public function import_info()
	{
		echo <<<EOT
<script type="text/javascript">
function waiting(id)
{
	var val = jQuery("#"+id).attr("status");
	if(val == 'wait'){
		jQuery("#"+id).html('<img src="images/loading.gif" width="16" height="16" />');
		jQuery("#"+id).attr("status","installing");
		jQuery("#"+id+"_status").html('...');
	}
	jQuery("#"+id+"_status").append('.');
	if(val != "ok" && val != 'error')
	{
		window.setTimeout(function(){waiting(id);},230);
	}
}
function to_admin()
{
	var d = jQuery("#installing").attr('installok');
	if(d == 'false'){
		alert('正在安装中，不能点击');
		return false;
	}
	window.location.href = 'admin.php';
}
function starting_install(id)
{
	var url = "?step=ajax_"+id;
	jQuery.ajax({
		'url':url,
		'cache':false,
		'async':true,
		'dataType':'html',
		'success':function(info){
			if(info){
				if(info == 'ok'){
					jQuery("#"+id).attr("status","ok");
					jQuery("#"+id).html('完成');
					if(id == 'endok'){
						jQuery("#installing").val("进入后台").attr('installok','true');
						jQuery(".step_num li").removeClass("current");
						jQuery(".step_num li").last().addClass("current");
						alert("PHPOK安装完成，您可以点击《进入后台》按钮可以进入后台设置");
					}else{
						var _i = 0;
						jQuery("span[name=install]").each(function(i){
							var tmp = jQuery(this).attr('status');
							if(tmp == "wait" && _i<1){
								_i++;
								var tmpid = jQuery(this).attr("id");
								waiting(tmpid);
								window.setTimeout(function(){starting_install(tmpid)},1500);
								return false;
							}
						});
					}
				}else{
					jQuery("#"+id).attr("status","error");
					jQuery("#"+id).html('<i class="col_red">'+info+'</i>');
				}
			}
		}
	});
}
</script>
<div class="tips_box">
	<div class="tips_title">提示消息</div>
	<div class="tips_txt">
		<p>数据库信息已保存，正在安装其他项目，请耐心等待</p>
    </div>    
</div>

<div class="tips_box">
	<div class="tips_title">安装进度</div>
	<div class="install">
		<ul>
		<li>数据库文件导入<span id="importsql_status"></span><span name="install" id="importsql" status="wait"></span></li>
		<li class="grey_bg">初始化数据<span id="initdata_status"></span><span name="install" id="initdata" status="wait"></span></li>
		<li>安装管理员信息<span id="iadmin_status"></span><span name="install" id="iadmin" status="wait"></span></li>
		<li class="grey_bg">清空缓存<span id="clearcache_status"></span><span name="install" id="clearcache" status="wait"></span></li>
		<li>完成安装<span id="endok_status"></span><span name="install" id="endok" status="wait"></span></li>
		</ul>
	</div>
</div>

<div class="btn_wrap">
	<input name="" type="button" class="next_btn" id="installing" value="安装中…" installok='false' onclick="to_admin()" />
	<div class="cl"></div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	waiting('importsql');
	window.setTimeout(function(){starting_install('importsql')},1500);
});
</script>
EOT;
	}
	
	private function func_check($funName = '',$id='')
	{
		$status =  function_exists($funName) ? true : false;
		if($status){
			$info = '<span class="darkblue">支持</span>';
		}else{
			$info = '<span class="red">不支持</span>';
		}
		return array('status'=>$status,'info'=>$info);
	}

	private function loading()
	{
		$info = 'images/loading.gif';
		return $info;
	}
	
	private function cssinfo()
	{
		echo '<style type="text/css">'."\n";
		echo 'body,h1,h2,h3,h4,h5,h6,p,th,td,dl,dd,form,fieldset,legend,input,textarea,select{margin:0;padding:0}'."\n";
		echo 'body{font:12px "微软雅黑","Arial Narrow",HELVETICA;background:#fff;-webkit-text-size-adjust:100%}'."\n";
		echo 'a{color:#172c45;text-decoration:none}'."\n";
		echo 'a:hover{color:#cd0200;text-decoration:underline}'."\n";
		echo 'em{font-style:normal}'."\n";
		echo 'img{border:0;vertical-align:middle}'."\n";
		echo 'table{border-collapse:collapse;border-spacing:0}'."\n";
		echo 'p{word-wrap:break-word}'."\n";
		echo 'input{font-family:"微软雅黑"}'."\n";
		echo '.cl{clear:both}'."\n";
		echo '.fl{float:left}'."\n";
		echo '.fr{float:right}'."\n";
		echo '.fz_16{font-size:16px}'."\n";
		echo '.fz_14{font-size:14px}'."\n";
		echo '.col_red{color:#ff0000}'."\n";
		echo '.red{color:#ff0000}'."\n";
		echo '.darkblue{color:darkblue}'."\n";
		echo '.lft{text-align:left}'."\n";
		echo '.header{height:63px;width:980px;margin:0 auto;padding:40px 0 30px}'."\n";
		echo '.logo{float:left;width:270px;height:64px;line-height:64px;font-size:2.6em;font-weight:bold;font-style:italic;color:#205696}'."\n";
		echo '.logo span{font-size:0.5em;color:#ff0000}'."\n";
		echo '.step{width:650px;float:right;height:50px;position:relative;margin-top:15px}'."\n";
		echo '.step .line{width:650px;height:3px;background:#dde6ed;position:absolute;left:0;top:13px;z-index:9}'."\n";
		echo '.step .step_num{width:650px;height:50px;position:absolute;left:0;top:0;z-index:10;margin:0;padding:0;list-style:none}'."\n";
		echo '.step .step_num li{width:100px;float:left;list-style:none}'."\n";
		echo '.step .step_num li .num{height:28px;width:28px;background:#DBE6EC;border-radius:14px;text-align:center;line-height:28px;color:#333;margin:0 auto;display:block}'."\n";
		echo '.step .step_num li .name{display:block;text-align:center;padding:5px 0 0;font-size:12px;color:#333}'."\n";
		echo '.step .step_num .current .num{background:#2C91D9;color:#fff}'."\n";
		echo '.step .step_num .current .name{color:#5496dd}'."\n";
		echo '.agreement{width:938px;border:1px solid #e6e6e6;background:#fdfdfd;margin:0 auto;height:400px;overflow-x:hidden;overflow-y:scroll;padding:20px}'."\n";
		echo '.agreement .txt_box{width:100%;font-size:12px;line-height:22px}'."\n";
		echo '.agreement .txt_box p{margin-bottom:20px}'."\n";
		echo '.btn_wrap{width:980px;margin:15px auto ;height:32px;overflow:hidden}'."\n";
		echo '.btn_wrap .choose_check{float:left;line-height:32px}'."\n";
		echo '.btn_wrap .choose_check input{float:left;margin:9px 5px 0 0}'."\n";
		echo '.next_btn{background:#4995E0;border:1px solid #4388CD;padding:5px 10px;color:#fff;text-align:center;font-size:14px;cursor:pointer;float:right;margin-left:15px}'."\n";
		echo '.prev_btn{background:#959595;border:1px solid #858585;padding:5px 10px;color:#fff;text-align:center;font-size:14px;cursor:pointer;float:right;margin-left:15px}'."\n";
		echo '.footer{width:980px;padding:20px 0 30px;border-top:1px solid #c7c7c7;text-align:center;font-size:14px;margin:0 auto}'."\n";
		echo '.tips_box{width:978px;border:1px solid #e6e6e6;margin:0 auto 15px}'."\n";
		echo '.tips_box .tips_title{background:#F5F5F5;height:39px;border-bottom:1px solid #e6e6e6;padding:0 20px;line-height:39px;font-size:16px;color:#03C}'."\n";
		echo '.tips_box .tips_txt{padding:20px;line-height:22px;font-size:14px}'."\n";
		//echo '.tips_box .tips_txt p{margin-bottom:15px}'."\n";
		echo '.tablebox{border-collapse:collapse;width:980px;margin:0 auto 15px;text-align:center}'."\n";
		echo '.tablebox td{border:#e6e6e6 solid 1px;padding:5px;height:30px}'."\n";
		echo '.tablebox .head_bg{background:#F5F5F5;height:38px}'."\n";
		echo '.input_box{padding:20px;overflow:hidden}'."\n";
		echo '.input_box ul{list-style:none}'."\n";
		echo '.input_box li{line-height:30px;margin-bottom:10px;overflow:hidden}'."\n";
		echo '.input_box select{padding:3px}'."\n";
		echo '.input_box .l_name{width:150px;float:left;text-align:right;font-size:14px;margin-right:5px}'."\n";
		echo '.input_box .infor_input{width:350px;height:22px;border:1px solid #cacaca;padding:3px;float:left;margin-right:10px}'."\n";
		echo '.input_box .tips_p{float:left;line-height:30px}'."\n";
		echo '.install{overflow:hidden}'."\n";
		echo '.install ul{list-style:none;margin:0;padding:0}'."\n";
		echo '.install li{height:30px;line-height:30px;padding:0 30px}'."\n";
		echo '.install .grey_bg{background:#fcfcfc}'."\n";
		echo '.install li span{color:#03C}'."\n";
		echo '</style>'."\n";
	}

	private function jsinfo()
	{
		echo '<link rel="stylesheet" type="text/css" href="css/artdialog.css" />'."\n";
		echo '<script type="text/javascript" src="js/jquery.js"></script>'."\n";
		echo '<script type="text/javascript" src="js/jquery.artdialog.js"></script>'."\n";
		$site = root_url();
		echo '<script type="text/javascript">'."\n";
		echo '$(document).ready(function(){'."\n";
		echo "\t".'(function (config) {'."\n\t";
		echo "\t\t".'config["path"] = "'.$site['url'].'";'."\n";
		echo "\t".'})(art.dialog.defaults);'."\n";
		echo '});'."\n";
		echo '</script>'."\n";
	}

	public function format_url($url)
	{
		$array = parse_url($url);
		if (!isset($array['host'])){
			if(isset($_SERVER["HTTP_HOST"])){
				$array['host'] = $_SERVER["HTTP_HOST"];
			}elseif(isset($_SERVER["SERVER_NAME"])){
				$array['host'] = $_SERVER["SERVER_NAME"];
			}else{
				$array['host'] = "localhost";
			}
		}
		if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == "off" || $_SERVER["HTTPS"] == ""){
			$array['scheme'] = "http";
		}else{
			$array['scheme'] = "https";
		}
		$array['port'] = $_SERVER["SERVER_PORT"] ? $_SERVER["SERVER_PORT"] : 80;
		if(!isset($array['path'])){
			$array['path'] = "/";
		}
		$tmp1 = substr($array['path'],0,1);
		$tmp2 = substr($_SERVER["PHP_SELF"],0,1);
		if($tmp1 != '/' && $tmp2 == '/'){
			$array['path'] = substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], '/') + 1) . $array['path'];
		}
		return $array;
	}

	public function get($id,$system=false)
	{
		$msg = isset($_POST[$id]) ? $_POST[$id] : (isset($_GET[$id]) ? $_GET[$id] : "");
		if($msg == ''){
			return false;
		}
		if($system && is_bool($system)){
			if(!preg_match('/^[a-zA-Z][a-zA-Z0-9\_\-]+$/u',$msg)){
				return false;
			}
			return $msg;
		}else{
			if($system == 'int' || $system == 'intval'){
				return intval($msg);
			}
			if($system == 'float' || $system == 'floatval'){
				return floatval($msg);
			}
			if(is_string($system) && function_exists($system)){
				return $system($msg);
			}
			$msg = stripslashes($msg);
			$msg = str_replace(array("\\","'",'"',"<",">"),array("&#92;","&#39;","&quot;","&lt;","&gt;"),$msg);
			return addslashes($msg);
		}
	}

	public function lib($id)
	{
		if(!$id){
			return false;
		}
		$classname = $id.'_lib';
		if(isset($this->$classname) && is_object($this->$classname)){
			return $this->$classname;
		}
		$file = ROOT.'framework/libs/'.$id.'.php';
		if(!file_exists($file)){
			return false;
		}
		include_once($file);
		$this->$classname = new $classname();
		return $this->$classname;
	}

}

if(file_exists(DIR_DATA.'install.lock')){
	error('已安装过，不能重复安装');
}

#[\AllowDynamicProperties]
class _init_auto
{
	public function __construct()
	{
		//
	}

	public function __destruct()
	{
		//
	}
}

include(ROOT.'framework/engine/db.php');

$install = new install();
$step = $install->get('step',true);
if(!$step){
	$install->head(1);
	$install->env_check();
	$install->foot();
}
if($step == 'checkdir'){
	$install->head(2);
	$install->check_dir();
	$install->foot();
}
if($step == 'readme'){
	$install->head(3);
	$install->readme();
	$install->foot();
}
if($step == 'install'){
	$install->head(4);
	$install->config();
	$install->foot();
}
if($step == 'checkdb'){
	$array = array('status'=>false,'info'=>'正在执行中');
	$file = $install->get('file',false);
	if(!$file){
		$file = 'mysqli';
	}
	$host = $install->get('host',false);
	if(!$host){
		$array['info'] = '数据库服务器不能为空';
		exit(json_encode($array));
	}
	$port = $install->get('port',false);
	if(!$port){
		$port  = '3306';
	}
	$user = $install->get('user',false);
	$pass = $install->get('pass',false);
	$chkpass = $install->get('chkpass',false);
	$data = $install->get('data',false);
	if(!$user){
		$array['info'] = '数据库用户不能为空';
		exit(json_encode($array));
	}
	if($pass && $chkpass){
		if($pass != $chkpass){
			$array['info'] = '两次密码输入不一致';
			exit(json_encode($array));
		}
	}
	$config = array('host'=>$host,'user'=>$user,'pass'=>$pass,'port'=>$port,'data'=>$data);
	$is_include = false;
	if($file == 'mysql' || $file == 'mysqli'){
		$is_include = true;
		include(ROOT.'framework/engine/db/mysqli.php');
	}elseif($file == 'pdo_mysql'){
		$is_include = true;
		include(ROOT.'framework/engine/db/pdo_mysql.php');
	}
	if(!$is_include){
		$array['info'] = '引用数据库链接失败';
		exit(json_encode($array));
	}
	
	$dbname = 'db_'.$file;
	$config['debug'] = true;
	$db = new $dbname($config);
	if(!$db){
		$array['info'] = '数据库类不存在，请检查';
		exit(json_encode($array));
	}
	$db->error_type = "json";
	$db->connect();
	if($db->error || $db->error_id){
		$array['info'] = '错误ID:'.$db->error_id.',错误信息:'.$db->error;
		exit(json_encode($array));
	}
	$array['status'] = true;
	$array['info'] = '成功';
	exit(json_encode($array));
}
if($step == 'save'){
	$file = $install->get("file",false);
	if(!$file){
		$file = "mysqli";
	}
	$is_include = false;
	if($file == 'mysql' || $file == 'mysqli'){
		$is_include = true;
		include(ROOT.'framework/engine/db/mysqli.php');
	}elseif($file == 'pdo_mysql'){
		$is_include = true;
		include(ROOT.'framework/engine/db/pdo_mysql.php');
	}
	if(!$is_include){
		error('引用数据库链接失败');
	}
	$dbconfig = array("file"=>$file);
	$dbconfig['host'] = $install->get("host",false);
	$dbconfig['port'] = $install->get("port",false);
	$dbconfig['user'] = $install->get("user",false);
	$dbconfig['pass'] = $install->get("pass",false);
	$dbconfig['data'] = $install->get("data",false);
	$dbconfig['prefix'] = $install->get("prefix",false);
	$content = file_get_contents(DIR_CONFIG."db.ini.php");
	$content = preg_replace('/file\s*=.*/i','file = "'.$dbconfig['file'].'"',$content);
	$content = preg_replace('/host\s*=.*/i','host = "'.$dbconfig['host'].'"',$content);
	$content = preg_replace('/port\s*=.*/i','port = "'.$dbconfig['port'].'"',$content);
	$content = preg_replace('/user\s*=.*/i','user = "'.$dbconfig['user'].'"',$content);
	$content = preg_replace('/pass\s*=.*/i','pass = "'.$dbconfig['pass'].'"',$content);
	$content = preg_replace('/data\s*=.*/i','data = "'.$dbconfig['data'].'"',$content);
	$content = preg_replace('/prefix\s*=.*/i','prefix = "'.$dbconfig['prefix'].'"',$content);
	file_put_contents(DIR_CONFIG."db.ini.php",$content);
	$info = array();
	$info['domain'] = $install->get('domain',false);
	$info['dir'] = $install->get('dir',false);
	$info['user'] = $install->get('admin_user',false);
	$info['email'] = $install->get('admin_email',false);
	$info['pass'] = $install->get('admin_newpass',false);
	$info['demo'] = $install->get('demo',false);
	$handle = fopen(DIR_DATA.'install.lock.php','wb');
	fwrite($handle,'<?php'."\n");
	foreach($info as $key=>$value){
		$value = str_replace('"','',$value);
		fwrite($handle,'$adminer["'.$key.'"] = "'.$value.'";'."\n");
	}
	$install->head(5);
	$install->import_info();
	$install->foot();
}
if($step == 'ajax_importsql'){
	$config = array();
	$config['db'] = parse_ini_string(file_get_contents(DIR_CONFIG.'db.ini.php'));
	$config['db']['debug'] = true;
	$file = $config['db']['file'];
	include(ROOT.'framework/engine/db/'.$file.'.php');
	$dbname = 'db_'.$file;
	$db = new $dbname($config['db']);
	$sqlist = $install->lib('file')->ls(DIR_DATA.'install/');
	if(!$sqlist){
		error("安装SQL文件不存在，请检查");
	}
	foreach($sqlist as $key=>$value){
		$sql = file_get_contents($value);
		if($db->prefix != 'qinggan_'){
			$sql = str_replace("qinggan_",$db->prefix,$sql);
		}
		$sql = str_replace("\r","\n",$sql);
		$ret = array();
		$num = 0;
		foreach(explode(";\n", trim($sql)) as $query) {
			$queries = explode("\n", trim($query));
			foreach($queries as $query) {
				$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
			}
			$num++;
		}
		unset($sql);
		foreach($ret as $query) {
			$query = trim($query);
			if($query) {
				$db->query($query);
			}
		}
	}
	exit('ok');
}
if($step == 'ajax_initdata'){
	$config = array();
	$config['db'] = parse_ini_string(file_get_contents(DIR_CONFIG.'db.ini.php'));
	$config['db']['debug'] = true;
	$file = $config['db']['file'];
	include(ROOT.'framework/engine/db/'.$file.'.php');
	$dbname = 'db_'.$file;
	$db = new $dbname($config['db']);
	//安装测试数据
	$file = DIR_DATA."install.lock.php";
	if(!file_exists($file)){
		exit("配置文件 _data/install.lock.php 不存在");
	}
	include($file);
	//更新站点信息
	$sql = "UPDATE ".$db->prefix."site_domain SET domain='".$adminer['domain']."'";
	$db->query($sql);
	//更新站点路径
	$sql = "UPDATE ".$db->prefix."site SET dir='".$adminer['dir']."'";
	$db->query($sql);

	

	if(!$adminer['demo']){
		$tblist = $db->list_tables();
		$sql = "SELECT * FROM ".$db->prefix."module";
		$tmplist = $db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$table = $db->prefix."list_".$value['id'];
				if(in_array($table,$tblist)){
					$sql = "DROP TABLE `".$table."`";
					$db->query($sql);
				}
				//删除独立模块
				$table = $db->prefix.$value['id'];
				if(in_array($table,$tblist)){
					$sql = "DROP TABLE `".$table."`";
					$db->query($sql);
				}
			}
		}
		$string  = 'module,fields,list,list_cate,list_biz,list_attr,reply,project,plugins,';
		$string .= 'res,tag,tag_stat,phpok,ext,extc,all,cate,user,user_ext,user_relation,';
		$string .= 'wealth_info,wealth_log,session,payment_log,order_product,order_price,';
		$string .= 'order_payment,order_log,order_invoice,order_express,order_address,order,log,fav,express,';
		$string .= 'cart,cart_product,menu';
		$tmplist = explode(",",$string);
		$tmplist = array_unique($tmplist);
		foreach($tmplist as $key=>$value){
			$table = $db->prefix."".$value;
			if(in_array($table,$tblist)){
				$sql = "TRUNCATE TABLE `".$table."`";
				$db->query($sql);
			}
		}
		$list = $install->lib('file')->ls(ROOT.'res/');
		if($list){
			foreach($list as $key=>$value){
				$install->lib('file')->rm($value,'folder');
			}
		}
	}

	//更新 config 表里的 api_code 值，确保每个密钥都不一样
	$api_code = rand_string();
	$sql = "UPDATE ".$db->prefix."config SET content='".$api_code."' WHERE identifier='api_code'";
	$db->query($sql);
	exit('ok');
}
if($step == 'ajax_iadmin'){
	$config = array();
	$config['db'] = parse_ini_string(file_get_contents(DIR_CONFIG.'db.ini.php'));
	$config['db']['debug'] = true;
	$file = $config['db']['file'];
	include(ROOT.'framework/engine/db/'.$file.'.php');
	$dbname = 'db_'.$file;
	$db = new $dbname($config['db']);
	$file = DIR_DATA."install.lock.php";
	if(file_exists($file)){
		include($file);
		$sql = "TRUNCATE ".$db->prefix."adm";
		$db->query($sql);
		$pass_sub = rand(11,99);
		$pass = md5($adminer['pass'].$pass_sub).":".$pass_sub;
		$data = array('account'=>$adminer['user'],'pass'=>$pass,'email'=>$adminer['email']);
		$data['status'] = 1;
		$data['if_system'] = 1;
		$db->insert_array($data,'adm');
	}
	exit('ok');
}
if($step == 'ajax_clearcache'){
	unlink(DIR_DATA."install.lock.php");
	exit('ok');
}
if($step == 'ajax_endok'){
	touch(DIR_DATA."install.lock");
	touch(DIR_DATA.'first.lock');
	exit('ok');
}