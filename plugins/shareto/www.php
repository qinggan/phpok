<?php
/**
 * 分享插件<前台应用>
 * @作者 phpok.com
 * @版本 6.0.007
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年05月19日 09时47分
**/
class www_shareto extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	
	
	public function ap_index_index_before()
	{
		$clientLangId = $_COOKIE['clientLangId'];
		if(!$clientLangId){
			setcookie('clientLangId',true);
			$hostname = $this->lib('server')->domain($this->config['get_domain_method']);
			$https = $this->lib('server')->https();
			$url  = $https ? 'https://' : 'http://';
			$url .= $hostname;
			if(strrpos(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 'zh-cn') !== false){
				if($this->site['id'] != 4){
					$this->_location($url.'?siteId=4');
				}
			}else{
				if($this->site['id'] != 1){
					$this->_location($url.'?siteId=1');
				}
			}
		}
	}
	
	/**
	 * 系统内置在</body>节点前输出HTML内容，如果不使用，请删除这个方法
	**/
	public function html_phpokbody()
	{
		//分享
		$this->_show("foot-share.html");
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