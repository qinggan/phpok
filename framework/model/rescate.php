<?php
/**
 * 资源分类
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年03月21日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rescate_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_all()
	{
		$gdall = $this->model('gd')->get_all('id');
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY id ASC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$tmplist = $this->model('gateway')->all('object-storage');
		$oss = array();
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$oss[$value['id']] = $value['title'];
			}
		}
		foreach($rslist as $key=>$value){
			$value['etype_title'] = ($value['etype'] && $oss && $oss[$value['etype']]) ? $oss[$value['etype']] : P_Lang('本地存储');
			if(!$value['gdall'] && $value['gdtypes']){
				$tmp = explode(",",$value['gdtypes']);
				$tmplist = array();
				foreach($tmp as $k=>$v){
					if($v && trim($v) && $gdall[trim($v)]){
						$tmplist[] = $gdall[trim($v)]['title'] ? $gdall[trim($v)]['title'] : $gdall[trim($v)]['title']['identifier'];
					}
				}
				$value['gdtypes_title'] = implode(" / ",$tmplist);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function get_one($id='')
	{
		if(!$id){
			return $this->get_default();
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE is_default=1";
		return $this->db->get_one($sql);
	}

	/**
	 * 获取分类信息，分类ID内容不存在时读默认分类
	 * @参数 $id 分类ID，为空读默认分类
	 * @返回 false 或 array
	**/
	public function cate_info($id='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE is_default=1";
		if($id && intval($id)>0){
			$sql .= " OR id='".intval($id)."'";
		}
		$sql .= " ORDER BY is_default ASC LIMIT 1";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得附件下的全部分类
	 * @返回 数组
	**/
	public function cate_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY id ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 获取存储类别样式
	**/
	public function etypes_all()
	{
		
		$list = $this->lib('file')->ls($this->dir_gateway.'object-storage/');
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$file = $value.'/config.xml';
			if(!is_file($file)){
				continue;
			}
			$info = $this->lib('xml')->read($file);
			if(!$info){
				continue;
			}
			$rslist[basename($value)] = $info;
		}
		if(!$rslist || count($rslist)<1){
			return false;
		}
		return $rslist;
	}

	public function etypes_one($id)
	{
		//
	}
}