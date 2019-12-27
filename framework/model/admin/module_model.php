<?php
/**
 * 模块管理
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年10月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_model extends module_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function module_next_taxis()
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."module WHERE taxis<255";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	public function fields_next_taxis($mid)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."fields WHERE ftype='".$mid."' AND taxis<255";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	/**
	 * 创建模块系统表
	 * @参数 $id 模块ID
	**/
	public function create_tbl($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		$tblname = $rs['tbl'] ? $this->db->prefix.$rs['tbl']."_".$id : $this->db->prefix."list_".$id;
		if($rs['mtype']){
			$tblname = $this->db->prefix.$id;
		}
		//检测表是否存在
		$list = $this->db->list_tables();
		if(!in_array($tblname,$list)){
			$pri_id = 'id';
			$note = $rs['title'];
			$this->db->create_table_main($tblname,$pri_id,$note);
			$list = $this->db->list_tables();
		}
		if(in_array($tblname,$list)){
			$fields = $this->db->list_fields($tblname,false);
			if(!in_array('site_id',$fields)){
				$data = array('id'=>'site_id','type'=>'MEDIUMINT','unsigned'=>true,'notnull'=>true,'default'=>'0');
				$data['comment'] = '网站ID';
				$this->db->update_table_fields($tblname,$data);
			}
			if(!in_array('project_id',$fields)){
				$data = array('id'=>'project_id','type'=>'MEDIUMINT','unsigned'=>true,'notnull'=>true,'default'=>'0');
				$data['comment'] = '项目ID';
				$this->db->update_table_fields($tblname,$data);
			}
			if(!in_array('cate_id',$fields)){
				$data = array('id'=>'cate_id','type'=>'MEDIUMINT','unsigned'=>true,'notnull'=>true,'default'=>'0');
				$data['comment'] = '主分类ID';
				$this->db->update_table_fields($tblname,$data);
			}
		}
		//检查索引是否有建
		$keys = $this->db->list_keys($tblname,false);
		if(!$keys){
			$keys = array();
		}
		if(!$keys['site_id']){
			$this->db->update_table_index($tblname,'site_id','site_id');
		}
		if(!$keys['site_project']){
			$this->db->update_table_index($tblname,'site_project',array('site_id','project_id'));
		}
		if(!$keys['site_cate']){
			$this->db->update_table_index($tblname,'site_cate',array('site_id','cate_id'));
		}
		if(!$keys['project_cate']){
			$this->db->update_table_index($tblname,'project_cate',array('site_id','project_id','cate_id'));
		}
		return true;
	}

	/**
	 * 更新字段
	 * @参数 $id module_fields 表中的字段ID
	**/
	public function update_fields($id)
	{
		return $this->_fields_action($id);
	}

	/**
	 * 创建字段
	 * @参数 $id module_fields 表中的字段ID
	 * @参数 $rs module_fields 数组
	**/
	public function create_fields($id,$rs='')
	{
		return $this->_fields_action($id);
	}

	private function _fields_action($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->field_one($id);
		if(!$rs){
			return false;
		}
		$table = $this->get_one($rs['ftype']);
		if(!$this->chk_tbl_exists($table['id'],$table['mtype'],$table['tbl'])){
			return false;
		}
		$tblname = $table['mtype'] ? $this->db->prefix.$table['id'] : $this->db->prefix.$table['tbl']."_".$table['id'];
		$data = array('id'=>$rs['identifier'],'type'=>$rs['field_type'],'unsigned'=>false);
		$data['notnull'] = true;
		if($rs['field_type'] == 'date' || $rs['field_type'] == 'datetime'){
			$data['notnull'] = false;
		}
		if($rs['content'] != ''){
			$data['default'] = (string) $rs['content'];
		}
		if(!$rs['content'] && ($rs['field_type'] == 'int' || $rs['field_type'] == 'float')){
			$date['default'] = '0';
		}
		$data['comment'] = $rs['title'];
		if($rs['type'] == 'varchar'){
			$data['length'] = 255;
		}
		return $this->db->update_table_fields($tblname,$data);
	}

	/**
	 * 删除字段
	 * @参数 $id 要删除的字段ID，数值
	**/
	public function field_delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->field_one($id);
		$table = $this->get_one($rs['ftype']);
		$tblname = $table['mtype'] ? $this->db->prefix.$table['id'] : $this->db->prefix.$table['tbl']."_".$table['id'];
		$idlist = $this->db->list_fields($tblname,false);
		if($idlist && in_array($rs['identifier'],$idlist)){
			$this->db->delete_table_fields($tblname,$rs['identifier']);
		}
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 删除模块操作
	 * @参数 $id 模块ID
	**/
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->get_one($id);
		
		$tblname = $rs['mtype'] ? $this->db->prefix.$id : $this->db->prefix.$rs['tbl']."_".$id;
		$this->db->delete_table($tblname,false);
		if(!$rs['mtype'] && $rs['tbl'] == 'list'){
			$sql = "SELECT id FROM ".$this->db->prefix."list WHERE module_id='".$id."'";
			$rslist = $this->db->get_all($sql);
			if($rslist){
				foreach($rslist as $key=>$value){
					//删除主题绑定的分类
					$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$value['id']."'";
					$this->db->query($sql);
					//删除主题电商相关
					$sql = "DELETE FROM ".$this->db->prefix."list_biz WHERE id='".$value['id']."'";
					$this->db->query($sql);
					//删除主题相关属性
					$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid='".$value['id']."'";
					$this->db->query($sql);
				}
				$sql = "DELETE FROM ".$this->db->prefix."list WHERE module_id='".$id."'";
				$this->db->query($sql);
			}
			//更新项目信息
			$sql = "UPDATE ".$this->db->prefix."project SET module='0' WHERE module='".$id."'";
			$this->db->query($sql);
		}
		if(!$rs['mtype'] && $rs['tbl'] == 'cate'){
			$sql = "UPDATE ".$this->db->prefix."cate SET module_id=0 WHERE module_id='".$rs['id']."'";
			$this->db->query($sql);
		}
		//删除扩展字段
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE ftype='".$id."'";
		$this->db->query($sql);
		//删除记录
		$sql = "DELETE FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."module SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新排序
	 * @参数 $id 模块ID
	 * @参数 $taxis 排序值
	**/
	public function update_taxis($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."module SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 存储模块下的字段表
	 * @参数 $data 数组
	 * @参数 $id 大于0表示更新，小于等于0或为空表示添加
	**/
	public function fields_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['module_id'] && !isset($data['ftype'])){
			$data['ftype'] = $data['module_id'];
		}
		if(isset($data['module_id'])){
			unset($data['module_id']);
		}
		if($data['ext'] && is_array($data['ext'])){
			$data['ext'] = serialize($data['ext']);
		}
		if($id){
			return $this->db->update_array($data,"fields",array("id"=>$id));
		}
		return $this->db->insert_array($data,"fields");
	}

	/**
	 * 存储模块表
	 * @参数 $data 模块信息，数组
	 * @参数 $id 大于0表示更新，小于等于0或为空表示添加
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"module",array("id"=>$id));
		}
		return $this->db->insert_array($data,"module");
	}

	/**
	 * 重命名数据表
	**/
	public function rename_tbl($old,$new='',$include_prefix=false)
	{
		if(!$old || !$new || $old == $new){
			return false;
		}
		$oldname = $include_prefix ? $old : $this->db->prefix.$old;
		$newname = $include_prefix ? $new : $this->db->prefix.$new;
		$list = $this->db->list_tables();
		if(!in_array($oldname,$list)){
			return false;
		}
		$sql = "RENAME TABLE ".$oldname." TO ".$newname;
		$this->db->query($sql);
	}
}