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
				'title':'选择层模式',
				'lock':true,
				'width':'720px',
				'height':'265px',
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
		layer_add_save:function(id,val)
		{
			var t = val.split(':');
			var pre_id = $.phpok.rand(10,'letter') + '' + $.phpok.rand(10,'fixed'); //增加到20个，减少出现相同的机率
			var html = '<div pre-type="layer" pre-id="'+pre_id+'" pre-name="'+id+'"><!-- layer '+pre_id+' --><div class="row no-gutters">';
			if(t[0] == 'row'){
				var list = t[1].split(',');
				for(var i in list){
					html += '<div class="col-'+list[i]+'" pre-name="'+id+'" pre-type="data" pre-id="'+pre_id+'-'+i+'">';
					html += '<div pre-type="content"></div>';
					html += '</div>';
				}
			}
			if(t[0] == 'avg'){
				for(var i=0;i<t[1];i++){
					html += '<div class="col" pre-type="data" pre-name="'+id+'" pre-id="'+pre_id+'-'+i+'">';
					html += '<div pre-type="content"></div>';
					html += '</div>';
				}
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
		layer_set:function(obj)
		{
			var id = $(obj).parents("div[pre-type=layer]").attr("pre-id");
			var url = get_url('design','layer_setting','id='+id);
			$.dialog.open(url,{
				'title':'父层属性设置',
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
				'title':'添加子层布局',
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
			$("div[pre-type=sublayer],div[pre-type=data]").unbind("click");
			$("div[pre-type=data]").click(function(){
				if($(this).hasClass("active")){
					$(this).removeClass("active");
					$(this).find("div[pre-type=toolbar]").remove();
				}else{
					//移除其他的active
					$("div[pre-type=data]").removeClass("active");
					$("div[pre-type=data]").find("div[pre-type=toolbar]").remove();
					//增加自身的
					
					var name = $(this).parents("div[pre-type=layer]").attr("pre-name");
					var t = $(this).parents("div[pre-type=sublayer]");
					if(t.length>0){
						$('#'+name+'_toolbar_data').find("button[toolbar-btn-name=set-sublayer]").hide();
					}else{
						$('#'+name+'_toolbar_data').find("button[toolbar-btn-name=set-sublayer]").show();
					}
					var html = $('#'+name+'_toolbar_data').html();
					var html = '<div pre-type="toolbar">'+html+'</div>';
					$(this).addClass("active").append(html);
					
				}
			});
		},
		set_content:function(obj)
		{
			var id = $(obj).parent().parent().parent().attr("pre-id");
			var type = $(obj).parent().parent().parent().attr("pre-vtype");
			var url = get_url('design','content','id='+id);
			if(type && type != 'undefined'){
				url += "&type="+type;
				if(type == 'image'){
					var tmp_id = $(obj).parent().parent().parent().attr("pre-image");
					if(tmp_id && tmp_id != 'undefined'){
						url += "&res_id="+tmp_id;
					}
				}
			}
			$.dialog.open(url,{
				'title':'内容管理',
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
			}
		}
	}
})(jQuery);

$(document).ready(function(){
	$.form_design.reload_act();
});