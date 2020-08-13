/**
 * 导航菜单设置
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月26日
**/
$(document).ready(function(){
	layui.form.on("radio(type)",function(data){
		var list = new Array('project','cate','content','link');
		for(var i in list){
			if(list[i] == data.value){
				$("#type-"+list[i]).show();
			}else{
				$("#type-"+list[i]).hide();
			}
		}
		if(data.value == 'content' || data.value == 'link'){
			$("#submenu_html").hide();
		}else{
			$("#submenu_html").show();
		}
	});
	layui.form.on("select(pid-project)",function(data){
		if(!data.value){
			$("#submenu").html("<option value=''>自定义…</option>");
			layui.form.render("select");
			return true;
		}
		$.admin_menu.update_submenu(data.value);
	});
	layui.form.on("select(pid-cate)",function(data){
		if(!data.value){
			$("#pid-catelist").html('').hide();
			return true;
		}
		var url = get_url('menu','catelist','pid='+data.value);
		$.phpok.json(url,function(rs){
			if(!rs.status){
				$.dialog.alert(rs.info);
				return false;
			}
			var html = template('pid-cate-catelist', {
				catelist: rs.info
			});
			$("#pid-catelist").html(html).show();
			$.admin_menu.update_submenu(data.value);
			layui.form.render('select');
		});
	});
});