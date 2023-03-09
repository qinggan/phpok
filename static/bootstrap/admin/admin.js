/**
 * 后台默认事件
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年1月2日
**/

;(function($){
	$.form_design = {
		layer_add:function(id)
		{
			var url = get_url('design','index','id='+id);
			$.dialog.open(url,{
				'title':'选择块的布局设置（后台隐藏无效，前台有效）',
				'lock':true,
				'width':'800px',
				'height':'270px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'cancel':true
			});
		},
		layer_add_save:function(id,type,pc,mobile)
		{
			var pre_id = $.phpok.rand(10,'letter') + '' + $.phpok.rand(10,'fixed'); //增加到20个，减少出现相同的机率
			var html = '<div pre-type="layer" pre-id="'+pre_id+'" pre-name="'+id+'"><!-- layer '+pre_id+' --><div class="row no-gutters">';
			if(type == 'row'){
				var max = 1;
				var tmp = 1;
				var numlist = {"pc":0,"mobile":0};
				var list_pc = new Array();
				var list_mobile = new Array();
				if(pc != 'none'){
					list_pc = (pc).toString().split(',');
					tmp = list_pc.length;
					if(tmp > max){
						max = tmp;
					}
					numlist['pc'] = tmp;
				}
				if(mobile != 'none'){
					list_mobile = (mobile).toString().split(',');
					tmp = list_mobile.length;
					if(tmp > max){
						max = tmp;
					}
					numlist['mobile'] = tmp;
				}
			}
			if(type == "avg"){
				var cls = "col";
				var max = 1;
				var tmp = 1;
				var val = 1;
				var numlist = {"pc":0,"mobile":0};
				var list_pc = new Array();
				var list_mobile = new Array();
				if(pc != 'none'){
					tmp = parseInt(pc);
					val = parseInt(12/tmp);
					for(var i=0;i<tmp;i++){
						list_pc.push(val);
					}
					if(tmp > max){
						max = tmp;
					}
					numlist['pc'] = tmp;
				}
				if(mobile != 'none'){
					tmp = parseInt(mobile);
					val = parseInt(12/tmp);
					for(var i=0;i<tmp;i++){
						list_mobile.push(val);
					}
					if(tmp > max){
						max = tmp;
					}
					numlist['mobile'] = tmp;
				}
			}
			for(var i=0;i<max;i++){
				tmp = "col";
				if(list_mobile[i]){
					tmp += " col-sm-"+list_mobile[i];
				}else{
					if(numlist["mobile"]>0){
						var ys = i%numlist["mobile"];
						if(ys>0 && list_mobile[ys]){
							tmp += " col-sm-"+list_mobile[ys];
						}else{
							tmp += " col-sm-"+list_mobile[0];
						}
					}
				}
				if(list_pc[i]){
					tmp += " col-lg-"+list_pc[i];
				}
				if(pc == 'none'){
					tmp += " d-lg-none d-xl-none";
				}
				if(mobile == 'none'){
					tmp += " d-sm-none";
				}
				html += '<div class="'+tmp+'" pre-name="'+id+'" pre-type="data" pre-id="'+pre_id+'-'+i+'"';
				html += '>';
				html += '<div pre-type="content"></div>';
				html += '</div>'
			}
			html += '</div><!-- /layer '+pre_id+' --></div>';
			$("#"+id+"_preview").append(html);
			this.reload_act();
			return true;
		},
		layer_clear:function(id)
		{
			$.dialog.confirm('确定要清空内容数据吗？清空后不能恢复',function(){
				$('#'+id+'_preview').html('');
				$('#'+id).val('');
			});
		},
		layer_code:function(id)
		{
			var url = get_url('design','code','id='+id);
			$.dialog.open(url,{
				'title':'源代码编辑（慎用，仅适开发人员微调代码使用）',
				'width':'780px',
				'height':'520px',
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				}
			});
		},
		layer_top:function(obj)
		{
			var id = $(obj).parents("div[pre-type=layer]").attr("pre-id");
			var url = get_url('design','layer_top','id='+id);
			$.dialog.open(url,{
				'title':'窗口设置',
				'width':'750px',
				'height':'300px',
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				}
			});
		},
		layer_set:function(obj)
		{
			var id = $(obj).parents("div[pre-type=layer]").attr("pre-id");
			var url = get_url('design','layer_setting','id='+id);
			$.dialog.open(url,{
				'title':'块级属性设置',
				'width':'750px',
				'height':'450px',
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				}
			});
		},
		layer_sub:function(obj)
		{
			var id = $(obj).parent().parent().parent().attr("pre-id");
			var url = get_url('design','layer2','id='+id);
			$.dialog.open(url,{
				'title':'添加层布局',
				'width':'750px',
				'height':'295px',
				'lock':true,
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				}
			});
		},
		reload_act:function(){
			//鼠标移到数据层的效果
			$("div[pre-type=data]").hover(function(){
				var name = $(this).parents("div[pre-type=layer]").attr("pre-name");
				var html = '<div pre-type="toolbar">'+$('#'+name+'_toolbar_data').html()+'</div>';
				$(this).addClass("active").append(html);
			},function(){
				$("div[pre-type=data]").removeClass("active");
				$("div[pre-type=data]").find("div[pre-type=toolbar]").remove();
			});
			$("div[pre-type=layer]").hover(function(){
				var name = $(this).attr("pre-name");
				html = '<div pre-type="toolbar-layer">'+$('#'+name+'_toolbar_layer').html()+'</div>';
				$(this).append(html);
				//检测是否隐藏
			},function(){
				$(this).find("div[pre-type=toolbar-layer]").remove();
			});
		},
		set_component:function(obj)
		{
			var tmpobj = $(obj).parent().parent().parent();
			var id = tmpobj.attr("pre-id");
			var url = get_url('design','component','id='+id);
			var val = tmpobj.attr("pre-code");
			if(val){
				url += "&val="+val;
			}
			$.dialog.open(url,{
				'title':'绑定组件',
				'width':'750px',
				'height':'510px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					var b = iframe.save();
					if(!b){
						return false;
					}
					return true;
				},
				'cancel':true
			});
		},
		set_content:function(obj)
		{
			var id = $(obj).parent().parent().parent().attr("pre-id");
			var type = $(obj).parent().parent().parent().attr("pre-vtype");
			var url = get_url('design','content','id='+id);
			if(!type || type == 'undefined'){
				$.dialog.tips("不支持编辑，请选择组件，再执行编辑").lock();
				return false;
			}
			url += "&type="+type;
			if(type == 'image'){
				var tmp_id = $(obj).parent().parent().parent().attr("pre-image");
				if(tmp_id && tmp_id != 'undefined'){
					url += "&res_id="+tmp_id;
				}
			}
			$.dialog.open(url,{
				'title':'内容管理',
				'width':'90%',
				'height':'80%',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'cancel':true
			});
		},
		set_delete:function(obj)
		{
			var sub = $(obj).parents("div[pre-type=sublayer]");
			if(sub && sub.length>0){
				if(sub.find("div[pre-type=data]").length>1){
					return $(obj).parent().parent().parent().remove();
				}
				sub.html('<div pre-type="content"></div>');
				sub.attr("pre-type","data");
				this.reload_act();
				return true;
			}
			var m = $(obj).parents("div[pre-type=layer]");
			if(m.find("div[pre-type=data]").length>1){
				return $(obj).parent().parent().parent().remove();
			}
			m.remove();
			return true;
		},
		set_delete_all:function(obj)
		{
			$(obj).parents("div[pre-type=layer]").remove();
			return true;
		},
		set_style:function(obj)
		{
			var id = $(obj).parent().parent().parent().attr("pre-id");
			var url = get_url('design','style','id='+id);
			$.dialog.open(url,{
				'title':'样式设置',
				'width':'750px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'cancel':true
			});
		},
		to_font:function(obj)
		{
			var $tr = $(obj).parents("div[pre-type=layer]");
			if ($tr.index() != 0) {
				$tr.fadeOut().fadeIn();
				$tr.prev().before($tr);
				$("html,body").scrollTop($tr.offset().top);
			}
		},
		to_back:function(obj)
		{
			var id = $(obj).parent().parent().parent().attr("pre-name");
			var len = $("div[pre-name="+id+"]").find("div[pre-type=layer]").length;
			var $tr = $(obj).parents("div[pre-type=layer]");
			if ($tr.index() != len - 1) {
				$tr.fadeOut().fadeIn();
				$tr.next().after($tr);
				$("html,body").scrollTop($tr.offset().top);
			}
		}
	}
})(jQuery);

$(document).ready(function(){
	$.form_design.reload_act();
});