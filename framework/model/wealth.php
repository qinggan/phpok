<?php
/**
 * 会员财富管理
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wealth_model_base extends phpok_model
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得全部财富规则
	 * @参数 $status 状态，为1时只读有效状态，为0读全部，为2只读无效状态
	 * @参数 $pri 主键值
	 * @返回 二维数组
	**/
	public function get_all($status=0,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth WHERE site_id='".$this->site_id."' ";
		if($status){
			$sql .= " AND status='".($status == 1 ? 1 : 0)."' ";
		}
		$sql .= " ORDER BY taxis ASC";
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 取得某个财富规则配置信息
	 * @参数 $id 财富ID
	 * @参数 $typeid 字段ID
	 * @返回 一维数组
	**/
	public function get_one($id,$typeid='id')
	{
		if(!$typeid){
			$typeid = 'id';
		}
		$sql = "SELECT * FROM ".$this->db->prefix."wealth WHERE `".$typeid."`='".$id."'";
		if($this->site_id){
			$sql .= " AND site_id='".$this->site_id."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 根据查询条件，获取财富规则，条件为空获取全部财富规则
	 * @参数 $condition 查询条件
	 * @返回 二给维组
	**/
	public function rule_all($condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_rule ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY taxis ASC";
		return $this->db->get_all($sql);
	}


	/**
	 * 保存财富日志
	 * @参数 $data 一维数组
	 * @返回 插入的ID或false
	**/
	public function save_log($data)
	{
		return $this->db->insert_array($data,'wealth_log');
	}

	/**
	 * 更新会员财富信息
	 * @参数 $data 一维数组，替代式更新
	 * @返回 插入的ID或false
	**/
	public function save_info($data)
	{
		return $this->db->insert_array($data,'wealth_info','replace');
	}

	/**
	 * 获取指定的会员及指定的财富方案对应的财富内容
	 * @参数 $uid 会员ID
	 * @参数 $wid 财富ID
	 * @返回 0 或 财富值（数字或浮点）
	**/
	public function get_val($uid,$wid)
	{
		if(!$uid || !$wid){
			return 0;
		}
		$sql = "SELECT val FROM ".$this->db->prefix."wealth_info WHERE uid='".$uid."' AND wid='".$wid."'";
		$rs = $this->db->get_one($sql);
		if($rs){
			return $rs['val'];
		}
		return 0;
	}

	/**
	 * 根据查询条件获取财富值信息
	 * @参数 $condition 查询条件
	**/
	public function vals($condition='')
	{
		$sql = "SELECT wid,uid,val FROM ".$this->db->prefix."wealth_info ";
		if($condition){
			$sql.= "WHERE ".$condition;
		}
		return $this->db->get_all($sql);
	}

	/**
	 * 取得目标用户列表
	**/
	public function goal_userlist()
	{
		$xmlfile = $this->dir_data.'xml/user_agent.xml';
		if(!file_exists($xmlfile)){
			return array('user'=>'用户','introducer'=>'一级推荐人','introducer2'=>'二级推荐人','introducer3'=>'三级推荐人');
		}
		$rslist = $this->lib('xml')->read($xmlfile);
		if(isset($rslist[$this->langid])){
			return $rslist[$this->langid];
		}
		if($rslist['default']){
			return $rslist['default'];
		}
		return $rslist;
	}

	/**
	 * 获取一条规则
	 * @参数 $id 规则ID
	 * @返回 false或数组
	**/
	public function rule_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_rule WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 财富日志，系统自动生成的
	 * @参数 $note 备注
	 * @返回 数组
	**/
	private function _data($note='')
	{
		$data = array('dateline'=>$this->time,'appid'=>$this->app_id);
		$data['ctrlid'] = $this->config['ctrl'];
		$data['funcid'] = $this->config['func'];
		if($this->app_id == 'admin'){
			$data['admin_id'] = $this->session->val('admin_id');
		}else{
			$data['user_id'] = $this->session->val('user_id');
		}
		$data['note'] = $note;
		return $data;
	}

	/**
	 * 目标ID
	 * @参数 $id 当前会员ID号
	 * @参数 $goal 目标类型
	 * @返回 
	 * @更新时间 
	**/
	private function _goal($id,$goal='user')
	{
		if(!$goal || $goal == 'user'){
			return $id;
		}
		if($goal == 'introducer'){
			return $this->model('user')->get_relation($id);
		}
		$num = str_replace('introducer','',$goal);
		if(!$num || !intval($num)){
			return $this->model('user')->get_relation($id);
		}
		$num = intval($num);
		for($i=0;$i<$num;$i++){
			$id = $this->model('user')->get_relation($id);
			if(!$id){
				return false;
			}
		}
		return $id;
	}

	/**
	 * 订单支付送财富
	 * @参数 $id 订单ID号，以防止重复赠送
	 * @参数 $note 备注
	 * @返回 true 或 false
	 * @更新时间 2016年08月16日
	**/
	public function order($id,$note='')
	{
		if(!$id){
			return false;
		}
		$rulelist = $this->_rule_list('payment');
		if(!$rulelist){
			return false;
		}
		$order = $this->model('order')->get_one($id);
		if(!$order || !$order['user_id'] || $order['status'] != 'end'){
			return false;
		}
		$prolist = $this->model('order')->product_list($order['id']);
		$user = $this->model('user')->get_one($order['user_id'],'id',false,false);
		if(!$user || !$user['status']){
			return false;
		}
		$data = $this->_data($note);
		$integral = $this->model('order')->integral($id);
		
		
		foreach($rulelist as $key=>$value){
			$goal_id = $this->_goal($user['id'],$value['goal']);
			if(!$goal_id){
				continue;
			}
			if($value['goal_group_id']){
				$tmp = $this->model('user')->get_one($goal_id,'id',false,false);
				if(!$tmp || !$tmp['group_id'] || $tmp['group_id'] != $value['goal_group_id']){
					continue;
				}
			}
			if($value['goal_uids']){
				$tmp = explode(",",$value['goal_uids']);
				if(!in_array($goal_id,$tmp)){
					continue;
				}
			}
			if($value['group_id'] && $value['group_id'] != $user['group_id']){
				continue;
			}
			if($value['uids']){
				$tmp = explode(",",$value['uids']);
				if(!in_array($user['id'],$tmp)){
					continue;
				}
			}
			if($value['qty'] && $value['qty_type'] == 'order'){
				$qty = $this->model('order')->get_count("o.user_id='".$user['id']."' AND o.status='end' AND o.id!='".$order['id']."'");
				if($value['qty'] >= $qty){
					continue;
				}
			}
			if($value['qty'] && $value['qty_type'] == 'order2'){
				$qty = $this->model('order')->get_count("o.user_id='".$goal_id."' AND o.status='end' AND o.id!='".$order['id']."'");
				if($value['qty'] >= $qty){
					continue;
				}
			}
			if($value['qty'] && $value['qty_type'] == 'order3'){
				$tmpchk = $this->model('user')->count_relation($goal_id);
				if(!$tmpchk){
					continue;
				}
				$tmpsql = "SELECT uid FROM ".$this->db->prefix."user_relation WHERE introducer='".$goal_id."'";
				$qty = $this->model('order')->get_count("o.user_id IN(".$tmpsql.") AND o.status='end' AND o.id!='".$order['id']."'");
				if($value['qty'] >= $qty){
					continue;
				}
			}
			if($value['qty'] && $value['qty_type'] == 'product'){
				$condition = "o.user_id='".$user['id']."' AND o.status='end' AND o.id!='".$order['id']."' ";
				if($value['project_id']){
					$condition .= " AND l.project_id='".$value['project_id']."' ";
				}
				if($value['title_id']){
					$condition .= " AND l.id IN(".$value['title_id'].") ";
				}
				$produt_qty = $this->model('order')->product_count($condition);
				if($value['qty'] > $produt_qty){
					continue;
				}
			}
			if($value['qty'] && $value['qty_type'] == 'product2'){
				$condition = "o.user_id='".$goal_id."' AND o.status='end' AND o.id!='".$order['id']."' ";
				if($value['project_id']){
					$condition .= " AND l.project_id='".$value['project_id']."' ";
				}
				if($value['title_id']){
					$condition .= " AND l.id IN(".$value['title_id'].") ";
				}
				$produt_qty = $this->model('order')->product_count($condition);
				if($value['qty'] > $produt_qty){
					continue;
				}
			}
			if($value['qty'] && $value['qty_type'] == 'product3'){
				$tmpchk = $this->model('user')->count_relation($goal_id);
				if(!$tmpchk){
					continue;
				}
				$tmpsql = "SELECT uid FROM ".$this->db->prefix."user_relation WHERE introducer='".$goal_id."'";
				$condition = "o.user_id IN(".$tmpsql.") AND o.status='end' AND o.id!='".$order['id']."' ";
				if($value['project_id']){
					$condition .= " AND l.project_id='".$value['project_id']."' ";
				}
				if($value['title_id']){
					$condition .= " AND l.id IN(".$value['title_id'].") ";
				}
				$produt_qty = $this->model('order')->product_count($condition);
				if($value['qty'] > $produt_qty){
					continue;
				}
			}
			if($value['price'] && $value['price_type'] == 'order'){
				$total_price = $this->model('order')->get_price("o.user_id='".$user['id']."' AND o.status='end' AND o.id!='".$order['id']."'");
				if($value['price'] >= $total_price){
					continue;
				}
			}
			if($value['price'] && $value['price_type'] == 'order2'){
				$total_price = $this->model('order')->get_price("o.user_id='".$goal_id."' AND o.status='end' AND o.id!='".$order['id']."'");
				if($value['price'] >= $total_price){
					continue;
				}
			}
			if($value['price'] && $value['price_type'] == 'order3'){
				$tmpchk = $this->model('user')->count_relation($goal_id);
				if(!$tmpchk){
					continue;
				}
				$tmpsql = "SELECT uid FROM ".$this->db->prefix."user_relation WHERE introducer='".$goal_id."'";
				$total_price = $this->model('order')->get_price("o.user_id IN(".$tmpsql.") AND o.status='end' AND o.id!='".$order['id']."'");
				if($value['price'] >= $total_price){
					continue;
				}
			}
			if($value['price'] && $value['price_type'] == 'product'){
				$condition = "o.user_id='".$user['id']."' AND o.status='end' AND o.id!='".$order['id']."' ";
				if($value['project_id']){
					$condition .= " AND l.project_id='".$value['project_id']."' ";
				}
				if($value['title_id']){
					$condition .= " AND l.id IN(".$value['title_id'].") ";
				}
				$produt_price = $this->model('order')->product_price($condition);
				if($value['price'] > $produt_price){
					continue;
				}
			}
			if($value['price'] && $value['price_type'] == 'product2'){
				$condition = "o.user_id='".$goal_id."' AND o.status='end' AND o.id!='".$order['id']."' ";
				if($value['project_id']){
					$condition .= " AND l.project_id='".$value['project_id']."' AND o.id!='".$order['id']."' ";
				}
				if($value['title_id']){
					$condition .= " AND l.id IN(".$value['title_id'].") ";
				}
				$produt_price = $this->model('order')->product_price($condition);
				if($value['price'] > $produt_price){
					continue;
				}
			}
			if($value['price'] && $value['price_type'] == 'product3'){
				$tmpchk = $this->model('user')->count_relation($goal_id);
				if(!$tmpchk){
					continue;
				}
				$tmpsql = "SELECT uid FROM ".$this->db->prefix."user_relation WHERE introducer='".$goal_id."'";
				$condition = "o.user_id IN(".$tmpsql.") AND o.status='end' AND o.id!='".$order['id']."' ";
				if($value['project_id']){
					$condition .= " AND l.project_id='".$value['project_id']."' ";
				}
				if($value['title_id']){
					$condition .= " AND l.id IN(".$value['title_id'].") ";
				}
				$produt_price = $this->model('order')->product_price($condition);
				if($value['price'] > $produt_price){
					continue;
				}
			}
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $goal_id;
			$log['user_id'] = $user['id'];
			$tmpprice = str_replace('price',$order['price'],$value['val']);
			$tmpprice = str_replace('integral',$integral,$tmpprice);
			eval('$value[\'val\'] = '.$tmpprice.';');
			$val = round($value['val'],$value['dnum']);
			$log['val'] = $val;
			$log['mid'] = $id;
			$chk = $this->chk_log($log);
			if(!$chk){
				continue;
			}
			$this->save_log($log);
			if($log['status']){
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$val2 = $get_val + $val;
				if($val2<0){
					$val2 = 0;
				}
				$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
				$this->save_info($array);
			}
			if($value['if_stop']){
				break;
			}
		}
		return true;
	}

	/**
	 * 充值到账对积分进行转换
	 * @参数 $logid 支付ID
	 * @参数 $myval 要充值的数量
	**/
	public function recharge($logid,$myval=0)
	{
		$order = $this->model('payment')->log_one($logid);
		if(!$order || !$order['status'] || !$order['user_id']){
			return false;
		}
		$ext = $order['ext'] ? unserialize($order['ext']) : array();
		//如果充值成功
		if($ext['phpok_status'] || !$ext['goal']){
			return true;
		}
		//查看金额
		$data = $this->_data(P_Lang('在线充值'));
		$rs = $this->get_one($ext['goal']);
		if(!$rs || !$rs['status'] || !$rs['ifpay']){
			return false;
		}
		$data['status'] = $rs['ifcheck'] ? 0 : 1;
		$data['rule_id'] = 0;
		$data['wid'] = $rs['id'];
		$data['goal_id'] = $order['user_id'];
		$data['val'] = round($order['price'] * $rs['pay_ratio'],2);
		if($myval){
			$data['val'] = $myval;
		}
		$data['mid'] = 0;
		$this->save_log($data);
		if($data['status']){
			$get_val = $this->get_val($data['goal_id'],$data['wid']);
			$val2 = $get_val + $data['val'];
			if($val2<0){
				$val2 = 0;
			}
			$array = array('wid'=>$data['wid'],'lasttime'=>$this->time,'uid'=>$data['goal_id'],'val'=>$val2);
			$this->save_info($array);
		}
		$ext['phpok_status'] = true;
		$tmp = serialize($ext);
		$this->model('payment')->log_update(array('ext'=>$tmp),$logid);
		return true;
	}

	/**
	 * 阅读/发布/评论赠送财富
	 * @参数 $id 主题ID
	 * @参数 $uid 会员ID
	 * @参数 $type 类型，content读主题，comment评论主题，post发布主题
	 * @参数 $note 备注
	 * @返回 true 或 false
	 * @更新时间 2016年08月16日
	**/
	public function add_integral($id=0,$uid=0,$type='content',$note='')
	{
		if(!$id || !$uid || !intval($id) || !intval($uid)){
			return false;
		}
		if(!in_array($type,array('content','comment','post'))){
			return false;
		}
		$id = intval($id);
		$uid = intval($uid);
		$rs = $this->model('list')->simple_one($id);
		if(!$rs || !$rs['status']){
			return false;
		}
		if($type == 'post' && $rs['user_id'] != $uid){
			return false;
		}
		if($type == 'comment'){
			$condition = "tid='".$id."' AND uid='".$uid."' AND status=1";
			$comment_list = $this->model('reply')->get_list($condition,0,1);
			if(!$comment_list){
				return false;
			}
		}
		$user = $this->model('user')->get_one($uid,'id',false,false);
		$tmplist = $this->_rule_list($type);
		if(!$tmplist){
			return false;
		}
		$data = $this->_data($note);
		$integral = $this->model('list')->integral($id);
		foreach($tmplist as $key=>$value){
			if($value['group_id'] && $value['group_id'] != $user['group_id']){
				continue;
			}
			if($value['uids']){
				$tmp = explode(",",$value['uids']);
				if(!in_array($user['id'],$tmp)){
					continue;
				}
			}
			if($value['project_id'] && $value['project_id'] != $rs['project_id']){
				continue;
			}
			if($value['title_id']){
				$tmp = explode(",",$value['title_id']);
				if(!in_array($id,$tmp)){
					continue;
				}
			}
			$goal_id = $this->_goal($user['id'],$value['goal']);
			if(!$goal_id){
				return false;
			}
			if($value['goal_group_id']){
				$tmp = $this->model('user')->get_one($goal_id,'id',false,false);
				if(!$tmp || !$tmp['group_id'] || $tmp['group_id'] != $value['goal_group_id']){
					continue;
				}
			}
			if($value['goal_uids']){
				$tmp = explode(",",$value['goal_uids']);
				if(!in_array($goal_id,$tmp)){
					continue;
				}
			}
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $goal_id;
			$tmpprice = str_replace('integral',$integral,$value['val']);
			eval('$value[\'val\'] = '.$tmpprice.';');
			$val = round($value['val'],$value['dnum']);
			$log['val'] = $val;
			$log['mid'] = $id;
			$chk = $this->chk_log($log);
			if(!$chk){
				continue;
			}
			$this->save_log($log);
			if($log['status']){
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$val2 = $get_val + $val;
				if($val2<0){
					$val2 = 0;
				}
				$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
				$this->save_info($array);
			}
		}
		return true;
	}

	/**
	 * 注册送财富（如果规则的值是负值，表示扣除）
	 * @参数 $uid 会员ID
	 * @参数 $note 备注
	 * @返回 true 或者 false
	 * @更新时间 2016年07月30日
	**/
	public function register($uid,$note='')
	{
		$tmplist = $this->_rule_list('register');
		if(!$tmplist){
			return false;
		}
		$user = $this->model('user')->get_one($uid,'id',false,false);
		$data = $this->_data($note);
		foreach($tmplist as $key=>$value){
			if($value['group_id'] && $value['group_id'] != $user['group_id']){
				continue;
			}
			if($value['uids']){
				$tmp = explode(",",$value['uids']);
				if(!in_array($user['id'],$tmp)){
					continue;
				}
			}
			$goal_id = $this->_goal($user['id'],$value['goal']);
			if(!$goal_id){
				return false;
			}
			if($value['goal_group_id']){
				$tmp = $this->model('user')->get_one($goal_id,'id',false,false);
				if(!$tmp || !$tmp['group_id'] || $tmp['group_id'] != $value['goal_group_id']){
					continue;
				}
			}
			if($value['goal_uids']){
				$tmp = explode(",",$value['goal_uids']);
				if(!in_array($goal_id,$tmp)){
					continue;
				}
			}
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $goal_id;
			$val = round($value['val'],$value['dnum']);
			$log['val'] = $val;
			$this->save_log($log);
			if($log['status']){
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$val2 = $get_val + $val;
				if($val2<0){
					$val2 = 0;
				}
				$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
				$this->save_info($array);
			}
		}
		return true;
	}

	/**
	 * 登录送财富，一天仅一次有效（如果规则的值是负值，表示扣除）
	 * @参数 $uid 会员ID
	 * @参数 $note 备注
	 * @返回 true 或 false
	 * @更新时间 2016年07月25日
	**/
	public function login($uid,$note='')
	{
		$tmplist = $this->_rule_list('login');
		if(!$tmplist){
			return false;
		}
		$user = $this->model('user')->get_one($uid,'id',false,false);
		$data = $this->_data($note);
		foreach($tmplist as $key=>$value){
			if($value['group_id'] && $value['group_id'] != $user['group_id']){
				continue;
			}
			if($value['uids']){
				$tmp = explode(",",$value['uids']);
				if(!in_array($user['id'],$tmp)){
					continue;
				}
			}
			$goal_id = $this->_goal($user['id'],$value['goal']);
			if(!$goal_id){
				return false;
			}
			if($value['goal_group_id']){
				$tmp = $this->model('user')->get_one($goal_id,'id',false,false);
				if(!$tmp || !$tmp['group_id'] || $tmp['group_id'] != $value['goal_group_id']){
					continue;
				}
			}
			if($value['goal_uids']){
				$tmp = explode(",",$value['goal_uids']);
				if(!in_array($goal_id,$tmp)){
					continue;
				}
			}
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $goal_id;
			$chk = $this->chk_log($log);
			if(!$chk){
				continue;
			}
			$val = round($value['val'],$value['dnum']);
			$log['val'] = $val;
			$this->save_log($log);
			if($log['status']){
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$val2 = $get_val + $val;
				if($val2<0){
					$val2 = 0;
				}
				$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
				$this->save_info($array);
			}
		}
		return true;
	}

	/**
	 * 手动增加或减去财富
	 * @参数 $wid 财富ID，支持数字ID或标识
	 * @参数 $uid 目标会员ID，仅支持数字
	 * @参数 $val 要增加多少，为负数时表示减去
	 * @参数 $note 备注
	**/
	public function save_val($wid,$uid,$val=0,$note='')
	{
		if(!$wid || !$uid || !$val || !$note){
			return false;
		}
		if(is_numeric($wid)){
			$rs = $this->get_one($wid,'id');
		}else{
			$rs = $this->get_one($wid,'identifier');
		}
		if(!$rs){
			return false;
		}
		$log = $this->_data($note);
		$log['status'] = 1;
		$log['rule_id'] = 0;
		$log['wid'] = $rs['id'];
		$log['goal_id'] = $uid;
		$log['val'] = round($val,$rs['dnum']);
		$this->save_log($log);
		$get_val = $this->get_val($uid,$rs['id']);
		$val2 = $get_val + $val;
		if($val2<0){
			$val2 = 0;
		}
		$array = array('wid'=>$rs['id'],'lasttime'=>$this->time,'uid'=>$uid,'val'=>$val2);
		$this->save_info($array);
		return true;
	}

	private function _rule_list($type='')
	{
		if(!$type){
			return false;
		}
		$sql = "SELECT r.*,w.dnum,w.ifcheck FROM ".$this->db->prefix."wealth_rule r LEFT JOIN ".$this->db->prefix."wealth w ";
		$sql.= "ON(r.wid=w.id) WHERE w.site_id='".$this->site_id."' AND w.status=1 AND r.action='".$type."' ";
		$sql.= "ORDER BY w.taxis,r.taxis ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 检查日志，主要是检查是否有记录，如主题防止多次刷新，登录24小时内只计一次等
	 * @参数 $data 一维数组
	 * @返回 
	 * @更新时间 
	**/
	private function chk_log($data)
	{
		if(!$data){
			return false;
		}
		if(!$data['wid'] || !$data['goal_id'] || !$data['rule_id']){
			return false;
		}
		$sql = " SELECT id FROM ".$this->db->prefix."wealth_log WHERE wid='".$data['wid']."' ";
		$sql.= " AND goal_id='".$data['goal_id']."' AND rule_id='".$data['rule_id']."' ";
		if($data['ctrlid'] == 'login'){
			$time1 = strtotime(date("Y-m-d",$this->time));
			$time2 = $time1 + 24 * 60 * 60;
			$sql .= " AND dateline>='".$time1."' AND dateline<'".$time2."' ";
		}
		if($data['mid']){
			$sql .= " AND mid='".$data['mid']."' ";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return true;
		}
		return false;
	}

	public function log_list($condition='',$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_log ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		$sql.= "ORDER BY dateline DESC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function log_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_log WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}
}