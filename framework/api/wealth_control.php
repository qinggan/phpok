<?php
/**
 * 财富操作
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2019年2月21日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class wealth_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 获取财富列表（不分页）
	**/
	public function index_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$rslist = $this->model('wealth')->get_all(1);
		if(!$rslist){
			$this->error(P_Lang('系统没有启用任何财富功能'));
		}
		$me = $this->model('user')->get_one($this->session->val('user_id'));
		if(!$me || !$me['status'] || $me['status'] == 2){
			$this->error('用户信息不存在或未审核或已锁定');
		}
		$wealth = $me['wealth'];
		foreach($rslist as $key=>$value){
			$value['val'] = $wealth[$value['identifier']]['val'];
			$rslist[$key] = $value;
		}
		$this->success($rslist);
	}

	/**
	 * 获取某个财富的日志
	**/
	public function log_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$me = $this->model('user')->get_one($this->session->val('user_id'));
		if(!$me || !$me['status'] || $me['status'] == 2){
			$this->error('用户信息不存在或未审核或已锁定');
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定财富规则'));
		}
		$rs = $this->model('wealth')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('财富信息不存在'));
		}
		$rs['val'] = $me['wealth'][$rs['identifier']]['val'];
		$data = array('id'=>$id,'rs'=>$rs);
		$data['val'] = $me['wealth'][$rs['identifier']]['val'];
		$pageid = $this->get('pageid','int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1)*$psize;
		$condition = "wid='".$id."' AND goal_id='".$this->session->val('user_id')."' AND status=1";
		$total = $this->model('wealth')->log_total($condition);
		if($total){
			$rslist = $this->model('wealth')->log_list($condition,$offset,$psize);
			if($rslist){
				foreach($rslist as $key=>$value){
					$value['dateline_format'] = date("Y-m-d H:i:s",$value['dateline']);
					$rslist[$key] = $value;
				}
			}
			$data['psize'] = $psize;
			$data['offset'] = $offset;
			$data['pageid'] = $pageid;
			$data['rslist'] = $rslist;
			$data['total'] = $total;
		}
		$this->success($data);
	}
	
	//财富充值（仅限启用了充值功能的财富才有效）
	public function recharge_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定财富ID或标识'));
		}
		$typeid = is_numeric($id) ? 'id' : 'identifier';
		$rs = $this->model('wealth')->get_one($id,$typeid);
		if(!$rs){
			$this->error(P_Lang('要支付的目标不存在，请检查'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('财富：{title} 未启用',array('title'=>$rs['title'])));
		}
		if(!$rs['ifpay']){
			$this->error(P_Lang('{title}不支持在线充值',array('title'=>$rs['title'])));
		}
		$price = $this->get('price','float');
		if(!$price){
			$this->error(P_Lang('未指定充值金额'));
		}
		if($price < 0.01){
			$this->error(P_Lang('充值金额不能少于0.01元'));
		}
		$payment = $this->get('payment','int');
		if(!$payment){
			$this->error(P_Lang('未指定支付方式'));
		}
		$sn = uniqid('CZ');
		$array = array('type'=>'recharge','price'=>$price,'currency_id'=>$this->site['currency_id'],'sn'=>$sn);
		$array['title'] = P_Lang('在线充值');
		$array['content'] = P_Lang('充值编号：{sn}',array('sn'=>$sn));
		$array['payment_id'] = $payment;
		$array['dateline'] = $this->time;
		$array['user_id'] = $this->session->val('user_id');
		$array['status'] = 0;
		$tmp = array('goal'=>$rs['id'],'phpok_val'=>$this->get('val','float'));
		$array['ext'] = serialize($tmp);
		$insert_id = $this->model('payment')->log_create($array);
		$this->success($insert_id);
	}
}
