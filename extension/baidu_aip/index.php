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
	private $AipNlp;
	public function __construct()
	{
		parent::__construct();
		$pharfile = 'phar://'.$this->dir_extension.'baidu_aip/baidu_aip.phar';
		include_once $pharfile.'/AipContentCensor.php';
		include_once $pharfile.'/AipNlp.php'; //自然语言处理
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

	/**
	 * 获取分类
	**/
	public function cate($title,$content='')
	{
		if(!$title){
			return false;
		}
		if(!$content){
			$content = $title;
		}
		if(!$this->AipNlp){
			$this->NLP();
		}
		$info = $this->AipNlp->topic($title,$content);
		if(!$info || !$info['item']){
			return false;
		}
		$data = array();
		if($info['lv1_tag_list']){
			foreach($info['lv1_tag_list'] as $key=>$value){
				$data[] = $value['tag'];
			}
		}
		if($info['lv2_tag_list']){
			foreach($info['lv2_tag_list'] as $key=>$value){
				$data[] = $value['tag'];
			}
		}
		return $data;
	}

	/**
	 * 取得标签
	**/
	public function tags($title,$content='')
	{
		if(!$title){
			return false;
		}
		if(!$content){
			$content = $title;
		}
		if(!$this->AipNlp){
			$this->NLP();
		}
		$info = $this->AipNlp->keyword($title,$content);
		if(!$info || !$info['items']){
			$this->error('没有找到相应的标签');
			return false;
		}
		$list = array();
		foreach($info['items'] as $key=>$value){
			$list[] = $value['tag'];
		}
		return $list;
	}

	/**
	 * 地址识别
	**/
	public function address($info)
	{
		if(!$info){
			return false;
		}
		if(!$this->AipNlp){
			$this->NLP();
		}
		$info = $this->AipNlp->address($info);
		if(!$info || !$info['text']){
			return false;
		}
		return $info;
	}

	/**
	 * 获取内容摘要
	**/
	public function note($content,$length=300,$title='')
	{
		if(!$content){
			return false;
		}
		if(!$this->AipNlp){
			$this->NLP();
		}
		$option = array();
		if($title){
			$option['title'] = $title;
		}
		$info = $this->AipNlp->newsSummary($content,$length,$option);
		if(!$info || !$info['summary']){
			return false;
		}
		return $info['summary'];
	}

	private function NLP()
	{
		$this->AipNlp = new AipNlp($this->app_id, $this->app_key, $this->app_secret);
		return $this->AipNlp;
	}
}