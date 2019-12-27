<?php
/*****************************************************************************************
	文件： {phpok}/model/popedom.php
	备注： 后台管理员权限类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 11时25分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class popedom_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_one($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."popedom WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_one_condition($condition="")
	{
		if(!$condition) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."popedom WHERE ".$condition;
		return $this->db->get_one($sql);
	}

	//取得模块模型下的权限ID
	public function get_list($gid,$pid=0)
	{
		if(!$gid) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."popedom WHERE gid='".$gid."' AND pid='".$pid."'";
		$sql.= ' ORDER BY taxis ASC,id DESC';
		return $this->db->get_all($sql);
	}

	public function get_all($condition="",$format=true,$ifpid=false)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."popedom ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY taxis ASC ";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		if(!$format){
			return $rslist;
		}
		$list = array();
		foreach($rslist as $key=>$value){
			if($ifpid){
				$list[$value["pid"]][$value["id"]] = $value;
			}else{
				$list[$value['gid']][$value['id']] = $value;
			}
		}
		return $list;
	}


	protected function _popedom_list($groupid)
	{
		$sql = "SELECT popedom FROM ".$this->db->prefix."user_group WHERE id='".$groupid."' AND status=1";
		$cache_id = $this->cache->id($sql);
		$rs = $this->cache->get($cache_id);
		if($rs){
			return explode(",",$rs[$this->site_id]);
		}
		$this->db->cache_set($cache_id);
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['popedom']){
			return false;
		}
		$popedom = unserialize($rs['popedom']);
		if(!$popedom[$this->site_id]){
			return false;
		}
		$rs = explode(",",$popedom[$this->site_id]);
		$this->cache->save($cache_id,$popedom);
		return $rs;
	}

	//判断是否有阅读权限
	//pid，为项目ID
	//groupid，为会员组ID
	public function check($pid,$groupid=0,$type='read')
	{
		$popedom = $this->_popedom_list($groupid);
		if(!$popedom){
			return false;
		}
		if(in_array($type.':'.$pid,$popedom)){
			return true;
		}
		return false;
	}

	/**
	 * 判断管理员是否有权限，仅限非系统管理员有效
	 * @参数 $admin_id 管理员ID
	 * @参数 $appfile APP文件，或是项目ID，为数字时表示项目ID
	 * @参数 $act 指定的权限标识
	**/
	public function admin_check($admin_id,$appfile,$act='')
	{
		if(!$admin_id || !$appfile){
			return false;
		}
		$popedom = array();
		if(is_numeric($appfile)){
			$condition = "pid='".$appfile."'";
			$tmp = $this->get_all($condition,false,false);
			if($tmp){
				$popedom = $tmp;
			}
			$appfile = 'list';
		}
		if(!$popedom || count($popedom)<1){
			$condition = "parent_id>0 AND appfile='".$appfile."'";
			$tmp = $this->model('sysmenu')->get_one_condition($condition);
			if(!$tmp){
				return false;
			}
			$gid = $tmp['id'];
			$condition = "pid=0 AND gid='".$gid."'";
			$tmp = $this->get_all($condition,false,false);
			if(!$tmp){
				return false;
			}
			$popedom = $tmp;
		}
		if(!$popedom || count($popedom)<1){
			return false;
		}
		$pid = 0;
		foreach($popedom as $key=>$value){
			if($value['identifier'] == $act){
				$pid = $value['id'];
				break;
			}
		}
		if(!$pid){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."adm_popedom WHERE id='".$admin_id."' AND pid='".$pid."'";
		return $this->db->get_one($sql);
	}
}