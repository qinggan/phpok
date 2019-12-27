<?php
/**
 * 导航菜单管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class menu_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 读写用户组
	**/
	public function group($data = array())
	{
		if($data && count($data)>0){
			$tmp = $this->lib('json')->encode($data,false,true);
			$this->lib('file')->save($tmp,$this->dir_data.'json/menu_group.json');
			return true;
		}
		$tmp = $this->lib('file')->cat($this->dir_data.'json/menu_group.json');
		if($tmp){
			return $this->lib('json')->decode($tmp);
		}
		return array('top'=>P_Lang('导航菜单'),'bottom'=>P_Lang('页脚导航'));
	}

	/**
	 * 导航菜单组
	**/
	public function group_delete($id)
	{
		$rslist = $this->group();
		if($rslist && $rslist[$id]){
			unset($rslist[$id]);
		}
		$this->group($rslist);
		$sql = "DELETE FROM ".$this->db->prefix."menu WHERE group_id='".$id."'";
		return $this->db->query($sql);
	}

	public function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."menu SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 读取某个菜单信息
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."menu WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['project_id']){
			$rs['project'] = $this->model('project')->get_one($rs['project_id'],false);
		}
		if($rs['cate_id']){
			$rs['cate'] = $this->model('cate')->cate_info($rs['cate_id'],false);
		}
		if($rs['list_id']){
			$rs['info'] = $this->model('list')->simple_one($rs['list_id']);
		}
		return $rs;
	}

	/**
	 * 取得全部分类信息
	 * @参数 $group_id 菜单组信息
	 * @参数 $status 状态，为1时表示仅读已审核数据
	 * @参数 $pid 父级分类ID
	**/
	public function get_all($group_id='',$status=0,$pid=0)
	{
		$sql  = "SELECT * FROM ".$this->db->prefix."menu WHERE group_id='".$group_id."'";
		if($status){
			$sql .= " AND status=1 ";
		}
		$sql .= " AND site_id='".$this->site_id."'";
		$sql .= " ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$plist = $clist = $tlist = array();
		foreach($rslist as $key=>$value){
			if($value['project_id']){
				$plist[] = $value['project_id'];
			}
			if($value['cate_id']){
				$clist[] = $value['cate_id'];
			}
			if($value['list_id']){
				$tlist[] = $value['list_id'];
			}
		}
		if($plist && count($plist)>0){
			$plist = array_unique($plist);
			$plist = $this->model('project')->plist($plist,false,'id');
		}
		if($clist && count($clist)>0){
			$clist = array_unique($clist);
			$clist = $this->model('cate')->catelist_cid($clist,false);
		}
		if($tlist && count($tlist)>0){
			$tlist = array_unique($tlist);
			$tlist = $this->model('list')->simple_all($tlist);
		}
		foreach($rslist as $key=>$value){
			if($value['project_id'] && $plist && $plist[$value['project_id']]){
				$value['project'] = $plist[$value['project_id']];
			}
			if($value['cate_id'] && $clist && $clist[$value['cate_id']]){
				$value['cate'] = $clist[$value['cate_id']];
			}
			if($value['list_id'] && $tlist && $tlist[$value['list_id']]){
				$value['info'] = $tlist[$value['list_id']];
			}
			$rslist[$key] = $value;
		}
		$tmplist = array();
		$this->format_list($tmplist,$rslist,$pid,0);
		return $tmplist;
	}

	public function treelist($group_id,$is_userid=0)
	{
		$sql  = "SELECT * FROM ".$this->db->prefix."menu WHERE group_id='".$group_id."' AND status=1";
		if($is_userid){
			$sql .= " AND is_userid IN(0,1)";
		}else{
			$sql .= " AND is_userid=0 ";
		}
		$sql .= " AND site_id='".$this->site_id."'";
		$sql .= " ORDER BY taxis ASC,id DESC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$plist = $clist = $tlist = array();
		foreach($tmplist as $key=>$value){
			if($value['project_id']){
				$plist[] = $value['project_id'];
			}
			if($value['cate_id']){
				$clist[] = $value['cate_id'];
			}
			if($value['list_id']){
				$tlist[] = $value['list_id'];
			}
			
		}
		if($plist && count($plist)>0){
			$plist = array_unique($plist);
			$plist = $this->model('project')->plist($plist,false,'id');
		}
		if($clist && count($clist)>0){
			$clist = array_unique($clist);
			$clist = $this->model('cate')->catelist_cid($clist,false);
		}
		if($tlist && count($tlist)>0){
			$tlist = array_unique($tlist);
			$tlist = $this->model('list')->simple_all($tlist);
		}
		foreach($tmplist as $key=>$value){
			if($value['project_id'] && $plist && $plist[$value['project_id']]){
				$value['project'] = $plist[$value['project_id']];
				if($value['type'] == 'project'){
					$value['link'] = $this->url($value['project']['identifier'],'','','www');
				}
			}
			if($value['cate_id'] && $clist && $clist[$value['cate_id']]){
				$value['cate'] = $clist[$value['cate_id']];
				if($value['project'] && $value['type'] == 'cate'){
					$value['link'] = $this->url($value['project']['identifier'],$value['cate']['identifier'],'','www');
				}
			}
			if($value['list_id'] && $tlist && $tlist[$value['list_id']]){
				$value['info'] = $tlist[$value['list_id']];
				if($value['type'] == 'content'){
					$tmpid = $value['info']['identifier'] ? $value['info']['identifier'] : $value['info']['id'];
					$value['link'] = $this->url($tmpid,'','','www');
				}
			}
			$value['target'] = $value['target'] ? '_blank' : '_top';
			$value['url'] = $value['link'];
			$tmplist[$key] = $value;
		}
		$list = array();
		$this->_treelist($list,$tmplist,0);
		return $list;
	}

	private function _treelist(&$list,$rslist,$parent_id=0)
	{
		foreach($rslist as $key=>$value){
			if($value['parent_id'] == $parent_id){
				$list[$value['id']] = $value;
				$this->_treelist($list[$value['id']]['sublist'],$rslist,$value['id']);
			}
		}
	}

	/**
	 * 格式化分类数组
	 * @参数 $rslist 存储目标
	 * @参数 $tmplist 原始数据
	 * @参数 $parent_id 父级ID
	 * @参数 $layer 层级位置
	**/
	public function format_list(&$rslist,$tmplist,$parent_id=0,$layer=0)
	{
		if(!$tmplist && !is_array($tmplist)){
			$tmplist = array();
		}
		foreach($tmplist as $key=>$value){
			if($value["parent_id"] == $parent_id){
				$is_end = true;
				foreach($tmplist as $k=>$v){
					if($v["parent_id"] == $value["id"]){
						$is_end = false;
						break;
					}
				}
				$value["_layer"] = $layer;
				$value["_is_end"] = $is_end;
				$rslist[] = $value;
				//执行子级
				$new_layer = $layer+1;
				$this->format_list($rslist,$tmplist,$value["id"],$new_layer);
			}
		}
	}

	public function get_next_taxis($id,$parent_id=0)
	{
		$sql = "SELECT max(taxis) FROM ".$this->db->prefix."menu WHERE group_id='".$id."' AND taxis<255";
		if($parent_id){
			$sql .= " AND parent_id='".$parent_id."'";
		}
		$taxis = $this->db->count($sql);
		return $this->return_next_taxis($taxis);
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"menu",array('id'=>$id));
		}
		return $this->db->insert_array($data,'menu');
	}

	public function check_parent($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."menu WHERE parent_id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."menu WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}