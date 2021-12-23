<?php
/**
 * 标识串自动生成API接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年09月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class api_identifier extends phpok_plugin
{
	private $me;
	private $trans_url = 'https://www.phpok.com/apix-31806';
	private $pingyin_url = "https://www.phpok.com/apix-24918";
	private $kunwu_appid = '';
	private $kunwu_appkey = '';
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		if($this->me && $this->me['param']){
			if($this->me['param']['phpok_appid']){
				$this->kunwu_appid = $this->me['param']['phpok_appid'];
			}
			if($this->me['param']['phpok_appkey']){
				$this->kunwu_appkey = $this->me['param']['phpok_appkey'];
			}
		}
	}

	public function fanyi()
	{
		$q = $this->get("q");
		if(!$q){
			$this->error("没有指定翻译内容");
		}
		$info = $this->trans_share($q);
		$this->success($info);
	}

	private function return_safe($content)
	{
		$safe_string = "abcdefghijklmnopqrstuvwxyz0123456789-_";
		$str_array = str_split($content);
		$safe_array = str_split($safe_string);
		$string = "";
		foreach($str_array as $key=>$value){
			if(in_array($value,$safe_array)){
				$string .= $value;
			}else{
				$string .= "-";
			}
		}
		//如果首字母为0-9的数字或非字母
		$array = array('0','1','2','3','4','5','6','7','8','9','-','_');
		$t1 = substr($string,0,1);
		if(in_array($t1,$array)){
			$string = $safe_array[rand(0,25)].$string;
		}
		return $string;
	}

	public function pingyin()
	{
		//取得关键字
		$title = $this->get('title');
		if(!$title){
			$this->error('没有指定要翻译的内容');
		}
		//取得拼音库
		$content = $this->py_share($title,false);
		$this->success($content);
	}

	public function py()
	{
		//取得关键字
		$title = $this->get('title');
		if(!$title){
			$this->error('没有指定要翻译的内容');
		}
		//取得拼音库
		$content = $this->py_share($title,true);
		$this->success($content);
	}

	private function py_share($title,$is_first=false)
	{
		if(!$title){
			$this->error('未指定要翻译的信息');
		}
		$url = $this->pingyin_url.'?_appid='.$this->kunwu_appid;
		$data = array("keywords"=>$title);
		$data['first'] = $is_first ? 1 : 0;
		$sign = $this->create_sign($data);
		$url .= "&_sign=".rawurlencode($sign);
		$url .= "&params[keywords]=".rawurlencode($title)."&params[first]=".$data['first'];
		$this->lib('curl')->user_agent($this->lib('server')->agent());
		$info = $this->lib('curl')->get_json($url);
		if(!$info){
			$this->error('获取内容失败');
		}
		if(!$info['status']){
			$tip = $info['error'] ? $info['error'] : $info['info'];
			$this->error($tip);
		}
		$info = $info['info'];
		$info = strtolower($info);
		$info = $this->return_safe($info);
		return $info;
	}

	private function trans_share($title)
	{
		if(!$title){
			$this->error('未指定要翻译的信息');
		}
		$url = $this->trans_url.'?_appid='.$this->kunwu_appid;
		$data = array("keywords"=>$title);
		$sign = $this->create_sign($data);
		$url .= "&_sign=".rawurlencode($sign);
		$url .= "&params[keywords]=".rawurlencode($title);
		$this->lib('curl')->user_agent($this->lib('server')->agent());
		$info = $this->lib('curl')->get_json($url);
		if(!$info){
			$this->error('获取内容失败');
		}
		if(!$info['status']){
			$tip = $info['error'] ? $info['error'] : $info['info'];
			$this->error($tip);
		}
		$info = $info['info'];
		$info = strtolower($info);
		$info = $this->return_safe($info);
		return $info;
	}

	private function create_sign($data)
	{
		$string = '_appid='.$this->kunwu_appid.'&_appkey='.$this->kunwu_appkey;
		if($data){
			ksort($data);
			foreach($data as $key=>$value){
				if($value !== ''){
					$string .= "&".$key."=".rawurlencode($value);
				}
			}
		}
		return md5($string);
	}
}