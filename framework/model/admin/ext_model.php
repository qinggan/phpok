<?php
/**
 * 扩展字段管理
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2015年02月20日 12时42分
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ext_model extends ext_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//添加验证
	public function check_identifier_add($identifier,$type='')
	{
		if(!$identifier || !$type){
			return false;
		}
		if(!preg_match('/^[a-zA-Z][a-z0-9A-Z\_\-]+$/u',$identifier)){
			return false;
		}
		if(strlen($type)>5 && substr($type,0,5) == 'list_'){
			$flist = $this->db->list_fields($this->db->prefix.$type);
			if(!$flist){
				return true;
			}
		}
		if(!$flist){
			$flist = array();
		}
		$chk = array('title','phpok','identifier','status','taxis','tag','parent_id','project');
		if(in_array($identifier,$flist) || in_array($identifier,$chk)){
			return false;
		}
		return true;
	}

	public function extc_save($content,$id)
	{
		if(!$id){
			return false;
		}
		$sql = "REPLACE INTO ".$this->db->prefix."extc(id,content) VALUES('".$id."','".$content."')";
		return $this->db->query($sql);
	}

	public function content_save($content,$id)
	{
		if(!$id || !$content) return false;
		$sql = "REPLACE INTO ".$this->db->prefix."extc(id,content) VALUES('".$id."','".$content."')";
		return $this->db->query($sql);
	}

	public function ext_delete($id,$module)
	{
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function delete($val,$module,$type="id")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE `".$type."`='".$val."' AND ftype='".$module."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		return true;
	}

	//存储表单
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if(!isset($data['ftype']) && isset($data['module'])){
			$data['ftype'] = $data['module'];
		}
		if(isset($data['module'])){
			unset($data['module']);
		}
		return $this->model('fields')->save($data,$id);
	}

	/**
	 * 删除表单
	 * @参数 $module 对应 qinggan_fields 表里的 ftype 值
	**/
	public function del($module)
	{
		$rslist = $this->model('fields')->flist($module);
		if(!$rslist){
			return true;
		}
		foreach($rslist as $key=>$value){
			$this->model('fields')->del($value);
		}
		return true;
	}

	public function get_from_identifier($identifier,$module)
	{
		return $this->model('fields')->get_from_identifier($identifier,$module);
	}

	public function ext_next_taxis($module)
	{
		return $this->model('fields')->next_taxis($module);
	}
}