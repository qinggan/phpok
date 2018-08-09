/**
 * 后台自定义表单中涉及到的JS触发
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年01月18日
**/
;(function($){
	$._configForm = {
		text:function(id,val)
		{
			if(id == 'form_btn'){
				if(val == '' || val == 'undefined'){
					$("#ext_quick_words_html").show();
					$("#ext_color_html").hide();
					return true;
				}
				if(val == 'color'){
					$("#ext_quick_words_html").hide();
					$("#ext_color_html").show();
					return true;
				}
				$("#ext_quick_words_html").hide();
				$("#ext_color_html").hide();
				return true;
			}
			if(id == 'eqt'){
				$("#ext_quick_type").val(val);
			}
		},

		extitle:function(id,val,eid,etype)
		{
			if(id == 'form_pid'){
				if(!val || val == 'undefined' || val == '0'){
					$("#fields_show_html,#fields_used_html,#true_delete_html").hide();
					return true;
				}
				var url = get_url('form','project_fields','pid='+val);
				if(eid && eid != "undefined"){
					url += "&eid="+eid;
				}
				if(etype && etype != "undefined"){
					url += "&etype="+etype;
				}
				$.phpok.json(url,function(data){
					if(data.status){
						if(!data.info){
							$("#fields_show_html,#fields_used_html,#true_delete_html").hide();
							return true;
						}
						var slist = data.info.show;
						var html = '<ul class="layout">';
						for(var i in slist){
							html += '<li><label><input type="checkbox" name="form_show_editing[]" value="'+i+'"';
							if(slist[i].status){
								html += ' checked';
							}
							html += ' />'+slist[i].title+'</label></li>'
						}
						html += "</ul>";
						$("#fields_show").html(html);
						$("#fields_show_html,#true_delete_html").show();
						//使用数据
						var elist = data.info.used;
						var html = '<ul class="layout">';
						for(var i in elist){
							html += '<li><label><input type="checkbox" name="form_field_used[]" value="'+i+'"';
							if(elist[i].status){
								html += ' checked';
							}
							html += ' />'+elist[i].title+'</label></li>'
						}
						html += "</ul>";
						$("#fields_used").html(html);
						$("#fields_used_html,#true_delete_html").show();
						return true;
					}
					$("#fields_show_html,#fields_used_html,#true_delete_html").hide();
					$.dialog.alert(data.info);
					return false;
				});
				return true;
			}
			if(id == 'form_is_single'){
				if(val == 1){
					$("#form_maxcount_li").hide();
					$("#form_maxcount").val(1);
				}else{
					$("#form_maxcount_li").show();
					$("#form_maxcount").val(20);
				}
				return true;
			}
		},

		/**
		 * 表单选择器，对表单内容进行格式化操作
		 * @参数 val 选择的表单类型
		 * @参数 id 要写入的HTML字段
		 * @参数 eid 已存在值
		 * @参数 etype 值的来源
		**/
		option:function(val,id,eid,etype)
		{
			if(!val || val == "undefined"){
				$("#"+id).html("").hide();
				return false;
			}
			var url = get_url("form","config","id="+$.str.encode(val));
			if(eid && eid != "undefined"){
				url += "&eid="+eid;
			}
			if(etype && etype != "undefined"){
				url += "&etype="+etype;
			}
			$.phpok.ajax(url,function(rs){
				if(rs && rs != 'exit'){
					$("#"+id).html(rs).show();
				}
			});
		}
	}
})(jQuery);