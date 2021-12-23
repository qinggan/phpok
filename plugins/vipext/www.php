<?php
/**
 * PHPOK-VIP插件扩展<前台应用>
 * @package phpok\plugins
 * @作者 phpok.com
 * @版本 5.3.135
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月11日 10时21分
**/
class www_vipext extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	
	/**
	 * 全局运行插件，在执行当前方法运行前，调整参数，如果不使用，请删除这个方法
	**/
	public function phpok_before()
	{
		//PHP代码;
	}
	
	/**
	 * 全局运行插件，在执行当前方法运行后，数据未输出前，如果不使用，请删除这个方法
	**/
	public function phpok_after()
	{
		//PHP代码;
	}
	
	/**
	 * 系统内置在</head>节点前输出HTML内容，如果不使用，请删除这个方法
	**/
	public function html_phpokhead()
	{
		//$this->_show("phpokhead.html");
	}
	
	/**
	 * 系统内置在</body>节点前输出HTML内容，如果不使用，请删除这个方法
	**/
	public function html_phpokbody()
	{
		//$this->_show("phpokbody.html");
	}
	
	/**
	 * 针对不同项目，配置不同的主题查询条件，如果不使用，请删除这个方法
	 * @参数 $project 项目信息，数组
	 * @参数 $module 模块信息，数组
	 * @返回 $dt数组或false 
	**/
	public function system_www_arclist($project,$module)
	{
		//$dt = array();
		//$dt["fields"] = "id,thumb";
		//$this->assign("dt",$dt);
	}
	
	
}