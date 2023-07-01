<?php
/**
 * 
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2019年11月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class id_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_ctrl($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.intval($site_id) : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs){
			return 'project';
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs){
			return 'content';
		}
		return false;
	}

	//检测标识ID是否被使用了
	//identifier：字符串
	//site_id，站点ID，整数
	function check_id($identifier,$site_id=0,$id=0)
	{
		$site_id = $site_id ? '0,'.intval($site_id) : '0';
		//在项目中检测
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE LOWER(identifier)='".strtolower($identifier)."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		if($id){
			$sql .= " AND id !=".intval($id);
		}
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		//在分类中检测
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE LOWER(identifier)='".strtolower($identifier)."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		//在内容里检测
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE LOWER(identifier)='".strtolower($identifier)."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		return false;
	}

	public function project_id($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.intval($site_id) : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE LOWER(identifier)='".strtolower($identifier)."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	/**
	 * 获取ID属性信息，优先级：项目>分类>主题
	 * @参数 $identifier 标识串
	 * @参数 $site_id 站点ID
	 * @参数 $status 为true时表示只检索状态为1的数据
	**/
	public function id($identifier,$site_id=0,$status=false)
	{
		$site_id = intval($site_id);
		$plist = $this->id_project($site_id,$status);
		if(isset($plist) && isset($plist[$identifier])){
			return $plist[$identifier];
		}
		$clist = $this->id_cate($site_id,$status);
		if(isset($clist) && isset($clist[$identifier])){
			return $clist[$identifier];
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND identifier='".$identifier."'";
		if($status){
			$sql .= " AND status=1 ";
		}
		$chk = $this->db->get_one($sql);
		if($chk && $chk['id']){
			return array('id'=>$chk['id'],'type'=>'content');
		}
		return false;
	}

	public function id_project($site_id=0,$status=0)
	{
		$site_id = intval($site_id);
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."project WHERE site_id='".$site_id."'";
		if($status){
			$sql .= " AND status=1 ";
		}
		$cache_id  = $this->cache->id($sql);
		$tmplist = $this->cache->get($cache_id);
		if(!$tmplist){
			$tmplist = $this->db->get_all($sql);
			if($tmplist){
				$this->cache->save($cache_id,$tmplist);
			}
		}
		if(!$tmplist){
			return false;
		}
		$plist = array();
		foreach($tmplist as $key=>$value){
			$plist[$value['identifier']] = array('id'=>$value['id'],'type'=>'project');
		}
		return $plist;
	}

	public function id_cate($site_id,$status=0)
	{
		$site_id = intval($site_id);
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		if($status){
			$sql .= " AND status=1 ";
		}
		$cache_id  = $this->cache->id($sql);
		$tmplist = $this->cache->get($cache_id);
		if(!$tmplist){
			$tmplist = $this->db->get_all($sql);
			if($tmplist){
				$this->cache->save($cache_id,$tmplist);
			}
		}
		if(!$tmplist){
			return false;
		}
		$clist = array();
		foreach($tmplist as $key=>$value){
			$clist[$value['identifier']] = array('id'=>$value['id'],'type'=>'cate');
		}
		return $clist;
	}

	//
	public function id_all($site_id=0,$status=0)
	{
		$site_id = intval($site_id);
		$cache_id = $this->cache->id('model','id','id_all',$site_id,$status);
		$rslist = $this->cache->get($cache_id);
		if($rslist){
			return $rslist;
		}
		$rslist = array();
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."project WHERE site_id='".$site_id."'";
		if($status){
			$sql.= " AND status=1 ";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'project');
			}
			unset($tmplist);
		}
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		if($status){
			$sql.= " AND status=1";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'cate');
			}
		}
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND identifier!=''";
		if($status){
			$sql.= " AND status=1";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'content');
			}
		}
		if($rslist && count($rslist)>0){
			$this->cache->save($cache_id,$rslist);
			return $rslist;
		}
		return false;
	}
}