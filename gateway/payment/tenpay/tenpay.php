<?php
/***********************************************************
	Filename: plugins/tenpay/tenpay.php
	Note	: 腾讯通即时到账 / 担保交易引挈
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年2月8日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tenpay_lib
{
	//网关地址
	public $gate_url = "https://www.tenpay.com/cgi-bin/v1.0/service_gate.cgi";
	//密钥
	public $key = '';
	//参数
	public $params='';
	//商户号
	public $biz = '';
	//财付通账号
	public $email = '';

	//Cert证书，双向https时需要使用
	public $cert_file = '';
	public $cert_pass = '';
	public $cert_type = 'PEM';


	//设置CA
	public $ca_file = '';

	//返回结果信息
	public $rs_info = '';

	//返回的错误信息
	public $rs_error = '';

	//初始化信息
	public function __construct()
	{
		$this->params = array();
	}

	//设置网关地址
	public function set_url($url='')
	{
		if($url) $this->gate_url = $url;
		return true;
	}

	//取得网关地址
	public function get_url()
	{
		return $this->gate_url;
	}

	//取得密钥
	public function get_key()
	{
		return $this->key;
	}

	//设置密钥，商户号及财付通账号
	public function set($array='')
	{
		if($array && is_array($array))
		{
			foreach($array AS $key=>$value)
			{
				$this->$key = $value;
			}
		}
	}

	//设置商户号
	public function set_biz($biz='')
	{
		if($biz) $this->biz = $biz;
		return true;
	}

	//设置密钥
	public function set_key($key='')
	{
		if($key) $this->key = $key;
		return true;
	}

	//设置财付通账号
	public function set_email($email='')
	{
		if($email) $this->email = $email;
		return true;
	}

	//设置参数
	public function param($id,$value='')
	{
		if($id && $value !='') $this->params[$id] = $value;
		if($id)
		{
			return $this->params[$id];
		}
		return true;
	}

	public function param_clear()
	{
		$this->params = array();
		return true;
	}

	//创建签名
	//创建md5摘要,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
	private function create_sign() 
	{
		$info = '';
		ksort($this->params);
		foreach($this->params as $key=>$value)
		{
			$value = trim($value);
			if($value != '' && $key != 'sign')
			{
				$info .= $key.'='.$value.'&';
			}
		}
		$info .= "key=" . $this->get_key();
		$sign = strtolower(md5($info));
		$this->param("sign",$sign);
	}

	//生成请求网址
	public function url()
	{
		//创建一个签名
		$this->create_sign();
		ksort($this->params);
		$urlext = '';
		foreach($this->params as $key=>$value)
		{
			$urlext .= $key."=".rawurlencode($value)."&";
		}
		if($urlext) $urlext = substr($urlext,0,-1);
		return $this->get_url().'?'.$urlext;
	}

	//验证签名
	//nocheck，数组，表示这些参数不加入参数验证
	public function check_sign($nocheck='')
	{
		if(!$nocheck || !is_array($nocheck)) $nocheck = array('sign');
		/* GET */
		foreach($_GET as $k => $v)
		{
			if(!in_array($k,$nocheck)) $this->param($k, $v);
		}
		/* POST */
		foreach($_POST as $k => $v)
		{
			if(!in_array($k,$nocheck))
			{
				$this->param($k, $v);
			}
		}
		ksort($this->params);
		$info = '';
		foreach($this->params as $k => $v)
		{
			if("sign" != $k && "" != $v)
			{
				$info .= $k . "=" . $v . "&";
			}
		}
		$info .= "key=" . $this->get_key();
		$sign = strtolower(md5($info));
		$chksign = isset($_GET['sign']) ? $_GET['sign'] : $_POST['sign'];
		if($chksign) $chksign = strtolower($chksign);
		if($sign == $chksign && $chksign)
		{
			return true;
		}
		return false;
	}

	//设置cert证书
	function cert($cert_file='',$cert_pass='',$cert_type='')
	{
		$this->cert_file = $cert_file;
		$this->cert_pass = $cert_pass;
		$this->cert_type = $cert_type;
	}

	//设置ca
	function ca($ca_file='')
	{
		$this->ca_file = $ca_file;
	}

	//请Curl请求
	function call($url,$type="post",$timeout=10)
	{
		//启动一个CURL会话
		$ch = curl_init();
		// 设置curl允许执行的最长秒数
		curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
		// 获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		// 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		$arr = explode("?", $url);
		if(count($arr) >= 2 && $type == "post")
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_URL, $arr[0]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $arr[1]);
		}
		else
		{
			curl_setopt($ch, CURLOPT_URL, $this->reqContent);
		}
		
		//设置证书信息
		if($this->cert_file != "")
		{
			curl_setopt($ch, CURLOPT_SSLCERT, $this->cert_file);
			curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->cert_pass);
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, $this->cert_type);
		}
		
		//设置CA
		if($this->ca_file != "")
		{
			// 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_CAINFO, $this->caFile);
		}
		else
		{
			// 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		// 执行操作
		$res = curl_exec($ch);
		$this->rs_info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if ($res == NULL)
		{ 
		   $this->rs_error = "call http err :" . curl_errno($ch) . " - " . curl_error($ch) ;
		   curl_close($ch);
		   return false;
		} 
		elseif($this->rs_info  != "200")
		{
			$this->rs_error = "call http err httpcode=" . $this->responseCode  ;
			curl_close($ch);
			return false;
		}
		curl_close($ch);
		$this->rs_info = $res;
		return true;
	}

	function get_info()
	{
		return $this->rs_info;
	}

	//
	function set_xml_content() 
	{
		$xml = simplexml_load_string($this->rs_info);
		$encode = $this->get_xml_encode($this->rs_info);
		
		if($xml && $xml->children())
		{
			foreach ($xml->children() as $node)
			{
				//有子节点
				if($node->children())
				{
					$k = $node->getName();
					$nodeXml = $node->asXML();
					$v = substr($nodeXml, strlen($k)+2, strlen($nodeXml)-2*strlen($k)-5);
					
				}
				else
				{
					$k = $node->getName();
					$v = (string)$node;
				}
				
				if($encode!="" && $encode != "UTF-8")
				{
					$k = iconv("UTF-8", $encode, $k);
					$v = iconv("UTF-8", $encode, $v);
				}
				$this->param($k, $v);			
			}
		}
	}

	//获取xml编码
	function get_xml_encode($xml)
	{
		$ret = preg_match ("/<?xml[^>]* encoding=\"(.*)\"[^>]* ?>/i", $xml, $arr);
		if($ret) {
			return strtoupper ( $arr[1] );
		} else {
			return "";
		}
	}

	//取得时间
	function get_date()
	{
		$time = $this->param('time_end');
		if($time)
		{
			$date = substr($time,0,4).'-'.substr($time,4,2).'-'.substr($time,6,2).' '.substr($time,8,2).':'.substr($time,10,2).':'.substr($time,12,2);
			return $date;
		}
		return false;
	}

}
?>