<?php
/**
 * 百度云 AIP-SDK 接口
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2021年2月13日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class baidu_aip_lib extends _init_lib
{
	private $app_id;
	private $app_key;
	private $app_secret;
	private $err_info = '';
	private $err_code = '';
	public function __construct()
	{
		parent::__construct();
		include_once $this->dir_extension.'baidu_aip/AipContentCensor.php';
	}

	public function config($app_id='',$app_key='',$app_secret='')
	{
		$this->app_id = $app_id;
		$this->app_key = $app_key;
		$this->app_secret = $app_secret;
		return true;
	}

	public function check($content='')
	{
		if(!$content){
			return true;
		}
		if(is_array($content)){
			$content = implode("\n",$content);
		}
		$content = strip_tags($content);
		if(!$content){
			return true;
		}
		$client = new AipContentCensor($this->app_id, $this->app_key, $this->app_secret);
		$rs = $client->textCensorUserDefined($content);
		if(isset($rs['error_code'])){
			$this->error($rs['error_msg'],$rs['error_code']);
			return false;
		}
		if($rs['conclusionType'] == 1){
			return true;
		}
		$errlist = array();
		foreach($rs['data'] as $key=>$value){
			if($value['hits']){
				foreach($value['hits'] as $k=>$v){
					if($v['words']){
						if(is_array($v['words'])){
							$errlist[] = implode("|",$v['words']);
						}else{
							$errlist[] = $v['words'];
						}
					}
				}
			}
		}
		$this->error(implode(", ",$errlist));
		return false;
	}

	public function error($info='',$code='')
	{
		if($info){
			$this->err_info = $info;
			if($errcode){
				$this->err_code = $errcode;
			}else{
				$this->err_code = '';
			}
		}
		$tip = $this->err_code ? $this->err_code.': '.$this->err_info : $this->err_info;
		return $tip;
	}
}