<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/cate_model.php
	备注： 分类后台操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年05月03日 09时48分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_model extends cate_model_base
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
			return $this->db->update_array($data,"cate",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"cate");
		}
	}

	/**
	 * 保存扩展表信息
	 * @参数 $data 数组，一维
	 * @参数 $mid 模块ID
	**/
	public function save_ext($data,$mid)
	{
		if(!$data || !is_array($data) || !$mid){
			return false;
		}
		if($data['id']){
			$sql = "SELECT id FROM ".$this->db->prefix."cate_".$mid." WHERE id='".$data['id']."'";
			$chk = $this->db->get_one($sql);
			if($chk){
				unset($data['id']);
				$this->db->update_array($data,'cate_'.$mid,array('id'=>$chk['id']));
				return true;
			}
		}
		return $this->db->insert_array($data,"cate_".$mid,"replace");
	}

	public function cate_next_taxis($parent_id=0)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."cate WHERE site_id='".$this->site_id."' AND parent_id='".$parent_id."'";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	public function cates_include_modules($mids)
	{
		if(!$mids){
			return false;
		}
		if(is_array($mids)){
			$mids = implode(",",$mids);
		}
		$sql = "SELECT id,title,module_id FROM ".$this->db->prefix."cate WHERE module_id IN(".$mids.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['module_id']][$value['id']] = $value['title'];
		}
		return $rslist;
	}
}