<?php
/**
 * 京东支付在线提交
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年5月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class jdpay_submit
{
	//支付接口初始化
	public $param;
	public $order;
	public $paydir;
	public $baseurl;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/jdpay/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."jdpay.class.php");
	}

	public function param($param)
	{
		$this->param = $param;
	}

	public function order($order)
	{
		$this->order = $order;
	}

	//创建订单
	function submit()
	{
		global $app;
		$config = array();
		if($this->param && $this->param['param']){
			if(is_string($this->param['param'])){
				$config = unserialize($this->param['param']);
			}else{
				$config = $this->param['param'];
			}
		}
		$jdpay = new jdpay_lib($config);
        $notify_url = $app->url('payment','notify','sn='.$this->order['sn'].'-'.$this->order['id'],'www',true);
        $return_url = $app->url('payment','notice','id='.$this->order['id'],'www',true);
        $show_url = $app->url('payment','show','id='.$this->order['id'],'www',true);
        $currency_id = $this->param['currency'] ? $this->param['currency']['id'] : $this->order['currency_id'];
        $total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency_id);
		$params = array(
			"sn" => $this->order['sn'],
			"notify_url"	=> $notify_url,
			"return_url"	=> $return_url
		);
		$params['title'] = $this->order['title'];
		if($this->order['type'] == 'order'){
			$orderinfo = $app->model('order')->get_one($this->order['sn'],'sn');
			$is_virtual = true;
			$productlist = $app->model('order')->product_list($orderinfo['id']);
			$qty = 0;
			if($productlist){
				$tmplist = array();
				foreach($productlist as $key=>$value){
					if(!$value['is_virtual']){
						$is_virtual = false;
					}
					$qty += $value['qty'];
					$tmp = array();
					$tmp['id'] = $value['tid'] ? $value['tid'] : $value['id'];
					$tmp['name'] = phpok_cut($value['title'],30,'…');
					$tmp['price'] = intval($value['price']*100);
					$tmp['num'] = $value['qty'];
					$tmp['type'] = $value['is_virtual'] ? 'GT02' : 'GT01';
					$tmplist[] = $tmp;
				}
				$params['goodsInfo'] = $app->lib('json')->encode($tmplist);
			}else{
				$qty = 1;
			}
			$params['is_virtual'] = $is_virtual;
			if($is_virtual){
				$params['industryCategoryCode'] = 1;
			}else{
				$address = $app->model('order')->address($orderinfo['id'],'shipping');
				if($address){
					$tmp = array();
					$tmp['name'] = $address['fullname'];
					$tmpaddress = $address['province'];
					if($address['city'] != $address['province']){
						$tmpaddress .= $address['city'];
					}
					if($address['county'] && $address['county'] != $address['city']){
						$tmpaddress .= $address['county'];
					}
					$tmp['address'] = $tmpaddress.$address['address'].$address['address2'];
					$tmp['mobile'] = $address['mobile'] ? $address['mobile'] : $orderinfo['mobile'];
					if($address['email']){
						$tmp['email'] = $address['email'];
					}
					$tmp['province'] = $address['province'];
					$tmp['city'] = $address['city'];
					$tmp['country'] = $address['country'];
					$params['receiverInfo'] = $app->lib('json')->encode($tmp);
				}
			}
			if($orderinfo['note']){
				$params['note'] = $orderinfo['note'];
			}
			$params['orderGoodsNum'] = $qty;
		}else{
			$params['is_virtual'] = true;
			$params['industryCategoryCode'] = 1;
			$params['orderGoodsNum'] = 1;
		}
		$params['addtime'] = $this->order['dateline'];
		$params['price'] = $this->order['price'];
		$params['user_token'] = $this->order['user_id'] ? $this->order['user_id'] : $app->session->sessid();
		//风控信息
		$tmp = array();
		$tmp['enterpriseId'] = $config['enterprise_id'];
		$tmp['brand'] = $config['brand'];
		if($tmp['enterpriseId'] || $tmp['brand']){
			$params['riskInfo'] = $app->lib('json')->encode($tmp);
		}
		$is_pc = $app->is_mobile() ? false : true;
		$html = $jdpay->submit($params,$is_pc);
		$html .= "\n";
		$html .= '<script type="text/javascript">'."\n";
		$html .= 'document.getElementById("jdpay_submit").submit();'."\n";
		$html .= '</script>'."\n";
		echo $html;
		exit;
	}
}