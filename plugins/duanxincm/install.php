<?php
/*****************************************************************************************
	文件： plugins/duanxincm/install.php
	备注： 安装短信注册插件接口
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月22日 11时27分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_duanxincm extends phpok_plugin
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
		//读取短信网关列表
		//$glist = $this->model('gateway')->get_all(0);
		//echo "<pre>".print_r($glist,true)."</pre>";
		return $this->plugin_tpl('install.html');
	}

	public function save()
	{
		//创建插件存储扩展表
		$sql = "CREATE TABLE IF NOT EXISTS `".$this->db->prefix."plugin_duanxincm`(";
		$sql.= "`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',";
		$sql.= "`code` varchar(255) NOT NULL COMMENT '验证码',";
		$sql.= "`mobile` varchar(255) NOT NULL COMMENT '手机号',";
		$sql.= "`ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证码创建时间',";
		$sql.= "`etime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证码失效时间',";
		$sql.= "`status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态0未被使用1被使用',";
		$sql.= "`utype` varchar(50) NOT NULL COMMENT '应用模块ID',PRIMARY KEY (`id`))";
		$sql.= "ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='插件莫名短信验证码' AUTO_INCREMENT=1";
		$this->db->query($sql);
		$id = $this->plugin_id();
		$ext = array();
		$ext['cm_account'] = $this->get('cm_account');
		$ext['cm_password'] = $this->get('cm_password');
		$ext['cm_server'] = $this->get('cm_server');
		$ext['cm_check_code'] = $this->get('cm_check_code');
		$this->plugin_save($ext,$id);
	}
}

?>