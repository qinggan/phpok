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
	 * @返回 一维数组
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth WHERE id='".$id."'";
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
		$xmlfile = $this->dir_root.'data/xml/user_agent.xml';
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
		$order = $this->model('order')->get_one($id);
		if(!$order){
			return false;
		}
		if(!$order['user_id']){
			return false;
		}
		$uid = $order['user_id'];
		$tmplist = $this->_rule_list('payment');
		if(!$tmplist){
			return false;
		}
		$data = $this->_data($note);
		foreach($tmplist as $key=>$value){
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $this->_goal($uid,$value['goal']);
			$tmpprice = str_replace('price',$order['price'],$value['val']);
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
		$data = $this->_data($note);
		foreach($tmplist as $key=>$value){
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $this->_goal($uid,$value['goal']);
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
		$data = $this->_data($note);
		foreach($tmplist as $key=>$value){
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $this->_goal($uid,$value['goal']);
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
	 * 根据规则自动计算财富
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	public function wealth_autosave($uid=0,$note='',$main_id='',$ext='')
	{
		if(!$uid){
			return false;
		}
		$data = array('dateline'=>$this->time,'appid'=>$this->app_id,'ctrlid'=>$this->config['ctrl'],'funcid'=>$this->config['func']);
		$url = $this->url.$_SERVER['PHP_SELF'];
		if($_SERVER['QUERY_STRING']){
			$url .= '?'.$_SERVER['QUERY_STRING'];
		}
		$data['url'] = substr($url,0,255);
		if($_SESSION['admin_id']){
			$data['admin_id'] = $_SESSION['admin_id'];
		}
		if($_SESSION['user_id']){
			$data['user_id'] = $_SESSION['user_id'];
		}
		$data['note'] = $note;
		//读取当前积分规则
		$wealth_list = $this->get_all(1,'id');
		if(!$wealth_list){
			return false;
		}
		$rule_list = $this->rule_all();
		if(!$rule_list){
			return false;
		}
		foreach($rule_list as $key=>$value){
			$wealth_list[$value['wid']]['rule'][] = $value;
		}
		unset($rule_list);
		foreach($wealth_list as $key=>$value){
			if(!$value['rule']){
				unset($wealth_list[$key]);
				continue;
			}
			$log = $data;
			$log['wid'] = $value['id'];
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['mid'] = $main_id;
			foreach($value['rule'] as $k=>$v){
				//当控制器不符合要求时，跳过
				if($v['action'] != $log['ctrlid']){
					continue;
				}
				if($v['goal'] == 'user'){
					$log['goal_id'] = $uid;
				}else{
					$tmp = $this->model('user')->get_relation($uid);
					if(!$tmp){
						continue;
					}
					$log['goal_id'] = $tmp;
					unset($tmp);
				}
				//判断符合这个条件的规则是否有记录
				$chk = $this->chk_log($log,$v['repeat'],$v['mintime'],$v['linkid']);
				if(!$chk){
					continue;
				}
				$val = $v['val'];
				if($ext && is_array($ext) && count($ext)>0){
					foreach($ext as $kk=>$vv){
						$val = str_replace($kk,$vv,$val);
					}
				}
				$val = round($val,$value['dnum']);
				$log['val'] = $val;
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$this->save_log($log);
				if($log['status']){
					$val2 = $get_val + $val;
					if($val2<0){
						$val2 = 0;
					}
					$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
					$this->save_info($array);
				}
			}
		}
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
}