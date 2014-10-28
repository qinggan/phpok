/**************************************************************************************************
	文件： js/global.js
	说明： PHPOK默认模板中涉及到的JS
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2014年9月1日
***************************************************************************************************/
function top_search()
{
	var title = $("#top-keywords").val();
	if(!title)
	{
		alert('请输入要搜索的关键字');
		return false;
	}
	return true;
}

// 退出
function logout(t)
{
	var q = confirm("您好，【"+t+"】，确定要退出吗？");
	if(q == '0')
	{
		return false;
	}
	$.phpok.go(get_url('logout'));
}

//jQuery插件之购物车相关操作
;(function($){
	$.cart = {
		//添加到购物车中
		//id为产品ID
		add: function(id,qty){
			var url = api_url('cart','add','id='+id);
			if(qty && qty != 'undefined')
			{
				url += "&qty="+qty;
			}
			var rs = $.phpok.json(url);
			if(rs.status == 'ok')
			{
				alert("商品已成功加入购物车中！");
				this.total();
			}
			else
			{
				alert(rs.content);
				return false;
			}
		},
		//更新产品数量
		//id为购物车自动生成的ID号（不是产品ID号，请注意）
		update: function(id){
			var qty = $("#qty_"+id).val();
			if(!qty || parseInt(qty) < 1)
			{
				alert("购物车产品数量不能为空");
				return false;
			}
			var url = api_url('cart','qty')+"&id="+id+"&qty="+qty;
			var rs = $.phpok.json(url);
			if(rs.status == 'ok')
			{
				$.phpok.reload();
			}
			else
			{
				if(!rs.content) rs.content = '更新失败';
				alert(rs.content);
				return false;
			}
		},
		//计算购物车数量
		//这里使用异步Ajax处理
		total:function(){
			var url = api_url('cart','total');
			$.ajax({
				'url':url,
				'dataType':'json',
				'cache':false,
				'success':function(rs){
					if(rs.status == 'ok')
					{
						$("#head_cart_num").html(rs.content);
					}
				}
			});
		},
		//删除产品信息
		//id为购物车自动生成的ID号（不是产品ID号，请注意）
		del: function(id){
			var t = $("#title_"+id).text();
			var qc = confirm("确定要删除产品：\n\n\t"+t+"\n\n\t 删除后是不能恢复的！");
			if(qc == '0')
			{
				return false;
			}
			var url = api_url('cart','delete','id='+id);
			var rs = $.phpok.json(url);
			if(rs.status == 'ok')
			{
				$.phpok.reload();
				return true;
			}
			else
			{
				if(!rs.content) rs.content = '删除失败';
				alert(rs.content);
				return false;
			}
		}
	};
})(jQuery);
