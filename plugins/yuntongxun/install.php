<?php
/*****************************************************************************************
	文件： plugins/yuntongxun/install.php
	备注： 安装短信注册插件接口
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月22日 11时27分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_yuntongxun extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	public function index()
	{
		return $this->plugin_tpl('install.html');
	}

	public function save()
	{
		//创建插件存储扩展表
		$sql = "CREATE TABLE IF NOT EXISTS `".$this->db->prefix."plugin_yuntongxun`(";
		$sql.= "`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',";
		$sql.= "`code` varchar(255) NOT NULL COMMENT '验证码',";
		$sql.= "`mobile` varchar(255) NOT NULL COMMENT '手机号',";
		$sql.= "`ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证码创建时间',";
		$sql.= "`etime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证码失效时间',";
		$sql.= "`status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态0未被使用1被使用',";
		$sql.= "`utype` varchar(50) NOT NULL COMMENT '应用模块ID',PRIMARY KEY (`id`))";
		$sql.= "ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='插件云通讯验证码' AUTO_INCREMENT=1";
		$this->db->query($sql);
		//
		$id = $this->plugin_id();
		$ext = array();
		$ext['ytx_account_sid'] = $this->get('ytx_account_sid');
		$ext['ytx_account_token'] = $this->get('ytx_account_token');
		$ext['ytx_app_id'] = $this->get('ytx_app_id');
		$ext['ytx_sever_ip'] = $this->get('ytx_sever_ip');
		$ext['ytx_server_port'] = $this->get('ytx_server_port');
		$ext['ytx_soft_version'] = $this->get('ytx_soft_version');
		$ext['ytx_check_code'] = $this->get('ytx_check_code');
		$this->plugin_save($ext,$id);
	}
}

?>