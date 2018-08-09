<?php
/**
 * 字段管理器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年05月18日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class fields_model extends fields_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 读取 xml/fields/ 下的一条 XML 字段配置信息
	 * @参数 $id 标识
	**/
	public function default_one($id)
	{
		$filename = $this->dir_data.'xml/fields/'.$id.'.xml';
		if(!file_exists($filename)){
			return false;
		}
		return $this->lib('xml')->read($filename);
	}

	/**
	 * 读取 xml/fields/ 下的全部 XML 文件信息
	**/
	public function default_all()
	{
		$flist = $this->lib('file')->ls($this->dir_data.'xml/fields/');
		if(!$flist){
			return false;
		}
		$rslist = array();
		foreach($flist as $key=>$value){
			$rs = $this->lib('xml')->read($value);
			$rslist[$rs['identifier']] = $rs;
		}
		ksort($rslist);
		return $rslist;
	}

	/**
	 * 删除常用字段
	 * @参数 $id 字段标识
	**/
	public function default_delete($id)
	{
		$filename = $this->dir_data.'xml/fields/'.$id.'.xml';
		if(file_exists($filename)){
			$this->lib('file')->rm($filename);
		}
		return true;
	}

	/**
	 * 保存常用字段
	 * @参数 $data 要保存的数据信息
	**/
	public function default_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['ext'] && is_string($data['ext'])){
			$data['ext'] = unserialize($data['ext']);
		}
		$filename = $this->dir_data.'xml/fields/'.$data['identifier'].'.xml';
		$this->lib('xml')->save($data,$filename);
		return true;
	}
}
