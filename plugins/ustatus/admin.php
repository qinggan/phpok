<?php
/*****************************************************************************************
	文件： ustatus/admin.php
	备注： 一键通知会员已通过审核
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年11月25日 09时31分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_ustatus extends phpok_plugin
{
	public $me;
	function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	function html_user_index_foot()
	{
		echo $this->plugin_tpl('user_status.html');
	}

	function email()
	{
		$uid = $this->get('uid');
		if(!$uid)
		{
			error('会员ID未指定');
		}
		$rs = $this->model('user')->get_one($uid);
		$this->tpl->assign('rs',$rs);
		$this->tpl->assign('sys',$this->config);
		$this->tpl->assign('config',$this->site);
		$this->tpl->assign('email',$rs['email']);
		$title = $this->tpl->fetch($this->me['param']["title"],"content");
		$content = $this->tpl->fetch($this->me['param']["content"],"content");
		$content = form_edit('content',$content,'editor','height=300&btn_image=1&etype=simple&width=700');
		$this->tpl->assign('content',$content);
		$this->tpl->assign('title',$title);
		//$this->lib('email')->send_admin($title,$content,$email);
		echo $this->plugin_tpl('user_email.html');
	}
}

?>