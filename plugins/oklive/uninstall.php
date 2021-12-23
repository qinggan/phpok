<?php
/**
 * 直播插件<插件卸载>
 * @作者 phpok.com
 * @版本 6.0
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年06月28日 11时05分
**/
class uninstall_oklive extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	
	/**
	 * 插件卸载时，执行的方法，如删除表，或去除其他一些选项，如果不使用，请删除这个方法
	**/
	public function index()
	{
		//删除附件
		$this->lib('file')->rm($this->dir_data.'bg.jpg');
		//删除表
		$sql = "DROP TABLE IF EXISTS ".$this->db->prefix."plugins_rtmp";
		$this->db->query($sql);
		$sql = "DROP TABLE IF EXISTS ".$this->db->prefix."plugins_stat";
		$this->db->query($sql);
		$sql = "DROP TABLE IF EXISTS ".$this->db->prefix."plugins_views";
		$this->db->query($sql);
	}
	
	
}