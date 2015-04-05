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


