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
				$.phpok.go(get_url('logout'));
			});
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

$(document).ready(function(){
    //返回顶部
    if($("meta[name=toTop]").attr("content")=="true"){$("<div id='toTop'><img src='tpl/www/images/to-top.png'></div>").appendTo('body');$("#toTop").css({width:'50px',height:'50px',bottom:'10px',right:'15px',position:'fixed',cursor:'pointer',zIndex:'999999'});if($(this).scrollTop()==0){$("#toTop").hide();}$(window).scroll(function(event){if($(this).scrollTop()==0){$("#toTop").hide();}if($(this).scrollTop()!=0){$("#toTop").show();}});$("#toTop").click(function(event){$("html,body").animate({scrollTop:"0px"},666)});}
});
