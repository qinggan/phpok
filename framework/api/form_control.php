<?php
/**
 * 自定义表单的字段异步处理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2022年6月7日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function selectpage_f()
	{
		$mid = $this->get('mid','int');
		if(!$mid){
			$this->error('未指定数据来源');
		}
		$module = $this->model('module')->field_one($mid);
		if(!$module){
			$this->error('字段信息不存在');
		}
		$ext = ($module['ext'] && is_string($module['ext'])) ? unserialize($module['ext']) : array();
		$tmp = explode(":",$ext['option_list']);
		if($tmp[0] != 'title'){
			$this->error('其他数据接口正在开发中');
		}
		$pid = intval($tmp[1]);
		$field_id = $ext['selectpage_id'] ? $ext['selectpage_id'] : 'id';
		$field_val = $ext['selectpage_show'] ? $ext['selectpage_show'] : 'title';
		if(!$pid){
			$this->error('未指定项目ID');
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目没有绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块信息不存在，请检查'));
		}
		
		$pageid = $this->get('pageNumber','int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('pageSize','int');
		if(!$psize){
			$psize = 10;
		}
		$offset = ($pageid-1)*$psize;
		$keywords = $this->get('q_word');
		if($module['mtype']){
			$condition = "project_id='".$project['id']."' AND status=1 AND hidden=0";
			if($keywords){
				if(is_array($keywords)){
					$tlist = array();
					foreach($keywords as $key=>$value){
						$tmpvalue = str_replace(' ','%',$value);
						$tlist[] = "`".$field_val."` LIKE '%".$tmpvalue."%'";
					}
					$condition .= " AND (".implode(" OR ",$tlist).")";
				}else{
					$tmp_keywords = str_replace(' ','%',$keywords);
					$condition .= " AND `".$field_val."` LIKE '%".$keywords."%'";
				}
				$tmp_keywords = str_replace(' ','%',$keywords);
				$condition .= " `".$field_val."` LIKE '%".$keywords."%'";
			}
			$total = $this->db->count("SELECT count(id) FROM ".$this->db->prefix.$module['id']." WHERE ".$condition);
			if(!$total){
				$this->error('暂无数据');
			}
			$fields = $field_id.','.$field_val;
			$orderby = $project['orderby'] ? $project['orderby'] : 'sort DESC,id DESC';
			$sql = "SELECT ".$fields." FROM ".$this->db->prefix.$module['id']." WHERE ".$condition." ORDER BY ".$orderby." LIMIT ".$offset.",".$psize;
			$rslist = $this->db->get_all($sql);
			$data = array('pageSize'=>$psize,'pageNumber'=>$pageid,'totalRow'=>$total,'totalPage'=>ceil($total/$psize));
			$data['list'] = $rslist;
			$this->success($data);
		}
		$fields = array();
		$field_id_type = 'l';
		$field_val_type = 'l';
		$tmplist = $this->model('fields')->flist($project['module'],'identifier');
		if($tmplist){
			if($tmplist[$field_id]){
				$fields[] = 'ext.'.$field_id;
				$field_id_type = 'ext';
			}else{
				$fields[] = 'l.'.$field_id;
				$field_id_type = 'l';
			}
			if($tmplist[$field_val]){
				$fields[] = 'ext.'.$field_val;
				$field_val_type = 'ext';
			}else{
				$fields[] = 'l.'.$field_val;
				$field_val_type = 'l';
			}
		}
		
		$condition = "l.project_id='".$project['id']."' AND l.status=1 AND l.hidden=0";
		if($keywords){
			if(is_array($keywords)){
				$tlist = array();
				foreach($keywords as $key=>$value){
					$tmpvalue = str_replace(' ','%',$value);
					$tlist[] = $field_val_type.".".$field_val." LIKE '%".$tmpvalue."%'";
				}
				$condition .= " AND (".implode(" OR ",$tlist).")";
			}else{
				$tmp_keywords = str_replace(' ','%',$keywords);
				$condition .= " AND ".$field_val_type.".".$field_val." LIKE '%".$keywords."%'";
			}
		}
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."list_".$module['id']." ext ON(l.id=ext.id) WHERE ".$condition;
		$total = $this->db->count($sql);
		if(!$total){
			$this->error('暂无数据');
		}
		$tmp = implode(",",$fields);
		$orderby = $project['orderby'] ? $project['orderby'] : 'l.sort DESC,l.id DESC';
		$sql = "SELECT ".$tmp." FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."list_".$module['id']." ext ON(l.id=ext.id) WHERE ".$condition." ORDER BY ".$orderby." LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql);
		$data = array('pageSize'=>$psize,'pageNumber'=>$pageid,'totalRow'=>$total,'totalPage'=>ceil($total/$psize));
		$data['list'] = $rslist;
		$this->success($data);
	}
}