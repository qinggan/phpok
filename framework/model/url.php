<?php
/*****************************************************************************************
	文件： {phpok}/model/url.php
	备注： URL网址生成，解读Model
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 20时39分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class url_model_base extends phpok_model
{
	protected $baseurl = '';
	protected $ctrl_id = "c";
	protected $func_id = "f";
	protected $phpfile = 'index.php';
	protected $page_id = 'pageid';
	public function __construct()
	{
		parent::model();
	}

	public function page_id($pageid)
	{
		$this->page_id = $pageid;
	}

	public function base_url($url='')
	{
		$this->base_url = $url;
	}

	public function ctrl_id($ctrlid)
	{
		$this->ctrl_id = $ctrlid;
	}

	public function app_file($appfile)
	{
		$this->phpfile = $appfile;
	}

	public function func_id($funcid)
	{
		$this->func_id = $funcid;
	}

	public function url($ctrl='index',$func='index',$ext='')
	{
		return $this->url_ctrl($ctrl,$func,$ext);
	}

	protected function url_ctrl($ctrl='index',$func='index',$ext='')
	{
		$url = $this->base_url.$this->phpfile.'?';
		if($ctrl != 'index')
		{
			$url .= $this->ctrl_id.'='.$ctrl.'&';
		}
		if($func && $func != 'index')
		{
			$url .= $this->func_id.'='.$func.'&';
		}
		if($ext)
		{
			$url .= $ext;
		}
		if(substr($url,-1) == "&" || substr($url,-1) == "?")
		{
			$url = substr($url,0,-1);
		}
		return $url;
	}
}

?>