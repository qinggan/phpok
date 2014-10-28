<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?>		<br />
		<?php echo $GLOBALS["app"]->plugin_html_ap("body");?>
	</div>
	<div class="clear"></div>
</div>
<div class="foot" style="text-align:center;"><?php echo debug_time();?></div>
<?php echo $GLOBALS["app"]->plugin_html_ap("foot");?>
<script type="text/javascript">
$(document).ready(function(){
	//自定义右键
	//var debug
	var r_menu = [[{
		'text':'刷新内容',
		'func':function(){
			$.phpok.reload();
		}
	},{
		'text': "显示桌面",
    	'func': function() {top.$.desktop.tohome();}    
	}],[{
		'text':"全局操作",
		'data':[[{
			'text':'清空缓存',
			'func': function() {top.phpok_admin_clear();}
		},{
			'text':'修改我的信息',
			'func':function(){top.phpok_admin_control();}
		},{
			'text':'访问网站首页',
			'func':function(){
				var url = "<?php echo $sys['www_file'];?>?siteId=<?php echo $session['admin_site_id'];?>";
				url = $.phpok.nocache(url);
				window.open(url);
			}
		}]]
	}],[{
		'text':'网页属性',
		'func':function(){
			var url = window.location.href;
			top.$.dialog({
				'title':'网址属性',
				'content':'网址：'+url+'<br /><div style="text-indent:36px"><a href="'+url+'" target="_blank" class="red">新窗口打开</a></div>',
				'lock':true,
				'drag':false,
				'fixed':true
			});
		}
	}]];
	$(window).smartMenu(r_menu);
});
</script>
</body>
</html>