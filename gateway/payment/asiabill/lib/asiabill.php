<?php
/**
 * AsiaBill支付类
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年5月17日
**/
class asiabill_payment
{
	private $config = array();
	private $params = array();
	public function __construct($config)
	{
		if($config){
			$this->config = $config;
		}
	}

	public function config($var,$val='')
	{
		if($var && is_array($var)){
			$config = array_merge($this->config,$var);
			$this->config = $config;
		}
		if($val != '' && $var){
			$this->config[$var] = $val;
		}
		if($val == '' && is_string($var)){
			if(isset($this->config[$var])){
				return $this->config[$var];
			}
			return false;
		}
		return $this->config;
	}

	public function params($var,$val='')
	{
		if($var && is_array($var)){
			$config = array_merge($this->config,$var);
			$this->params = $config;
		}
		if($val != '' && $var){
			$this->params[$var] = $val;
		}
		if($val == '' && is_string($var)){
			if(isset($this->params[$var])){
				return $this->params[$var];
			}
			return false;
		}
		return $this->params;
	}

	public function create_html()
	{
		$sign = $this->sha256sign();
		$target = $this->config['utype']== 'iframe' ? 'ifrm_asiabill_checkout' : '_top';
		$html  = "<form method='post' name='asiabill_payment' id='asiabill_payment' action='".$this->config['action_url']."' target='".$target."'>";
		$html .= '<input type="hidden" name="merNo" value="'.$this->config['mer_no'].'" />';
		$html .= '<input type="hidden" name="gatewayNo" value="'.$this->config['gateway_no'].'" />';
		$html .= '<input type="hidden" name="orderNo" value="'.$this->params['sn'].'" />';
		$html .= '<input type="hidden" name="orderCurrency" value="'.$this->params['currency'].'" />';
		$html .= '<input type="hidden" name="orderAmount" value="'.round($this->params['price'],2).'" />';
		$html .= '<input type="hidden" name="signInfo" value="'.$sign.'" />';
		$html .= '<input type="hidden" name="returnUrl" value="'.$this->params['return_url'].'" />';
		if($this->params['notify_url']){
			$html .= '<input type="hidden" name="callbackUrl" value="'.$this->params['notify_url'].'" />';
		}
		$html .= '<input type="hidden" name="firstName" value="'.$this->params['firstname'].'" />';
		$html .= '<input type="hidden" name="lastName" value="'.$this->params['lastname'].'" />';
		$html .= '<input type="hidden" name="email" value="'.$this->params['email'].'" />';
		$html .= '<input type="hidden" name="phone" value="'.$this->params['mobile'].'" />';
		$html .= '<input type="hidden" name="paymentMethod" value="Credit Card" />';
		$html .= '<input type="hidden" name="country" value="'.$this->params['country'].'" />';
		if($this->params['state']){
			$html .= '<input type="hidden" name="state" value="'.$this->params['state'].'" />';
		}
		$html .= '<input type="hidden" name="city" value="'.$this->params['city'].'" />';
		$html .= '<input type="hidden" name="address" value="'.$this->params['address'].'" />';
		$html .= '<input type="hidden" name="zip" value="'.$this->params['zipcode'].'" />';
		if($this->params['is_mobile']){
			$html .= '<input type="hidden" name="isMobile" value="1" />';
			$height = "484px";
		}else{
			//$html .= '<input type="hidden" name="style" value="003" />';
			$height = "410px";
		}
		//$html .= '<input type="submit" value="提交" />';
		
		$html .= "</form>";
		if($this->config['utype'] == 'iframe'){
			$html .= '<iframe width="100%" height="'.$height.'" scrolling="no" name="ifrm_asiabill_checkout" id="ifrm_asiabill_checkout" style="border:none; margin: 0; overflow:hidden;"></iframe>';
			//$html .= '<script type="text/javascript">document.checkout_creditcard.target = "ifrm_creditcard_checkout";document.checkout_creditcard.submit();</script>';
		}
		return $html;
	}

	public function sha256sign()
	{
		$url = $this->params['return_url'];
		$url = str_replace("&","&amp;",$url);
		$string = $this->config['mer_no'].$this->config['gateway_no'];
		$string.= $this->params['sn'].$this->params['currency'].round($this->params['price'],2).$url;
		$string.= $this->config['sign_key'];
		$info = hash("sha256",$string);
		return $info;
	}

	public function sha256check($sign='')
	{
		$string = $this->config['mer_no'].$this->config['gateway_no'].$this->params['trade_no'];
		$string.= $this->params['sn'].$this->params['currency'].$this->params['price'].$this->params['status'].$this->params['info'];
		$string.= $this->config['sign_key'];
		$chk = strtolower(hash('sha256',$string));
		if($chk == strtolower($sign)){
			return true;
		}
		return false;
	}
}