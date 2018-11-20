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
	private $youdao_url = 'http://openapi.youdao.com/api';
	private $youdao_appid = '';
	private $youdao_appkey = '';
	private $kunwu_url = "https://www.phpok.com/apix-24918";
	private $kunwu_appid = '';
	private $kunwu_appkey = '';
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		if($this->me && $this->me['param']){
			if($this->me['param']['youdao_https']){
				$this->youdao_url = 'https://openapi.youdao.com/api';
			}
			if($this->me['param']['youdao_appid']){
				$this->youdao_appid = $this->me['param']['youdao_appid'];
			}
			if($this->me['param']['youdao_appkey']){
				$this->youdao_appkey = $this->me['param']['youdao_appkey'];
			}
			if($this->me['param']['phpok_appid']){
				$this->kunwu_appid = $this->me['param']['phpok_appid'];
			}
			if($this->me['param']['phpok_appkey']){
				$this->kunwu_appkey = $this->me['param']['phpok_appkey'];
			}
		}
	}

	private function err_code($id=0)
	{
		if(!$id){
			return false;
		}
		$data = $this->lib('xml')->read($this->me['path'].'err.xml');
		if($data['info'.$id]){
			return $data['info'.$id];
		}
		return false;
	}

	public function fanyi()
	{
		$rs = $this->plugin_info();
		$q = $this->get("q");
		if(!$q){
			$this->error("没有指定翻译内容");
		}
		$post = array('q'=>$q,'from'=>'auto','to'=>'EN','appKey'=>$this->youdao_appid);
		$post['salt'] = $this->lib('common')->str_rand(3,'number');
		$post['sign'] = strtoupper(md5($this->youdao_appid.$q.$post['salt'].$this->youdao_appkey));
		$this->lib('curl')->is_post(true);
		foreach($post as $key=>$value){
			$this->lib('curl')->post_data($key,$value);
		}
		$data = $this->lib('curl')->get_json($this->youdao_url);
		if($data['errorCode']){
			$tip = $this->err_code($data['errorCode']);
			if(!$tip){
				$tip = '异常错误，请检查';
			}
			$this->error($tip);
		}
		$info = current($data['translation']);
		$info = strtolower($info);
		$info = $this->return_safe($info);
		$this->success(strtolower($info));
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
		$url = $this->kunwu_url.'?_appid='.$this->kunwu_appid;
		$data = array("keywords"=>$title);
		$data['first'] = $is_first ? 1 : 0;
		$sign = $this->create_sign($data);
		$url .= "&_sign=".rawurlencode($sign);
		$url .= "&params[keywords]=".rawurlencode($title)."&params[first]=".$data['first'];
		$info = $this->lib('curl')->get_json($url);
		if(!$info){
			$this->error('获取内容失败');
		}
		if(!$info['status']){
			$this->error($info['info']);
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