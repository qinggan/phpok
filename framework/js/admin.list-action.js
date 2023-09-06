/**
 * 内容动作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年08月11日
**/
;(function($){
	$(document).ready(function(){
		$("#project li").mouseover(function(){
			$(this).addClass("hover");
		}).mouseout(function(){
			$(this).removeClass("hover");
		}).click(function(){
			var url = $(this).attr("href");
			var txt = $(this).find('.txt').text();
			if(url){
				$.win(txt,url);
				return true;
			}
			$.dialog.alert(p_lang('未指定动作'));
			return false;
		});
		//渲染表头
		var total = $("#tablelist").attr("data-total");
		var psize = $("#tablelist").attr("data-psize");
		var totalRow = $("#tablelist").attr("data-totalRow");
		var ftype = $("#tablelist").attr("data-ftype");
		var opt = {'limit':psize};
		if(totalRow == 1){
			opt['totalRow'] = true;
		}
		layui.table.init("tablelist",opt);
		//注：edit是固定事件名，tablelist是table原始容器的属性 lay-filter="对应的值"
		layui.table.on('edit(tablelist)', function(obj){
			var pid = $("#pid").val();
			var url = get_url('list','quickedit','id='+obj.data.id+"&field="+obj.field+"&val="+obj.value+"&pid="+pid);
			$.phpok.json(url,function(data){
				if(data.status){
					$.dialog.tips(p_lang('操作成功'));
					return true;
				}
				$.dialog.alert(data.info,function(){
					$.phpok.reload();
				});
				return false;
			});
			return true;
		});
		layui.table.on('checkbox(tablelist)', function(obj){
			if(obj.type == 'all'){
				if(obj.checked){
					$.checkbox.all();
				}else{
					$.checkbox.none();
				}
			}else{
				var id = obj.data.id;
				$("#id_"+id).prop("checked",obj.checked);
			}
		});
		layui.table.on('colResized(tablelist)',function(obj){
			var e = obj.col;
			var url = get_url('fields','width','mid='+ftype+"&width="+e.width+"&field="+e.field);
			$.phpok.json(url,function(rs){
				if(rs.status){
					return true;
				}
				$.dialog.tips(rs.info);
				return false;
			});
		});
	});
})(jQuery);