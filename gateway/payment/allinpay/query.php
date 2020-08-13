<?php
/**
 * 订单查询
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年3月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class allinpay_query
{
	public $param;
	public $order;
	public $baseurl;
	public $paydir;
	private $config;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/allinpay/';
		$this->baseurl = $GLOBALS['app']->url;
		if($this->param['param']){
			$this->config = array('mch_no'=>$this->param['param']["mch_no"]);
			$this->config['access_code'] = $this->param['param']["access_code"];
			$this->config['private_key'] = $this->param['param']["private_key"];
			$this->config['public_key'] = $this->param['param']['public_key'];
			$this->config['utype'] = $this->param['param']['utype'];
			$this->config['wx_appid'] = $this->param['param']['wx_appid'];
			$this->config['institution'] = $this->param['param']['institution'];
			$this->config['env'] = $this->param['param']['env'];
		}
		include_once($this->paydir."allinpay.php");
	}

	public function submit()
	{
		global $app;
		if($this->order['status']){
			$app->success();
		}
		$obj = new allinpay_payment($this->config);
		$obj->params('sn',$this->order['sn'].'-'.$this->order['id']);
		
		$data = $obj->query();
		if(!$data){
			$app->error('查询失败');
		}
		if($data['returnCode'] != '0000'){
			$app->error($data['returnMsg']);
		}
		if($data['resultCode'] == '0000'){
			$ext = $this->order['ext'] ? unserialize($this->order['ext']) : array();
			$ext = array_merge($ext,$data);
			$array = array('status'=>1,'ext'=>serialize($ext));
			$app->model('payment')->log_update($array,$this->order['id']);
			if($this->order['type'] == 'order'){
				$order = $app->model('order')->get_one_from_sn($this->order['sn']);
				if($order){
					$payinfo = $app->model('order')->order_payment_notend($order['id']);
					if($payinfo){
						$payment_data = array('dateline'=>$app->time,'ext'=>serialize($ext));
						$app->model('order')->save_payment($payment_data,$payinfo['id']);
						//更新订单日志
						$app->model('order')->update_order_status($order['id'],'paid');
						$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
						$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
						$app->model('order')->log_save($log);
					}
				}
			}
			if($this->order['type'] == 'recharge'){
				$app->model('wealth')->recharge($this->order['id']);
			}
			$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
			$app->success();
		}
		if($data['resultCode'] != '0000' && $data['resultCode'] != 'P000' && $data['resultCode'] != '9997'){
			$app->error($data['resultCode'].'：'.$data['resultMsg']);
		}
		$app->tip($data['resultCode'].'：'.$data['resultMsg']);
	}
}