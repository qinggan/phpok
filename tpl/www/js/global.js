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

function toDesktop(sUrl, sName) {
	try {
		var WshShell = new ActiveXObject("WScript.Shell");
		var oUrlLink = WshShell.CreateShortcut(WshShell.SpecialFolders("Desktop") + "\\" + sName + ".url");
		oUrlLink.TargetPath = sUrl;
		oUrlLink.Save();
	} catch (e) {
		alert("当前IE安全级别不允许操作！");
	}
}

function set_home(obj, vrl)
{
	try {
		obj.style.behavior = 'url(#default#homepage)';
		obj.setHomePage(vrl);
	} catch (e) {
		if (window.netscape) {
			try {
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			} catch (e) {
				alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入“about:config”并回车\n然后将 [signed.applets.codebase_principal_support]的值设置为'true',双击即可。");
			}
			var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
			prefs.setCharPref('browser.startup.homepage', vrl);
		} else {
			alert("您的浏览器不支持，请按照下面步骤操作：\n1. 打开浏览器设置。\n2. 点击设置网页。\n3. 输入：" + vrl + "\n4. 点击确定。");
		}
	}
}

function add_fav(sTitle,sURL) 
{
	try {
		window.external.addFavorite(sURL, sTitle);
	} catch (e) {
		try {
			window.sidebar.addPanel(sTitle, sURL, "");
		} catch (e) {
			alert("加入收藏失败，请使用Ctrl+D进行添加");
		}
	}
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



//会员
;(function($){
	$.user = {
		login: function(title){
			var url = get_url('login','open');
			$.dialog.open(url,{
				'title':title,
				'lock':true,
				'width':'500px',
				'height':'400px'
			});
		},
		logout: function(title){
			$.dialog.confirm('您好，<span class="red">'+title+'</span>，您确定要退出吗？',function(){
				var url = api_url('logout');
				var rs = $.phpok.json(url);
				if(rs.status == 'ok'){
					$.dialog.alert('您已成功退出',function(){
						top.$.phpok.reload();
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

//jQuery插件之购物车相关操作
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
				this.total();
			}
			else
			{
				$.dialog.alert(rs.content);
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
				alert('产品数量不能少于1');
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

