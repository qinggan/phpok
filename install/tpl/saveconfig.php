<?php
$current1 = $current2 = $current3 = $current4 = $current5 = '';
$current4 = 'current';
include_once(INSTALL_DIR."tpl/head.php");
?>
<script type="text/javascript">
function waiting(id)
{
	var val = $("#"+id).attr("status");
	if(val == 'wait')
	{
		$("#"+id).html('<img src="images/loading.gif" width="16" height="16" />');
		$("#"+id).attr("status","installing");
		$("#"+id+"_status").html('...');
	}
	$("#"+id+"_status").append('.');
	if(val != "ok" && val != 'error')
	{
		window.setTimeout(function(){waiting(id);},230);
	}
}
function starting_install(id)
{
	//装载安装进度
	//waiting(id);
	var url = "index.php?step=ajax_"+id;
	$.ajax({
		'url':url,
		'cache':false,
		'async':true,
		'dataType':'html',
		'success':function(info){
			if(info)
			{
				if(info == 'ok')
				{
					$("#"+id).attr("status","ok");
					$("#"+id).html('完成');
					if(id == 'endok')
					{
						$("#installing").val("进入后台");
						$("#installing").click(function(){
							$.phpok.go("../admin.php");
						}).after('<input name="" type="button" class="next_btn" id="install_prev" value="访问首页" onclick="$.phpok.go(\'../index.php\')"  />');
						$(".step_num li").removeClass("current");
						$(".step_num li").last().addClass("current");
						$.dialog.alert("PHPOK安装完成，您可以点击《进入后台》按钮可以进入后台设置");
					}
					else
					{
						var _i = 0;
						$("span[name=install]").each(function(i){
							var tmp = $(this).attr('status');
							if(tmp == "wait" && _i<1)
							{
								_i++;
								var tmpid = $(this).attr("id");
								waiting(tmpid);
								window.setTimeout(function(){starting_install(tmpid)},1500);
								return false;
							}
						});
					}
				}
				else
				{
					$("#"+id).attr("status","error");
					$("#"+id).html('<i class="col_red">'+info+'</i>');
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
	<input name="" type="button" class="next_btn" id="installing" value="安装中…" />
	<div class="cl"></div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	waiting('importsql');
	window.setTimeout(function(){starting_install('importsql')},1500);
	$("#installing").click(function(){
		$.dialog.alert('正在安装中，不能点击');
		return false;
	});
});
</script>
<?php include_once(INSTALL_DIR."tpl/foot.php");?>

