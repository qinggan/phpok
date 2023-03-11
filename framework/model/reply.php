<?php
/**
 * 评论信息维护
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年04月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class reply_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得全部回复
	 * @参数 $condition 查询条件
	 * @参数 $offset 起始值
	 * @参数 $psize 每页查询数
	**/
	public function get_all($condition="",$offset=0,$psize=30)
	{
		return $this->get_list($condition,$offset,$psize);
	}

	/**
	 * 统计回复中的已审核主题信息，未审核信息
	 * @参数 $id 主题ID，多个ID用英文逗号隔开
	**/
	public function total_status($id)
	{
		$list = array();
		$sql = "SELECT tid,count(id) total FROM ".$this->db->prefix."reply WHERE status=1 AND tid IN(".$id.") GROUP BY tid";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$list[$value["tid"]]["checked"] = $value["total"];
				$list[$value["tid"]]["uncheck"] = 0;
			}
		}
		$sql = "SELECT tid,count(id) total FROM ".$this->db->prefix."reply WHERE status=0 AND tid IN(".$id.") GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				if(!$list[$value["tid"]]){
					$list[$value["tid"]]["checked"] = 0;
				}
				$list[$value["tid"]]["uncheck"] = $value["total"];
			}
		}
		return $list;
	}

	/**
	 * 获取回复列表
	 * @参数 $condition 查询条件
	 * @参数 $offset 开始位置
	 * @参数 $psize 每页查询数
	 * @参数 $orderby 排序
	**/
	public function get_list($condition="",$offset=0,$psize=30,$orderby="")
	{
		if(!$orderby){
			$orderby = 'r.addtime DESC,r.id DESC';
		}
		$sql  = " SELECT r.*,u.user,u.avatar FROM ".$this->db->prefix."reply r";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(r.uid=u.id) ";
		$sql .= " WHERE ".$condition." ORDER BY ".$orderby;
		if($psize && intval($psize)){
			$offset = intval($offset);
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$value['avatar'] = isset($value['avatar']) ? $value['avatar'] : 'images/avatar.gif';
			$value['user'] = isset($value['user']) ? $value['user'] : P_Lang('游客');
			$rslist[$value['id']] = $value;
		}
		$rslist = $this->_admin_reply($rslist);
		$rslist = $this->_res($rslist,true);
		$rslist = $this->_users($rslist);
		return $rslist;
	}

	/**
	 * 分组读取子主题数据
	 * @参数 $ids 父级ID
	 * @参数 $condition 查询条件
	 * @参数 $offset 开始位置
	 * @参数 $psize 每组查询个数
	**/
	public function group_parent_list($ids,$condition="",$offset=0,$psize=30)
	{
		$ids = $this->_ids($ids,true);
		if(!$ids){
			return false;
		}
		$sqlist = array();
		foreach($ids as $key=>$value){
			$sql  = " SELECT r.*,u.user,u.avatar FROM ".$this->db->prefix."reply r";
			$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(r.uid=u.id) ";
			$sql .= " WHERE r.parent_id='".$value."'";
			if($condition){
				$sql .= " AND ".$condition." ";
			}
			$sql .= " ORDER BY r.id DESC ";
			if($psize && intval($psize)){
				$offset = intval($offset);
				$sql .= " LIMIT ".$offset.",".$psize;
			}
			$sqlist[] = "(".$sql.")";
		}
		$sql = implode(" UNION ALL ",$sqlist);
		$dlist = $this->db->get_all($sql);
		if(!$dlist){
			return false;
		}
		$tmplist = array();
		foreach($dlist as $key=>$value){
			$value['avatar'] = isset($value['avatar']) ? $value['avatar'] : 'images/avatar.gif';
			$value['user'] = isset($value['user']) ? $value['user'] : P_Lang('游客');
			$tmplist[$value['id']] = $value;
		}
		$tmplist = $this->_admin_reply($tmplist);
		$tmplist = $this->_res($tmplist);
		$rslist = array();
		foreach($tmplist as $key=>$value){
			if(!isset($rslist[$value['parent_id']])){
				$rslist[$value['parent_id']] = array();
			}
			$rslist[$value['parent_id']][] = $value;
		}
		return $rslist;
	}

	protected function _admin_reply($rslist)
	{
		$ids = array();
		foreach($rslist as $key=>$value){
			$ids[] = $value['id'];
		}
		$ids = $this->_ids($ids);
		$sql = "SELECT id,content,addtime,admin_id,parent_id FROM ".$this->db->prefix."reply WHERE parent_id IN(".$ids.") AND admin_id>0 ORDER BY addtime ASC,id ASC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return $rslist;
		}
		foreach($tmplist as $key=>$value){
			if(!isset($rslist[$value['parent_id']]['adm_reply'])){
				$rslist[$value['parent_id']]['adm_reply'] = array();
			}
			$rslist[$value['parent_id']]['adm_reply'][] = $value;
		}
		return $rslist;
	}

	protected function _users($rslist,$ext=false)
	{
		$ids = array();
		foreach($rslist as $key=>$value){
			if($value['uid']){
				$ids[] = $value['uid'];
			}
		}
		if(!$ids){
			return $rslist;
		}
		$ids = $this->_ids($ids);
		if(!$ids){
			return $rslist;
		}
		$condition = "u.status=1 AND u.id IN(".$ids.")";
		$tmplist = $this->model('user')->get_list($condition,0,0);
		if(!$tmplist){
			return $rslist;
		}
		$userlist = array();
		foreach($tmplist as $key=>$value){
			if(!$value['avatar']){
				$value['avatar'] = 'images/avatar.gif';
			}
			$userlist[$value['id']] = $value;
		}
		foreach($rslist as $key=>$value){
			$value['userinfo'] = array();
			if(isset($userlist[$value['uid']])){
				$value['userinfo'] = $userlist[$value['uid']];
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 评论中的附件
	 * @参数 $rslist 回复数据，数组格式
	**/
	protected function _res($rslist,$ext=false)
	{
		$ids = array();
		foreach($rslist as $key=>$value){
			if($value['res']){
				$ids[] = $value['res'];
			}
		}
		if(!$ids){
			return $rslist;
		}
		$ids = $this->_ids($ids);
		if(!$ids){
			return $rslist;
		}
		$reslist = $this->model('res')->get_list_from_id($ids,$ext);
		if(!$reslist){
			return $rslist;
		}
		foreach($rslist as $key=>$value){
			if(!$value['res']){
				continue;
			}
			$tmp = $this->_ids($value['res'],true);
			$tmplist = array();
			foreach($tmp as $k=>$v){
				if(isset($reslist[$v])){
					$tmplist[$v] = $reslist[$v];
				}
			}
			$mylist = array();
			foreach($tmplist as $k=>$v){
				$v['full_thumb'] = $this->_pic2link($v['ico']);
				$v['full_link'] = ($v['gd'] && $v['gd']['auto']) ? $this->_pic2link($v['gd']['auto']) : $this->_pic2link($v['filename']);
				$mylist[] = $v;
			}
			$value['res'] = $mylist;
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	private function _pic2link($url)
	{
		if(substr($url,0,7) == 'http://' || substr($url,0,8) == 'https://'){
			return $url;
		}
		return $this->config['url'].$url;
	}

	/**
	 * 查询数量
	 * @参数 $condition 条件
	**/
	public function get_total($condition="")
	{
		$sql  = "SELECT count(r.id) FROM ".$this->db->prefix."reply r ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 分组统计主题下的回复数量
	 * @参数 $ids 要查询的主题ID
	 * @参数 $condition 查询条件
	 * @返回 成功返回数组，失败返回 false
	**/
	public function group_tid_total($ids,$condition='')
	{
		if(!$ids){
			return false;
		}
		$sql  = "SELECT count(r.id) total,r.tid FROM ".$this->db->prefix."reply r ";
		$sql .= "WHERE r.tid IN(".$ids.") ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql .= " GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']] = $value['total'];
		}
		return $rslist;
	}

	/**
	 * 分组统计父组下的回复数量
	 * @参数 $ids 要查询的父级ID
	 * @参数 $condition 查询条件
	 * @参数 $is_sub 是否包括所有子回复，默认是
	**/
	public function group_parent_total($ids,$condition='',$is_sub=true)
	{
		$ids = $this->_ids($ids,true);
		if(!$ids){
			return false;
		}
		if(!$is_sub){
			$ids = $this->_ids($ids,false);
			$sql  = "SELECT count(r.id) total,r.parent_id FROM ".$this->db->prefix."reply r ";
			$sql .= "WHERE r.parent_id IN(".$ids.") ";
			if($condition){
				$sql .= " AND ".$condition;
			}
			$sql .= " GROUP BY r.parent_id";
			$tmplist = $this->db->get_all($sql);
			if(!$tmplist){
				return false;
			}
			$rslist = array();
			foreach($tmplist as $key=>$value){
				$rslist[$value['parent_id']] = $value['total'];
			}
			return $rslist;
		}
		$sqlist = array();
		foreach($ids as $key=>$value){
			$tmp = array($value);
			$this->_sub_id($tmp,$value);
			$sub_condition = " r.parent_id IN(".implode(",",$tmp).") AND r.admin_id<1 ";
			if($condition){
				$sub_condition .= " AND ".$condition." ";
			}
			$sql  = "SELECT count(r.id) total,".$value." as pid FROM ".$this->db->prefix."reply r ";
			$sql .= " WHERE ".$sub_condition;
			$sqlist[] = $sql;
		}
		$sql = implode(" UNION ",$sqlist);
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['pid']] = isset($value['total']) ? $value['total'] : 0;
		}
		return $rslist;
	}

	private function _sub_id(&$ids,$parent_id=0,$count=10)
	{
		if($count<1){
			return true;
		}
		$parent_id = $this->_ids($parent_id);
		$sql = "SELECT id FROM ".$this->db->prefix."reply WHERE parent_id IN(".$parent_id.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$n_ids = array();
		foreach($tmplist as $key=>$value){
			$ids[] = $value['id'];
			$n_ids[] = $value['id'];
		}
		$count--;
		$this->_sub_id($ids,$n_ids,$count);
	}

	/**
	 * 保存回复数据
	 * @参数 $data 数组，要保存的数据
	 * @参数 $id 回复ID，不为空时表示更新
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"reply",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"reply");
		}
	}

	/**
	 * 删除回复
	 * @参数 $id 回复ID
	**/
	public function delete($id)
	{
		$id = $this->_ids($id,true);
		if(!$id){
			return false;
		}
		$all_ids = array();
		foreach($id as $key=>$value){
			$ids = array($value);
			$this->_sub_id($ids,$value);
			$all_ids = array_merge($ids,$all_ids);
		}
		if(!$all_ids || count($all_ids)<1){
			return false;
		}
		$id_string = $this->_ids($all_ids);
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE id IN(".$id_string.")";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 取得一条回复信息
	 * @参数 $id 回复ID
	**/
	public function get_one($id)
	{
		$sql  = " SELECT r.*,u.user,u.avatar FROM ".$this->db->prefix."reply r";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(r.uid=u.id) ";
		$sql .= " WHERE r.id='".$id."' ";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$value['avatar'] = isset($value['avatar']) ? $value['avatar'] : 'images/avatar.gif';
			$value['user'] = isset($value['user']) ? $value['user'] : P_Lang('游客');
			$rslist[$value['id']] = $value;
		}
		$rslist = $this->_admin_reply($rslist);
		$rslist = $this->_res($rslist,true);
		return current($rslist);
	}

	/**
	 * 回复统计
	 * @参数 $ids 主题ID，多个主题用英文逗号隔开，也支持多个主题的数组
	**/
	public function comment_stat($ids)
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT count(tid) as total,tid FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.") GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']] = $value['total'];
		}
		return $rslist;
	}

	/**
	 * 取得主题属性信息，如绑定的项目ID，如分页页码等
	 * @参数 int $id 主题ID或主题标识
	 */
	public function get_title_info($id)
	{
		$sql = "SELECT l.id,l.project_id,p.psize,p.comment_status FROM ".$this->db->prefix."list l ";
		$sql.= "LEFT JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) WHERE ";
		if(is_numeric($id)){
			$sql.= "l.id='".$id."'";
		}else{
			$sql.= "l.identifier='".$id."' AND l.site_id='".$this->site_id."'";
		}
		$sql.= " AND p.status=1";
		return $this->db->get_one($sql);
	}

	public function adm_reply($id)
	{
		$id = intval($id);
		$sql = "SELECT id,addtime,content,admin_id FROM ".$this->db->prefix."reply WHERE parent_id=".$id." AND admin_id!=0 ORDER BY addtime ASC,id ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 检测上一次发布评论的时间
	**/
	public function check_time($tid,$uid='',$sessid='')
	{
		if(!$uid && !$sessid){
			return false;
		}
		$sql = "SELECT addtime FROM ".$this->db->prefix."reply WHERE tid='".$tid."'";
		if($uid){
			$sql .= " AND uid='".$uid."'";
		}else{
			$sessid  .= " AND session_id='".$sessid."'";
		}
		$sql .= " ORDER BY addtime DESC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return true;
		}
		if(($rs['addtime'] + 30) > $this->time){
			return false;
		}
		return true;
	}
}