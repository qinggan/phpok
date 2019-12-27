<?php
/**
 * 财富规则管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wealth_control extends phpok_control
{
	private $popedom;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('wealth');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 全部财富规则
	**/
	public function index_f()
	{
		$rslist = $this->model('wealth')->get_all();
		$this->assign('rslist',$rslist);
		$this->view('wealth_index');
	}

	/**
	 * 财富明细，会员下的财宣清单
	 * @参数 id 财富ID
	**/
	public function info_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url('wealth'));
		}
		$rs = $this->model('wealth')->get_one($id);
		$this->assign('rs',$rs);
		$condition = "w.wid=".$id;
		$pageurl = $this->url('wealth','list','id='.$id);
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND u.user LIKE '%".$keywords."%'";
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$total = $this->model('wealth')->info_total($condition);
		if($total){
			$psize = $this->config['psize'];
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('wealth')->info_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
			$this->assign('rslist',$rslist);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
		}
		$this->view('wealth_info');
	}

	/**
	 * 每个会员的财富日志
	 * @参数 wid 财富ID
	 * @参数 uid 会员ID
	**/
	public function log_f()
	{
		$wid = $this->get('wid','int');
		$uid = $this->get('uid','int');
		if(!$wid){
			$this->error(P_Lang('未指定财富ID'));
		}
		if(!$uid){
			$this->error(P_Lang('未指定会员ID'));
		}
		$rs = $this->model('wealth')->get_one($wid);
		$this->assign('rs',$rs);
		$pageurl = $this->url('wealth','log','wid='.$wid.'&uid='.$uid);
		$condition = "wid='".$wid."' AND goal_id='".$uid."' AND status=1";
		$total = $this->model('wealth')->log_total($condition);
		if($total){
			$psize = $this->config['psize'];
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('wealth')->log_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
			$this->assign('rslist',$rslist);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
		}
		$this->view('wealth_log');
	}

	/**
	 * 新增或扣除财富
	 * @参数 wid 财富ID
	 * @参数 uid 会员ID
	 * @参数 note 备注
	 * @参数 val 财富值
	 * @参数 type 为-号时表示扣除
	**/
	public function val_f()
	{
		$wid = $this->get('wid','int');
		$uid = $this->get('uid','int');
		if(!$wid){
			$this->json(P_Lang('未指定财富ID'));
		}
		if(!$uid){
			$this->json(P_Lang('未指定会员ID'));
		}
		$note = $this->get('note');
		$val = $this->get('val');
		if(!$val){
			$this->json(P_Lang('未指定数值'));
		}
		$val = abs($val);
		$type = $this->get('type');
		$from_uid = $this->get('from_uid');
		$vouch_user_id = 0;
		if($from_uid == 'vouch'){
			$vouch_user_id = $this->get('vouch','int');
			if(!$vouch_user_id){
				$this->json('未指定推荐人');
			}
		}
		if($from_uid == 'other'){
			$tmp = $this->get('username');
			if(!$tmp){
				$this->json('请填写会员账号');
			}
			$tmp_rs = $this->model('user')->chk_name($tmp);
			if(!$tmp_rs){
				$this->json('会员不存在');
			}
			$vouch_user_id = $tmp_rs['id'];
		}
		$time = $this->time;
		$dateline = $this->get('dateline');
		if($dateline){
			$time = strtotime($dateline);
		}
		if($type && $type == '-'){
			$savelogs = array('wid'=>$wid,'goal_id'=>$uid,'mid'=>0,'val'=>'-'.$val);
			$savelogs['appid'] = $this->app_id;
			$savelogs['dateline'] = $time;
			$savelogs['user_id'] = $vouch_user_id;
			$savelogs['admin_id'] = $this->session->val('admin_id');
			$savelogs['ctrlid'] = 'wealth';
			$savelogs['funcid'] = 'val';
			$savelogs['url'] = 'admin.php...';
			$savelogs['note'] = $note ? $note : P_Lang('管理员操作');
			$savelogs['status'] = 1;
			$data = array('wid'=>$wid,'uid'=>$uid,'lasttime'=>$this->time);
			$user_val = $this->model('wealth')->get_val($uid,$wid);
			if($user_val){
				if($user_val>$val){
					$data['val'] = round(($user_val-$val),2);
				}else{
					$savelogs['val'] = '-'.$user_val;
					$data['val'] = '0';
				}
			}else{
				$savelogs['val'] = '0';
				$data['val'] = '0';
			}
			$this->model('wealth')->save_log($savelogs);
			$this->model('wealth')->save_info($data);
		}else{
			//增加日志记录
			$savelogs = array('wid'=>$wid,'goal_id'=>$uid,'mid'=>0,'val'=>$val);
			$savelogs['appid'] = $this->app_id;
			$savelogs['dateline'] = $time;
			$savelogs['user_id'] = $vouch_user_id;
			$savelogs['admin_id'] = $this->session->val('admin_id');
			$savelogs['ctrlid'] = 'wealth';
			$savelogs['funcid'] = 'val';
			$savelogs['url'] = 'admin.php...';
			$savelogs['note'] = $note ? $note : P_Lang('管理员操作');
			$savelogs['status'] = 1;
			$data = array('wid'=>$wid,'uid'=>$uid,'lasttime'=>$this->time);
			$this->model('wealth')->save_log($savelogs);
			//更新统计
			$user_val = $this->model('wealth')->get_val($uid,$wid);
			if($user_val){
				$data['val'] = round(($user_val+$val),2);
			}else{
				$data['val'] = round($val,2);
			}
			$this->model('wealth')->save_info($data);
		}
		$this->json(true);
	}

	/**
	 * 配置财富信息，要求新增有增加权限（wealth:add），修改需要有修改权限（wealth:modify）
	 * @参数 id 财富ID
	**/
	public function set_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom["add"]){
				error(P_Lang('您没有权限执行此操作'),'','error');
			}
		}else{
			if(!$this->popedom["modify"]){
				error(P_Lang('您没有权限执行此操作'),'','error');
			}
			$rs = $this->model('wealth')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		$this->view('wealth_set');
	}

	/**
	 * 保存财富配置信息
	 * @参数 id 财富ID，为0或空表示添加新财富
	 * @参数 title 财富名称，如积分，威望，金币等
	 * @参数 identifer 财富标识
	 * @参数 unit 计量单位，如点，元，星等
	 * @参数 dnum 财富计量类型，整数，一位小数，及两位小数
	 * @参数 ifpay 是否支持前台充值
	 * @参数 pay_ratio 充值兑换比例
	 * @参数 ifcash 是否支持提现
	 * @参数 cash_ratio 提现兑换比例
	 * @参数 ifcheck 是否审核，请慎用。建议启用审核
	 * @参数 taxis 排序，范围0-255 值越小越往前靠
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		$array = array();
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$array['site_id'] = $_SESSION['admin_site_id'];
		}
		$array['title'] = $this->get('title');
		if(!$array['title']){
			$this->error(P_Lang('名称不能为空'));
		}
		$array['identifier'] = $this->get('identifier');
		if(!$array['identifier']){
			$this->error(P_Lang('标识不能为空'));
		}
		$array['identifier'] = $this->format($array['identifier'],'system');
		if(!$array['identifier']){
			$this->error(P_Lang('标识不符合系统要求'));
		}
		$chk = $this->model('wealth')->chk_identifier($array['identifier'],$id);
		if($chk){
			$this->error(P_Lang('标识已被使用'));
		}
		$array['unit'] = $this->get('unit');
		if(!$array['unit']){
			$this->error(P_Lang('计量单位不能为空'));
		}
		$array['dnum'] = $this->get('dnum','int');
		$array['ifpay'] = $this->get('ifpay');
		if($array['ifpay']){
			$array['pay_ratio'] = $this->get('pay_ratio','float');
			if(!$array['pay_ratio']){
				$array['pay_ratio'] = 1;
			}
		}else{
			$array['pay_ratio'] = 0;
		}
		$array['ifcash'] = $this->get('ifcash');
		if($array['ifcash']){
			$array['cash_ratio'] = $this->get('cash_ratio','float');
			if(!$array['cash_ratio']){
				$array['cash_ratio'] = 1;
			}
		}else{
			$array['cash_ratio'] = 0;
		}
		$array['ifcheck'] = $this->get('ifcheck','int');
		$array['taxis'] = $this->get('taxis','int');
		$array['min_val'] = $this->get('min_val','float');
		$this->model('wealth')->save($array,$id);
		$this->success();
	}

	/**
	 * 财富状态
	 * @参数 id 财富ID
	**/
	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('wealth')->get_one($id);
		$status = $rs['status'] ? 0 : 1;
		$this->model('wealth')->update_status($id,$status);
		$this->success($status);
	}

	/**
	 * 财富删除
	 * @参数 id 财富ID
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('wealth')->delete($id);
		$this->success();
	}

	/**
	 * 财富规则配置
	 * @参数 id 财富ID
	**/
	public function rule_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'),$this->url('wealth'),'error');
		}
		if(!$this->popedom["setting"]){
			error(P_Lang('您没有权限执行此操作'),$this->url('wealth'),'error');
		}
		$rs = $this->model('wealth')->get_one($id);
		$this->assign('rs',$rs);
		$rslist = $this->model('wealth')->rule_all("wid='".$id."'");
		$alist = $this->model('wealth')->act_list();
		$agentlist = $this->model('wealth')->goal_userlist();
		$usergroup = $this->model('usergroup')->get_all("is_guest!=1",'id');
		$projectlist = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',"module!=0");
		if($rslist){
			foreach($rslist as $key=>$value){
				$value['action_title'] = ($alist && $alist[$value['action']]) ? $alist[$value['action']] :P_Lang('未知');
				$value['goal_title'] = ($agentlist && $agentlist[$value['goal']]) ? $agentlist[$value['goal']] :P_Lang('未知');
				$value['group_title'] = ($usergroup && $usergroup[$value['group_id']]) ? $usergroup[$value['group_id']]['title'] : P_Lang('未知');
				$value['project_title'] = ($projectlist && $projectlist[$value['project_id']]) ? $projectlist[$value['project_id']]['title'] : P_Lang('未知');
				$value['goal_group_title'] = ($usergroup && $usergroup[$value['goal_group_id']]) ? $usergroup[$value['goal_group_id']]['title'] : P_Lang('未知');
				$rslist[$key] = $value;
			}
		}
		$this->assign('rslist',$rslist);
		$this->assign('alist',$alist);
		$this->assign('agentlist',$agentlist);
		$this->view('wealth_rule');
	}

	public function rule_set_f()
	{
		$alist = $this->model('wealth')->act_list();
		$agentlist = $this->model('wealth')->goal_userlist();
		$usergroup = $this->model('usergroup')->get_all("is_guest!=1");
		$projectlist = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',"module!=0");
		$this->assign('alist',$alist);
		$this->assign('agentlist',$agentlist);
		$this->assign('usergroup',$usergroup);
		$this->assign('projectlist',$projectlist);
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('wealth')->rule_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}else{
			$wid = $this->get('wid','int');
			if(!$wid){
				$this->error(P_Lang('未指定财富ID'));
			}
			$wrs = $this->model('wealth')->get_one($wid);
			$this->assign('wrs',$wrs);
			$this->assign('wid',$wid);
		}
		$this->view('wealth_rule_set');
	}

	public function delete_rule_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$this->popedom['setting']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$this->model('wealth')->delete_rule($id);
		$this->json(true);
	}

	/**
	 * 保存规则
	 * @参数 wid 财富ID，不为空时表示添加
	 * @参数 id 当前规则ID，为空时wid不能为空
	 * @返回 Json字串
	**/
	public function save_rule_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$wid = $this->get('wid','int');
		if(!$wid){
			$id = $this->get('id','int');
			if(!$id){
				$this->error(P_Lang('未指定规则ID'));
			}
		}
		$action = $this->get('action');
		if(!$action){
			$this->error(P_Lang('动作未指定'));
		}
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('值为空的规则不需要创建'));
		}
		$goal = $this->get('goal');
		if(!$goal){
			$this->error(P_Lang('未指定目标对象'));
		}
		$taxis = $this->get('taxis','int');
		$data = array('action'=>$action,'val'=>$val,'goal'=>$goal,'taxis'=>$taxis);
		$data['project_id'] = $this->get('project_id','int');
		$data['title_id'] = $this->get('title_id');
		$data['qty_type'] = $this->get('qty_type');
		$data['qty'] = $this->get('qty','int');
		$data['price_type'] = $this->get('price_type');
		$data['price'] = $this->get('price','float');
		$data['group_id'] = $this->get('group_id','int');
		$data['uids'] = $this->get('uids');
		$data['goal_group_id'] = $this->get('goal_group_id','int');
		$data['goal_uids'] = $this->get('goal_uids');
		$data['if_stop'] = $this->get('if_stop','int');
		if($wid){
			$data['wid'] = $wid;
			$this->model('wealth')->save_rule($data);
		}else{
			$this->model('wealth')->save_rule($data,$id);
		}
		$this->success();
	}

	public function notcheck_f()
	{
		$pageurl = $this->url('wealth','notcheck');
		$condition = "l.status=0";
		$total = $this->model('wealth')->log_total_notcheck($condition);
		if($total){
			$psize = $this->config['psize'];
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('wealth')->log_list_notcheck($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
			$this->assign('rslist',$rslist);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
		}
		$this->view('wealth_notcheck');
	}

	/**
	 * 财富审核
	 * @参数 id 日志ID
	 * @参数 $action 动作
	**/
	public function action_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$action = $this->get('action');
		if($action == 'ok'){
			$this->model('wealth')->setok($id);
		}else{
			$this->model('wealth')->log_delete($id);
		}
		$this->json(true);
	}

	public function action_user_f()
	{
		$id = $this->get('wid','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$uid = $this->get('uid','int');
		if(!$uid){
			$this->error(P_Lang('未指定会员ID'));
		}
		$rs = $this->model('wealth')->get_one($id);
		$this->assign('rs',$rs);
		$this->assign('uid',$uid);
		$this->view("wealth_action");
	}
}