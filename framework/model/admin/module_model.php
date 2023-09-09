<?php
/**
 * 模块管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
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
	 * 返回所有表
	 * @参数 $clear_prefix 清除前缀
	 * @参数 $not_user_create 不包含后台自建模块（仅限有数字的模块）
	 * @返回 列表 
	**/
	public function table_all($clear_prefix=true,$not_user_create=true)
	{
		$list = $this->db()->list_tables_more();
		if(!$list){
			return false;
		}
		$rslist = array();
		$forbid = $this->system_tables();
		$mlist = $this->get_all();
		$mids = array();
		if($mlist){
			foreach($mlist as $key=>$value){
				//表别名登记
				if($value['tbname']){
					$mids[] = $value['tbname'];
				}
				if($value['mtype']){
					$mids[] = $value['id'];
				}else{
					$mids[] = $value['tbl'].'_'.$value['id'];
				}
			}
		}
		foreach($list as $key=>$value){
			$name = str_replace($this->db->prefix,'',$value['table_name']);
			if($forbid && in_array($name,$forbid)){
				$rslist[] = array('id'=>$name,'title'=>$name,'note'=>$value['table_comment'],'vtype'=>'system');
				continue;
			}
			if($mids && in_array($name,$mids)){
				$rslist[$name] = array('id'=>$name,'title'=>$name,'note'=>$value['table_comment'],'vtype'=>'ext');
				continue;
			}
			$rslist[] = array('id'=>$name,'title'=>$name,'note'=>$value['table_comment'],'vtype'=>'other');
		}
		return $rslist;
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
		$tblname = tablename($rs);
		//检测表是否存在
		$list = $this->db->list_tables();
		if(!in_array($tblname,$list)){
			$pri_id = 'id';
			$note = $rs['title'];
			$this->db->create_table_main($tblname,$pri_id,$note);
			$list = $this->db->list_tables();
			if(!in_array($tblname,$list)){
				return false;
			}
		}
		$fields = $this->db->list_fields($tblname,false);
		if(!in_array('site_id',$fields)){
			$data = array('id'=>'site_id','type'=>'INT','unsigned'=>true,'notnull'=>true,'default'=>'0');
			$data['comment'] = '网站ID';
			$this->db->update_table_fields($tblname,$data);
		}
		if(!in_array('project_id',$fields)){
			$data = array('id'=>'project_id','type'=>'INT','unsigned'=>true,'notnull'=>true,'default'=>'0');
			$data['comment'] = '项目ID';
			$this->db->update_table_fields($tblname,$data);
		}
		if(!in_array('cate_id',$fields)){
			$data = array('id'=>'cate_id','type'=>'INT','unsigned'=>true,'notnull'=>true,'default'=>'0');
			$data['comment'] = '主分类ID';
			$this->db->update_table_fields($tblname,$data);
		}
		//独立表创建状态，隐藏，排序及发布时间
		if($rs['mtype']){
			if(!in_array('status',$fields)){
				$data = array('id'=>'status','type'=>'TINYINT','unsigned'=>true,'notnull'=>true,'default'=>'0');
				$data['comment'] = '状态';
				$this->db->update_table_fields($tblname,$data);
			}
			if(!in_array('hidden',$fields)){
				$data = array('id'=>'hidden','type'=>'TINYINT','unsigned'=>true,'notnull'=>true,'default'=>'0');
				$data['comment'] = '隐藏';
				$this->db->update_table_fields($tblname,$data);
			}
			if(!in_array('sort',$fields)){
				$data = array('id'=>'sort','type'=>'INT','unsigned'=>true,'notnull'=>true,'default'=>'0');
				$data['comment'] = '排序';
				$this->db->update_table_fields($tblname,$data);
			}
			if(!in_array('dateline',$fields)){
				$data = array('id'=>'dateline','type'=>'INT','unsigned'=>true,'notnull'=>true,'default'=>'0');
				$data['comment'] = '发布时间';
				$this->db->update_table_fields($tblname,$data);
			}
			if(!in_array('hits',$fields)){
				$data = array('id'=>'hits','type'=>'INT','unsigned'=>true,'notnull'=>true,'default'=>'0');
				$data['comment'] = '查看次数';
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
		if($rs['ftype'] == 'user'){
			$tblname = $this->db->prefix.'user_ext';
		}else{
			$table = $this->get_one($rs['ftype']);
			if(!$this->chk_tbl_exists($table)){
				return false;
			}
			$tblname = tablename($table);
		}
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
		if($rs['ftype'] == 'user'){
			$tblname = $this->db->prefix.'user_ext';
		}else{
			$table = $this->get_one($rs['ftype']);
			if(!$this->chk_tbl_exists($table)){
				return false;
			}
			$tblname = tablename($table);
		}
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
		$tblname = tablename($rs);
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
					//删除库存
					$this->model('stock')->clean($value['id']);
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

	public function fields_auto($module)
	{
		if(!$module){
			$this->errmsg(P_Lang('未指定ID'));
			return false;
		}
		if(is_numeric($module)){
			$module = $this->get_one($module);
		}
		if(!$module){
			$this->errmsg(P_Lang('模块不存在'));
			return false;
		}
		if($module['mtype']){
			$system_fields = array('cate_id','sort','dateline','hits');
		}else{
			$system_fields = array('title','cate_id','sort','dateline','hits','user_id');
		}
		//更新系统自定
		$tablename = tablename($module);
		$list = $this->db->list_tables();
		if(!in_array($tablename,$list)){
			$this->errmsg(P_Lang('表 {table} 不存在',$tablename));
			return false;
		}
		$flist = $this->db->list_fields_more($tablename);
		if(!$flist){
			$this->errmsg(P_Lang('表 {table} 异常',$tablename));
			return false;
		}
		$elist = $this->model('fields')->flist($module['id'],'identifier');
		if(!$elist){
			$elist = array();
		}
		$ids_add = array_keys($flist);
		$ids_del = array_keys($elist);
		//交集，要增加的
		$add = array_diff($ids_add,$ids_del);
		$del = array_diff($ids_del,$ids_add);
		if($add){
			$system = array('id','site_id','project_id','cate_id','status','hidden','sort','hits','dateline');
			$m=1;
			foreach($add as $key=>$value){
				if(!$value || in_array($value,$system)){
					continue;
				}
				if($flist[$value]){
					$this->create_form($flist[$value],$module,false,5*$m);
					$m++;
				}
			}
		}
		if($del && $elist){
			foreach($del as $key=>$value){
				if(!$value || !$elist[$value]){
					continue;
				}
				if($elist[$value]){
					$this->field_delete($elist[$value]['id']);
				}
			}
		}
		return true;
	}

	/**
	 * 存储模块下的字段表
	 * @参数 $data 数组
	 * @参数 $id 大于0表示更新，小于等于0或为空表示添加
	**/
	public function fields_save($data,$id=0)
	{
		return $this->model('fields')->save($data,$id);
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
		if(!in_array($oldname,$list) && !in_array($newname,$list)){
			return false;
		}
		if(!in_array($newname,$list)){
			$sql = "RENAME TABLE ".$oldname." TO ".$newname;
			$this->db->query($sql);
		}
	}

	private function create_form($rs,$module,$is_system=false,$taxis=5)
	{
		$array = array('module_id'=>$module['id']);
		$array['title'] = $rs['comment'] ? $rs['comment'] : $rs['field'];
		$array['identifier'] = $rs['field'];
		$array['field_type'] = $this->field_type($rs['type']);
		$array['form_type'] = 'text';
		$array['format'] = 'safe';
		$array['taxis'] = $taxis;
		if($is_system){
			$array['hidden'] = 1;
			$array['is_system'] = 1;
		}else{
			$array['hidden'] = 0;
			$array['is_system'] = 0;
		}
		$this->fields_save($array);
	}

	private function field_type($str='')
	{
		if(!$str){
			return 'varchar';
		}
		$int = array('mediumint','tinyint','int');
		$is_int = false;
		$str = strtolower($str);
		foreach($int as $key=>$value){
			$len = strlen($value);
			if(substr($str,0,$len) == $value){
				$is_int = true;
				break;
			}
		}
		if($is_int){
			return 'int';
		}
		return 'varchar';
	}

	private function system_tables()
	{
		$data = array();
		$data[] = "adm";
		$data[] = "adm_popedom";
		$data[] = "all";
		$data[] = "attr";
		$data[] = "attr_values";
		$data[] = "cart";
		$data[] = "cart_product";
		$data[] = "cate";
		$data[] = "click";
		$data[] = "config";
		$data[] = "currency";
		$data[] = "design";
		$data[] = "email";
		$data[] = "express";
		$data[] = "extc";
		$data[] = "fields";
		$data[] = "fields_ext";
		$data[] = "freight";
		$data[] = "freight_price";
		$data[] = "freight_zone";
		$data[] = "gateway";
		$data[] = "gd";
		$data[] = "list";
		$data[] = "list_attr";
		$data[] = "list_biz";
		$data[] = "list_cate";
		$data[] = "log_content";
		$data[] = "menu";
		$data[] = "module";
		$data[] = "opt";
		$data[] = "opt_group";
		$data[] = "order";
		$data[] = "order_address";
		$data[] = "order_express";
		$data[] = "order_invoice";
		$data[] = "order_log";
		$data[] = "order_payment";
		$data[] = "order_price";
		$data[] = "order_product";
		$data[] = "order_refund";
		$data[] = "payment";
		$data[] = "payment_group";
		$data[] = "payment_log";
		$data[] = "phpok";
		$data[] = "plugins";
		$data[] = "popedom";
		$data[] = "project";
		$data[] = "reply";
		$data[] = "res";
		$data[] = "res_cate";
		$data[] = "search";
		$data[] = "site";
		$data[] = "site_domain";
		$data[] = "stock";
		$data[] = "sysmenu";
		$data[] = "tag";
		$data[] = "tag_node";
		$data[] = "tag_stat";
		$data[] = "task";
		$data[] = "token";
		$data[] = "tpl";
		$data[] = "user";
		$data[] = "user_address";
		$data[] = "user_ext";
		$data[] = "user_group";
		$data[] = "user_links";
		$data[] = "user_relation";
		$data[] = "wealth";
		$data[] = "wealth_info";
		$data[] = "wealth_log";
		$data[] = "wealth_rule";
		$data[] = "wholesale";
		$data[] = "world_location";
		$data[] = "world_price";
		$data[] = "yunmarket_client";
		return $data;
	}
}