<?php
/*****************************************************************************************
	文件： {phpok}/model/www/tag_model.php
	备注： Tag标签前端调用
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月22日 01时04分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model extends tag_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_one($id,$field='id',$site_id=0)
	{
		$me = parent::get_one($id,$field,$site_id);
		if($me){
			return $me;
		}
		if(strpos($id,'-') !== false){
			$id = str_replace('-',' ',$id);
			return parent::get_one($id,$field,$site_id);
		}
		return false;
	}
}