<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/list_model.php
	备注： 主题内容管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月06日 22时18分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_model extends list_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function update_sort($id,$sort=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET sort='".$sort."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function biz_save($data)
	{
		return $this->db->insert_array($data,'list_biz','replace');
	}

	public function biz_attr_save($data)
	{
		return $this->db->insert_array($data,'list_attr');
	}

	public function biz_attr_update($data,$id)
	{
		$aids = array();
		foreach($data as $key=>$value){
			$aids[] = $value['aid'];
		}
		$aids = array_unique($aids);
		$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid='".$id."' AND aid IN(".implode(",",$aids).")";
		$this->db->query($sql);
		foreach($data as $key=>$value){
			$value['tid'] = $id;
			$this->db->insert_array($value,'list_attr');
		}
		return true;
	}

	public function biz_attr_delete($tid,$aid=0)
	{
		$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid='".$tid."'";
		if($aid){
			$sql .= " AND aid='".$aid."'";
		}
		return $this->db->query($sql);
	}

	public function biz_all($ids='')
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_biz WHERE id IN(".$ids.")";
		return $this->db->get_all($sql,'id');
	}


	public function ext_catelist($id)
	{
		$sql = "SELECT cate_id FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[] = $value['cate_id'];
		}
		return $rslist;
	}

	public function admin_list_rs($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list_admin WHERE tid='".$id."'";
		return $this->db->get_one($sql);
	}

	//复制一个主题
	public function copy_id($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		unset($rs["id"]);
		$insert_id = $this->db->insert_array($rs,"list");
		if(!$insert_id){
			return false;
		}
		if($rs["module_id"]){
			$m_id = $rs["module_id"];
			$sql = "SELECT * FROM ".$this->db->prefix."list_".$m_id." WHERE id='".$id."'";
			$ext_rs = $this->db->get_one($sql);
			if($ext_rs){
				$ext_rs["id"] = $insert_id;
				$this->save_ext($ext_rs,$m_id);
			}
		}
		//绑定扩展分类
		$sql = "SELECT * FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$catelist = $this->db->get_all($sql);
		if($catelist){
			foreach($catelist as $key=>$value){
				$tmp = array('id'=>$insert_id,'cate_id'=>$value['cate_id']);
				$this->db->insert_array($tmp,'list_cate','replace');
			}
		}
		return true;
	}

	//存储扩展分类
	public function save_ext_cate($id,$catelist)
	{
		if(!$id || !$catelist){
			return false;
		}
		if(is_string($catelist)){
			$catelist = explode(",",$catelist);
		}
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "INSERT INTO ".$this->db->prefix."list_cate(id,cate_id) VALUES ";
		foreach($catelist as $key=>$value){
			if($key>0){
				$sql .= ",";
			}
			$sql .= "('".$id."','".$value."')";
		}
		$this->db->query($sql);
		return true;
	}

	public function list_cate_add($cateid,$tid)
	{
		$sql = "REPLACE INTO ".$this->db->prefix."list_cate(id,cate_id) VALUES('".$tid."','".$cateid."')";
		return $this->db->query($sql);
	}

	public function list_cate_delete($cateid,$id)
	{
		$sql = "SELECT cate_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if($rs && $rs['cate_id'] == $cateid){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$id."' AND cate_id='".$cateid."'";
		$this->db->query($sql);
		return true;
	}

	public function catelist($ids)
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_cate WHERE id IN(".$ids.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['id']][] = $value['cate_id'];
		}
		return $rslist;
	}

}

?>