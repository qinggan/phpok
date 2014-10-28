<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><script type="text/javascript">
function youdao_translate()
{
	var c = $("#title").val();
	if(!c)
	{
		alert("无法取到要转拼音的内容！");
		return false;
	}
	var url = api_url('plugin','','id=<?php echo $pinfo['id'];?>&exec=fanyi&q='+$.str.encode(c));
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$("#identifier").val(rs.content);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

//取得拼音
function pingyin_btn()
{
	var title = $("#title").val() ? $("#title").val() : $("#title").text();
	if(!title)
	{
		$.dialog.alert('没有要拼音的标题');
		return false;
	}
	var url = api_plugin_url('<?php echo $pinfo['id'];?>','pingyin','title='+$.str.encode(title));
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$("#identifier").val(rs.content);
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

function py_btn()
{
	var title = $("#title").val() ? $("#title").val() : $("#title").text();
	if(!title)
	{
		$.dialog.alert('没有要拼音的标题');
		return false;
	}
	var url = api_plugin_url('<?php echo $pinfo['id'];?>','py','title='+$.str.encode(title));
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$("#identifier").val(rs.content);
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

$(document).ready(function(){
	$("#identifier").after($("#<?php echo $pinfo['id'];?>_hidden").html());
});
</script>
<div id="<?php echo $pinfo['id'];?>_hidden" style="display:none;">
	<?php if($pinfo['param']['is_youdao']){ ?>
	<input type="button" value="有道翻译" class="btn" onclick="youdao_translate()" />
	<?php } ?>
	<?php if($pinfo['param']['is_pingyin']){ ?>
	<input type="button" value="取拼音" class="btn" onclick="pingyin_btn()" />
	<?php } ?>
	<?php if($pinfo['param']['is_py']){ ?>
	<input type="button" value="取拼音首字母" class="btn" onclick="py_btn()" />
	<?php } ?>
</div>