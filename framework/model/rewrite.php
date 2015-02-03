<?php
/*****************************************************************************************
	文件： {phpok}/model/rewrite.php
	备注： 伪静态页规则配置器
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 12时57分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rewrite_model_base extends phpok_model
{
	protected $site_id = 0;
	public function __construct()
	{
		parent::model();
	}

	public function site_id($id)
	{
		$this->site_id = $id;
	}

	public function type_all()
	{
		$optlist = array('project'=>'项目网址');
		$optlist['content'] = "详细页网址";
		//$optlist['cart'] = '购物车';
		//$optlist['checkout'] = "订单确认";
		//$optlist['download'] = "附件下载";
		//$optlist['login'] = "登录";
		//$optlist['logout'] = "登出";
		//$optlist['order'] = "订单信息";
		//$optlist['payment'] = "付款";
		//$optlist['plugin'] = "插件";
		//$optlist['post'] = "发布";
		//$optlist['register'] = "注册";
		//$optlist['search'] = "搜索";
		//$optlist['tag'] = "标签";
		//$optlist['ueditor'] = "编辑器";
		//$optlist['upload'] = "上传";
		//$optlist['usercp'] = "个人中心";
		//$optlist['user'] = "会员";
		return $optlist;
	}

	public function type_ids()
	{
		$list = $this->type_all();
		return array_keys($list);
	}

	public function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."rewrite WHERE site_id='".$this->site_id."'";
		return $this->db->get_all($sql,'id');
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."rewrite WHERE site_id='".$this->site_id."' AND id='".$id."'";
		return $this->db->get_one($sql);
	}
}
?>