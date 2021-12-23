<?php
/**
 * 网站前台_适用于整个PHPOK5平台的优惠系统
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
class www_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	//获取全部有效的优惠码列表
	public function index_f()
	{
		//活动结束时间
		$condition  = " types='user' AND stopdate>".$this->time." AND status=1 AND times!='0' ";
		if($this->session->val('user_id')){
			$tmp  = " (user_groupid=0 AND users='') ";
			$tmp .= " || user_groupid='".$this->session->val('user_gid')."' ";
			$tmp .= " || users LIKE '%".$this->session->val('user_id')."%' ";
			$condition .= " AND (".$tmp.") ";
		}else{
			$condition .= " AND user_groupid=0 AND users='' ";
		}
		$rslist = $this->model('coupon')->get_list($condition,0,9999);
		if(!$rslist){
			$this->error(P_Lang('暂无优惠券'));
		}
		$this->assign('rslist',$rslist);
		$this->display('www_index');
	}

	public function mylist_f()
	{
		if(!$this->session->val('user_id')){
			$this->error('非用户不能执行此操作');
		}
		$rslist = $this->model('coupon')->ulist($this->session->val('user_id'));
		if(!$rslist){
			$this->error('暂无优惠券');
		}
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
			$value['tip'] = $tip;
			$value['is_expire'] = $is_expire;
			$value['start_date'] = date("Y-m-d",$value['startdate']);
			$value['stop_date'] = date("Y-m-d",$value['stopdate']);
			$value['price_show'] = price_format($value['price'],$currency_id,$currency_id,$currency_rate,$currency_rate);
			$value['price_val'] = price_format_val($value['price'],$currency_id,$currency_id,$currency_rate,$currency_rate);
			$rslist[$key] = $value;
		}
		$this->assign('rslist',$rslist);
		$this->display('www_mycoupon');
	}

	/**
	 * 优惠码加入购物车
	 * @参数 code 优惠码
	**/
	public function get_f()
	{
		$this->model('coupon')->site_id($this->site['id']);
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('没有指定优惠码'));
		}
		$rs = $this->model('coupon')->get_one($code,'code');
		if(!$rs){
			$this->error(P_Lang('优惠码信息不存在'));
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
		if($rs['users'] || $rs['user_groupid']){
			$is_ok = false;
			if($this->session->val('user_gid') && $rs['user_groupid'] && $rs['user_groupid'] == $this->session->val('user_gid')){
				$is_ok = true;
			}
			if(!$is_ok && $rs['users']){
				$tmp = explode(",",$rs['users']);
				if($this->session->val('user_id') && in_array($this->session->val('user_id'),$tmp)){
					$is_ok = true;
				}
			}
			if($is_ok){
				$this->model('coupon')->to_user($rs['id'],$this->session->val('user_id'));
			}
		}
		$cart_id = $this->model('cart')->cart_id($this->session->sessid(),$this->session->val('user_id'));
		$this->model('coupon')->cart_id($cart_id,$rs['id']);
		$this->success(P_Lang('优惠码已放入购物车中，请及时使用'),$this->url('index'));
	}
}
