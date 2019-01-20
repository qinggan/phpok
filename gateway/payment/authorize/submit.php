<?php
/**
 * Authorize.Net 信用卡支付接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月21日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}


class authorize_submit
{
	//支付接口初始化
	public $param;
	public $order;
	public $paydir;
	public $baseurl;
	private $app;
	public function __construct($order,$param)
	{
		global $app;
		$this->app = $app;
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $this->app->dir_root.'gateway/payment/authorize/';
		$this->baseurl = $this->app->url;
		include_once($this->paydir.'authorize.class.php');
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
        $cc_data = $this->app->get('cc_data');
        if(!$cc_data || !is_array($cc_data)){
	        $this->app->tpl->assign('id',$this->order['id']);
	        $this->app->tpl->display("payment/authorize/submit");
	        exit;
        }
        
		$env = $this->param['param']['env'] == 'test' ? true : false;
		$pay = new authorize_lib();
		$pay->config($this->param['param']['name'],$this->param['param']['transactionKey']);
		$pay->sandbox($env);
		$pay->ref_id($this->order['id']);
		$currency_id = $this->param['currency'] ? $this->param['currency']['id'] : $this->order['currency_id'];
		$total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency_id);
		$pay->amount($total_fee);
		

        //付款信用卡信息
        $data = array();
        if($cc_data['number']){
	        $data['cardNumber'] = $cc_data['number'];
        }
        if($cc_data['expDateYear'] && $cc_data['expDateMonth']){
	        $data['expirationDate'] = $cc_data['expDateYear'].'-'.$cc_data['expDateMonth'];
        }
        if($cc_data['cvv2']){
	        $data['cardCode'] = $cc_data['cvv2'];
        }
        $pay->post('payment',array('creditCard'=>$data));
        //订单信息
        $data = array('invoiceNumber'=>$this->order['sn']);
        if($this->order['content']){
	        $data['description'] = $this->order['content'];
        }
		$pay->post('order',$data);
		$email = '';
		if($this->order['type'] == 'order'){
			$orderinfo = $this->app->tpl->val('orderinfo');
		}
		$this->app->lib('curl')->is_post(true);
        $this->app->lib('curl')->post_data($pay->to_json());
        $data = $this->app->lib('curl')->get_json($pay->url());
        if(!$data){
	        $this->app->error(P_Lang('数据获取失败'));
        }
        if(!$data['messages'] || !$data['messages']['resultCode']){
	        $this->app->error(P_Lang('数据异常'));
        }
        if($data['messages']['resultCode'] != 'Ok'){
	        $info = $data['messages']['message'][0];
	        $this->app->error($info['text']);
        }
        //更新订单信息
		if($this->order['type'] == 'order' && $orderinfo){
			$payinfo = $this->app->model('order')->order_payment_notend($orderinfo['id']);
			if($payinfo){
				$payment_data = array('dateline'=>$this->app->time);
				$this->app->model('order')->save_payment($payment_data,$payinfo['id']);
				//更新订单日志
				$this->app->model('order')->update_order_status($order['id'],'paid');
				$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$orderinfo['sn']));
				$log = array('order_id'=>$orderinfo['id'],'addtime'=>$this->app->time,'who'=>$this->app->user['user'],'note'=>$note);
				$this->app->model('order')->log_save($log);
			}
		}
		//充值操作
		if($this->order['type'] == 'recharge'){
			$this->app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
        $this->app->success();
	}
}