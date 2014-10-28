/**************************************************************************************************
	文件： js/global.js
	说明： 手机版中常用到的JS
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2014年10月14日 21时48分
***************************************************************************************************/
function logout()
{
	var qc = confirm("您确定要退出吗?");
	if(qc == '0') return false;
	var url = api_url('logout');
	var rs = $.phpok.json(url);
	if(rs.status == 'ok')
	{
		$.phpok.reload();
	}
	else
	{
		alert(rs.content);
		return false;
	}
}