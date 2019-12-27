<?php
/**
 * 会员数据增删查改
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年09月07日
**/

class user_model_base extends phpok_model
{
	public $psize = 20;
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得单条会员数组
	 * @参数 $id 会员ID或其他唯一标识
	 * @参数 $field 指定的标识，当为布尔值时表示是否格式化扩展数据
	 * @参数 $ext 布尔值或1或0，当$field为布尔值时这里表示是否显示财富
	 * @参数 $wealth 布尔值或1或0，表示财富
	**/
	public function get_one($id,$field='id',$ext=true,$wealth=true)
	{
		if(!$id){
			return false;
		}
		if(is_bool($field) || is_numeric($field)){
			$wealth = $ext;
			$ext = $field;
			$field = 'id';
		}
		if(!$field){
			$field = 'id';
		}
		$flist = $this->fields_all();
		$ufields = "u.*";
		$field_type = 'main';
		$condition = "u.".$field."='".$id."'";
		if($flist){
			foreach($flist as $key=>$value){
				$ufields .= ",e.".$value['identifier'];
				if($value['identifier'] == $value){
					$condition = "e.".$field."='".$id."'";
				}
			}
		}
		$sql = " SELECT ".$ufields." FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		$sql.= " WHERE ".$condition;
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($wealth){
			$rs['wealth'] = $this->wealth($rs['id']);
		}
		if($ext && $flist){
			foreach($flist AS $key=>$value){
				$rs[$value['identifier']] = $this->lib('form')->show($value,$rs[$value['identifier']]);
			}
		}
		return $rs;
	}

	/**
	 * 取得会员的财富信息
	 * @参数 $uid 会员ID
	 * @参数 $wid 指定的财富ID，为0或空时返回会员下的所有财富信息
	 * @参数 $return 返回，仅在$wid大于0时有效，支持两个参数，一个是value，返回值，一个是array，返回数组
	**/
	public function wealth($uid,$wid=0,$return='value')
	{
		$wlist = $this->model('wealth')->get_all(1,'id');
		if(!$wlist){
			return false;
		}
		$wealth = array();
		foreach($wlist as $key=>$value){
			$val = number_format(0,$value['dnum']);
			$wealth[$value['identifier']] = array('id'=>$value['id'],'title'=>$value['title'],'val'=>$val,'unit'=>$value['unit']);
		}
		$condition = "uid='".$uid."'";
		$tlist = $this->model('wealth')->vals($condition);
		if($tlist){
			foreach($tlist as $key=>$value){
				$tmp = $wlist[$value['wid']];
				$val = round($value['val'],$tmp['dnum']);
				$wealth[$tmp['identifier']]['val'] = $val;
			}
		}
		if($wid){
			if(is_numeric($wid)){
				$tmp = false;
				foreach($wealth as $key=>$value){
					if($value['id'] == $wid){
						$tmp = $value;
						break;
					}
				}
				if(!$tmp){
					return false;
				}
				if($return == 'array'){
					return $tmp;
				}
				return $tmp['val'];
			}
			//字串
			if(!$wealth[$wid]){
				return false;
			}
			if($return == 'array'){
				return $wealth[$wid];
			}
			return $wealth[$wid]['val'];
		}
		return $wealth;
	}

