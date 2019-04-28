<?php
/**
 * APP管理工具
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月05日
**/

class appsys_model_base extends phpok_model
{
	private $iList;
	private $local_all;
	private $cacheid = '';
	public function __construct()
	{
		parent::model();
		$this->cacheid = $this->cache->id('appsys_cache.php');
	}

	/**
	 * 获取或保存远程配置信息
	 * @参数 $data 数组，要保存的信息，如果为空表示读远程配置信息
	**/
	public function server($data='')
	{
		if(isset($data) && is_array($data)){
			$this->lib('xml')->save($data,$this->dir_data.'xml/app_setting.xml');
			return true;
		}
		if(!is_file($this->dir_data.'xml/app_setting.xml')){
			return false;
		}
		return $this->lib('xml')->read($this->dir_data.'xml/app_setting.xml');
	}

	/**
	 * 读取系统全部未安装的模型
	**/
	public function get_all()
	{
		$rslist = array();
		if(is_file($this->dir_data.'xml/appall.xml')){
			$tmplist = $this->lib('xml')->read($this->dir_data.'xml/appall.xml',true);
			if($tmplist){
				$rslist = $tmplist;
			}
		}
		$install = $this->local_list();
		if($install){
			foreach($rslist as $key=>$value){
				if($install[$key]){
					if($install[$key]['installed']){
						$rslist[$key]['installed'] = true;
					}
					$rslist[$key]['local'] = true;
				}
			}
			foreach($install as $key=>$value){
				if(!$rslist[$key]){
					$value['local'] = true;
					$rslist[$key] = $value;
				}
			}
		}
		return $rslist;
	}

	public function local_list()
	{
		if($this->local_all){
			return $this->local_all;
		}
		if($this->cacheid){
			$this->local_all = $this->cache->get($this->cacheid);
			if($this->local_all){
				return $this->local_all;
			}
		}
		$list = $this->lib('file')->ls($this->dir_app);
		if(!$list){
			return false;
		}
		$install = array();
		foreach($list as $key=>$value){
			if(!is_dir($value)){
				continue;
			}
			if(!is_file($value.'/config.xml')){
				continue;
			}
			$info = $this->lib('xml')->read($value.'/config.xml',true);
			$tmpid = basename($value);
			$install[$tmpid] = $info;
		}
		$this->local_all = $install;
		if($this->cacheid){
			$this->cache->save($this->cacheid,$install);
		}
		return $this->local_all;
	}

	/**
	 * 获取已安装的应用
	**/
	public function installed()
	{
		if($this->iList){
			return $this->iList;
		}
		$local_all = $this->local_list();
		if(!$local_all){
			return false;
		}
		$list = array();
		foreach($local_all as $key=>$value){
			if($value['installed']){
				$list[$key] = $value;
				continue;
			}
		}
		$this->iList = $list;
		return $this->iList;
	}

	public function get_one($id)
	{
		if(!$id){
			return false;
		}
		$all = $this->local_list();
		if(!$all){
			if(is_file($this->dir_app.$id.'/config.xml')){
				return $this->lib('xml')->read($this->dir_app.$id.'/config.xml',true);
			}
			return false;
		}
		if($all[$id]){
			return $all[$id];
		}
		return false;
	}

	public function uninstall($id)
	{
		if(!$id){
			return false;
		}
		$all = $this->installed();
		if(!$all){
			return false;
		}
		//变成未安装模式
		if(is_file($this->dir_app.$id.'/config.xml')){
			$info = $this->lib('xml')->read($this->dir_app.$id.'/config.xml',true);
			if(isset($info['installed'])){
				unset($info['installed']);
			}
			$this->lib('xml')->save($info,$this->dir_app.$id.'/config.xml');
		}
		if($this->cacheid){
			$this->cache->delete($this->cacheid);
		}
		return true;
	}

	public function install($id)
	{
		//检查Config文件
		$info = array();
		if(is_file($this->dir_app.$id.'/config.xml')){
			$info = $this->lib('xml')->read($this->dir_app.$id.'/config.xml',true);
		}
		$info['installed'] = true;
		$this->lib('xml')->save($info,$this->dir_app.$id.'/config.xml');
		if($this->cacheid){
			$this->cache->delete($this->cacheid);
		}
		return true;
	}

	public function backup_all($is_group=false)
	{
		$list = $this->lib('file')->ls($this->dir_data.'zip');
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			if(!is_file($value)){
				continue;
			}
			$tmp = basename($value);
			if(substr(strtolower($tmp),-3) != 'zip'){
				continue;
			}
			$date = date("Y-m-d",filemtime($value));
			$tmp = substr($tmp,0,-4);
			$tmplist = explode("-",$tmp);
			$tmpid = $tmp;
			if(count($tmplist) == 1){
				$tmpid = $tmp;
			}elseif(count($tmplist) == 2){
				$tmpid = $tmplist[0];
			}else{
				$last = end($tmplist);
				if(is_numeric($last)){
					$tmpid = substr($tmp,0,-(strlen($last)+1));
					$date = substr($last,0,4).'-'.substr($last,4,2).'-'.substr($last,-2);
				}
			}
			$array = array('zip'=>basename($value),'date'=>$date);
			if($is_group){
				if(!$rslist[$tmpid]){
					$rslist[$tmpid] = array(0=>$array);
				}else{
					$rslist[$tmpid][] = $array;
				}
			}else{
				$array['identifier'] = $tmpid;
				$array['id'] = $tmp;
				$rslist[] = $array;
			}
		}
		return $rslist;
	}
}
