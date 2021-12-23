<?php
/**
 * 公共接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年2月21日
**/
namespace phpok\app\coupon;

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class nodes_phpok extends \_init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 将优惠码扔到历史记录中
	**/
	public function PHPOK_coupon_to_history()
	{
		$cart_id = $this->data("cart_id");
		$order_id = $this->data("order_id");
		$price = $this->data("price");
		if($cart_id && $order_id){
			$this->model('coupon')->to_history($cart_id,$order_id,$price);
		}
		return true;
	}

	/**
	 * 获取购物车的优惠码
	**/
	public function PHPOK_cart_coupon()
	{
		$cart_id = $this->data('cart_id');
		$idlist = $this->data('cart_ids');
		$rslist = $this->model('cart')->get_all($cart_id,$idlist);
		if(!$rslist){
			return false;
		}
		$this->undata('cart_coupon');
		$coupon = $this->model('coupon')->user_coupon($cart_id);
		if(!$coupon || $coupon['types'] == 'list'){
			return false;
		}
		//计算哪些优惠的金额
		$totalprice = 0;
		foreach($rslist as $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty']);
		}
		//如果优惠码限定单个优惠
		if(!$coupon['pid'] && !$coupon['tids']){
			//优惠无效
			if($coupon['min_price'] && $coupon['min_price'] > $totalprice){
				return false;
			}
			if(!$coupon['discount_type']){
				$tmp_price = round($totalprice * $coupon['discount_val'] / 100,2);
			}else{
				$tmp_price = $coupon['discount_val'];
			}
			$tmp = array();
			$tmp['price'] = $tmp_price;
			$tmp['code'] = $coupon['code'];
			$tmp['title'] = $coupon['title'];
			$this->data('cart_coupon',$tmp);
			return true;
		}
		
		//检测产品
		$myprice = 0;
		foreach($rslist as $key=>$value){
			if(!$value['tid']){
				continue;
			}
			if($coupon['tids']){
				$tmp = explode(",",$coupon['tids']);
				if(in_array($value['tid'],$tmp)){
					$myprice += $value['price'] * $value['qty'];
					continue;
				}
			}
			if($coupon['pid']){
				$tmp = $this->model('list')->simple_one($value['tid']);
				if($tmp && $tmp['project_id'] == $coupon['pid']){
					$myprice += $value['price'] * $value['qty'];
					continue;
				}
			}
		}
		if($coupon['min_price'] && $coupon['min_price'] > $myprice){
			return false;
		}
		if(!$coupon['discount_type']){
			$price = round($myprice * $coupon['discount_val'] / 100,2);
		}else{
			$price = $coupon['discount_val'];
		}
		$tmp = array();
		$tmp['price'] = $price;
		$tmp['code'] = $coupon['code'];
		$tmp['title'] = $coupon['title'];
		$this->data('cart_coupon',$tmp);
		return $tmp;
	}

	/**
	 * 列表节点，所有通过数据调用中心获取到的文章列表
	**/
	public function PHPOK_arclist()
	{
		$rslist = $this->data('rslist');
		$pid = $this->data('pid');
		if(!$rslist || !is_array($rslist) || !$pid || !is_numeric($pid)){
			return false;
		}
		$ids = array_keys($rslist);
		$time = strtotime(date("Y-m-d",$this->time));
		$condition  = " types='list' AND stopdate>=".$time." AND FROM_UNIXTIME(time_start,'%k')<=".date("G",$this->time);
		$condition .= " AND FROM_UNIXTIME(time_stop,'%k')>=".date("G",$this->time);
		$clist = $this->model('coupon')->get_list($condition,0,999);
		if(!$clist){
			return false;
		}
		$couponlist = array();
		$_user = array();
		if($this->session->val('user_id')){
			$_user = $this->model('user')->get_one($this->session->val('user_id'));
		}
		foreach($clist as $key=>$value){
			//如果主题优惠码有针对用户及用户组，将会在这里筛选
			if($value['user_groupid'] || $value['users']){
				if(!$this->session->val('user_id')){
					continue;
				}
				$tmp_users = $value['users'] ? explode(",",$value['users']) : array();
				if($value['user_groupid'] != $_user['group_id'] && !in_array($_user['id'],$tmp_users)){
					continue;
				}
			}
			if($value['pid'] && $value['cateid']){
				$cids = array($value['cateid']);
				$this->model('cate')->get_sonlist_id($cids,$value['cateid'],true);
				$value['cateid_list'] = $cids;
			}
			if($value['tids']){
				$value['tids_list'] = explode(",",$value['tids']);
				if(array_intersect($ids,$value['tids_list'])){
					$couponlist[$value['id']] = $value;
					continue;
				}
			}
			if($value['cateid_list']){
				$couponlist[$value['id']] = $value;
				continue;
			}
			if($value['pid'] && $value['pid'] == $pid){
				$couponlist[$value['id']] = $value;
			}
		}
		if(!$couponlist || count($couponlist) < 1){
			return false;
		}
		foreach($rslist as $key=>$value){
			if(!$value['price']){
				continue;
			}
			$cateids = array();
			if($value['cate_id']){
				$cateids[] = $value['cate_id'];
			}
			if($value['catelist']){
				$cateids = array_merge($cateids,array_keys($value['catelist']));
			}
			if($value['_catelist']){
				foreach($value['_catelist'] as $kk=>$vv){
					$cateids[] = $vv['id'];
				}
			}
			if($cateids){
				$cateids = array_unique($cateids);
			}
			$tmplist = array();
			foreach($couponlist as $k=>$v){
				if($v['min_price'] && $v['min_price'] > $value['price']){
					continue;
				}
				if($v['tids'] && $v['tids_list'] && in_array($value['id'],$v['tids_list'])){
					$tmplist[] = $v;
					continue;
				}
				if($v['pid']){
					if($v['cateid_list'] && $cateids && array_intersect($v['cateid_list'],$cateids)){
						$tmplist[] = $v;
						continue;
					}
					if(!$v['cateid_list'] && $v['pid'] == $value['project_id']){
						$tmplist[] = $v;
						continue;
					}
				}
			}
			if($tmplist && count($tmplist)>0){
				$value['price_old'] = $value['price'];
				$value['price'] = $this->_format($value['price'],$tmplist[0]);
				$value['apps']['coupon'] = array('rs'=>$tmplist[0],'list'=>$tmplist);
				$rslist[$key] = $value;
			}
		}
		$this->data('rslist',$rslist);
		return true;
	}

	/**
	 * 内容节点格式化
	 */
	public function PHPOK_arc()
	{
		$arc = $this->data('arc');
		if(!$arc){
			return false;
		}
		if(!$arc['price']){
			$this->data('arc',$arc);
			return false;
		}
		$time = strtotime(date("Y-m-d",$this->time));
		$condition  = " status=1 AND types='list' ";
		$condition .= " AND stopdate>=".$time." AND FROM_UNIXTIME(time_start,'%k')<=".date("G",$this->time)." ";
		$condition .= " AND FROM_UNIXTIME(time_stop,'%k')>=".date("G",$this->time)." ";
		$clist = $this->model('coupon')->get_list($condition,0,999);
		if(!$clist){
			$this->data('arc',$arc);
			return false;
		}
		
		$pid = $arc['project_id'];
		$cateid = $arc['cate_id'];
		$cateids = array();
		if($cateid && $arc['_catelist']){
			$cateids = array($cateid);
			foreach($arc['_catelist'] as $key=>$value){
				$cateids[] = $value['id'];
			}
			$cateids = array_unique($cateids);
		}
		$tmplist = array();
		$_user = array();
		if($this->session->val('user_id')){
			$_user = $this->model('user')->get_one($this->session->val('user_id'));
		}
		foreach($clist as $key=>$value){
			if($value['user_groupid'] || $value['users']){
				if(!$this->session->val('user_id')){
					continue;
				}
				$tmp_users = $value['users'] ? explode(",",$value['users']) : array();
				if($value['user_groupid'] != $_user['group_id'] && !in_array($_user['id'],$tmp_users)){
					continue;
				}
			}
			if($value['min_price'] && $value['min_price']>$arc['price']){
				continue;
			}
			if($value['tids']){
				$tmp = explode(",",$value['tids']);
				if(in_array($arc['id'],$tmp)){
					$tmplist[] = $value;
					continue;
				}
			}
			if(!$value['pid']){
				continue;
			}
			if(!$value['tids'] && $value['pid'] != $arc['project_id']){
				continue;
			}
			if($value['pid'] && !$value['cateid'] && $value['pid'] == $arc['project_id']){
				$tmplist[] = $value;
				continue;
			}
			if($value['cateid'] && $cateids && count($cateids)>0){
				$cids = array($value['cateid']);
				$this->model('cate')->get_sonlist_id($cids,$value['cateid'],true);
				$cids = array_unique($cids);
				if(array_intersect($cateids,$cids)){
					$tmplist[] = $value;
					continue;
				}
			}
		}
		if(!$tmplist || count($tmplist)<1){
			$this->data('arc',$arc);
			return false;
		}
		foreach($tmplist as $key=>$value){
			$value['price'] = $this->_format($arc['price'],$value);
			$tmplist[$key] = $value;
		}
		$me = $this->model('appsys')->get_one('coupon');
		$arc['apps']['coupon'] = array('rs'=>$tmplist[0],'list'=>$tmplist,'me'=>$me);
		$this->data('arc',$arc);
		return true;
	}

	public function PHPOK_payment()
	{
		$order = $this->data('order');
		if(!$order || !$order['user_id']){
			return false;
		}
		$user = $this->model('user')->get_one($order['user_id']);
		$unpaid_price = $this->model('order')->unpaid_price($order['id']);
		if(!$unpaid_price || $unpaid_price < 0.01){
			return false;
		}
		$code = $this->get('coupon');
		if(!$code){
			return false;
		}
		$type = is_numeric($code) ? 'id' : 'code';
		$rs = $this->model('coupon')->get_one($code,$type);
		if(!$rs || $rs['types'] != 'user'){
			return false;
		}
		if($rs['min_price'] && $rs['min_price'] > $order['price']){
			return false;
		}
		//判断是否已领取
		$u = $this->model('coupon')->get_info($order['user_id'],$rs['id']);
		if(!$u || $u['startdate'] > $this->time || $u['stopdate']<$this->time){
			return false;
		}
		if(!$rs['discount_type']){
			$discount = round($unpaid_price * $rs['discount_val'] / 100,2);
		}else{
			$discount = $rs['discount_val'];
		}
		$data = array('coupon_id'=>$rs['id'],'order_id'=>$order['id']);
		$data['title'] = $coupon['title'];
		$data['price'] = $discount;
		$data['user_id'] = $order['user_id'];
		$data['dateline'] = $this->time;
		$data['currency_id'] = $order['currency_id'];
		$data['currency_rate'] = $order['currency_rate'];
		$data['code'] = $rs['code'];
		$this->model('coupon')->save_history($data);
		$this->model('coupon')->user_delete($u['id']);
		//执行金额
		$myprice = price_format($discount,$order['currency_id'],$order['currency_id'],$order['currency_rate'],$order['currency_rate']);
		$tmparray = array('price'=>$myprice,'code'=>$rs['code']);
		$note = P_Lang('使用优惠码【{code}】抵扣{price}',$tmparray);
		$log = array('order_id'=>$order['id'],'addtime'=>$this->time,'who'=>$user['user'],'note'=>$note);
		$this->model('order')->log_save($log);
		$this->model('order')->integral_discount($order['id'],$discount);
		$this->undata('order');
		return true;
	}
	
	private function _format($price,$rule)
	{
		if(!$price){
			return false;
		}
		if(!is_array($rule)){
			return $price;
		}
		$price = floatval($price);
		if($rule['min_price'] && $price < $rule['min_price']){
			return $price;
		}
		if($rule['discount_type']){
			return $price - floatval($rule['discount_val']);
		}
		return $price - ($price * $rule['discount_val'] / 100);
	}

}
