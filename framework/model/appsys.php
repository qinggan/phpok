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
	public function __construct()
	{
		parent::model();
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
		if(!is_file($this->dir_data.'xml/appall.xml')){
			return false;
		}
		$rslist = $this->lib('xml')->read($this->dir_data.'xml/appall.xml',true);
		if(!$rslist){
			return false;
		}
		if(!is_file($this->dir_data.'xml/app.xml')){
			return $rslist;
		}
		$install = $this->installed();
		if(!$install){
			return $rslist;
		}
		foreach($rslist as $key=>$value){
			if($install[$key]){
				$rslist[$key]['installed'] = true;
			}
		}
		return $rslist;
	}

	/**
	 * 获取已安装的应用
	**/
	public function installed()
	{
		$install = $this->lib('xml')->read($this->dir_data.'xml/app.xml',true);
		if(!$install){
			return false;
		}
		foreach($install as $key=>$value){
			if(!is_file($this->dir_app.$key.'/config.xml')){
				continue;
			}
			$info = $this->lib('xml')->read($this->dir_app.$key.'/config.xml',true);
			if($info){
				$install[$key] = array_merge($info,$value);
			}
		}
		return $install;
	}

	public function get_one($id)
	{
		if(!$id){
			return false;
		}
		$all = $this->installed();
		if(!$all){
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
		$list = array();
		foreach($all as $key=>$value){
			if($key == $id){
				continue;
			}
			$tmparray = array('title'=>$value['title']);
			$tmparray['status']['admin'] = $value['status']['admin'];
			$tmparray['status']['api'] = $value['status']['api'];
			$tmparray['status']['www'] = $value['status']['www'];
			$list[$key] = $tmparray;
		}
		if(!$list){
			$this->lib('file')->rm($this->dir_data.'xml/app.xml');
		}else{
			$this->lib('xml')->save($list,$this->dir_data.'xml/app.xml');
		}
		$this->lib('file')->rm($this->dir_app.$id,'folder');
		return true;
	}

	public function install($id)
	{
		//检查Config文件
		$info = array();
		if(is_file($this->dir_app.$id.'/config.xml')){
			$info = $this->lib('xml')->read($this->dir_app.$id.'/config.xml',true);
		}
		$array = array('title'=>($info['title'] ? $info['title'] : $id));
		if($info['status']){
			$array['status']['www'] = $info['status']['www'];
			$array['status']['admin'] = $info['status']['admin'];
			$array['status']['api'] = $info['status']['api'];
		}
		$all = $this->installed();
		if(!$all){
			$all = array($id=>$array);
		}
		$all[$id] = $array;
		$this->lib('xml')->save($all,$this->dir_data.'xml/app.xml');
		return true;
	}
}
