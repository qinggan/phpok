<?php
/**
 * 模型内容信息_针对社交信息增加的一些服务，如关注，粉丝，黑名单等功能
 * @作者 phpok.com <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @许可 www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年07月16日 10时13分
**/
namespace phpok\app\model\social;
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

	public function attr($user_id=0,$me_id=0)
	{
		if(!$user_id || !$me_id){
			return false;
		}
		if(!$me_id){
			return $this->_relation($user_id);
		}
		if(is_array($user_id)){
			$user_id = implode(",",$user_id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."user_links WHERE (user_id='".$me_id."' AND who_id IN(".$user_id.")) OR (who_id='".$me_id."' AND user_id IN(".$user_id."))";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return $this->_relation($user_id);
		}
		$mylist = array();
		$rlist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array();
			if($value['user_id'] == $me_id){
				if(!isset($rlist[$value['who_id']])){
					$rlist[$value['who_id']] = array('is_black'=>false,'is_idol'=>false,'is_fans'=>false);
				}
				if($value['is_black']){
					$rlist[$value['who_id']]['is_black'] = true;
				}else{
					if($value['is_idol']){
						$rlist[$value['who_id']]['is_idol'] = true;
					}
				}
			}else{
				if(!isset($rlist[$value['user_id']])){
					$rlist[$value['user_id']] = array('is_black'=>false,'is_idol'=>false,'is_fans'=>false);
				}
				if($value['is_black']){
					$rlist[$value['user_id']]['is_black'] = true;
				}else{
					if($value['is_idol']){
						$rlist[$value['user_id']]['is_fans'] = true;
					}
				}
			}
		}
		
		$tlist = explode(",",$user_id);
		$mylist = array();
		foreach($tlist as $key=>$value){
			if($rlist[$value]){
				$mylist[$value] = $rlist[$value];
			}else{
				$tmp = array("is_idol"=>false,"is_fans"=>false,"is_black"=>false);
				$mylist[$value] = $tmp;
			}
		}
		return $mylist;
	}

	private function _relation($users)
	{
		if(!$users){
			return false;
		}
		if(!is_array($users)){
			$users = explode(",",$users);
		}
		$rslist = array();
		foreach($users as $key=>$value){
			$tmp = array("is_idol"=>false,"is_fans"=>false,"is_black"=>false);
			$rslist[$value] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 用户与用户之前的关系
	**/
	public function links_info($user_id=0,$who_id=0)
	{
		if(!$user_id || !$who_id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."social WHERE user_id='".$user_id."' AND who_id='".$who_id."'";
		return $this->db->get_one($sql);
	}

	public function links_list($user_id=0,$who_id=0,$condition="")
	{
		if(!$user_id || !$who_id){
			return false;
		}
		if(is_array($who_id)){
			$who_id = implode(",",$who_id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."social WHERE user_id='".$user_id."' AND who_id IN(".$who_id.")";
		if($condition){
			$sql .= " AND ".$condition;
		}
		return $this->db->get_all($sql);
	}
	
	/**
	 * 获取我关注的用户列表
	**/
	public function idol_list($uid,$offset=0,$psize=20,$condition='')
	{
		$sql  = " SELECT e.id,e.*,u.id,u.user,u.avatar,l.addtime FROM ".$this->db->prefix."social l ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user_ext e ON(l.who_id=e.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.who_id=u.id) WHERE l.user_id='".$uid."' ";
		$sql .= " AND l.is_idol=1 AND l.is_black=0 ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql .= " ORDER BY l.id DESC LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql);
	}

	/**
	 * 关注用户数
	**/
	public function idol_count($uid,$condition='')
	{
		if(!$uid){
			return false;
		}
		if(is_array($uid)){
			$sql  = " SELECT count(l.id) total,l.user_id FROM ".$this->db->prefix."social l ";
			$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.who_id=u.id) WHERE l.user_id IN(".implode(",",$uid).") ";
			$sql .= " AND l.is_idol=1 AND l.is_black=0 ";
			if($condition){
				$sql .= " AND ".$condition." ";
			}
			$sql .= " GROUP BY l.user_id";
			return $this->db->get_all($sql,'user_id');
		}
		$sql  = " SELECT count(l.id) FROM ".$this->db->prefix."social l ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.who_id=u.id) WHERE l.user_id='".$uid."' ";
		$sql .= " AND l.is_idol=1 AND l.is_black=0 ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 删除关注
	**/
	public function idol_del($uid=0,$who_id=0)
	{
		if(!$uid || !$who_id){
			return false;
		}
		if($uid == $who_id){
			return false;
		}
		$info = $this->links_info($uid,$who_id);
		if(!$info){
			return true;
		}
		if($info['is_black']){
			$sql = "UPDATE ".$this->db->prefix."social SET is_idol=0 WHERE id='".$info['id']."'";
			return $this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."social WHERE id='".$info['id']."'";
		return $this->db->query($sql);
	}

	/**
	 * 添加关注
	**/
	public function idol_add($uid=0,$who_id=0)
	{
		if(!$uid || !$who_id){
			return false;
		}
		if($uid == $who_id){
			return false;
		}
		$info = $this->links_info($uid,$who_id);
		if(!$info){
			$data = array('user_id'=>$uid,'who_id'=>$who_id);
			$data['addtime'] = $this->time;
			$data['is_black'] = 0;
			$data['is_idol'] = 1;
			return $this->db->insert($data,'social');
		}
		//黑名单里不允许加为关注
		if($info && $info['is_black']){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."social SET is_idol=1,addtime='".$this->time."' WHERE id='".$info['id']."'";
		return $this->db->query($sql);		
	}

	/**
	 * 粉丝列表
	**/
	public function fans_list($uid,$offset=0,$psize=20,$condition='')
	{
		$sql  = " SELECT e.id,e.*,u.id,u.user,u.avatar,l.addtime FROM ".$this->db->prefix."social l ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user_ext e ON(l.user_id=e.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) WHERE l.who_id='".$uid."' ";
		$sql .= " AND l.is_idol=1 AND l.is_black=0 ";
		$sql .= " AND l.user_id NOT IN(SELECT who_id FROM ".$this->db->prefix."social WHERE user_id='".$uid."' AND is_black=1) ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql .= " ORDER BY l.id DESC LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql);
	}

	/**
	 * 粉丝数
	**/
	public function fans_count($uid,$condition='')
	{
		if(!$uid){
			return false;
		}
		if(is_array($uid)){
			$sql  = " SELECT count(l.id) total,l.who_id FROM ".$this->db->prefix."social l ";
			$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) WHERE l.who_id IN(".implode(",",$uid).") ";
			$sql .= " AND l.is_idol=1 AND l.is_black=0 ";
			$sql .= " AND l.user_id NOT IN(SELECT who_id FROM ".$this->db->prefix."social WHERE user_id='".$uid."' AND is_black=1) ";
			if($condition){
				$sql .= " AND ".$condition." ";
			}
			$sql .= " GROUP BY l.who_id";
			return $this->db->get_all($sql,'who_id');
		}
		$sql  = " SELECT count(l.id) FROM ".$this->db->prefix."social l ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) WHERE l.who_id='".$uid."' ";
		$sql .= " AND l.is_idol=1 AND l.is_black=0 ";
		$sql .= " AND l.user_id NOT IN(SELECT who_id FROM ".$this->db->prefix."social WHERE user_id='".$uid."' AND is_black=1) ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 黑名单列表
	**/
	public function black_list($uid,$offset=0,$psize=20,$condition='')
	{
		$sql  = " SELECT e.id,e.*,u.id,u.user,u.avatar,l.addtime FROM ".$this->db->prefix."social l ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user_ext e ON(l.who_id=e.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.who_id=u.id) WHERE l.user_id='".$uid."' ";
		$sql .= " AND l.is_black=1 ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql .= " ORDER BY l.id DESC";
		return $this->db->get_all($sql);
	}

	/**
	 * 黑名单统计数
	**/
	public function black_count($uid,$condition='')
	{
		if(!$uid){
			return false;
		}
		if(is_array($uid)){
			$sql  = " SELECT count(l.id) as total,l.user_id FROM ".$this->db->prefix."social l ";
			$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.who_id=u.id) WHERE l.user_id='".$uid."' ";
			$sql .= " AND l.is_black=1 ";
			if($condition){
				$sql .= " AND ".$condition." ";
			}
			$sql .= " GROUP BY l.user_id ";
			return $this->db->get_all($sql,'user_id');
		}
		$sql  = " SELECT count(l.id) FROM ".$this->db->prefix."social l ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.who_id=u.id) WHERE l.user_id='".$uid."' ";
		$sql .= " AND l.is_black=1 ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 移除黑名单
	**/
	public function black_del($uid=0,$who_id=0)
	{
		if(!$uid || !$who_id){
			return false;
		}
		if($uid == $who_id){
			return false;
		}
		$info = $this->links_info($uid,$who_id);
		if(!$info){
			return true;
		}
		//非爱豆成员
		if(!$info['is_idol']){
			$sql = "DELETE FROM ".$this->db->prefix."social WHERE id='".$info['id']."'";
			return $this->db->query($sql);
		}
		$sql = "UPDATE ".$this->db->prefix."social SET is_black=0  WHERE id='".$info['id']."'";
		return $this->db->query($sql);
	}

	/**
	 * 添加黑名单
	**/
	public function black_add($uid=0,$who_id=0)
	{
		if(!$uid || !$who_id){
			return false;
		}
		if($uid == $who_id){
			return false;
		}
		$info = $this->links_info($uid,$who_id);
		if(!$info){
			//添加黑名单前需要将同时解除关注关系
			$data = array('user_id'=>$uid,'who_id'=>$who_id);
			$data['addtime'] = $this->time;
			$data['is_black'] = 1;
			$data['is_idol'] = 0;
			return $this->db->insert($data,'social');
		}
		$sql = "UPDATE ".$this->db->prefix."social SET is_black=1 WHERE id='".$info['id']."'";
		return $this->db->query($sql);
	}

	public function homepage($id,$data='')
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."social_homepage WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$data || !is_array($data)){
			return $rs;
		}
		if($data && is_array($data)){
			if(!$rs){
				$data['id'] = $id;
				return $this->db->insert($data,"social_homepage");
			}
			return $this->db->update($data,'social_homepage',array('id'=>$id));
		}
		return false;
	}
}
