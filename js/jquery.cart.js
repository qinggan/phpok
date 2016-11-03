/**
 * 购物车中涉及到的JS操作，此处使用jQuery封装
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2016年09月01日
**/

;(function($){
	$.cart = {
		//添加到购物车中
		//id为产品ID
		add: function(id,qty){
			var url = this._addurl(id,qty);
			var rs = $.phpok.json(url);
			if(rs.status){
				$.dialog.tips(p_lang('成功加入购物车'));
				this.total();
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		},
		add2: function(title,price,qty,thumb){
			var url = this._addurl2(title,price,qty,thumb);
			if(!url){
				return false;
			}
			var rs = $.phpok.json(url);
			if(rs.status){
				$.dialog.tips(p_lang('成功加入购物车'));
				this.total();
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		},
		onebuy2: function(title,price,qty,thumb){
			var url = this._addurl2(title,price,qty,thumb);
			if(!url){
				return false;
			}
			var rs = $.phpok.json(url);
			if(rs.status){
				$.phpok.go(get_url('cart','checkout'));
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		},
		onebuy: function(id,qty){
			var url = this._addurl(id,qty);
			var rs = $.phpok.json(url);
			if(rs.status){
				$.phpok.go(get_url('cart','checkout'));
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		},
		_addurl2: function(title,price,qty,thumb){
			if(!title || title == 'undefined'){
				$.dialog.alert(p_lang('名称不能为空'));
				return false;
			}
			if(!price || price == 'undefined'){
				$.dialog.alert(p_lang('价格不能为空'));
				return false;
			}
			if(!qty || qty == 'undefined'){
				qty = 1;
			}
			qty = parseInt(qty,10);
			if(qty < 1){
				qty = 1;
			}
			var url = api_url('cart','add','title='+$.str.encode(title)+"&price="+$.str.encode(price)+"&qty="+qty);
			if(thumb && thumb != 'undefined'){
				url += "&thumb="+$.str.encode(thumb);
			}
			return url;
		},
		_addurl:function(id,qty){
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
					$.dialog.alert(p_lang('请选择商品属性'));
					return false;
				}
				url += "&ext="+attr;
			}
			return url;
		},
		//更新产品数量
		//id为购物车自动生成的ID号（不是产品ID号，请注意）
		update: function(id){
			var qty = $("#qty_"+id).val();
			if(!qty || parseInt(qty) < 1){
				$.dialog.alert("购物车产品数量不能为空");
				return false;
			}
			var url = api_url('cart','qty')+"&id="+id+"&qty="+qty;
			var rs = $.phpok.json(url);
			if(rs.status){
				$.phpok.reload();
			}else{
				if(!rs.info) rs.info = '更新失败';
				$.dialog.alert(rs.info);
				return false;
			}
		},
		//计算购物车数量
		//这里使用异步Ajax处理
		total:function(func){
			var url = api_url('cart','total');
			$.phpok.json(url,function(rs){
				if(rs.status && rs.info){
					$("#head_cart_num").html(rs.info);
					if(func && func != 'undefined'){
						(func)(rs);
					}
				}
			})
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
			qty = parseInt(qty) + parseInt(num);
			$("#qty_"+id).val(qty);
			this.update(id);
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
			qty = parseInt(qty) - parseInt(num);
			$("#qty_"+id).val(qty);
			this.update(id);
		},
		//删除产品信息
		//id为购物车自动生成的ID号（不是产品ID号，请注意）
		del: function(id){
			var t = $("#title_"+id).text();
			$.dialog.confirm(p_lang('确定删除产品：')+t+"<br />"+p_lang('删除后是不能恢复的！'),function(){
				var url = api_url('cart','delete','id='+id);
				var rs = $.phpok.json(url);
				if(rs.status){
					$.phpok.reload();
					return true;
				}
				if(!rs.info){
					rs.info = p_lang('删除失败');
				}
				$.dialog.alert(rs.info);
				return false;
			});
		}
	};
})(jQuery);
