<?php
/**
 * 点击事件，包括支持自定义表的点击
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2022年10月25日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class click_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function events($code='')
	{
		$datalist = array();
		$datalist['zan'] = array('title'=>'好评','value'=>1,'icon1'=>'images/zan.png','icon2'=>'images/zan-noclick.png');
		$file = $this->dir_data.'xml/click_data.xml';
		if(file_exists($file)){
			$tmplist = $this->lib('xml')->read($file);
			foreach($tmplist as $key=>$value){
				$datalist[$key] = $value;
			}
		}
		if($code){
			if($datalist[$code]){
				return $datalist[$code];
			}
			return false;
		}
		return $datalist;
	}

	public function get_one($id,$code,$type="list")
	{
		$sql = "SELECT SUM(val) total FROM ".$this->db->prefix."click WHERE tid='".$id."' AND tbl='".$type."' AND code='".$code."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp || !$tmp['total']){
			return 0;
		}
		$t = abs($tmp['total']);
		return $t;
	}

	public function users_total($tid,$type="list",$is_user=false)
	{
		$sql  = " SELECT count(user_id) FROM ".$this->db->prefix."click ";
		$sql .= " WHERE tid='".$tid."' AND tbl='".$type."'";
		if($is_user){
			$sql .= " AND user_id>0 ";
		}
		return $this->db->count($sql);
	}

	public function users($tid,$type="list",$offset=0,$psize=30)
	{
		$sql  = " SELECT user_id FROM ".$this->db->prefix."click ";
		$sql .= " WHERE tid='".$tid."' AND tbl='".$type."'";
		$sql .= " AND user_id>0 ";
		$sql .= " ORDER BY id DESC ";
		if($psize && intval($psize)){
			$sql .= " LIMIT ".intval($offset).",".intval($psize);
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$uids = array();
		foreach($rslist as $key=>$value){
			$uids[] = $value['user_id'];
		}
		$condition = "u.status=1 AND u.id IN(".implode(",",$uids).")";
		$tmplist = $this->model('user')->get_list($condition,0,0);
		if(!$tmplist){
			return false;
		}
		return $tmplist;
	}

	/**
	 * 获取快评信息
	 * @参数 $id，多个ID用英文逗号隔开，或传和数组
	 * @参数 $type，类型，目前仅支持 list 和 reply 两个单词
	 * @参数 $user_id，会员ID，返回指定的会员ID是否有点赞数据
	 * @参数 $session_id，游客ID，仅限三小时内有交
	 * @返回 成功返回数组，失败返回 false
	**/
	public function get_all($id,$type='list',$user_id=0,$session_id='')
	{
		if(!$id){
			return false;
		}
		$id = $this->_ids($id);
		if(!$id){
			return false;
		}
		$ids = explode(",",$id);
		$sql = "SELECT SUM(val) total,code,tid FROM ".$this->db->prefix."click WHERE tid IN(".$id.") AND tbl='".$type."' GROUP BY code,tid";
		$tmplist = $this->db->get_all($sql);
		$elist = $this->events();
		if(!$tmplist){
			$rslist = array();
			
			foreach($ids as $key=>$value){
				foreach($elist as $k=>$v){
					if(!isset($rslist[$value])){
						$rslist[$value] = array();
					}
					$tmp = $v;
					$tmp['total'] = 0;
					$tmp['is_clicked'] = false;
					$tmp['wholist'] = array();
					$rslist[$value][$k] = $tmp;
				}
			}
			return $rslist;
		}
		$sqlist = array();
		foreach($ids as $key=>$value){
			foreach($elist as $k=>$v){
				$sql = "(SELECT c.tid,c.code,u.user,u.mobile,u.email,u.avatar,ue.* FROM ".$this->db->prefix."click c";
				$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(c.user_id=u.id) ";
				$sql .= " LEFT JOIN ".$this->db->prefix."user_ext ue ON(u.id=ue.id) ";
				$sql .= " WHERE c.tid='".$value."' AND c.user_id>0 ";
				$sql .= " AND c.code='".$k."' AND tbl='reply' ";
				$sql .= " ORDER BY c.id DESC LIMIT 10)";
				$sqlist[] = $sql;
			}
		}
		$sql = implode(" UNION ALL ",$sqlist);
		$zan_tmplist = $this->db->get_all($sql);
		$zanlist = array();
		if($zan_tmplist){
			foreach($zan_tmplist as $key=>$value){
				if(!$value['avatar']){
					$value['avatar'] = 'images/avatar.gif';
				}
				if(!isset($zanlist[$value['tid']])){
					$zanlist[$value['tid']] = array();
				}
				if(!isset($zanlist[$value['tid']][$value['code']])){
					$zanlist[$value['tid']][$value['code']] = array();
				}
				$zanlist[$value['tid']][$value['code']][] = $value;
			}
		}
		$vlist = array();
		foreach($tmplist as $key=>$value){
			if(!isset($vlist[$value['tid']])){
				$vlist[$value['tid']] = array();
			}
			$vlist[$value['tid']][$value['code']] = $value['total'];
		}
		$showlist = array();
		$time = $this->time - 3*60*60;
		if($user_id){
			$sql = "SELECT * FROM ".$this->db->prefix."click WHERE tid IN(".$id.") AND tbl='".$type."' AND (user_id='".$user_id."' OR (session_id='".$session_id."' AND dateline>=".$time."))";
		}else{
			$sql = "SELECT * FROM ".$this->db->prefix."click WHERE tid IN(".$id.") AND tbl='".$type."' AND session_id='".$session_id."' AND dateline>=".$time;
		}
		$tlist = $this->db->get_all($sql);
		if($tlist){
			foreach($tlist as $key=>$value){
				if(!isset($showlist[$value['tid']])){
					$showlist[$value['tid']] = array();
				}
				$showlist[$value['tid']][$value['code']] = true;
			}
		}
		
		$rslist = array();
		foreach($ids as $key=>$value){
			foreach($elist as $k=>$v){
				if(!isset($rslist[$value])){
					$rslist[$value] = array();
				}
				$tmp = $v;
				//统计已点击的数量
				if(isset($vlist[$value][$k])){
					$tmp['total'] = abs($vlist[$value][$k]);
				}else{
					$tmp['total'] = 0;
				}
				//指定会员是否已点击
				if(isset($showlist[$value][$k])){
					$tmp['is_clicked'] = true;
				}else{
					$tmp['is_clicked'] = false;
				}
				$tmp['wholist'] = array();
				if(isset($zanlist[$value][$k])){
					$tmp['wholist'] = $zanlist[$value][$k];
				}
				$rslist[$value][$k] = $tmp;
			}
		}
		return $rslist;
	}

	/**
	 * 主题动作保存
	 * @参数 $id 主题ID
	 * @参数 $code 对应的事件的标识
	 * @参数 $user_id 用户ID
	**/
	public function save_list($id=0,$code='zan',$user_id=0)
	{
		if(!$id || !$code){
			return false;
		}
		return $this->action($id,$code,'list',$user_id);
	}

	/**
	 * 回复动作保存
	 * @参数 $id 主题ID
	 * @参数 $code 对应的事件的标识
	 * @参数 $user_id 用户ID
	**/
	public function save_reply($id=0,$code='zan',$user_id=0)
	{
		if(!$id || !$code){
			return false;
		}
		return $this->action($id,$code,'reply',$user_id);
	}

	/**
	 * 执行动作，如果数据存在则删除，不存在取保存
	 * @参数 $id 主题ID
	 * @参数 $code 对应的事件的标识
	 * @参数 $tbl 表名，不带前缀
	 * @参数 $user_id 用户ID
	**/
	public function action($id=0,$code='zan',$tbl="list",$user_id=0)
	{
		if(!$id || !$code || !$tbl){
			return false;
		}
		$info = $this->events($code);
		$ip = $this->lib('common')->ip();
		$session_id = $this->session->sessid();
		//针对用户操作
		if($user_id){
			$chk = $this->_chk_user($id,$code,$tbl,$user_id);
			if($chk){
				$this->_delete($chk);
				return true;
			}
			$data = array('tid'=>$id,'code'=>$code,'tbl'=>$tbl,'user_id'=>$user_id);
			$data['ip'] = $ip;
			$data['session_id'] = $session_id;
			$data['val'] = $info['value'];
			return $this->_save($data);
		}
		//针对访客操作
		$chk = $this->_chk_guest($id,$code,$tbl,$session_id,$ip);
		if($chk){
			$this->_delete($chk);
			return true;
		}
		$data = array('tid'=>$id,'code'=>$code,'tbl'=>$tbl,'user_id'=>$user_id);
		$data['ip'] = $ip;
		$data['session_id'] = $session_id;
		$data['val'] = $info['value'];
		return $this->_save($data);
	}

	private function _save($data)
	{
		$data['dateline'] = $this->time;
		return $this->db->insert($data,'click');
	}

	/**
	 * 验证数据是否存在，针对会员版
	 * @参数 $id 主题ID
	 * @参数 $code 对应的事件的标识
	 * @参数 $tbl 表名，不带前缀
	 * @参数 $user_id 表示会员ID
	 * @返回 存在返回ID，不存在返回 false 
	**/
	private function _chk_user($id,$code='zan',$tbl='list',$user_id='0')
	{
		if(!$id || !$code || !$tbl || !$user_id){
			return false;
		}
		$sql  = "SELECT id FROM ".$this->db->prefix."click WHERE tid='".$id."' ";
		$sql .= " AND code='".$code."' AND tbl='".$tbl."' AND user_id='".$user_id."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			return $chk['id'];
		}
		return false;
	}

	/**
	 * 验证数据是否存在，针对游客
	 * @参数 $id 主题ID
	 * @参数 $code 对应的事件的标识
	 * @参数 $tbl 表名，不带前缀
	 * @参数 $user_id 表示会员ID
	 * @返回 存在返回ID，不存在返回 false 
	**/
	private function _chk_guest($id,$code='zan',$tbl='list',$session_id='',$ip='')
	{
		if(!$id || !$code || !$tbl || !$session_id || !$ip){
			return false;
		}
		$time = $this->time - 3*60*60;
		$sql  = "SELECT id FROM ".$this->db->prefix."click WHERE tid='".$id."' ";
		$sql .= " AND code='".$code."' AND tbl='".$tbl."' ";
		$sql .= " AND session_id='".$session_id."' AND ip='".$ip."' ";
		$sql .= " AND dateline>=".$time;
		$chk = $this->db->get_one($sql);
		if($chk){
			return $chk['id'];
		}
		return false;
	}

	private function _delete($id=0)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."click WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
