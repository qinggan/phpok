<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/rewrite_model.php
	备注： 伪静态页后台相关操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 12时58分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rewrite_model extends rewrite_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function save($data,$id='',$stripslashes=true)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($stripslashes){
			$data['rule'] = stripslashes($data['rule']);
		}
		$rslist = $this->rlist();
		if(!$data['id']){
			$data['id'] = md5(serialize($data));
		}
		if($id){
			foreach($rslist as $key=>$value){
				if($value['id'] == $id){
					$rslist[$key] = $data;
				}
			}
		}else{
			$rslist[] = $data;
		}
		return $this->_save($rslist);
	}

	public function update_taxis($id,$taxis=255)
	{
		if(!$id){
			return false;
		}
		$rslist = $this->rlist();
		foreach($rslist as $key=>$value){
			if($value['id'] == $id){
				$rslist[$key]['sort'] = $taxis;
			}
		}
		$this->_save($rslist);
	}

	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$rslist = $this->rlist();
		foreach($rslist as $key=>$value){
			if($value['id'] == $id){
				unset($rslist[$key]);
				break;
			}
		}
		return $this->_save($rslist);
	}

	public function ctrl_list()
	{
		$list = $this->lib('file')->ls($this->dir_phpok.'www/');
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$tmp = basename($value);
			if(substr($tmp, -12) == '_control.php'){
				$id = substr($tmp,0,-12);
				$fp = fopen($value,'rb');
				$info = fread($fp,1024);
				fclose($fp);
				preg_match_all("/(Note|摘要|说明|内容|备注)\s*(\:|：)(.+)\n/isU",$info,$matches);
				if($matches && $matches[3] && $matches[3][0]){
					$title = trim($matches[3][0]).'('.ucwords($id).')';
				}else{
					preg_match_all('/\/\*\*\s+\*(.+)\n\s+\*\s+@package.+/isU',$info,$matches);
					if($matches && $matches[1] && $matches[1][0]){
						$title = trim($matches[1][0]).'('.ucwords($id).')';
					}else{
						$title = ucwords($id);
					}
				}
				$rslist[$id] = $title;
			}
		}
		return $rslist;
	}

	public function get_func($ctrl)
	{
		$file = $this->dir_phpok.'www/'.$ctrl.'_control.php';
		$info = $this->lib('file')->cat($file);
		if(!$info){
			return false;
		}
		preg_match_all("/[public]*\s*function\s+([a-zA-Z0-9\_]+)\_f\(/isU",$info,$matches);
		if(!$matches || !$matches[1]){
			return false;
		}
		$rslist = array();
		foreach($matches[1] as $key=>$value){
			$rslist[$value] = ucwords($value);
		}
		return $rslist;
	}

	private function _save($data)
	{
		foreach($data as $key=>$value){
			if($value['ctrl'] && is_array($value['ctrl'])){
				$value['ctrl'] = implode("|",$value['ctrl']);
			}
			if($value['func'] && is_array($value['func'])){
				$value['func'] = implode("|",$value['func']);
			}
			if($value['var'] && is_array($value['var'])){
				$value['var'] = implode("|",$value['var']);
			}
			$data[$key] = $value;
		}
		$this->lib('xml')->save($data,$this->dir_data.'xml/rewrite_'.$this->site_id.'.xml','url');
		return true;
	}
}

?>