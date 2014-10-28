<?php
$current1 = $current2 = $current3 = $current4 = $current5 = '';
$current2 = 'current';
include_once(INSTALL_DIR."tpl/head.php");
?>
<script type="text/javascript">
function submit_next()
{
	$.dialog.confirm('请确认检查您的空间是否都符合要求了，只有都符合要求，安装才能正常！',function(){
		$.phpok.go('index.php?step=config');
	});
}
</script>
<div class="tips_box">
	<div class="tips_title">提示消息</div>
	<div class="tips_txt">
		<p>本系统采用PHP+MySQL编写，这里对系统环境进行简单测试，请保证下列文件或目录可读写(Unix和Linux服务器请设置以下文件夹的属性为777，文件属性为666）：星号 <span style="color:red">*</span> 表示任意值</p>
		<ol>
			<li>data/</li>
			<li>data/tpl_*/</li>
			<li>data/cache/</li>
			<li>res/</li>
		</ol>
    </div>    
</div>

<table width="980" border="0" cellspacing="0" cellpadding="0" class="tablebox">
	<tr class="head_bg">
		<td>&nbsp;</td>
		<td>PHPOK最低要求</td>
		<td>PHPOK最佳配置</td>
		<td>当前环境检测</td>
	</tr>
	<tr>
		<td class="lft">操作系统</td>
		<td>不限</td>
		<td>UNIX/LINUX/FREEBSD</td>
		<td><?php echo PHP_OS;?></td>
	</tr>
	<tr>
		<td class="lft">PHP版本</td>
		<td>5.0.x</td>
		<td>5.3.x</td>
		<td><?php echo PHP_VERSION;?></td>
	</tr>
	<tr>
		<td class="lft">附件上传</td>
		<td>2M+</td>
		<td>10M+</td>
		<td><?php echo $rs['upload'];?></td>
	</tr>
	<tr>
		<td class="lft">MYSQL支持</td>
		<td>5.0.x</td>
		<td>5.5.x</td>
		<td>
			<?php echo $rs['mysql'] ? '支持' : '<span class="col_red">不支持</span>';?>
			<?php
			if($rs['mysql_server'])
			{
				echo ",Server: ".$rs['mysql_server'];
			}
			?>
		</td>
	</tr>
	<tr>
		<td class="lft">磁盘空间</td>
		<td>10M+</td>
		<td>50M+</td>
		<td><?php echo $rs['space'];?></td>
	</tr>
	<tr>
		<td class="lft">PHP组件：Curl库</td>
		<td>支持</td>
		<td>支持</td>
		<td><?php echo $rs['curl'];?></td>
	</tr>
	<tr>
		<td class="lft">PHP组件：Session</td>
		<td>支持</td>
		<td>支持</td>
		<td><?php echo $rs['session'];?></td>
	</tr>
	<tr>
		<td class="lft">PHP组件：GD库</td>
		<td>支持</td>
		<td>支持</td>
		<td><?php echo $rs['gd'];?></td>
	</tr>
	<tr>
		<td class="lft">PHP组件：Zlib压缩</td>
		<td>支持</td>
		<td>支持</td>
		<td><?php echo $rs['zlib'];?></td>
	</tr>
	<tr>
		<td class="lft">目录 data/</td>
		<td>读写</td>
		<td>读写</td>
		<td><?php echo $rs['data_write'];?></td>
	</tr>
	<tr>
		<td class="lft">目录 data/cache/</td>
		<td>读写</td>
		<td>读写</td>
		<td><?php echo $rs['cache_write'];?></td>
	</tr>
	<tr>
		<td class="lft">目录 data/tpl_admin/</td>
		<td>读写</td>
		<td>读写</td>
		<td><?php echo $rs['admin_write'];?></td>
	</tr>
	<tr>
		<td class="lft">目录 data/tpl_www/</td>
		<td>读写</td>
		<td>读写</td>
		<td><?php echo $rs['www_write'];?></td>
	</tr>
	<tr>
		<td class="lft">目录 res/</td>
		<td>读写</td>
		<td>读写</td>
		<td><?php echo $rs['res_write'];?></td>
	</tr>
</table>
<div class="btn_wrap">
	<input name="" type="button" class="next_btn" value="下一步" onclick="submit_next()" />
	<input name="" type="button" class="previous_btn" value="上一步" onclick="$.phpok.go('index.php')" />
	<div class="cl"></div>
</div>
<?php include_once(INSTALL_DIR."tpl/foot.php");?>
