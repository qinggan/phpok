<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/task_model.php
	备注： 计划任务
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年10月22日 01时09分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class task_model extends task_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'task',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'task');
		}
	}
}

?>