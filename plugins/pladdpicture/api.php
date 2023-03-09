<?php
/**
 * 批量加图<接口应用>
 * @作者 phpok.com
 * @版本 6.3.153
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2023年03月06日 13时59分
**/
class api_pladdpicture extends phpok_plugin
{
	public $me;
	private $pid = 0;
	private $pids = array();
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		$this->pid = $this->me["pid"];
		$this->pids = $this->me["pids"];
		if($this->me && $this->me["param"]){
			//
		}
	}
	
	/**
	 * 全局运行插件，在执行当前方法运行前，调整参数，如果不使用，请删除这个方法
	**/
	public function phpok_before()
	{
		//PHP代码;
	}
	
	
}