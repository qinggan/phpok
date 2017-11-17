<?php
/***********************************************************
	备注：微信响应类
	版本：5.0.0
	官网：www.phpok.com
	作者：qinggan <qinggan@188.com>
	更新：2016年02月21日
***********************************************************/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class weixin_lib
{
	private $obj;
	private $app_id; //微信开放平台申请到的APP ID
	private $app_secret = ''; // 微信开放平台申请到的APP Secret
	private $expire_time = 0;
	private $token = '';
	private $debug = false;
	private $error = '';
	private $openid = '';//目标用户的open ID
	private $account = '';//微信公众号原始账号
	
	public function __construct()
	{
		$this->datadir = ROOT.'data/';
	}

	public function get_data()
	{
		$data = file_get_contents("php://input");
		if($data){
			libxml_disable_entity_loader(true);
			$this->obj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
			$this->wx_type = trim($this->obj->MsgType);
		}
	}

	public function app_id($appid='')
	{
		if($appid){
			$this->app_id = $appid;
		}
		return $this->app_id;
	}

	public function app_secret($app_secret='')
	{
		if($app_secret){
			$this->app_secret = $app_secret;
		}
		return $this->app_secret;
	}


	public function debug($debug='')
	{
		if($debug != ''){
			$this->debug = $debug;
		}
		return $this->debug;
	}

	public function error($info='')
	{
		if($info){
			$this->error = $info;
		}
		return $this->error;
	}


	public function openid($openid='')
	{
		if($openid){
			$this->openid = $openid;
		}
		return $this->openid;
	}

	public function account($wx_account='')
	{
		if($wx_account){
			$this->account = $wx_account;
		}
		return $this->account;
	}

	public function error_xml($content)
	{
		echo $this->_txt($content);
		exit;
	}

	public function echo_xml($content)
	{
		echo $this->_txt($content);
		exit;
	}

	public function image_xml($content='',$picurl='')
	{
		if(!$content){
		    echo '';
		    exit;
	    }
        $xml  = "<xml>\n"."\t<ToUserName><![CDATA[".$this->openid()."]]></ToUserName>\n";
        $xml .= "\t<FromUserName><![CDATA[".$this->account()."]]></FromUserName>\n";
        $xml .= "\t<CreateTime>".time()."</CreateTime>\n\t<MsgType><![CDATA[image]]></MsgType>\n";
        //$xml.= "\t<PicUrl><![CDATA[".$picurl."]]></PicUrl>\n";
        $xml .= "\t<Image>\n\t";
        $xml .= "\t<MediaId><![CDATA[".$content."]]></MediaId>\n";
        $xml .= "\t</Image>\n";
        $xml .= "</xml>";
        echo $xml;
        exit;
	}

	private function _txt($content='')
    {
	    if(!$content){
		    return '';
	    }
        $xml = "<xml>\n"."\t<ToUserName><![CDATA[".$this->openid()."]]></ToUserName>\n";
        $xml.= "\t<FromUserName><![CDATA[".$this->account()."]]></FromUserName>\n";
        $xml.= "\t<CreateTime>".time()."</CreateTime>\n\t<MsgType><![CDATA[text]]></MsgType>\n";
        $xml.="\t<Content><![CDATA[".$content."]]></Content>\n</xml>";
        return $xml;
    }

    public function userinfo($openid)
    {
	    $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->access_token()."&openid=".rawurlencode($openid)."&lang=zh_CN";
	    $info = $this->curl($url);
	    if(!$info){
		    return false;
	    }
	    $info = json_decode($info,true);
	    if($info['errcode']){
		    if($this->debug){
				phpok_log($info['errcode'].':'.$info['errmsg']);
			}
			$this->error = $info['errcode'].':'.$info['errmsg'];
			return false;
	    }
	    return $info;
    }

	//获取access_token
	public function access_token()
	{
		$token = '';
		$cachefile = $this->datadir.'weixin_access_token.php';
		$ctime = time() - 7000;
		if(file_exists($cachefile) && filemtime($cachefile) > $ctime){
			$token = file_get_contents($cachefile);
			$token = substr($token,strlen('<?php exit();?>'));
			return trim($token);
		}
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential";
		$url.= "&appid=".rawurlencode($this->app_id)."&secret=".rawurlencode($this->app_secret);
		if($this->debug){
			phpok_log($url);
		}
		$info = $this->curl($url);
		if(!$info){
			if($this->debug){
				phpok_log($info['errcode'].':'.$info['errmsg'].'异常：获取数据失败');
			}
			return false;
		}
		$info = json_decode($info,true);
		if($info['errcode'] || $info['errmsg']){
			if($this->debug){
				phpok_log($info['errcode'].':'.$info['errmsg']);
			}
			$this->error = $info['errcode'].':'.$info['errmsg'];
			return false;
		}
		file_put_contents($cachefile,'<?php exit();?>'.$info['access_token']);
		return trim($info['access_token']);
	}

	public function create_menu($data='')
	{
		if($data && is_array($data)){
			$data = json_encode($data,JSON_UNESCAPED_UNICODE);
			//$data = rawurldecode($data);
		}
		$url = " https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->access_token();
		$info = $this->curl($url,$data);
		if(!$info){
			return false;
		}
		$info = json_decode($info,true);
		
		if($info['errcode']){
			$this->error = $info['errcode'].':'.$info['errmsg'];
			return false;
		}
		return true;
	}

	public function curl($url,$post='',$headers='')
	{
		$url = trim($url);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_HEADER,true);//结果中包含头部信息
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);//把结果返回，而非直接输出
		if($post){
			if($headers && $headers == 'upload'){
				if(class_exists('CURLFile')){
					curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
				}else{
					if(defined( 'CURLOPT_SAFE_UPLOAD' )){
						curl_setopt($curl,CURLOPT_SAFE_UPLOAD,false);
					}
				}
			}
			curl_setopt($curl,CURLOPT_POST,true);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
		}else{
			curl_setopt($curl, CURLOPT_HTTPGET,true);
		}
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,30);
		curl_setopt($curl,CURLOPT_ENCODING ,'gzip');//GZIP压缩
		curl_setopt($curl, CURLOPT_TIMEOUT,30);
		$purl = $this->format_url($url);
		if($headers && is_array($headers)){
			$header = array();
			$header[] = "Host: ".$purl["host"].":".$purl['port'];
			$header[] = "Referer: ".$purl['protocol'].$purl["host"];
			foreach($headers AS $key=>$value){
				$header[] = $key.": ".$value;
			}
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}
		if($post){
			$length = strlen($post);
			$header[] = 'Content-length: '.$length;
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		$content = curl_exec($curl);
		if (curl_errno($curl) != 0){
			if($this->debug){
				$this->error = curl_errno($curl).':'.curl_error($curl);
			}
			return false;
		}
		$separator = '/\r\n\r\n|\n\n|\r\r/';
		list($http_header, $http_body) = preg_split($separator, $content, 2);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$last_url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
		curl_close($curl);
		//非200
		if($http_code != '200'){
			if($this->debug){
				$this->error = 'httpcode:'.$http_code;
			}
			return false;
		}
		//判断是否是301或302跳转
		if ($http_code == 301 || $http_code == 302){
			//判断
			$matches = array();
			preg_match('/Location:(.*?)\n/', $http_header, $matches);
			$url = @parse_url(trim(array_pop($matches)));
			if (!$url) return $data;
			$new_url = $url['scheme'] . '://' . $url['host'] . $url['path']
			. (isset($url['query']) ? '?' . $url['query'] : '');
			$new_url = stripslashes($new_url);
			return $this->curl($new_url, $post);
		}
		return $http_body;
	}

	private function format_url($url)
	{
		$data = parse_url($url);
		if (!isset($data['host'])){
			if(isset($_SERVER["HTTP_HOST"])){
				$data['host'] = $_SERVER["HTTP_HOST"];
			}elseif(isset($_SERVER["SERVER_NAME"])){
				$data['host'] = $_SERVER["SERVER_NAME"];
			}else{
				$data['host'] = "localhost";
			}
		}
		if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == "off" || $_SERVER["HTTPS"] == ""){
			$data['scheme'] = "http";
		}else{
			$data['scheme'] = "https";
		}
		if(!$data['port']){
			$data['port'] = $_SERVER["SERVER_PORT"] ? $_SERVER["SERVER_PORT"] : ($data['scheme'] == 'http' ? '80' : '443');
		}
		if(!isset($this->purl['path'])){
			$data['path'] = "/";
		}
		elseif(($data['path']{0} != '/') && ($_SERVER["PHP_SELF"]{0} == '/'))
		{
			$data['path'] = substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], '/') + 1) . $data['path'];
		}
		return $data;
	}

	public function qrcode($uid)
	{
		$token = $this->access_token();
		if(!$token){
			return false;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$token;
		$param = '{"expire_seconds": 2592000, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$uid.'}}}';
		$info = $this->curl($url,$param);
		if(!$info){
			return false;
		}
		$info = json_decode($info,true);
		if($info['errcode']){
			if($this->debug){
				phpok_log($info['errcode'].':'.$info['errmsg']);
			}
			$this->error = $info['errcode'].':'.$info['errmsg'];
			return false;
		}
		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".rawurlencode($info['ticket']);
		$info = $this->curl($url);
		return $info;
	}

	public function upload($file,$root='')
	{
		$extension = pathinfo($file,PATHINFO_EXTENSION);
		$type = 'image/jpg';
		if($extension == 'png'){
			$type = 'image/png';
		}
		if($extension == 'gif'){
			$type = 'image/gif';
		}
		$data = array("media" => "@".realpath($root.$file), 'form-data' => array('filename'=>$file,'content-type'=>$type,'filelength'=>filesize($file)));
		if(class_exists('CURLFile')){
			$data['media'] = new CURLFile ( realpath ( $root.$file ),$type );
		}
        $info = $this->curl("https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->access_token()."&type=image",$data,'upload');
        if(!$info){
	        $this->error('上传临时素材失败');
	        return false;
        }
        $info = json_decode($info,true);
		if($info['errcode']){
			$this->error($info['errcode'].':'.$info['errmsg']);
			return false;
		}
		return array('id'=>$info['media_id'],'time'=>$info['created_at']);
	}

	public function jsapi_ticket()
	{
		$ticket = '';
		$cachefile = $this->datadir.'weixin_jsapi_ticket.php';
		$ftime = file_exists($cachefile) ? filemtime($cachefile) : 0;
		$ctime = time() - 7000;
		if(file_exists($cachefile) && $ftime > $ctime){
			$ticket = file_get_contents($cachefile);
			$ticket = substr($ticket,strlen('<?php exit();?>'));
			return trim($ticket);
		}
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$this->access_token()."&type=jsapi";
		if($this->debug){
			phpok_log($url);
		}
		$info = $this->curl($url);
		if(!$info){
			if($this->debug){
				phpok_log($info['errcode'].':'.$info['errmsg'].'异常：获取数据失败');
			}
			return false;
		}
		$info = json_decode($info,true);
		if($info['errcode'] || $info['errmsg'] != 'ok'){
			if($this->debug){
				phpok_log($info['errcode'].':'.$info['errmsg']);
			}
			$this->error = $info['errcode'].':'.$info['errmsg'];
			return false;
		}
		file_put_contents($cachefile,'<?php exit();?>'.$info['ticket']);
		return trim($info['ticket']);
	}

	public function jsapi_config($url='')
	{
		$rs = array("appid"=>$this->app_id);
		$rs['noncestr'] = $this->noncestr();
		$rs['timestamp'] = $GLOBALS['app']->time;
		$rs['url'] = $url;
		$rs['ticket'] = $this->jsapi_ticket();
		$sign = $this->jsapi_sign($rs);
		$rs['sign'] = $sign;
		return $rs;
	}

	/**
	 * JSAPI 签名生成
	**/
	public function jsapi_sign($data)
	{
		$str = "jsapi_ticket=".$data['ticket']."&noncestr=".$data['noncestr']."&timestamp=".$data['timestamp']."&url=".$data['url'];
		return sha1($str);
	}

	/**
	 * 随机码
	**/
	public function noncestr()
	{
		return md5(time().rand(100,999));
	}
}