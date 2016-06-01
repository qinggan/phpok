<?php
/*****************************************************************************************
	文件： {phpok}/model/rewrite.php
	备注： 伪静态页规则配置器
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 12时57分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rewrite_model_base extends phpok_model
{
	private $rlist = '';
	public function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function type_ids()
	{
		$list = $this->type_all();
		return array_keys($list);
	}

	public function get_all()
	{
		return $this->rlist();
	}

	public function get_one($id)
	{
		if(!$id){
			return false;
		}
		$rslist = $this->rlist();
		if(!$rslist){
			return false;
		}
		$rs = false;
		foreach($rslist as $key=>$value){
			if($value['id'] == $id){
				$rs = $value;
				break;
			}
		}
		if($rs['ctrl'] && is_array($rs['ctrl'])){
			$rs['ctrl'] = implode("|",$rs['ctrl']);
		}
		if($rs['func'] && is_array($rs['func'])){
			$rs['func'] = implode("|",$rs['func']);
		}
		if($rs['var'] && is_array($rs['var'])){
			$rs['var'] = implode("|",$rs['var']);
		}
		return $rs;
	}

	public function uri_format($uri='/')
	{
		if(!$uri || $uri == '/'){
			return false;
		}
		$this->rlist();
		$rs = false;
		foreach($this->rlist as $key=>$value){
			//规则长度是不一致，跳过
			$matches = false;
			preg_match_all('/'.$value['rule'].'/isU',$uri,$matches);
			if($matches !== false && $matches[0] && $matches[0][0] == $uri){
				$rs = $value;
				break;
			}
		}
		if(!$rs){
			return false;
		}
		foreach($matches as $key=>$value){
			if($key<1){
				continue;
			}
			$rs['val'] = str_replace('$'.$key,$value[0],$rs['val']);
		}
		$info = strstr($rs['val'],'?');
		$info = $info ? substr($info,1) : $rs['val'];
		parse_str($info,$tmp);
		if($tmp && is_array($tmp)){
			foreach($tmp as $key=>$value){
				$_GET[$key] = $value;
			}
		}
		return true;
	}

	public function rlist()
	{
		//如果存在，无需再读取，直接使用
		if($this->rlist && is_array($this->rlist)){
			return $this->rlist;
		}
		$file = $this->dir_root.'data/xml/rewrite_'.$this->site_id.'.xml';
		if(!file_exists($file)){
			$file = $this->dir_root.'data/xml/rewrite.xml';
			if(!file_exists($file)){
				return false;
			}
		}
		$list = $this->lib('xml')->read($file);
		$list = $list['url'];
		usort($list,array($this,'_sort'));
		foreach($list as $key=>$value){
			if($value['var']){
				$value['var'] = explode('|',$value['var']);
			}
			if($value['func']){
				$value['func'] = explode('|',$value['func']);
			}
			if($value['ctrl']){
				$value['ctrl'] = explode('|',$value['ctrl']);
			}
			if(!$value['id']){
				$value['id'] = md5(serialize($value));
			}
			$list[$key] = $value;
		}
		$this->rlist = $list;
		return $this->rlist;
	}

	private function _sort($a,$b)
	{
		if($a['sort'] == $b['sort']){
			return 0;
		}
		return ($a['sort'] < $b['sort']) ? -1 : 1;
	}

	private function _rule_format($string)
	{
		if(!$string){
			return false;
		}
		preg_match_all('/\[([a-zA-Z0-9\_\-\:]+)\]/isU',$string,$matches);
		if(!$matches || !$matches[1]){
			return false;
		}
		$rs = array();
		foreach($matches[1] as $key=>$value){
			$tmp = explode(":",$value);
			if(!$tmp[1]){
				$tmp[1] = 'string';
			}
			$rs[$tmp[0]] = array('var'=>'['.$tmp[0].']','type'=>$tmp[1],'string'=>$value);
		}
		return $rs;
	}

	private function _val_format($string)
	{
		if(!$string){
			return false;
		}
		parse_str($string,$list);
		return $list;
	}
}
?>