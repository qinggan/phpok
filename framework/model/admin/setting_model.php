<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/setting_model.php
	备注： 常规项目配置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月09日 13时00分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_model extends setting_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function order_status_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_status WHERE site_id='".$this->site_id."' ORDER BY taxis ASC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->order_status_create();
			return $this->order_status_all();
		}
		return $rslist;
	}

	public function order_status_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_status WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function order_status_update($data,$id=0)
	{
		if(!$id || !$data){
			return false;
		}
		return $this->db->update_array($data,'order_status',array('id'=>$id));
	}

	private function order_status_create()
	{
		//删除记录
		$sql = "DELETE FROM ".$this->db->prefix."setting_status WHERE site_id='".$this->site_id."'";
		$this->db->query($sql);
		$string = $this->config['order']['status'];
		if(!$string){
			$string = 'create,unpaid,paid,shipped,received';
		}
		$list = explode(",",$string);
		foreach($list as $key=>$value){
			$taxis = $key+1;
			$tmp = array('site_id'=>$this->site_id,'identifier'=>$value,'taxis'=>$taxis,'status'=>1,'title'=>P_Lang($value));
			$this->db->insert_array($tmp,'order_status');
		}
		return true;
	}
}

?>