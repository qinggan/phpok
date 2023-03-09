/**
 * 在线升级执行动作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月11日
**/

var lock = false;
function start_update()
{
	$("#start_id").attr("disabled",true).val('正在升级，请稍候…');
	ok_start();
}
function ok_start()
{
	var is_end = true;
	$(".mylist").each(function(i){
		var name = $(this).attr("name");
		var t = 'my_'+name;
		if($("#"+t).text() == '-' && !lock){
			is_end = false;
			lock = true;
			update_load_file(name);
		}
	});
	if(is_end){
		$.dialog.alert(p_lang('升级成功'),function(){
			top.window.location.reload();
		},'succeed');
	}
}
function update_load_file(name)
{
	var url = get_url('update','file','file='+$.str.encode(name));
	$.phpok.json(url,function(rs){
		lock = false;
		if(rs.status == 'ok'){
			$("#my_"+name).html('&#8730;').css("color","blue");

			ok_start();
			return true;
		}
		$("#my_"+name).html('&#215;').css("color","red");
		return false;
	});
}