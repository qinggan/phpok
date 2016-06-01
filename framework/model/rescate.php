<?php
/*****************************************************************************************
	文件： {phpok}/model/rescate.php
	备注： 资源分类管理工具
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月25日 00时02分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rescate_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY id ASC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gd ORDER BY id ASC";
		$gdlist = $this->db->get_all($sql,'id');
		if(!$gdlist){
			$gdlist = array();
		}
		foreach($rslist as $key=>$value){
			$gds = false;
			if($value['gdall']){
				foreach($gdlist as $k=>$v){
					$gds[] = $v['identifier'];
				}
			}else{
				$types = $value['gdtypes'] ? explode(',',$value['gdtypes']) : array();
				foreach($types as $k=>$v){
					if($gdlist[$v]){
						$gds[] = $gdlist[$v]['identifier'];
					}
				}
			}
			$value['gdtypes'] = $gds ? implode('/',$gds) : '';
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
}

?>