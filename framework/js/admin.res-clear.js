/**
 * 附件清理操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月29日
**/
var is_stop = false;
function set_res_clear_stop()
{
	console.log('--9999');
	is_stop = true;
}

;(function($){
	var tip_action;
	function date_format(time)
	{
		var date = new Date(parseInt(time));
		var Y = date.getFullYear() + '-';
        var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
        var D = date.getDate() + ' ';
        var h = date.getHours() + ':';
        var m = date.getMinutes() + ':';
        var s = date.getSeconds();
        return Y+M+D+h+m+s;
	}

	$.admin_res_clear = {
		start:function()
		{
			is_stop = false;
			var url = get_url('res','clearlist');
			var start_date = $("#start_date").val();
			if(start_date){
				url += "&start_date="+$.str.encode(start_date);
			}
			var stop_date = $("#stop_date").val();
			if(stop_date){
				url += "&stop_date="+$.str.encode(stop_date);
			}
			var id_start = $("#id_start").val();
			if(id_start){
				url += "&id_start="+$.str.encode(id_start);
			}
			var id_stop = $("#id_stop").val();
			if(id_stop){
				url += "&id_stop="+$.str.encode(id_stop);
			}
			tip_action = $.dialog.tips(p_lang('正在检索数据，请稍候…'),100).lock();
			var that = this;
			$.phpok.json(url,function(rs){
				tip_action.close();
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				var total = rs.info.total;
				var id_min = rs.info.id_min;
				var id_max = rs.info.id_max;
				if(total < 1){
					$.dialog.alert(p_lang('没有符事要求的附件'));
					return false;
				}
				var t = p_lang('共检测到附件数：{total} 份','<span class="red">'+total+'</span>');
				tip_action = $.dialog.tips(t,100);
				$("#tips").html(t);
				is_stop = false;
				$("#cancel_btn_html").show();
				that.check(id_min,id_max);
			});
		},

		check:function(id_start,id_stop)
		{
			if(is_stop){
				tip_action.content(p_lang('用户手动取消检查')).time(2).lock();
				return true;
			}
			var that = this;
			tip_action.content(p_lang('开始检测附件ID：{id}，请稍候…','<span class="red">'+id_start+'</span>')).time(100);
			var url = get_url('res','check','id_start='+id_start+"&id_stop="+id_stop);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					tip_action.close();
					$("#cancel_btn_html").hide();
					$.dialog.alert(rs.info);
					return false;
				}
				var data = rs.info;
				if(data == 'end'){
					tip_action.close();
					$("#cancel_btn_html").hide();
					$.dialog.alert(p_lang('附件已检查完毕，请执行后续操作'));
					return false;
				}
				if(data.status){
					var next_id = parseInt(data.id) + 1;
					if(next_id > id_stop){
						tip_action.close();
						$("#cancel_btn_html").hide();
						$.dialog.alert(p_lang('附件检查完毕，请执行后续操作'));
						return false;
					}
					if(is_stop){
						tip_action.content(p_lang('用户手动取消检查')).time(2).lock();
						return true;
					}
					window.setTimeout(function(){
						that.check(next_id,id_stop);
					}, 150);
					return true;
				}
				//HTML
				var html = '<li class="layui-col-sm6 layui-col-md4 layui-col-lg3" id="thumb_'+data.id+'">';
				html += '<div class="layui-card">';
				html += '	<div class="layui-card-header"><input type="checkbox" title="'+data.title+'" id="attrid_'+data.id+'" value="'+data.id+'" lay-skin="primary"/></div>';
				html += '	<div class="layui-card-body layui-clear">';
				html += '		<div class="layui-row layui-col-space10">';
				html += '			<div class="layui-col-sm3"><img src="'+data.ico+'" width="100%" /></div>';
				html += '			<div class="layui-col-sm9">';
				html += '				<div>'+p_lang('文件名')+' '+data.title+'</div>';
				html += '				<div>'+p_lang('上传时间')+' '+data.addtime_format+'</div>';
				html += '			</div>';
                html += '		</div>'
				html += '	</div>';
				html += '</div>';
				html += '</li>';
				$("#rslist ul").append(html);
				layui.form.render();
				//检查下一个
				var next_id = parseInt(data.id) + 1;
				if(next_id > id_stop){
					tip_action.close();
					$("#cancel_btn_html").hide();
					$.dialog.alert(p_lang('附件检查完毕，请选择要删除的文件'));
					return false;
				}
				if(is_stop){
					tip_action.content(p_lang('用户手动取消检查')).time(2).lock();
					$("#cancel_btn_html").hide();
					return true;
				}
				window.setTimeout(function(){
					that.check(next_id,id_stop);
				}, 150);
			});
		},
		stop:function()
		{
			$("#cancel_btn_html").hide();
			is_stop = true;
			return true;
		}
	}
})(jQuery);
$(document).ready(function(){

	layui.use('laydate', function() {
		var laydate = layui.laydate;

		//执行一个laydate实例
		laydate.render({
			elem: '#start_date' //指定元素
		});
		laydate.render({
			elem: '#stop_date' //指定元素
		});
	});
});