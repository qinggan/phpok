/**
 * 样式管理器对应的CSS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月26日
**/
$(document).ready(function(){
	var obj = $.dialog.opener;
	var id = '#'+$("#id").val();
	var vid = '#'+$("#vid").val();
	var css = obj.$(id).val();
	if(css && css != 'undefined'){
		css = css.toLowerCase();
		$('#content').val(css);
		//对CSS进行格式化处理
		var list = css.split(';');
		for(var i in list){
			var tmp = (list[i]).split(":");
			if(tmp[0] == 'font-weight' && tmp[1] == 'bold'){
				$("#bold").attr("checked",true);
			}
			if(tmp[0] == 'font-style' && tmp[1] == 'italic'){
				$("#italic").attr("checked",true);
			}
			if(tmp[0] == 'text-decoration' && tmp[1]){
				$("input[name=text-decoration][value="+tmp[1]+"]").attr('checked',true);
			}
			if(tmp[0] == 'color' && tmp[1]){
				$("#color").val(tmp[1]);
			}
			if(tmp[0] == 'background-color' && tmp[1]){
				$("#bgcolor").val(tmp[1]);
			}
		}
	}
	$('#content').on('focus',function () {
		$(this).blur();
	});

});

function save()
{
	var id = '#'+$("#id").val();
	var vid = '#'+$("#vid").val();
	var css = '';
	if($("#bold").is(":checked")){
		css += "font-weight:bold;";
	}
	if($("#italic").is(":checked")){
		css += "font-style:italic;";
	}
	var text = $("input[name=text-decoration]:checked").val();
	if(text){
		css += "text-decoration:"+text+";";
	}
	var color = $("#color").val();
	if(color){
		css += "color:"+color+";";
	}
	var bgcolor = $("#bgcolor").val();
	if(bgcolor){
		css += "background-color:"+bgcolor+";";
	}
	var obj = $.dialog.opener;
	if(css){
		obj.$(id).val(css);
		obj.$(vid).attr("style",css);
	}else{
		obj.$(id).val('');
		obj.$(vid).removeAttr("style");
	}
	$.dialog.close();
	return true;
}
