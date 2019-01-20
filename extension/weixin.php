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
    private $datadir;
	
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

    public function news_xml($newsArray)
    {
        if(!is_array($newsArray)){
            return '';
        }
        $itemTpl = <<<EOT
<item>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <PicUrl><![CDATA[%s]]></PicUrl>
    <Url><![CDATA[%s]]></Url>
</item>
EOT;
        $item_str = "";
        $i = 1;
        foreach ($newsArray as $item){
            if($i > 8){
                break;
            }
            $item_str .= sprintf($itemTpl, $item['title'], $item['note'], rawurldecode($item['thumb']), rawurldecode($item['url']));
            $i++;
        }
        $xmlTpl = <<<EOT
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime><![CDATA[%s]]></CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount><![CDATA[%s]]></ArticleCount>
    <Articles>
    $item_str
    </Articles>
</xml>
EOT;
        $result = sprintf($xmlTpl, $this->openid, $this->account, time(), count($newsArray));
        return $result;
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
			curl_setopt($curl,CURLOPT_POST,1);
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
		if($post && is_string($post)){
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
			if (!$url) return ;
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

    /**
     * 获取素材列表
     */
    public function get_material_count()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=".$this->access_token();
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
        return $info;
    }

    /**
     * 获取素材列表
     * @param $type string 素材类型
     * @param $offset int 开始位置
     * @param $count int 条数
     * @return bool|mixed|void
     */
    public function get_material_list($type,$offset='0',$count=20)
    {
        $data = [
            'type'      => $type,
            'offset'    => $offset,
            'count'     => $count
        ];
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=".$this->access_token();
        if($this->debug){
            phpok_log($url);
        }
        $info = $this->curl($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        if(!$info){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg'].'异常：获取数据失败');
            }
            return false;
        }
        $info = json_decode($info,true);
        if($info['errcode'] || ($info['errmsg'] && $info['errmsg'] != 'ok')){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
	}

    /**
     * 添加图文素材
     * @param $articles
     * @return bool|array
     */
    public function add_news($articles)
    {
        if($articles && is_array($articles)){
            $articles = json_encode($articles,JSON_UNESCAPED_UNICODE);
            //$data = rawurldecode($data);
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token='.$this->access_token();
        phpok_log($url);
        $info = $this->curl($url,$articles);
        if(!$info){
            return false;
        }
        $info = json_decode($info,true);
        if($info['errcode']){
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
	}

    /**
     * 更新图文素材
     * @param $media_id
     * @param $index
     * @param $articles
     * @return bool|mixed
     */
    public function update_news($media_id,$index,$articles)
    {
        $data = [
            "media_id"  => $media_id,
            "index"     => $index,
            'articles'  => $articles
        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/material/update_news?access_token='.$this->access_token();
        $result = $this->curl($url,json_encode($data));
        if(!$result){
            if($this->debug){
                phpok_log($result['errcode'].':'.$result['errmsg'].'异常：获取数据失败');
            }
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode'] || $info['errmsg'] != 'ok'){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
	}
	
    /**
     * 新增素材
     * @param $file_info
     * @return bool|mixed
     */
    public function add_material($file_info,$type,$root='',$is_forever=0,$file_type='image'){
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->access_token();
        if ($is_forever){
            $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->access_token().'&type='.$file_type;
        }
        $data= [
            "media" => "@".realpath($root.$file_info['filename']),
            'form-data'=> [
                'filename'      => $file_info['filename'],
                'content-type'  => $type,
                'filelength'    => filesize($file_info['filename'])
            ]
        ];
        if(class_exists('CURLFile')){
            $data['media'] = new CURLFile ( realpath ( $root.$file_info['filename'] ),$type );
        }
        $result = $this->curl($url,$data);
        if(!$result){
            $this->error('上传素材失败');
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode'] || ($info['errmsg'] && $info['errmsg'] != 'ok')){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
    }

    public function get_material($media_id,$is_forever=0)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$this->access_token();
        if ($is_forever){
            $url = 'https://api.weixin.qq.com/cgi-bin/material/get_material?access_token='.$this->access_token();
        }
        $data= array('media_id'=>$media_id);
        $result = $this->curl($url,json_encode($data));
        if(!$result){
            $this->error('获取素材失败');
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode'] || ($info['errmsg'] && $info['errmsg'] != 'ok')){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
    }
    
    public function uploadimg($file,$root='')
    {
        $extension = pathinfo($file,PATHINFO_EXTENSION);
        $type = 'image/jpg';
        if($extension == 'png'){
            $type = 'image/png';
        }
        if($extension == 'gif'){
            $type = 'image/gif';
        }
        $data = array("media" => "@".realpath($file), 'form-data' => array('filename'=>$root.'/'.$file,'content-type'=>$type,'filelength'=>filesize($file)));
        if(class_exists('CURLFile')){
            $data['media'] = new CURLFile (realpath($file),$type);
        }
        $info = $this->curl("https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=".$this->access_token()."&type=image",$data,'upload');
        if(!$info){
            $this->error('上传图片素材失败');
            return false;
        }
        $info = json_decode($info,true);
        if($info['errcode']){
            $this->error($info['errcode'].':'.$info['errmsg']);
            return false;
        }
        if(!$info['url']){
            return false;
        }
        return $info['url'];
    }

    /**
     * 删除永久素材
     * @param $media_id int 素材id
     * @return bool|string
     */
    public function del_material($media_id){
        $url = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token='.$this->access_token();
        $data= array('media_id'=>$media_id);
        $result = $this->curl($url,json_encode($data));
        phpok_log($result);
        if ($result){
            $info = json_decode($result,true);
            if($info['errcode']!=0){
                return false;
            }
        }
        return true;
    }
    
    /**
     * 群发消息
     * @param $touser array 用户openid
     * @param $msgtype string 消息类型
     * @param $text array 消息内容
     */
    public function send_text($touser,$msgtype,$content)
    {
        $data = [
            'touser'    => $touser,
            'msgtype'   => $msgtype,
            $msgtype    => $content
        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->access_token();
        $result = $this->curl($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        if(!$result){
            if($this->debug){
                phpok_log($result['errcode'].':'.$result['errmsg'].'异常：获取数据失败');
            }
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode'] || $info['errmsg'] != 'ok'){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
    }

    /**
     * 修改模版消息所属行业行业
     * @param $industry_id1 主行业
     * @param $industry_id2 副行业
     */
    public function set_industry($industry_id1,$industry_id2)
    {
        $data = [
            'industry_id1'    => $industry_id1,
            'industry_id2'   => $industry_id2,
        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token='.$this->access_token();
        $result = $this->curl($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        if(!$result){
            if($this->debug){
                phpok_log($result['errcode'].':'.$result['errmsg'].'异常：获取数据失败');
            }
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode'] || $info['errmsg'] != 'ok'){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
    }

    /**
     * 获取设置的行业信息
     */
    public function get_industry()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_industry?access_token='.$this->access_token();
        $result = $this->curl($url);
        if(!$result){
            if($this->debug){
                phpok_log($result['errcode'].':'.$result['errmsg'].'异常：获取数据失败');
            }
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode'] || $info['errmsg'] != 'ok'){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
    }

    /**
     * 获取模板列表
     */
    public function get_all_template()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token='.$this->access_token();
        $result = $this->curl($url);
        if(!$result){
            if($this->debug){
                phpok_log($result['errcode'].':'.$result['errmsg'].'异常：获取数据失败');
            }
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode']){
            phpok_log($info);
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return $info;
    }

    /**
     * 删除模版
     * @param $template_id 模版ID
     * @return bool|array
     */
    public function del_template($template_id)
    {
        $data = [
            'template_id'    => $template_id,
        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_industry?access_token='.$this->access_token();
        $result = $this->curl($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        if(!$result){
            if($this->debug){
                phpok_log($result['errcode'].':'.$result['errmsg'].'异常：获取数据失败');
            }
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode'] || $info['errmsg'] != 'ok'){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return true;
    }

    /**
     * 发送模版消息
     * @param $message array 模板消息
     * @return bool|array
     */
    public function send_template($message)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->access_token();
        $result = $this->curl($url,json_encode($message,JSON_UNESCAPED_UNICODE));
        if(!$result){
            if($this->debug){
                phpok_log($result['errcode'].':'.$result['errmsg'].'异常：获取数据失败');
            }
            return false;
        }
        $info = json_decode($result,true);
        if($info['errcode'] || $info['errmsg'] != 'ok'){
            if($this->debug){
                phpok_log($info['errcode'].':'.$info['errmsg']);
            }
            $this->error = $info['errcode'].':'.$info['errmsg'];
            return false;
        }
        return true;
    }


}