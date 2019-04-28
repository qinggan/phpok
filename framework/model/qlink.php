<?php
/**
 * 后台快速链接创建及保存
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年2月14日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class qlink_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 获取XML中的一个信息
	**/
	public function get_one($id)
	{
		$list = $this->_xml();
		if(!$list){
			return false;
		}
		return $list[$id];
	}

	public function get_all()
	{
		return $this->_xml();
	}

	public function save($data)
	{
		$list = $this->_xml();
		if(!$list){
			$list = array();
		}
		$list[$data['id']] = $data;
		$this->lib('xml')->save($list,$this->dir_data.'xml/qlink.xml');
		return true;
	}

	public function delete($id)
	{
		$list = $this->_xml();
		if($list && $list[$id]){
			unset($list[$id]);
			if($list){
				$this->lib('xml')->save($list,$this->dir_data.'xml/qlink.xml');
			}else{
				$this->lib('file')->rm($this->dir_data.'xml/qlink.xml');
			}
		}
		return true;
	}

	public function func_list($file)
	{
		if(!is_file($file)){
			return false;
		}
		$info = $this->lib('file')->cat($file);
		if(!$info){
			return false;
		}
		$list = array();
		preg_match_all('/\/\*.*\s+\*\s*([^\n]+)\s+.*\*\/\s+[public]*\s*function\s+(.+)\_f\s*\(\)/isU',$info,$matches);
		if($matches && $matches[1] && $matches[2] && $matches[2]){
			foreach($matches[2] as $key=>$value){
				$tmp = array('id'=>$value);
				if($matches[1][$key]){
					$tmp['title'] = trim($matches[1][$key]);
				}
				$list[$value] = $tmp;
			}
		}
		if($matches){
			unset($matches);
		}
		preg_match_all('/\s+(#|\/\/)+\s*([^\n]+)\s+[public]*\s*function\s+(.+)\_f\s*\(\)/isU',$info,$matches);
		if($matches && $matches[2] && $matches[3]){
			foreach($matches[3] as $key=>$value){
				$tmp = array('id'=>$value);
				if($matches[2][$key]){
					$tmp['title'] = trim($matches[2][$key]);
				}
				if(!$list[$value]){
					$list[$value] = $tmp;
				}
			}
		}
		if($matches){
			unset($matches);
		}
		preg_match_all('/\s+[public]*\s+function\s+(.+)\_f\s*\(\)/isU',$info,$matches);
		if($matches && $matches[1]){
			foreach($matches[1] as $key=>$value){
				if(substr($value,0,2) == '__'){
					continue;
				}
				if($list[$value]){
					continue;
				}
				$list[$value] = array('id'=>$value,'title'=>$value);
			}
		}
		return $list;
	}

	/**
	 * 读取XML信息
	**/
	private function _xml()
	{
		if(is_file($this->dir_data.'xml/qlink.xml')){
			return $this->lib('xml')->read($this->dir_data.'xml/qlink.xml',true);
		}
		return false;
	}
}
