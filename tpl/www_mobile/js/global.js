/**************************************************************************************************
	文件： js/global.js
	说明： 前台通用JS页
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2016年03月29日
***************************************************************************************************/
function kfonline()
{
	$.dialog({
		'content':$("#popupkf")[0],
		'title':'在线客服',
		'padding':0,
		'lock':true
	});
}

function fav_add(id,obj)
{
	var val = ($(obj).val()).trim();
	if(val == '已收藏'){
		$.dialog.alert('已收藏过，不能重复执行');
		return false;
	}
	var url = api_url('fav','add','id='+id);
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			$(obj).val('加入收藏成功');
			window.setTimeout(function(){
				$(obj).val('已收藏')
			}, 1000);
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

;(function($){
	$.cart = {
		//添加到购物车中
		//id为产品ID
		add: function(id,qty){
			var url = api_url('cart','add','id='+id);
			if(qty && qty != 'undefined'){
				url += "&qty="+qty;
			}
			//判断属性
			if($("input[name=attr]").length>0){
				var attr = '';
				var showalert = false;
				$("input[name=attr]").each(function(i){
					var val = $(this).val();
					if(!val){
						showalert = true;
					}
					if(attr){
						attr += ",";
					}
					attr += val;
				});
				if(!attr || showalert){
					$.dialog.alert('请选择商品属性');
					return false;
				}
				url += "&ext="+attr;
			}
			var rs = $.phpok.json(url);
			if(rs.status == 'ok'){
				$.dialog.tips('成功加入购物车');
				window.setTimeout(function(){
					$.phpok.reload();
				}, 1000);
			}else{
				$.dialog.alert(rs.content);
				return false;
			}
		},
		//更新产品数量
		//id为购物车自动生成的ID号（不是产品ID号，请注意）
		update: function(id,old){
			var qty = $("#qty_"+id).val();
			if(!qty || parseInt(qty) < 1){
				$.dialog.alert("购物车产品数量不能为空");
				return false;
			}
			if(qty == old){
				return false;
			}
			var url = api_url('cart','qty')+"&id="+id+"&qty="+qty;
			var rs = $.phpok.json(url);
			if(rs.status == 'ok'){
				$.phpok.reload();
			}else{
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
					if(rs.status == 'ok'){
						//$.phpok.reload();
						if(parseInt(rs.content)>0){
							$("#head_cart_num").removeClass("ui-icon-cart").addClass("ui-icon-cart2");
						}else{
							$("#head_cart_num").removeClass("ui-icon-cart2").addClass("ui-icon-cart");
						}
					}else{
						$("#head_cart_num").removeClass("ui-icon-cart2").addClass("ui-icon-cart");
					}
				}
			});
		},
		//产品增加操作
		//id为购物车里的ID，不是产品ID
		//qty，是要增加的数值，
		plus:function(id,num){
			var qty = $("#qty_"+id).val();
			if(!qty){
				qty = 1;
			}
			if(!num || num == 'undefined'){
				num = 1;
			}
			var total = parseInt(qty) + parseInt(num);
			$("#qty_"+id).val(total);
			this.update(id,qty);
		},
		minus:function(id,num){
			var qty = $("#qty_"+id).val();
			if(!qty){
				qty = 1;
			}
			if(qty<2){
				$.dialog.alert('产品数量不能少于1');
				return false;
			}
			if(!num || num == 'undefined'){
				num = 1;
			}
			var total = parseInt(qty) - parseInt(num);
			$("#qty_"+id).val(total);
			this.update(id,qty);
		},
		//删除产品信息
		//id为购物车自动生成的ID号（不是产品ID号，请注意）
		del: function(id){
			var t = $("#title_"+id).text();
			$.dialog.confirm('确琮要删除产品：<span class="red">'+t+'</span><br />删除后是不能恢复的！',function(){
				var url = api_url('cart','delete','id='+id);
				var rs = $.phpok.json(url);
				if(rs.status == 'ok'){
					$.phpok.reload();
				}else{
					if(!rs.content){
						rs.content = '删除失败';
					}
					$.dialog.alert(rs.content);
					return false;
				}
			});
		}
	};
})(jQuery);


;(function($){
	$.user = {
		logout: function(title,homeurl){
			$.dialog.confirm('您好，<span class="red">'+title+'</span>，您确定要退出吗？',function(){
				var url = api_url('logout');
				var rs = $.phpok.json(url);
				if(rs.status == 'ok'){
					$.dialog.alert('您已成功退出',function(){
						$.phpok.go(homeurl);
					},'succeed');
				}else{
					if(!rs.content){
						rs.content = '退出失败，请检查';
					}
					$.dialog.alert(rs.content,'','error');
					return false;
				}
			});
		}
	};
})(jQuery);


$(document).ready(function(){
	$.mobile.ajaxEnabled = false;
	$.cart.total();
});