<?php
/*****************************************************************************************
	文件： {phpok}/model/model.php
	备注： 会员财富管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年07月16日 08时15分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wealth_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	//取得全部财富规则
	public function get_all($status=0,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth WHERE site_id='".$this->site_id."' ";
		if($status){
			$sql .= " AND status='".($status == 1 ? 1 : 0)."' ";
		}
		$sql .= " ORDER BY taxis ASC";
		return $this->db->get_all($sql,$pri);
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function rule_all($condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_rule WHERE 1=1 ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY taxis ASC";
		return $this->db->get_all($sql);
	}

	public function save_log($data)
	{
		return $this->db->insert_array($data,'wealth_log');
	}

	public function save_info($data)
	{
		return $this->db->insert_array($data,'wealth_info','replace');
	}

	public function get_val($uid,$wid)
	{
		$sql = "SELECT val FROM ".$this->db->prefix."wealth_info WHERE uid='".$uid."' AND wid='".$wid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return 0;
		}else{
			return $rs['val'];
		}
	}

	public function vals($condition='')
	{
		$sql = "SELECT wid,uid,val FROM ".$this->db->prefix."wealth_info ";
		if($condition){
			$sql.= "WHERE ".$condition;
		}
		return $this->db->get_all($sql);
	}

	//根据规则自动累加财富
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

	private function chk_log($data,$repeat=0,$mintime=30,$linkid=0)
	{
		if(!$data){
			return false;
		}
		if(!$data['wid'] || !$data['goal_id'] || !$data['ctrlid']){
			return false;
		}
		$sql = "SELECT id,dateline FROM ".$this->db->prefix."wealth_log WHERE wid='".$data['wid']."' ";
		$sql.= "AND goal_id='".$data['goal_id']."' AND ctrlid='".$data['ctrlid']."' ";
		$time1 = strtotime(date("Y-m-d",$this->time));
		$time2 = $time1 + $mintime;
		$sql.= "AND dateline>='".$time1."' AND dateline<'".$time2."' ";
		if($linkid && $data['mid']){
			$sql.= "AND mid='".$data['mid']."' ";
		}
		$sql.= "ORDER BY dateline DESC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return true;
		}
		//如果已有数据记录，但当前规则不允许重复时，返回否
		//超过重复记录数，返回否
		if(!$repeat || ($repeat && count($rslist)>=$repeat)){
			return false;
		}
		return true;
	}
}

?>