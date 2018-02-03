<?php
/**
 * 演示插件<后台应用>
 * @package phpok\plugins
 * @作者 phpok.com
 * @版本 4.8.000
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年11月18日 10时44分
**/
class admin_demo extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}

	public function phpok_after()
	{
		//$str = $this->ctrl.'///'.$this->func."\n";
		//$str .= print_r($this,true);
	}

	/**
	 * 这是一个演示用的方法
	**/
	public function phpokdemo()
	{
		//
		$sql = "SELECT * FROM ".$this->db->prefix."list LIMIT 10";
		$rslist = $this->db->get_all($sql);
		$this->assign('rslist',$rslist);
		$this->_view("admin_demo.html");
	}

	public function demoset()
	{
		$this->_view('admin_demoset.html');
	}

	/**
	 * 编辑自定义内容
	**/
	public function edit()
	{
		$tid = $this->get('tid');
		if(!$tid){
			$this->error('未指定编辑的主题');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$tid."'";
		$rs = $this->db->get_one($sql);
		$this->assign('rs',$rs);
		$this->_view('admin_demo_edit.html');
	}



	/**
	 * 更新或添加保存完主题后触发动作，如果不使用，请删除这个方法
	 * @参数 $id 主题ID
	 * @参数 $project 项目信息，数组
	 * @返回 true 
	**/
	public function system_admin_title_success($id,$project)
	{
		//
	}
}