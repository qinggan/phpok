/**
 * 个人中心更换头像涉及到的JS
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2023年5月26日
 * @更新 2023年5月26日
**/
function update_avatar(rs)
{
	if(!rs || !rs.status){
		$.dialog.alert(rs.info);
		return false;
	}
	$.phpok.json(api_url('usercp','avatar'),function(rs){
		return true;
	},{'data':rs.info.filename});
}
function preview(img, selection)
{
	$('#x1').val(selection.x1);
	$('#y1').val(selection.y1);
	$('#x2').val(selection.x2);
	$('#y2').val(selection.y2);
	$('#w').val(selection.width);
	$('#h').val(selection.height);
}
function preview2(img, selection)
{
	$('#x1').val(selection.x1);
	$('#y1').val(selection.y1);
	$('#x2').val(selection.x2);
	$('#y2').val(selection.y2);
	$('#w').val(selection.width);
	$('#h').val(selection.height);
}
function ready_cut(width,height)
{
	$('#thumbnail').imgAreaSelect({
		"aspectRatio"	: '1:1',
		"minWidth"		: "150",
		"minHeight"		: "150",
		"x1"			: "0",
		"y1"			: "0",
		"x2"			: "150",
		"y2"			: "150",
		"onSelectChange": preview,
		"imageWidth"	: width,
		"imageHeight"	: height,
		"handles"		: true
	});
}
function save_thumb()
{
	var x1 = $('#x1').val();
	var y1 = $('#y1').val();
	var x2 = $('#x2').val();
	var y2 = $('#y2').val();
	var w = $('#w').val();
	var h = $('#h').val();
	var thumb_id = $("#thumb_id").val();
	if(!thumb_id){
		$.dialog.alert('未上传图片');
		return false;
	}
	if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
		$.dialog.alert("未设置裁剪框！");
		return false;
	}
	var url = api_url('usercp','avatar_cut');
	url += "&thumb_id="+thumb_id;
	url += "&x1="+x1;
	url += "&y1="+y1;
	url += "&x2="+x2;
	url += "&y2="+y2;
	url += "&x1="+x1;
	url += "&w="+w;
	url += "&h="+h;
	//存储并更新图片
	$.phpok.json(url,function(rs){
		if(!rs.status){
			$.dialog.tips(rs.info);
			return false;
		}
		$.dialog.tips('头像更新成功',function(){
			$.phpok.reload();
		}).lock();
		return false;
	});
}