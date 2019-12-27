<?php
/**
 * Tag标签在后台的调用
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年10月31日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model extends tag_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	public function get_total($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."tag WHERE site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 删除标签操作
	**/
	public function delete($id)
	{
		//删除记录
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE tag_id='".$id."'";
		$this->db->query($sql);
		//删除节点
		$sql = "DELETE FROM ".$this->db->prefix."tag_node WHERE tag_id='".$id."'";
		$this->db->query($sql);
		//删除记录
		$sql = "DELETE FROM ".$this->db->prefix."tag WHERE id='".$id."'";
		return $this->db->query($sql);
	}



	public function node_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."tag_node WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function node_save($data,$id='')
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'tag_node',array('id'=>$id));
		}
		return $this->db->insert_array($data,'tag_node');
	}

	public function node_check($identifier,$tag_id,$id=0)
	{
		$list = array('identifier','rslist','rs','node');
		$flist = $this->db->list_fields('tag');
		if($flist){
			$list = array_merge($list,$flist);
		}
		$list = array_unique($list);
		if(in_array($identifier,$list)){
			return true;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tag_node WHERE tag_id='".$tag_id."' AND identifier='".$identifier."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	public function delete_title($tag_id,$title_id)
	{
		if(!$tag_id || !$title_id){
			return false;
		}
		$tag = parent::get_one($tag_id,'id');
		if(!$tag){
			$this->stat_delete($tag_id,'tag_id');
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE tag_id='".$tag_id."' AND title_id='".$title_id."'";
		$this->db->query($sql);
		$config = $this->config();
		$separator = ($config && $config['separator']) ? $config['separator'] : ',';
		if(substr($title_id,0,1) == 'p'){
			$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id='".substr($title_id,1)."'";
			return $this->_delete_tag_stat($sql,$tag,$separator,'project');
		}
		if(substr($title_id,0,1) == 'c'){
			$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".substr($title_id,1)."'";
			return $this->_delete_tag_stat($sql,$tag,$separator,'cate');
		}
		if(is_numeric($title_id)){
			$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$title_id."'";
			return $this->_delete_tag_stat($sql,$tag,$separator,'list');
		}
		return true;		
	}

	private function _delete_tag_stat($sql,$tag,$separator=',',$type="list")
	{
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['tag']){
			return true;
		}
		if($rs['tag'] == $tag['title']){
			$sql = "UPDATE ".$this->db->prefix.$type." SET tag='' WHERE id='".$rs['id']."'";
			$this->db->query($sql);
			return true;
		}
		$list = explode($separator,$rs['tag']);
		foreach($list as $key=>$value){
			$value = trim($value);
			if($value == $tag['title']){
				unset($list[$key]);
				continue;
			}
			$list[$key] = $value;
		}
		$tag = implode($separator,$list);
		$sql = "UPDATE ".$this->db->prefix.$type." SET tag='".$tag."' WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		return true;
	}
}