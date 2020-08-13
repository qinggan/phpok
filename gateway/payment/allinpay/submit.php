<?php
/**
 * 通华收银宝
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年3月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class allinpay_submit
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

	/**
	 * 执行提交按钮
	**/
	public function submit()
	{
		global $app;
		$orderinfo = $app->model('order')->get_one($this->order['sn'],'sn');
		if(!$orderinfo){
			$app->error(P_Lang('订单信息不存在'));
		}
		$obj = new allinpay_payment($this->config);
		$return_url = $app->url('payment','notice','id='.$this->order['id'],'www',true);
		$notify_url = $this->baseurl."gateway/payment/allinpay/notify_url.php";
		if($this->param['currency']){
			if(is_array($this->param['currency'])){
				$currency = $this->param['currency'];
			}else{
				$currency = $app->model('currency')->get_one($this->param['currency'],'code');
			}
		}else{
			$currency = $app->model('currency')->get_one('CNY','code');
		}
		$price = price_format_val($this->order['price'],$this->order['currency_id'],$currency['id']);
		$obj->params('sn',$this->order['sn'].'-'.$this->order['id']);
		$obj->params('return_url',$return_url);
		$obj->params('notify_url',$notify_url);
		$obj->price($price);
		$obj->currency($currency['code']);
		if(strpos($this->config['utype'],'alipay') !== false){
			$obj->params('goods','订单：'.$this->order['sn']);
			$obj->params('refer_url',$app->url('payment','show','id='.$this->order['id'],'www',true));
		}
		if($this->config['utype'] == 'wxpay_branch_mp' || $this->config['utype'] == 'wxpay_app'){
			if(!$app->session->val('user_id')){
				$app->error('非会员不支持公众号或微信APP支付');
			}
			$obj->params('wx_openid','会员信息 OpenId');
			$obj->params('ip',$app->lib('common')->ip());
		}
		//支付宝PCWEB支付
		$data = $obj->unified_order();
		if(!$data){
			$app->error('订单创建失败');
		}
		if($data['returnCode'] != '0000'){
			$app->error($data['returnCode'].'：'.$data['returnMsg']);
		}
		if($data['resultCode'] != '0000' && $data['resultCode'] != 'P000' && $data['resultCode'] != '9997'){
			$app->error($data['resultCode'].'：'.$data['resultMsg']);
		}
		if($this->config['utype'] == 'alipay_pcweb' || $this->config['utype'] == 'alipay_h5'){
			$info = $app->lib('json')->decode($data['payInfo']);
			$html  = "<form method='".$info['method']."' name='allinpay_payment' id='allinpay_payment' action='".$info['action']."' accept-charset='GBK' target='_top'>\n";
			foreach($info as $key=>$value){
				if($key == 'action' || $key == 'method'){
					continue;
				}
				$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
			}
			$html .= "</form>";
			$html .= '<script type="text/javascript">'."\n";
			$html .= 'document.getElementById("allinpay_payment").submit();'."\n";
			$html .= '</script>'."\n";
			echo $html;
			exit;
		}
		//支付宝扫码支付
		if($this->config['utype'] == 'alipay_scancode'){
			$app->assign('order',$orderinfo);
			$app->assign('qrcode',$data['codeUrl']);
			$app->assign('logid',$this->order['id']);
			$app->assign('sn',$this->order['sn']);
			$tplfile = $app->tpl->dir_root.$app->tpl->dir_tpl.'allinpay/alipay_scancode.'.$app->tpl->tpl_ext;
			if(file_exists($tplfile)){
				$app->view("allinpay/alipay_scancode");
				exit;
			}
			$app->view($app->dir_gateway.'payment/allinpay/template/alipay_scancode.html','abs-file');
			exit;
		}
		if($this->config['utype'] == 'wxpay_scancode'){
			$app->assign('order',$orderinfo);
			$app->assign('qrcode',$data['codeUrl']);
			$app->assign('logid',$this->order['id']);
			$app->assign('sn',$this->order['sn']);
			$tplfile = $app->tpl->dir_root.$app->tpl->dir_tpl.'allinpay/weixin_scancode.'.$app->tpl->tpl_ext;
			if(file_exists($tplfile)){
				$app->view("allinpay/weixin_scancode");
				exit;
			}
			$app->view($app->dir_gateway.'payment/allinpay/template/weixin_scancode.html','abs-file');
			exit;
		}
	}
}