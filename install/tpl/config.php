<?php
$current1 = $current2 = $current3 = $current4 = $current5 = '';
$current3 = 'current';
include_once(INSTALL_DIR."tpl/head.php");
?>
<script type="text/javascript">
function check_save()
{
	if(!$("#host").val())
	{
		$("#host").val('localhost');
	}
	if(!$("#port").val())
	{
		$("#port").val('3306');
	}
	if(!$("#user").val())
	{
		$.dialog.alert("请填写数据库账号！");
		return false;
	}
	var pass = $("#pass").val();
	var chkpass = $("#chkpass").val();
	if(pass)
	{
		if(!chkpass)
		{
			$.dialog.alert('请再次输入数据库密码');
			return false;
		}
		if(pass != chkpass)
		{
			$.dialog.alert('两次输入的数据库密码不一致');
			return false;
		}
	}
	else
	{
		if(!pass)
		{
			var q = confirm("请填写数据库密码，如果您确定为空，请按确定");
			if(q == '0')
			{
				return false;
			}
		}
	}
	if(!$("#data").val())
	{
		$.dialog.alert("请填写您的数据库名称，不能为空");
		return false;
	}
	var prefix = $("#prefix").val();
	if(!prefix)
	{
		$("#prefix").val('qinggan_');
	}
	if(!$("#admin_user").val())
	{
		$.dialog.alert('请输入管理员账号');
		return false;
	}
	if(!$("#admin_email").val())
	{
		$.dialog.alert("请输入管理员的邮箱！");
		return false;
	}
	var admin_newpass = $("#admin_newpass").val();
	var admin_chkpass = $("#admin_chkpass").val();
	if(!admin_newpass || !admin_chkpass)
	{
		$.dialog.alert('管理员密码不能为空');
		return false;
	}
	if(admin_newpass != admin_chkpass)
	{
		$.dialog.alert("两次输入的管理员密码不致");
		return false;
	}
	return true;
}
</script>
<div class="tips_box">
	<div class="tips_title">提示消息</div>
	<div class="tips_txt">
   	  <p>请在下面填写你的数据库帐号信息，通常情况下不需要修改红色选项的内容。</p>
    </div>    
</div>

<form method="post" action="index.php?step=saveconfig&_noCache=0.<?php echo rand(100000,999999);?>" onsubmit="return check_save()">
<div class="tips_box">
	<div class="tips_title">配置数据库连接</div>
	<div class="input_box">
		<ul>
			<li><span class="l_name">数据库引挈：</span>
				<select name="file" id="file">
					<?php if($is_mysql) { ?><option value="mysql">使用MySQL连接数据库</option><?php } ?>
					<?php if($is_mysqli) { ?><option value="mysqli" selected>使用 MySQLi 连接数据库（推荐）</option><?php } ?>
				</select>
			</li>
    		<li>
	    		<span class="l_name">数据库服务器：</span>
	    		<input  name="host" id="host" type="text" class="infor_input col_red" value="<?php echo $dbconfig['host'];?>" />
	    		<p class="tips_p">MySQL数据库服务器地址，一般为localhost</p>
	    	</li>
        	<li><span class="l_name">数据库端口：</span>
				<input type="text" class="infor_input col_red" name="port" id="port" value="<?php echo $dbconfig['port'] ? $dbconfig['port'] : '3306';?>" />
				<p class="tips_p">MySQL默认端口为3306，请根据您实际情况调整</p>
			</li>
        	<li><span class="l_name">数据库用户名：</span>
				<input type="text" class="infor_input" name="user" id="user" value="<?php echo $dbconfig['user'] ? $dbconfig['user'] : 'root';?>" />
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
				<input type="text" class="infor_input" name="data" id="data" value="<?php echo $dbconfig['data'] ? $dbconfig['data'] : '';?>" />
				<p class="tips_p">数据库名称<span class="col_red">（数据库必须存在，不存在请先创建！）</span></p>
			</li>
			<li><span class="l_name">表名前缀：</span>
				<input type="text" class="infor_input" name="prefix" id="prefix" value="<?php echo $dbconfig['prefix'] ? $dbconfig['prefix'] : '';?>" />
				<p class="tips_p">同一数据库安装多个应用时可改变前缀</p>
			</li>
        </ul>
    </div>   
</div>
<div class="tips_box">
	<div class="tips_title">站点信息设置</div>
	<div class="input_box">
		<ul>
			<li><span class="l_name">网站名称：</span>
				<input type="text" class="infor_input" name="title" id="title" value="<?php echo $site['title'];?>" />
				<p class="tips_p">设置网站的名称</p>
			</li>
			
			<li><span class="l_name">网站域名：</span>
				<input type="text" class="infor_input" name="domain" id="domain" value="<?php echo $site['domain'];?>" />
				<p class="tips_p">设置网站绑定的域名，不能有/和http://</p>
			</li>
			<li><span class="l_name">安装目录：</span>
				<input type="text" class="infor_input" name="dir" id="dir" value="<?php echo $site['dir'] ;?>" />
				<p class="tips_p">根目录请设为/</p>
			</li>
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

<div class="btn_wrap">
	<input name="" type="submit" class="next_btn" value="下一步" />
	<input name="" type="button" class="previous_btn" value="上一步" onclick="$.phpok.go('index.php?step=check')" />
	<div class="cl"></div>
</div>
</form>
<?php include_once(INSTALL_DIR."tpl/foot.php");?>
