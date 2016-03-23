<?php
/*****************************************************************************************
	文件： {phpok}/model/fav.php
	备注： 收藏夹基类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月20日 11时14分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fav_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function chk($id,$uid=0,$field='lid')
	{
		$sql = "SELECT id FROM ".$this->db->prefix."fav WHERE user_id='".$uid."' AND ".$field."='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return true;
		}
		return false;
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'fav',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'fav');
		}
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."fav WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fav WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_list($uid=0,$offset=0,$psize=30,$condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fav WHERE user_id='".$uid."' ";
		if($condition){
			$sql.= " AND ".$condition." ";
		}
		$sql .= "ORDER BY addtime DESC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function get_total($uid=0,$condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."fav WHERE user_id='".$uid."' ";
		if($condition){
			$sql.= "AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	//取得主题被收藏数
	public function title_fav_count($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."fav WHERE lid='".$id."'";
		return $this->db->count($sql);
	}
}

?>