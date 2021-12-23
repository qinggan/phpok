<?php
/**
 * 接口应用_适用于整个PHPOK5平台的优惠系统
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年01月02日 15时35分
**/
namespace phpok\app\control\coupon;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class api_control extends \phpok_control
{
	private $cart_id;
	public function __construct()
	{
		parent::control();
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$this->session->val('user_id'));
	}

	/**
	 * 基于优惠编码使用
	**/
	public function use_f()
	{
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('优惠码不能为空'));
		}
		$this->model('coupon')->site_id($this->site['id']);
		$rs = $this->model('coupon')->get_one($code,'code');
		if(!$rs){
			$this->error(P_Lang('优惠码不存在'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('优惠码未启用'));
		}
		if($rs['stopdate'] <= $this->time){
			$this->error(P_Lang('优惠码已过期'));
		}
		if($rs['startdate'] > $this->time){
			$this->error(P_Lang('优惠码还未生效'));
		}
		if($rs['types'] != 'user'){
			$this->error(P_Lang('此优惠码仅限系统内部使用'));
		}
		if($rs['times'] && $rs['times']>0){
			//检测优惠码使用次数
			$used_count = $this->model('coupon')->history_count($rs['id']);
			if($rs['times'] <= $used_count){
				$this->error(P_Lang('优惠码已超出使用次数'));
			}
		}
		//检查限制
		if($rs['users'] || $rs['user_groupid']){
			if(!$this->session->val('user_id')){
				$this->error(P_Lang('当前优惠码仅限用户使用'));
			}
			$is_ok = false;
			if($rs['user_groupid'] && $rs['user_groupid'] == $this->session->val('user_gid')){
				$is_ok = true;
			}
			if(!$is_ok && $rs['users']){
				$tmp = explode(",",$rs['users']);
				if(in_array($this->session->val('user_id'),$tmp)){
					$is_ok = true;
				}
			}
			if(!$is_ok){
				$this->error(P_Lang('您没有权限使用当前优惠码'));
			}
			$tmp = $this->model('coupon')->to_user($rs['id'],$this->session->val('user_id'));
			if(!$tmp){
				$this->error(P_Lang('优惠码领取失败，请检查'));
			}
		}
		$this->model('coupon')->cart_id($this->cart_id,$rs['id']);
		$this->success();
	}

	public function index_f()
	{
		$offset = $this->get('offset','int');
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		}
		$first = $this->get('first','int');
		$is_vouch = $this->get('vouch','int');
		$type = $this->get('type');
		if(!$type){
			$type = 'list';
		}
		if(!in_array($type,array('list','user'))){
			$this->error(P_Lang('参数不正确'));
		}
		$condition  = " types='".$type."' AND status=1 ";
		$condition .= " AND stopdate>=".$this->time." ";
		if($type == 'list'){
			$condition .= " AND FROM_UNIXTIME(time_start,'%k')<=".date("G",$this->time);
			$condition .= " AND FROM_UNIXTIME(time_stop,'%k')>".date("G",$this->time)." ";
		}
		if($first || $is_vouch){
			$condition .= " AND is_vouch=1 ";
		}
		$clist = $this->model('coupon')->get_list($condition,$offset,$psize);
		if(!$clist){
			$this->error(P_Lang('没有优惠码活动'));
		}
		foreach($clist as $key=>$value){
			//非用户模式设置需要限定项目及主题
			if($type != 'user'){
				if(!$value['tids'] && !$value['pid']){
					unset($clist[$key]);
					continue;
				}
				$tlist = $alist = false;
				if($value['tids']){
					$tlist = $this->model('list')->simple_all($value['tids'],true);
				}
				if($value['pid'] && $value['types'] != 'user'){
					$alist = $this->model('list')->all_list("l.project_id='".$value['pid']."' AND l.status=1",0,1);
				}
				if(!$tlist && !$alist){
					unset($clist[$key]);
					continue;
				}
			}
			$clist[$key]['is_used'] = false;
			if($this->session->val('user_id')){
				$tmp = $this->model('coupon')->get_info($this->session->val('user_id'),$value['id']);
				if($tmp){
					$clist[$key]['is_used'] = true;
				}
			}
		}
		if($first){
			reset($clist);
			$clist = current($clist);
		}
		$this->success($clist);
	}

	public function info_f()
	{
		if(!$this->session->val('user_id')){
			$this->error('非用户不能执行此操作');
		}
		$id = $this->get('id','int');
		$code = $this->get('code');
		if(!$id && !$code){
			$this->error('参数不完整');
		}
		$this->model('coupon')->site_id($this->site['id']);
		if($id){
			$rs = $this->model('coupon')->user_one($id,'id');
		}else{
			$rs = $this->model('coupon')->user_one($code,'code');
		}
		if(!$rs){
			$this->error(P_Lang('优惠码不存在'));
		}
		$this->success($rs);
	}
	
	//用户优惠券（包含已过期的优惠券）
	public function mylist_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能查看自己的优惠券'));
		}
		$rslist = $this->model('coupon')->ulist($this->session->val('user_id'));
		if(!$rslist){
			$this->error('暂无优惠券');
		}
		$price = $this->get('price','float'); //高于产品价格的优惠券不显示
		$active = $this->get('active','int'); //仅读取有效的优惠券（已过期和未开始的不显示）
		$currency_id = $this->get('currency_id');
		if(!$currency_id){
			$currency_id = $this->site['currency_id'];
		}
		$currency_rate = $this->get('currency_rate');
		if(!$currency_rate){
			$currency = $this->model('currency')->get_one($currency_id);
			$currency_rate = $currency['val'];
		}
		$quick_expire_time = $this->time - 3*24*3600;
		$tlist = array();
		foreach($rslist as $key=>$value){
			$tip = '';
			$is_expire = false;
			if($value['stopdate'] <= $quick_expire_time){
				$tip = P_Lang('即将到期');
			}
			if($value['stopdate'] <= $this->time){
				$tip = P_Lang('已过期');
				$is_expire = true;
			}
			//已过期的不显示
			if($active && $value['stopdate'] <= $this->time){
				continue;
			}
			//未开始的不显示
			if($active && $value['startdate'] > $this->time){
				continue;
			}
			//价格超过总价的不显示
			if($price && $value['discount_type'] && $value['discount_val']>=$price){
				continue;
			}
			$value['tip'] = $tip;
			$value['is_expire'] = $is_expire;
			$value['start_date'] = date("Y-m-d",$value['startdate']);
			$value['stop_date'] = date("Y-m-d",$value['stopdate']);
			$value['price_show'] = price_format($value['price'],$currency_id,$currency_id,$currency_rate,$currency_rate);
			$value['price_val'] = price_format_val($value['price'],$currency_id,$currency_id,$currency_rate,$currency_rate);
			$rslist[$key] = $value;
		}
		$this->success($rslist);
	}
	
	public function cart_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定优惠券'));
		}
		$rs = $this->model('coupon')->user_one($id);
		if(!$rs){
			$this->error(P_Lang('优惠券信息不存在'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('优惠券信息还未审核通过'));
		}
		if($rs['startdate']>=$this->time){
			$this->error(P_Lang('优惠券信息还未生效'));
		}
		if($rs['stopdate']<=$this->time){
			$this->error(P_Lang('优惠券信息已过期'));
		}
		$this->model('coupon')->cart_id($this->cart_id,$rs['id']);
		$this->success();
	}

	/**
	 * 删除优惠券
	**/
	public function cart_delete_f()
	{
		$this->model('coupon')->cart_id($this->cart_id,0);
		$this->success();
	}

	/**
	 * 检测订单中是否使用了优惠码，仅限用户
	 * @参数 order_id 订单ID
	 * @参数 
	**/
	public function check_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不执行此检测'));
		}
		$order_id = $this->get('order_id','int');
		$condition = "user_id='".$this->session->val('user_id')."' AND order_id='".$order_id."' AND price>0";
		$chk = $this->model('coupon')->get_list($condition,0,10);
		if($chk){
			$info = current($chk);
			$info['price_show'] = price_format($info['price'],$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
			$info['price_val'] = price_format_val($info['price'],$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
			$this->tip($info);
		}
		$this->success();
	}

	/**
	 * 领取优惠码
	**/
	public function receive_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('仅限用户有领取优惠码功能'));
		}
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('未指定优惠码'));
		}
		$this->model('coupon')->site_id($this->site['id']);
		$rs = $this->model('coupon')->get_one($code,'code');
		if(!$rs){
			$this->error(P_Lang('优惠码不存在'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('优惠码未启用'));
		}
		if($rs['stopdate'] <= $this->time){
			$this->error(P_Lang('优惠码已过期'));
		}
		if($rs['types'] != 'user'){
			$this->error(P_Lang('此优惠码仅限系统内部使用'));
		}
		if($rs['times'] && $rs['times']>0){
			//检测优惠码使用次数
			$used_count = $this->model('coupon')->history_count($rs['id']);
			if($rs['times'] <= $used_count){
				$this->error(P_Lang('优惠码已超出使用次数'));
			}
		}
		$is_ok = true;
		if($rs['user_groupid'] || $rs['users']){
			$tmp = $rs['users'] ? explode(",",$rs['users']) : array();
			if($rs['user_groupid'] != $this->session->val('user_gid') && !in_array($this->session->val("user_id"),$tmp)){
				$is_ok = false;
			}
		}
		if(!$is_ok){
			$this->error(P_Lang('您没有权限领取当前优惠码'));
		}
		//检测优惠码是否已存在
		$chk = $this->model('coupon')->get_info($this->session->val('user_id'),$rs['id']);
		if($chk){
			$this->error('您已领过优惠码了，不能重复领取');
		}
		$tmp = $this->model('coupon')->to_user($rs['id'],$this->session->val('user_id'));
		if(!$tmp){
			$this->error(P_Lang('优惠码领取失败，请检查'));
		}
		$this->success();
	}
}
