<?php
/**
 * 提交支付
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年2月25日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class citconpay_submit
{
	public $param;
	public $order;
	public $baseurl;
	public $paydir;
	
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/citconpay/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."citcon.php");
	}

	/**
	 * 执行提交按钮
	**/
	public function submit()
	{
		global $app;
		$notify_url = $this->baseurl."gateway/payment/citconpay/notify_url.php";
        $return_url = $GLOBALS['app']->url('payment','notice','id='.$this->order['id'],'www',true);
        $cancel_url = $GLOBALS['app']->url('payment','show','id='.$this->order['id'],'www',true);
		$citconpay = new citcon_payment($this->param['param']["token_id"]);
		$currency = $GLOBALS['app']->model('currency')->get_one($this->param['currency_id']);
        $price = price_format_val($this->order['price'],$this->order['currency_id'],$this->param['currency_id']);
        $citconpay->price($price);
        $citconpay->currency($currency['code']);
        $citconpay->sn($this->order['sn'].'-'.$this->order['id']);
        $citconpay->server_url($this->param['param']['server_url']);
        $citconpay->callback_url($return_url);
        $citconpay->notify_url($notify_url);
        $citconpay->cancel_url($cancel_url);
        $citconpay->success_url($return_url);
        $citconpay->fail_url($cancel_url);
        $citconpay->platform($this->param['param']['platform']);
        if($this->param['param']['platform'] == 'generic'){
	        $link = $citconpay->qr_link();
	        $app->assign('qrlink',$link);
	        $file = $app->dir_root.$app->tpl->tpl_dir."payment/citconpay/qrcode.html";
	        $app->assign('order',$this->order);
	        $rs = $app->model('order')->get_one($this->order['sn'],'sn');
	        $app->assign('rs',$rs);
	        if(file_exists($file)){
				$app->view($file,"abs-file");
	        }else{
		        $app->view($this->paydir."template/qrcode.html","abs-file");
	        }
        }
        //信用卡支付平台
        /*if($this->param['param']['platform'] == 'cc'){
	        $cc_data = $app->get('cc_data');
	        $app->assign('id',$this->order['id']);
	        if(!$cc_data){
		        $file = $app->dir_root.$app->tpl->tpl_dir."payment/citconpay/cc.html";
		        if(file_exists($file)){
			        $app->view($file,"abs-file");
		        }
				$app->view($this->paydir."template/cc.html","abs-file");
	        }
	        $app->config('is_ajax',true);
	        $citconpay->cc($cc_data);
			//如果订单付款成功
			$rs = $citconpay->cc_submit();
			phpok_log($rs);
			if(!$rs || !is_array($rs)){
				$app->error('付款失败，请联系管理员');
			}
			$state = strtolower($rs['status']);
			if($state != 'success'){
				$app->error($rs['result_message']);
			}
			//更新订单信息
			if($this->order['type'] == 'order'){
				$order = $app->model('order')->get_one_from_sn($this->order['sn']);
				if($order){
					$payinfo = $app->model('order')->order_payment_notend($order['id']);
					if($payinfo){
						$payment_data = array('dateline'=>$app->time);
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
        }*/
        if($this->param['param']['platform'] == 'cc'){
	        $link = $citconpay->submit_chop();
	        if(!$link && $citconpay->error_status()){
		        $app->error($citconpay->error_info());
	        }
	        if(!$link){
		        $app->error('获取连接失败');
		        exit;
	        }
	        $app->_location($link);
	        exit;
        }
	    $link = $citconpay->submit_link();
		$app->_location($link);
		exit;
	}
}