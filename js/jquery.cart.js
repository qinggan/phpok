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
			var self = this;
			var url = this._addurl(id,qty);
			if(!url){
				return false;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('成功加入购物车')).lock().time(1);
					self.total();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
			return false;
		},
		/**
		 * 自定义产品加入购物车
		 * @参数 title 产品名称
		 * @参数 price 价格
		 * @参数 qty 数量
		 * @参数 thumb 缩略图
		**/
		add2: function(title,price,qty,thumb){
			var url = this._addurl2(title,price,qty,thumb);
			if(!url){
				return false;
			}
			var self = this;
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('成功加入购物车')).lock().time(1);
					self.total();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
			return false;
		},

		/**
		 * 自定义产品立即订购
		 * @参数 title 产品名称
		 * @参数 price 价格
		 * @参数 qty 数量
		 * @参数 thumb 缩略图
		**/
		onebuy2: function(title,price,qty,thumb){
			var url = this._addurl2(title,price,qty,thumb);
			if(!url){
				return false;
			}
			$.phpok.json(url+"&_clear=1",function(data){
				if(data.status){
					$.phpok.go(get_url('cart','checkout','id[]='+data.info));
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			});
			return false;
		},
		/**
		 * 系统产品立即订购
		 * @参数 id 产品ID
		 * @参数 qty 数量
		**/
		onebuy: function(id,qty){
			var url = this._addurl(id,qty);
			if(!url){
				return false;
			}
			$.phpok.json(url+"&_clear=1",function(data){
				if(data.status){
					$.phpok.go(get_url('cart','checkout','id[]='+data.info));
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			});
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
			//增加优惠方案
			if($("select[data-name=apps]").length>0){
				$("select[data-name=apps]").each(function(i){
					var val = $(this).val();
					var name = $(this).attr("data-id");
					if(val && val != 'undefined' && name){
						url += "&"+name+"_id="+$.str.encode(val);
					}
				});
			}
			return url;
		},
		//取得选中的产品价格
		price:function()
		{
			var ids = $.checkbox.join();
			if(!ids){
				$.dialog.alert(p_lang('请选择要进入结算的产品'),function(){
					$("#total_price").text('--.--');
				});
				return true;
			}
			var url = api_url('cart','price','id='+$.str.encode(ids));
			$.phpok.json(url,function(data){
				if(data.status){
					$("#total_price").html(data.info.price);
					return true;
				}
				$("#total_price").text('--.--');
				$.dialog.alert(data.info);
				return false;
			});
		},
		//更新产品数量
		//id为购物车自动生成的ID号（不是产品ID号，请注意）
		update: function(id,showtip)
		{
			var qty = $("#qty_"+id).val();
			if(!qty || parseInt(qty) < 1){
				$.dialog.alert("购物车产品数量不能为空");
				return false;
			}
			var url = api_url('cart','qty')+"&id="+id+"&qty="+qty;
			if(showtip && showtip != 'undefined'){
				var tip = $.dialog.tips(showtip);
			}
			$.phpok.json(url,function(rs){
				if(showtip && showtip != 'undefined'){
					tip.close();
				}
				if(rs.status){
					$.phpok.reload();
				}else{
					if(!rs.info) rs.info = '更新失败';
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		//计算购物车数量
		//这里使用异步Ajax处理
		total:function(func){
			$.phpok.json(api_url('cart','total'),function(rs){
				if(rs.status){
					if(rs.info){
						$("#head_cart_num").html(rs.info).show();
					}else{
						$("#head_cart_num").html('0').hide();
					}
					if(func && func != 'undefined'){
						(func)(rs);
					}
				}
			});
			return false;
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
			if(!id || id == 'undefined'){
				var id = $.checkbox.join();
				if(!id){
					$.dialog.alert(p_lang('请选择要删除的产品'));
					return false;
				}
				var tmplist = id.split(',');
				var title = [];
				for(var i in tmplist){
					var t = $("#title_"+tmplist[i]).text();
					if(t){
						title.push(t);
					}
				}
				var tip = p_lang('确定要删除产品<br><span style="color:red">{title}</span><br>删除后不能恢复',title.join("<br/>"));
			}else{
				title = $("#title_"+id).text();
				var tip = p_lang('确定要删除产品<br><span style="color:red">{title}</span><br>删除后不能恢复',title);
			}
			$.dialog.confirm(tip,function(){
				var url = api_url('cart','delete','id='+$.str.encode(id));
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					if(!data.info){
						data.info = p_lang('删除失败');
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		}
	};
})(jQuery);
