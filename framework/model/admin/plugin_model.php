<?php
/**
 * 插件后台的一些专属模型
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年07月24日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_model extends plugin_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//取得下一个默认排序
	public function get_next_taxis()
	{
		$sql = "SELECT taxis FROM ".$this->db->prefix."plugins";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return 10;
		}
		$taxis = 0;
		foreach($rslist as $key=>$value){
			if($value['taxis'] != 255 && $value['taxis']>$taxis){
				$taxis = $value['taxis'];
			}
		}
		if(!$taxis){
			return 10;
		}
		$next = ($taxis+10)<255 ? ($taxis+10) : 255;
		return $next;
	}

	public function iconlist($id)
	{
		$file = $this->dir_data.'plugin-'.$id.'-icon.xml';
		if(!file_exists($file)){
			return false;
		}
		$list = $this->lib('xml')->read($file);
		if(!$list){
			return false;
		}
		if($list['plugin'] && $list['plugin']['title']){
			$tmp = false;
			$tmplist = array();
			foreach($list['plugin'] as $key=>$value){
				if(is_numeric($key)){
					$tmplist[$key] = $value;
				}else{
					$tmp[$key] = $value;
				}
			}
			if($tmp){
				$list = array(0=>$tmp);
				foreach($tmplist as $key=>$value){
					$list[] = $value;
				}
			}
		}else{
			$list = $list['plugin'];
		}
		usort($list,array($this,'_sort'));
		return $list;
	}

	private function _sort($a,$b)
	{
		if($a['taxis'] == $b['taxis']){
			return 0;
		}
		return ($a['taxis'] < $b['taxis']) ? -1 : 1;
	}

	public function icon_one($id,$vid)
	{
		if(!$id || !$vid){
			return false;
		}
		$iconlist = $this->iconlist($id);
		if($iconlist){
			$rs = false;
			foreach($iconlist as $key=>$value){
				if($value['id'] == $vid){
					$rs = $value;
					break;
				}
			}
			return $rs;
		}
		return false;
	}

	public function icon_taxis_next($id)
	{
		$iconlist = $this->iconlist($id);
		if(!$iconlist){
			return 10;
		}
		$taxis = 0;
		foreach($iconlist as $key=>$value){
			if($value['taxis'] && $value['taxis'] > $taxis){
				$taxis = $value['taxis'];
			}
		}
		return ($taxis+10);
	}

	/**
	 * 快捷图标保存
	 * @参数 $data 要保存的数据
	 * @参数 $id 插件ID标识
	**/
	public function icon_save($data,$id)
	{
		if(!$data || !$id){
			return false;
		}
		if(!$data['id']){
			$data['id'] = md5(serialize($data));
		}
		$rslist = $this->iconlist($id);
		if(!$rslist){
			$rslist = array();
		}
		$add = true;
		foreach($rslist as $key=>$value){
			if($value['id'] == $data['id']){
				$rslist[$key] = $data;
				$add = false;
			}
		}
		if($add){
			$rslist[] = $data;
		}
		$this->lib('xml')->save($rslist,$this->dir_data.'plugin-'.$id.'-icon.xml','plugin');
		return true;
	}

	/**
	 * 删除插件的快捷配置
	 * @参数 $id 插件ID标识
	 * @参数 $vid 要删除的快捷ID
	**/
	public function icon_delete($id,$vid)
	{
		if(!$id || !$vid){
			return false;
		}
		$rslist = $this->iconlist($id);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['id'] == $vid){
				unset($rslist[$key]);
				break;
			}
		}
		if(!$rslist){
			$this->lib('file')->rm($this->dir_data.'plugin-'.$id.'-icon.xml');
			return true;
		}
		$this->lib('xml')->save($rslist,$this->dir_data.'plugin-'.$id.'-icon.xml','plugin');
		return true;
	}

	public function methods($id)
	{
		if(!file_exists($this->dir_plugin.$id.'/admin.php')){
			return false;
		}
		$content = $this->lib('file')->cat($this->dir_plugin.$id.'/admin.php');
		if(!$content){
			return false;
		}
		$info = strstr($content,'class admin_'.$id.' extends phpok_plugin');
		if(!$info){
			return false;
		}
		$list = array();
		preg_match_all('/\/\*.*\s+\*\s*([^\n]+)\s+.*\*\/\s+[public]*\s*function\s+([a-zA-Z0-9\_\-]+)\(\)/isU',$info,$matches);
		if($matches && $matches[1] && $matches[2] && $matches[2]){
			foreach($matches[2] as $key=>$value){
				$tmp = array('id'=>$value);
				if($matches[1][$key]){
					$tmp['title'] = $matches[1][$key];
				}
				$list[$value] = $tmp;
			}
		}
		if($matches){
			unset($matches);
		}
		preg_match_all('/\s+(#|\/\/)+\s*([^\n]+)\s+[public]*\s*function\s+([a-zA-Z0-9\_\-]+)\(\)/isU',$info,$matches);
		if($matches && $matches[2] && $matches[3]){
			foreach($matches[3] as $key=>$value){
				$tmp = array('id'=>$value);
				if($matches[2][$key]){
					$tmp['title'] = $matches[2][$key];
				}
				if(!$list[$value]){
					$list[$value] = $tmp;
				}
			}
		}
		if($matches){
			unset($matches);
		}
		preg_match_all('/\s+[public]*\s+function\s+([a-zA-Z0-9\_\-]+)\(\)/isU',$info,$matches);
		if($matches && $matches[1]){
			foreach($matches[1] as $key=>$value){
				if(substr($value,0,2) == '__' || substr($value,0,5) == 'html_' || substr($value,0,3) == 'ap_'){
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
	 * 执行SQL
	 * @参数 $sql 要执行的SQL
	 * @参数 $isfile 是否文件，有判断布尔值，如果是数组，会覆盖第三个参数
	 * @参数 $breaktables 忽略的表
	**/
	public function loadsql($sql='',$isfile=false,$breaktables=array())
	{
		if($isfile && is_bool($isfile) && !file_exists($sql)){
			return false;
		}
		if($isfile && is_bool($isfile)){
			$sql = file_get_contents($sql);
		}
		if($isfile && is_array($isfile)){
			$breaktables = $isfile;
		}
		$sql = str_replace("\r","\n",$sql);
		if($this->db->prefix != 'qinggan_'){
			$sql = str_replace("qinggan_",$this->db->prefix,$sql);
		}
		$ret = array();
		$num = 0;
		foreach(explode(";\n", trim($sql)) as $query){
			$queries = explode("\n", trim($query));
			foreach($queries as $query){
				$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
			}
			$num++;
		}
		foreach($ret as $query){
			$query = trim($query);
			if(!$query){
				continue;
			}
			if($breaktables && count($breaktables)>0 && is_array($breaktables) && strpos(strtolower($query),'create table') !== false){
				foreach($breaktables as $k=>$v){
					if(strpos($query,$v) !== true){
						$this->db->query($sql);
					}
				}
			}else{
				$this->db->query($query);
			}
		}
		return true;
	}
}