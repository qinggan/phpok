<?php
/*****************************************************************************************
	文件： tools/admin_password_reset.php
	备注： 管理员密码重置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月18日 09时32分
*****************************************************************************************/
error_reporting(E_ALL ^ E_NOTICE);
define('PHPOK_SET',true);
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");
$html_root = '';
if(is_file(ROOT.'config.php')){
	$dir_root = ROOT;
}elseif(is_file(ROOT.'../config.php')){
	$dir_root = ROOT.'../';
	$html_root = '../';
}

function head()
{
	global $html_root;
	header("Content-type: text/html; charset=utf-8");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
	header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
	header("Cache-control: no-cache,no-store,must-revalidate,max-age=1"); 
	header("Pramga: no-cache"); 
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
	echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
	echo '<head>'."\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
	echo '<title>管理员密码重置</title>'."\n";
	echo '<style type="text/css">'."\n";
	echo 'html,body{font-size:14px;font-family:"Microsoft Yahei","宋体","Tahoma","Arial"}'."\n";
	echo '</style>'."\n";
	echo '<link href="'.$html_root.'css/artdialog.css" rel="stylesheet" type="text/css" />'."\n";
	echo '<script type="text/javascript" src="'.$html_root.'js/jquery.js"></script>'."\n";
	echo '<script type="text/javascript" src="'.$html_root.'js/jquery.artdialog.js"></script>'."\n";
	echo '<script type="text/javascript" src="'.$html_root.'framework/js/jquery.phpok.js"></script>'."\n";
	echo '</head>'."\n";
	echo '<body style="margin:10px auto;">'."\n";
}

function foot()
{
	echo '</body></html>';
}

function password_create($pass)
{
	$password = md5($pass);
	$get_rand = substr($password,rand(0,30),2);
	$newpass = md5($pass.$get_rand).":".$get_rand;
	return $newpass;
}

include($dir_root.'config.php');
include($dir_root.'framework/engine/db.php');
include($dir_root.'framework/engine/db/'.$config['db']['file'].'.php');
$classname = 'db_'.$config['db']['file'];
$db = new $classname($config['db']);
$db->cache_close();
$action = isset($_GET['action']) ? $_GET['action'] : "";
if($action && $action == 'setpass'){
	$adminid = intval($_GET['adminid']);
	$password = $_GET['password'];
	if(!$adminid){
		echo json_encode(array('content'=>'未指定管理员','status'=>'error'));
		exit;
	}
	if(!$password){
		echo json_encode(array('content'=>'密码不能为空','status'=>'error'));
		exit;
	}
	$sql = "UPDATE ".$db->prefix."adm SET pass='".password_create($password)."' WHERE id='".$adminid."'";
	$db->query($sql);
	echo json_encode(array('status'=>'ok'));
	exit;
}
//读管理员列表
$sql = "SELECT * FROM ".$db->prefix."adm WHERE if_system=1";
$rslist = $db->get_all($sql);
head();
echo '<select id="admin_id" style="padding:3px;">';
echo '<option value="">请选择管理员…</option>';
foreach($rslist as $key=>$value){
	echo '<option value="'.$value['id'].'">'.$value['account'].'</option>';
}
echo '</select>';
echo '<input type="text" id="password" style="padding:3px;width:200px;" placeholder="请输入管理员密码" />';
echo '<input type="button" value="修改管理员密码" style="padding:3px 10px;" onclick="update_password()">'
?>
<script type="text/javascript">
function update_password()
{
	var url = "admin_password_reset.php?action=setpass";
	var adminid = $("#admin_id").val();
	if(!adminid){
		$.dialog.alert('请选择要修改的管理员');
		return false;
	}
	url += "&adminid="+adminid;
	var pass = $("#password").val();
	if(!pass){
		$.dialog.alert('管理员密码不能为空');
		return false;
	}
	url += "&password="+$.str.encode(pass);
	$.dialog.confirm("确定要将密码修改成：<span style='color:red'>"+pass+"</span>",function(){
		var rs = $.phpok.json(url);
		if(rs.status == 'ok'){
			$.dialog.alert('密码修改成功',function(){
				$("#password").val('');
			});
		}else{
			$.dialog.alert(rs.content);
		}
		return true;
	});
}
</script>
<div style="line-height:33px;font-size:14px;">
	修改完管理员后，请删除这个工具文件！
</div>
<?php
foot();
?>