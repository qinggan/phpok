<?php
/*****************************************************************************************
	文件： {phpok}/model/www/url_model.php
	备注： 伪静态网址生成及解析
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 20时46分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class url_model extends url_model_base
{
	private $urltype = 'default';
	private $protected_id = array('js','ajax','inp');
	private $rule_list = false;
	private $rule = false;
	private $clist = false;
	private $ilist = false;
	private $plist = false;
	private $kids = array('{project_root}','{project}','{cate_root}','{cate}','{identifier}','{pageid}');
	private $type_ids = false;
	private $rule_id = false;
	
	public function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function url($ctrl='index',$func='index',$ext='')
	{
		return $this->url_ctrl($ctrl,$func,$ext);
	}
}
?>