/***********************************************************
	Filename: js/opt.js
	Note	: 选项组用到的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-02 20:16
***********************************************************/
var base_url = basefile + "?" + ctrl_id + "=opt&"+func_id+"=";
;(function($){
	var config = {
		'title':'信息',
		'width':'500px',
		'id':'html-edit-content',
		'okVal':'添加',
		'cancelVal':'取消',
		'taxis':10,
		'group_id':0,
		'pid':0,
		'tid':0
	}
	var opt = {
		init:function(opts){
			config = $.extend({},config,opts);
			if(config.total<1){
				config.total = 10;
			}
			return opt;
		},
		edit:function(id){
			var content = document.getElementById(config.id);
			var that = this;
			$.dialog({
				'width':config.width,
				'title':'修改',
				'content':content,
				'init':function(){
					var obj = $("#opt_"+id+" td");
					var val = obj.eq(1).text();
					var title = obj.eq(2).text();
					var taxis = obj.eq(3).text();
					$("input[name=taxis]").val(taxis);
					$("input[name=title]").val(title);
					$("input[name=val]").val(val);
				},
				'lock':true,
				'ok':function(){
					var url = get_url('opt','edit','id='+id);
					var title = $("input[name=title]").val();
					if(!title){
						$.dialog.alert('显示信息不能为空');
						return false;
					}
					url += "&title="+$.str.encode(title);
					var val = $("input[name=val]").val();
					if(!val){
						$.dialog.alert('值不能为空');
						return false;
					}
					url += "&val="+$.str.encode(val);
					url += "&taxis="+$.str.encode($("input[name=taxis]").val());
					var obj = $.dialog.tips('正在保存数据，请稍候…');
					$.phpok.ajax(url,function(info){
						obj.close();
						if(info == 'ok'){
							$.phpok.reload();
							return true;
						}else{
							if(info){
								$.dialog.alert(info);
							}
						}
						return false;
					});
					
					
				},
				'okVal':config.okVal,
				'cancelVal':config.cancelVal,
				'cancel':true
			});
		},
		create:function(opts){
			this.init(opts);
			var content = document.getElementById(config.id);
			var that = this;
			$.dialog({
				'width':config.width,
				'title':config.title,
				'content':content,
				'init':function(){
					$("input[name=taxis]").val(config.taxis);
				},
				'lock':true,
				'ok':function(){
					var url = get_url('opt','add','group_id='+config.group_id+"&pid="+config.pid);
					var title = $("input[name=title]").val();
					if(!title){
						$.dialog.alert('显示信息不能为空');
						return false;
					}
					url += "&title="+$.str.encode(title);
					var val = $("input[name=val]").val();
					if(!val){
						$.dialog.alert('值不能为空');
						return false;
					}
					url += "&val="+$.str.encode(val);
					url += "&taxis="+$.str.encode($("input[name=taxis]").val());
					var obj = $.dialog.tips('正在保存数据，请稍候…');
					$.phpok.ajax(url,function(info){
						obj.close();
						if(info == 'ok'){
							$.phpok.reload();
						}else{
							if(info){
								$.dialog.alert(info);
							}
							return false;
						}
					});
				},
				'okVal':config.okVal,
				'cancelVal':config.cancelVal,
				'cancel':true
			});
		}
	}
	$.extend({
		phpok_opt:function(opts){
			var method = arguments[0];
			if(opt[method]) {
				method = opt[method];
				arguments = Array.prototype.slice.call(arguments, 1);
			} else if( typeof(method) == 'object' || !method ) {
				method = opt.init;
			} else {
				$.error( 'Method ' +  method + ' does not exist on jQuery.phpok_opt' );
				return this;
			}
			return method.apply(this, arguments);
		}
	});
})(jQuery);

//添加选项组
function add_opt_group()
{
	var t = $("#title_0").val();
	if(!t){
		alert("名称不能为空！");
		return false;
	}
	var url = base_url + "group_save&title="+$.str.encode(t);
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		$.phpok.reload();
	}
	else
	{
		if(!msg) msg = "添加失败！";
		alert(msg);
		return false;
	}
}

//更新选项组
function update_opt_group(id)
{
	var t = $("#title_"+id).val();
	if(!t)
	{
		alert("名称不能为空！");
		return false;
	}
	var url = base_url + "group_save&title="+$.str.encode(t)+"&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		alert("选项组更新成功！");
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "更新失败！";
		alert(msg);
		return false;
	}
}

//删除选项组
function delete_opt_group(id)
{
	var t = $("#title_"+id).val();
	if(!t)
	{
		var qc = confirm("确定要删除此选项组吗？");
	}
	else
	{
		var qc = confirm("确要定删除选项组："+t+"，删除后相应的数据也会删除，请慎用！");
	}
	if(qc == '0') return false;
	var url = base_url + "group_del&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		alert("选项组信息删除成功！");
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "选项组删除失败！";
		alert(msg);
		return false;
	}
}

//跳转到指定选项组内容列表
function opt_list(id)
{
	var url = base_url+"list&group_id="+id;
	direct(url);
}

//更新选项内容
function update_opt(id)
{
	if(!id)
	{
		alert("操作异常，没有指定要更新的内容ID！");
		return false;
	}
	var url = base_url+"edit&id="+id;
	//值
	var v = $("#val_"+id).val();
	if(!v)
	{
		alert("值不能为空！");
		return false;
	}
	url += "&val="+$.str.encode(v);
	//显示
	var s = $("#title_"+id).val();
	if(!s)
	{
		alert("显示信息不能为空，您可以设置成与值一样！");
		return false;
	}
	url += "&title="+$.str.encode(s);
	var taxis = $("#taxis_"+id).val();
	if(taxis)
	{
		url += "&taxis="+$.str.encode(taxis);
	}
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		alert('更新成功');
	}
	else
	{
		if(!msg) msg = "内容更新失败！";
		alert(msg);
		return false;
	}
}

//删除选项内容
function delete_opt(id)
{
	$.dialog.confirm("确定要删除此内容吗？<br />此操作将同时删除子项内容！且不能恢复，请慎用！",function(){
		var url = get_url('opt','del','id='+id);
		$.phpok.ajax(url,function(info){
			if(info == 'ok'){
				$.phpok.reload();
			}else{
				if(info){
					$.dialog.alert(info);
				}
				return false;
			}
		})
	});
}

