/**
 * 数据调用
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年09月23日
**/
;(function($){
	$.admin_call = {
		type_id:function(val)
		{
			$("div[ext=param]").hide();
			if(!val || val == 'undefined'){
				val = $("input[name=type_id]:checked").val();
				if(!val){
					return false;
				}
			}
			var showid = $("input[name=type_id][value="+val+"]").attr('showid');
			if(!showid || showid == 'undefined'){
				return false;
			}
			var lst = showid.split(",");
			for(var i in lst){
				$("div[name=ext_"+lst[i]+"]").show();
			}
			//动态执行Ajax
			var chk_ajax = $("input[name=type_id][value="+val+"]").attr('ajax');
			if(chk_ajax && chk_ajax != 'undefined'){
				eval(chk_ajax+'()');
			}
			return true;
		},
		
		update_param:function(id,val)
		{
			var url = get_url('call','cate_list');
			if(id && id != 'undefined'){
				id = $("#pid").val();
				if(id && id != 'undefined'){
					url += "&id="+id;
				}
			}
			var typeid = $("input[name=type_id]:checked").val();
			if(typeid != 'arclist' && typeid != 'total' && typeid != 'cate' && typeid != 'catelist' && typeid != 'subcate'){
				return true;
			}
			//异步更新分类
			$.phpok.json(url,function(data){
				if(data.status){
					var cate = data.info.cate;
					var rslist = data.info.catelist;
					var html = '';
					var space = '';
					if(cate){
						html += '<option value="'+cate.id+'">'+p_lang('根分类')+cate.title+'</option>';
						space = '&nbsp; &nbsp;';
					}else{
						html += '<option value="">'+p_lang('请选择…')+'</option>';
					}
					if(rslist){
						for(var i in rslist){
							html += '<option value="'+rslist[i].id+'"';
							if(rslist[i].id == val){
								html += ' selected';
							}
							html += '>'+space+' '+rslist[i]._space +  ' '+rslist[i].title+'</option>';
						}
					}
					$("#cateid").html(html);
					$("div[name=ext_cateid]").show();
					layui.form.render();
				}else{
					$("#cateid").html('<option value="0">.</option>');
					$("div[name=ext_cateid]").hide();
				}
				//更新
				if($("input[name=type_id]:checked").val() == 'arclist'){
					$.admin_call.end_param();
				}
			},true);
		},
		end_param:function()
		{
			$("div[name=ext_need_list],div[name=ext_orderby],div[name=ext_attr],div[name=ext_fields]").hide();
			var pid = $("#pid").val();
			if(!pid || pid == "undefined"){
				return true;
			}
			var url = get_url('call','arclist')+"&pid="+pid;
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$("#fields_need_list").html($("#fields_need_default").html()).parent().show();
					$("#orderby_li").html($("#orderby_default").html());
					$("div[name=ext_need_list],div[name=ext_orderby]").show();
					layui.form.render();
					return true;
				}
				var info = rs.info;
				var mtype = (info.mtype && info.mtype == '1') ? true : false;
				var html = $("#fields_need_default").html() + info.need;
				if(mtype){
					html = info.need;
				}
				$("#fields_need_list").html(html).parent().show();
				html = $("#orderby_default").html() + info.orderby;
				if(mtype){
					html = '<input type="button" value="ID" onclick="phpok_admin_orderby(\'orderby\',\'id\')" class="layui-btn layui-btn-sm" />';
					html+= info.orderby;
				}
				$("#orderby_li").html(html);
				if(info.attr == 1){
					$("div[name=ext_attr]").show();
				}
				html = '<div class="button-group">';
				html += '<input type="button" value="'+p_lang('全部字段')+'" onclick="input_fields(\'*\')" class="layui-btn layui-btn-sm" />';
				html += '<input type="button" value="'+p_lang('仅主表字段')+'" onclick="input_fields(\'id\')" class="layui-btn layui-btn-sm" />';
				var lst = info.rslist;
				for(var i in lst){
					html += '<input type="button" value="'+lst[i].title+'" onclick="input_fields(\''+lst[i].identifier+'\')" class="layui-btn layui-btn-sm" />'
				}
				html += '</div>';
				$("#fields_list").html(html);
				$("div[name=ext_fields]").show();
				if(info.mtype && info.mtype == '0'){
					$("div[name=ext_in_sub]").show();
				}
				$("div[name=ext_need_list],div[name=ext_orderby]").show();
				return true;
			})			
		},
		save:function(id)
		{
			$("#"+id).ajaxSubmit({
				'url':get_url('call','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var tip = $("#id").length > 0 ? p_lang('调用信息编辑成功') : p_lang('调用信息添加成功');
						$.dialog.tips(tip,function(){
							$.admin.reload(get_url('call'));
							$.admin.close();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		del:function(id,title)
		{
			var url = get_url("call","delete","id="+id);
			var tip = p_lang('确定要删除 {title}，删除后前台关于此调用的数据将都失效','<span class="red">'+title+'</span>');
			$.dialog.confirm(tip,function(){
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('删除成功'));
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		status:function(id)
		{
			$.phpok.json(get_url("call","status","id="+id),function(rs){
				if(rs.status){
					if(!rs.info){
						rs.info = '0';
					}
					var oldvalue = $("#status_"+id).attr("value");
					var old_cls = "status"+oldvalue;
					$("#status_"+id).removeClass(old_cls).addClass("status"+rs.info);
					$("#status_"+id).attr("value",rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		}
	}
})(jQuery);

function update_type_id(val)
{
	return $.admin_call.type_id(val);
}

function load_catelist()
{
	$("#pid").find('option').show();
	$("#pid").find("option[module=0]").hide();
	var pid = $("#pid").val();
	var cateid =$("#cateid").val();
	$.admin_call.update_param(pid,cateid);
}

function load_catelist2()
{
	$("#pid").find('option').show();
	$("#pid").find("option[module=0]").hide();
	$("#pid").find("option[rootcate=0]").hide();
	var pid = $("#pid").val();
	var cateid =$("#cateid").val();
	$.admin_call.update_param(pid,cateid);
}

function load_project()
{
	$("#pid").find('option').show();
}

function load_project2()
{
	$("#pid").find('option').show();
	$("#pid").find("option[module=0]").hide();
}

function load_project3()
{
	$("#pid").find('option').show();
	$("#pid").find("option[parentid=0]").hide();
}

function load_project4()
{
	$("#pid").find('option').show();
	$("#pid").find("option[parentid!=0]").hide();
}


function input_fields(val)
{
	if(val == '*' || val == 'id'){
		$("#fields").val(val);
	}else{
		var tmp = $("#fields").val();
		if(tmp == '*'){
			$("#fields").val(val);
		}else{
			var n = tmp;
			if(tmp){
				n += ',';
			}
			n += val;
			$("#fields").val(n);
		}
	}
}

//不能为空字段选集
function fields_click(val)
{
	var tmp = $("#fields_need").val();
	if(tmp)
	{
		tmp = tmp+","+val;
	}
	else
	{
		tmp = val;
	}
	$("#fields_need").val(tmp);
}

function open_fields(id)
{
	var project_val = $("#pid").val();
	if(!project_val || project_val == "undefined"){
		$.dialog.alert("动态调用不支持字符选择框，请人工输入<br />不会编写的朋友，请登录官网查看帮助");
		return false;
	}
	var url = get_url("call","fields") +"&id="+$.str.encode(id);
	url += "&project_id="+project_val;
	$.dialog.open(url,{
		"title":p_lang('字符串选择器'),
		"width" : "700px",
		"height" : "80%",
		"resize" : false,
		"lock" : true,
		"ok" : function(){
			if($.dialog.data(id)){
				$("#"+id).val($.dialog.data(id));
			}
		}
	});
}

function orderby_set(val)
{
	var str = $("#orderby").val();
	if(str){
		str += ","+val;
	}else{
		str = val;
	}
	$("#orderby").val(str);
}

function random_string(len) {
	$("#identifier").val($.phpok.rand(len,'letter'));
	return true;
}

$(document).ready(function(){
	chktype = $("input[name=type_id]:checked").val();
	if(chktype){
		$.admin_call.type_id(chktype);
	}
});