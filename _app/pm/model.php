<?php
/**
 * 模型内容信息_系统通知消息
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年12月25日 22时25分
**/
namespace phpok\app\model\pm;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class model extends \phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function delete($id)
	{
		return $this->db->delete("pm",array("id"=>$id));
	}

	public function get_count($condition='')
	{
		$sql  = " SELECT count(p.id) FROM ".$this->db->prefix."pm p ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(p.user_id=u.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."adm a ON(p.admin_id=a.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function get_list($condition='',$offset=0,$psize=20)
	{
		$sql  = " SELECT p.*,u.user,a.account FROM ".$this->db->prefix."pm p ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(p.user_id=u.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."adm a ON(p.admin_id=a.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY p.id DESC LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql);
	}

	public function get_one($id)
	{
		return $this->db->one("pm",array('id'=>$id));
	}

	public function save($data,$id=0)
	{
		if(!$data){
			return false;
		}
		if($id){
			return $this->db->update($data,'pm',array('id'=>$id));
		}
		return $this->db->insert($data,'pm');
	}

	public function set_read_all($uid)
	{
		$sql = "UPDATE ".$this->db->prefix."pm SET isread=1,readtime='".$this->time."' WHERE isread=0 AND user_id='".$uid."'";
		return $this->db->query($sql);
	}
}
