<?php
/**
 * 财富操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
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
	
	//财富充值（仅限启用了充值功能的财富才有效）
	public function recharge_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('未会员不能执行充值操作'));
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