	/**
	 * 根据条件取得会员列表数据
	 * @参数 $condition 查询条件，主表使用关键字 u，扩展表使用关键字 e
	 * @参数 $offset 起始位置
	 * @参数 $psize 查询数量
	 * @参数 $pri 绑定的主键
	**/
	public function get_list($condition="",$offset=0,$psize=30)
	{
		$flist = $this->fields_all();
		$ufields = "u.*";
		if($flist){
			foreach($flist as $key=>$value){
				$ufields .= ",e.".$value['identifier'];
			}
		}
		$sql = " SELECT ".$ufields." FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$offset = intval($offset);
		$psize = intval($psize);
		$sql.= " ORDER BY u.regtime DESC,u.id DESC ";
		if($psize){
			$offset = intval($offset);
			$sql .= "LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$idlist = array_keys($rslist);
		//读取推荐人信息
		$vlist = $this->get_relation_all($idlist);
		if($vlist){
			foreach($vlist as $key=>$value){
				if($rslist[$value['uid']]){
					$rslist[$value['uid']]['introducer'] = $value;
				}
			}
		}
		//读取会员积分信息
		$wlist = $this->model('wealth')->get_all(1,'id');
		if($wlist){
			$condition = "uid IN(".implode(",",$idlist).")";
			$tlist = $this->model('wealth')->vals($condition);
			if($tlist){
				$wealth = array();
				foreach($tlist as $key=>$value){
					$tmp = $wlist[$value['wid']];
					$rslist[$value['uid']]['wealth'][$tmp['identifier']] = $value['val'];
				}
			}
		}
		if(!$flist){
			return $rslist;
		}
		foreach($rslist AS $key=>$value){
			foreach($flist AS $k=>$v){
				$value[$v['identifier']] = $this->lib('form')->show($v,$value[$v['identifier']]);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}


	/**
	 * 取得指定条件下的会员数量
	 * @参数 $condition 查询条件，主表使用关键字 u，扩展表使用关键字 e
	**/
	public function get_count($condition="")
	{
		$sql = " SELECT count(u.id) FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 检测账号是否冲突
	 * @参数 $name 账号名称
	 * @参数 $id 会员ID，表示不包含这个会员ID
	**/
	public function chk_email($email,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE email='".$email."' ";
		if($id){
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 检测账号是否冲突
	 * @参数 $name 账号名称
	 * @参数 $id 会员ID，表示不包含这个会员ID
	**/
	public function chk_name($name,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE user='".$name."' ";
		if($id){
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 邀请码验证，必须是唯一的
	 * @参数 $code 邀请码
	 * @参数 $id 会员ID，表示不包含这个会员ID
	**/
	public function chk_code($code,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE code='".$code."' ";
		if($id){
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 取得扩展字段的所有扩展信息
	 * @参数 $condition 取得会员扩展字段配置
	 * @参数 $pri_id 主键ID
	**/
	public function fields_all($condition="",$pri_id="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE ftype='user' ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri_id);
	}

	/**
	 * 取得指定表的字段
	 * @参数 $tbl 表名
	**/
	public function tbl_fields_list($tbl='user')
	{
		return $this->db->list_fields($tbl);
	}

	/**
	 * 取得某一条扩展字段配置信息
	 * @参数 $id 主键ID
	**/
	public function field_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得指定会员ID下的全部会员主表信息
	 * @参数 $uid 会员ID，多个ID用英文逗号隔开
	 * @参数 $pri 绑定的主键
	 * @参数 
	**/
	public function get_all_from_uid($uid,$pri="")
	{
		if(!$uid){
			return false;
		}
		if(is_string($uid)){
			$tmp = explode(",",$uid);
			foreach($tmp as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($tmp[$key]);
				}
			}
			$uid = implode(",",$tmp);
		}else{
			if(is_array($uid)){
				$uid = implode(",",$uid);
			}
		}
		$condition = "u.id IN(".$uid.")";
		$rslist = $this->get_list($condition,0,0);
		if(!$rslist){
			return false;
		}
		if(!$pri){
			$tmplist = array();
			foreach($rslist as $key=>$value){
				$tmplist[] = $value;
			}
			return $tmplist;
		}
		if($pri && $pri != 'id'){
			$tmplist = array();
			foreach($rslist as $key=>$value){
				$tmpid = $value[$pri];
				$tmplist[$tmpid] = $value;
			}
			return $tmplist;
		}
		return $rslist;
	}

	/**
	 * 取得会员主表字段
	**/
	public function fields()
	{
		return $this->db->list_fields($this->db->prefix."user");
	}

	/**
	 * 通过邮箱取得会员的ID
	 * @参数 $email 指定的邮箱
	 * @参数 $id 不包括会员ID
	**/
	public function uid_from_email($email,$id="")
	{
		if(!$email){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE email='".$email."'";
		if($id){
			$sql.= " AND id !='".$id."'";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['id'];
	}

	/**
	 * 通过验证串获取会员ID，注意，此项及有可能获得到的会员ID是不准确的，适用于忘记密码
	 * @参数 $code 验证串
	**/
	public function uid_from_chkcode($code)
	{
		if(!$code){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE code='".$code."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['id'];
	}

	/**
	 * 保存会员主要资料
	 * @参数 $data 一维数组，会员内容
	 * @参数 $id 会员ID，为空或0时表示新增
	**/
	public function save($data,$id=0)
	{
		if($id){
			$status = $this->db->update_array($data,"user",array("id"=>$id));
			if($status){
				return $id;
			}
			return false;
		}else{
			if(!$data['regtime']){
				$data['regtime'] = $this->time;
			}
			return $this->db->insert_array($data,"user");
		}
	}

	/**
	 * 写入会员扩展数据，适用于新注册会员
	 * @参数 $data 一维数组
	**/
	public function save_ext($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$fields = $this->fields_all();
		if($fields){
			foreach($fields as $key=>$value){
				if(!isset($data[$value['identifier']])){
					$data[$value['identifier']] = $value['content'];
				}
			}
		}
		return $this->db->insert_array($data,"user_ext","replace");
	}

	/**
	 * 更新会员扩展表数据
	 * @参数 $data 一维数组，要更新的会员数据内容
	 * @参数 $id 会员ID
	**/
	public function update_ext($data,$id)
	{
		if(!$data || !is_array($data) || !$id){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$chk = $this->db->get_one($sql);
		if(!$chk){
			$data['id'] = $id;
			return $this->save_ext($data);
		}
		return $this->db->update_array($data,"user_ext",array("id"=>$id));
	}

	/**
	 * 取得会员的推荐人会员ID
	 * @参数 $uid 当前会员ID
	**/
	public function get_relation($uid)
	{
		$sql = "SELECT introducer FROM ".$this->db->prefix."user_relation WHERE uid='".$uid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['introducer'];
	}

	public function get_relation_all($uid)
	{
		if(!$uid){
			return false;
		}
		if(is_array($uid)){
			$uid = implode(",",$uid);
		}
		$sql = "SELECT u.id,u.user,u.email,u.avatar,u.mobile,ur.uid FROM ".$this->db->prefix."user_relation ur ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(ur.introducer=u.id) ";
		$sql.= "WHERE u.id IN(".$uid.")";
		return $this->db->get_all($sql,'uid');
	}

	/**
	 * 保存会员与推荐人关系
	 * @参数 $uid 会员ID
	 * @参数 $introducer 推荐人ID
	**/
	public function save_relation($uid=0,$introducer=0)
	{
		if(!$uid){
			return false;
		}
		if(!$introducer){
			$sql = "DELETE FROM ".$this->db->prefix."user_relation WHERE uid='".$uid."'";
			$this->db->query($sql);
			return true;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."user_relation WHERE uid='".$uid."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			if($chk['introducer'] != $introducer){
				$sql = "UPDATE ".$this->db->prefix."user_relation SET introducer='".$introducer."',dateline='".$this->time."' WHERE uid='".$uid."'";
				$this->db->query($sql);
			}
			return true;
		}
		$sql = "INSERT INTO ".$this->db->prefix."user_relation(uid,introducer,dateline) VALUES('".$uid."','".$introducer."','".$this->time."')";
		return $this->db->query($sql);
	}

	/**
	 * 取得会员推荐列表
	 * @参数 $uid 当前会员ID
	 * @参数 $offset 初始位置
	 * @参数 $psize 查询数量
	 * @参数 $condition 其他查询条件
	**/
	public function list_relation($uid,$offset=0,$psize=30,$condition='')
	{
		$sql  = " SELECT u.id,u.group_id,u.user,u.mobile,u.email,u.avatar,u.regtime,u.status,ug.title group_title FROM ".$this->db->prefix."user_relation ur ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(ur.uid=u.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user_group ug ON(u.group_id=ug.id) ";
		$sql .= " WHERE ur.introducer='".$uid."' ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY u.regtime DESC LIMIT ".intval($offset).",".intval($psize);
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if(!$value['id']){
				unset($rslist[$key]);
				continue;
			}
		}
		$list = array();
		foreach($rslist as $key=>$value){
			$list[$value['id']] = $value;
		}
		return $list;
	}

	/**
	 * 取得总数量
	 * @参数 $uid 当前会员ID
	 * @参数 $condition 其他查询条件
	**/
	public function count_relation($uid,$condition="")
	{
		$sql = "SELECT count(uid) FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."'";
		if($condition){
			$sql .= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得最大时间和最小时间
	 * @参数 $uid 会员ID
	**/
	public function time_relation($uid)
	{
		$sql = "SELECT max(dateline) max_time,min(dateline) min_time FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."'";
		return $this->db->get_one($sql);
	}

	public function stat_relation($uid)
	{
		$sql = "SELECT count(uid) as total,FROM_UNIXTIME(dateline,'%Y%m') as month FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."' ";
		$sql.= "GROUP BY FROM_UNIXTIME(dateline,'%Y%m') ORDER BY dateline ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得会员有验证串是否一致，一致则自动登录
	 * @参数 $uid 会员ID
	 * @参数 $chk 验证串
	**/
	public function token_check($uid,$sign)
	{
		if(!$uid || !$sign){
			return false;
		}
		$sql = "SELECT id,group_id,user,pass FROM ".$this->db->prefix."user WHERE id='".$uid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$code = md5($uid.'-'.$rs['user'].'-'.$rs['pass']);
		if(strtolower($code) == strtolower($sign)){
			$this->session->assign('user_id',$uid);
			$this->session->assign('user_name',$rs['user']);
			$this->session->assign('user_gid',$rs['group_id']);
			return true;
		}
		return false;
	}

	/**
	 * 生成验证串
	 * @参数 $uid 会员ID
	**/
	public function token_create($uid,$keyid='')
	{
		if(!$uid || !$keyid){
			return false;
		}
		$sql = "SELECT id,group_id,user,pass FROM ".$this->db->prefix."user WHERE id='".$uid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$code = md5($uid.'-'.$rs['user'].'-'.$rs['pass']);
		$array = array('id'=>$uid,'code'=>$code);
		$this->lib('token')->keyid($keyid);
		return $this->lib('token')->encode($array);
	}

	/**
	 * 更新会员验证串
	 * @参数 $code 验证码，为空表示清空验证码
	 * @参数 $id 会员ID
	**/
	public function update_code($code,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET code='".$code."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 会员地址库保存
	 * @参数 $data 要保存的数组
	 * @参数 $id 地址ID
	**/
	public function address_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'user_address',array("id"=>$id));
		}
		return $this->db->insert_array($data,'user_address');
	}

	/**
	 * 会员下的地址信息
	 * @参数 $uid 会员ID号
	**/
	public function address_all($uid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_address WHERE user_id='".$uid."' ORDER BY id DESC LIMIT 999";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得单条地址信息
	 * @参数 $id 地址ID
	**/
	public function address_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_address WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 设置默认地址
	 * @参数 $id 要指定的默认地址
	**/
	public function address_default($id)
	{
		$chk = $this->address_one($id);
		$user_id = $chk['user_id'];
		$sql = "UPDATE ".$this->db->prefix."user_address SET is_default=0 WHERE user_id='".$user_id."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."user_address SET is_default=1 WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 删除一条会员地址库
	 * @参数 $id 指定的地址ID
	**/
	public function address_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user_address WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 设置会员状态
	**/
	public function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//邮箱登录
	function user_email($email,$uid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE email='".$email."'";
		if($uid){
			$sql .= " AND id != '".$uid."'";
		}
		return $this->db->get_one($sql);
	}

	public function user_mobile($mobile,$uid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE mobile='".$mobile."'";
		if($uid){
			$sql .= " AND id != '".$uid."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 删除会员操作
	 * @参数 $id 会员ID
	**/
	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$this->db->query($sql);
		//删除推荐人关系
		$relation = $this->get_relation($id);
		if($relation){
			$sql = "UPDATE ".$this->db->prefix."user_relation SET introducer='".$relation."' WHERE introducer='".$id."'";
		}else{
			$sql = "DELETE FROM ".$this->db->prefix."user_relation WHERE introducer='".$id."'";
		}
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."user_relation WHERE uid='".$id."'";
		$this->db->query($sql);
		//删除相应的积分
		$sql = "DELETE FROM ".$this->db->prefix."wealth_info WHERE uid='".$id."'";
		$this->db->query($sql);
		//删除积分日志
		$sql = "DELETE FROM ".$this->db->prefix."wealth_log WHERE goal_id='".$id."'";
		$this->db->query($sql);
		//会员订单变成游客订单
		$sql = "UPDATE ".$this->db->prefix."order SET user_id=0 WHERE user_id='".$id."'";
		$this->db->query($sql);
		//删除会员的主题关联
		$sql = "UPDATE ".$this->db->prefix."list SET user_id=0 WHERE user_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function relation_order_count($uid)
	{
		$condition = "SELECT uid FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."'";
		$sql = "o.user_id IN(".$condition.")";
		return $this->model('order')->get_count($sql);
	}

	public function relation_product_count($uid)
	{
		$condition = "SELECT uid FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."'";
		$sql = "o.user_id IN(".$condition.")";
		return $this->model('order')->product_count($sql);
	}
}