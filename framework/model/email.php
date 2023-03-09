<?php
/**
 * 邮件内容管理器
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年04月22日
**/

class email_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_one($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."email WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_list($condition="",$offset=0,$psize=20)
	{
		$sql = " SELECT * FROM ".$this->db->prefix."email ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function simple_list($siteid=0)
	{
		$condition = $siteid ? "site_id IN(0,".$siteid.")" : "site_id=0";
		$sql = "SELECT id,identifier,title,note FROM ".$this->db->prefix."email WHERE ".$condition;
		return $this->db->get_all($sql);
	}

	//取得总数量
	public function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."email ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 存储邮件内容信息
	 * @参数 $data 数组，要写入的数据
	 * @参数 $id 大于0时表示更新
	**/
	public function save($data,$id=0)
	{
		if($id){
			$this->db->update_array($data,"email",array("id"=>$id));
			return true;
		}else{
			$insert_id = $this->db->insert_array($data,"email");
			return $insert_id;
		}
	}

	/**
	 * 删除邮件内容
	 * @参数 $id 要删除的邮件ID，多个ID用英文逗号隔开
	**/
	public function del($id=0)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."email WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	/**
	 * 检测标识是否存在
	 * @参数 $identifier 标识
	 * @参数 $site_id 站点ID
	 * @参数 $id 不检查指定的ID
	**/
	public function get_identifier($identifier,$site_id=0,$id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."email WHERE identifier='".$identifier."' AND site_id='".$site_id."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		$sql .= " ORDER BY id DESC LIMIT 1";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得模板内容
	 * @参数 $code 标识ID
	 * @参数 $site_id 站点ID
	**/
	public function tpl($code,$site_id=0)
	{
		return $this->get_identifier($code,$site_id);
	}
}