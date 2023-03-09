<?php
/**
 * 读取主题内容
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class content_model_base extends phpok_model
{

	private $error_info = '';
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::model();
	}


	/**
	 * 取得单个主题信息
	 * @参数 $id 主题ID或标识
	 * @参数 $status 是否已审核
	**/
	public function get_one($id,$status=true,$site_id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql  = "SELECT * FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND ";
		$sql .= is_numeric($id) ? " id='".$id."' " : " identifier='".$id."' ";
		if($status){
			$sql.= " AND status=1 ";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_biz WHERE id='".$rs['id']."'";
		$biz_rs = $this->db->get_one($sql);
		if($biz_rs){
			foreach($biz_rs as $key=>$value){
				$rs[$key] = $value;
			}
			$rs['wholesale'] = $this->model('wholesale')->all($rs['id']);
			unset($biz_rs);
		}
		if($rs['module_id']){
			$sql = "SELECT * FROM ".$this->db->prefix."list_".$rs['module_id']." WHERE id='".$rs['id']."'";
			$ext_rs = $this->db->get_one($sql);
			if($ext_rs){
				$rs = array_merge($ext_rs,$rs);
			}
		}
		//读取产品的属性
		$tmplist = $this->model('stock')->val_all($rs['id']);
		if($tmplist){
			//产品属性价格
			$min = $max = 0;
			foreach($tmplist as $key=>$value){
				if(!$min){
					$min = $value['price'];
				}
				if(!$max){
					$max = $value['price'];
				}
				if($min>$value['price']){
					$min = $value['price'];
				}
				if($max<$value['price']){
					$max = $value['price'];
				}
			}
			//价格范围
			if($min != $max){
				$rs['min-max'] = array('min'=>$min,'max'=>$max);
			}
			$rs['attrs'] = $tmplist;
			$keys = array_keys($tmplist);
			$ids = implode(",",$keys);
			$list = explode(",",$ids);
			$list = array_unique($list);
			$ids = implode(",",$list);
			$sql  = " SELECT av.*,a.title group_title FROM ".$this->db->prefix."attr_values av ";
			$sql .= " LEFT JOIN ".$this->db->prefix."attr a ON(av.aid=a.id) WHERE av.id IN(".$ids.") ORDER BY av.taxis ASC,a.id ASC";
			$attrlist = $this->db->get_all($sql);
			if($attrlist){
				$attrs = array();
				foreach($attrlist as $key=>$value){
					if(!$attrs[$value['aid']]){
						$attrs[$value['aid']] = array();
						$attrs[$value['aid']]['title'] = $value['group_title'];
						$attrs[$value['aid']]['id'] = $value['aid'];
						$attrs[$value['aid']]['rslist'] = array();
					}
					$attrs[$value['aid']]['rslist'][] = $value;
				}
				$rs['attrlist'] = $attrs;
			}

		}
		$ext = $this->model('ext')->get_all('list-'.$rs['id'],false);
		if($ext){
			$rs = array_merge($rs,$ext);
		}
		//批发价

		return $rs;
	}

	/**
	 * 通过主题ID获取对应的模块ID
	 * @参数 $id 主题ID
	**/
	public function get_mid($id)
	{
		$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs["module_id"];
	}

	/**
	 * 获取扩展字段并格式化内容
	 * @参数 $mid 模块ID
	 * @参数 $ids 主题，多个主题用英文逗号隔开
	 * @参数
	**/
	public function ext_list($mid,$ids)
	{
		if(!$mid || !$ids){
			return false;
		}
		$flist = $this->model("module")->fields_all($mid);
		if(!$flist){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$ids.")";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			foreach($flist as $k=>$v){
				if($value[$v["identifier"]]){
					$v["content"] = $value[$v["identifier"]];
					$value[$v["identifier"]] = $this->lib('ext')->content_format($v);
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function price($id,$attr='',$qty=1)
	{
		if(is_numeric($id)){
			$rs = $this->get_one($id);
			$id = $rs['id'];
		}else{
			$rs = $id;
			$id = $rs['id'];
		}
		if(!$rs){
			$this->error_info('产品信息不存在');
			return false;
		}
		if($attr){
			$attrlist = explode(",",$attr);
			sort($attrlist);
			$attr = implode(",",$attrlist);
		}
		if($attr && $rs['attrs'] && $rs['attrs'][$attr]){
			if(!$rs['attrs'][$attr]['qty'] || $rs['attrs'][$attr]['qty']<$qty){
				$stock = $rs['attrs'][$attr]['qty'];
				$this->error_info('库存不足');
				return false;
			}
			$price = $rs['price'];
			if($rs['attrs'][$attr]['price'] > $price){
				$price = $rs['attrs'][$attr]['price'];
			}
			$stock = $rs['attrs'][$attr]['qty'];
			if($rs['wholesale']){
				foreach($rs['wholesale'] as $key=>$value){
					if($qty>=$value['qty']){
						$price = $value['price'];
					}
				}
			}
			$price_format = price_format($price,$rs['currency_id'],$this->site['currency_id']);
			$price_val = price_format_val($price,$rs['currency_id'],$this->site['currency_id']);
			$data = array();
			$data['price'] = $price;
			$data['price_val'] = $price_val;
			$data['price_format'] = $price_format;
			$data['currency_id'] = $rs['currency_id'];
			$data['qty'] = $stock;
			return $data;
		}
		if(!$attr){
			$price = $rs['price'];
			$price_format = price_format($price,$rs['currency_id'],$this->site['currency_id']);
			$price_val = price_format_val($price,$rs['currency_id'],$this->site['currency_id']);
			$data = array();
			$data['price'] = $price;
			$data['price_val'] = $price_val;
			$data['price_format'] = $price_format;
			$data['currency_id'] = $rs['currency_id'];
			$data['qty'] = $rs['qty'];
			return $data;
		}
		$this->error_info(true);
		return false;
	}

	public function error_info($error='')
	{
		if($error){
			$this->error_info = $error;
		}
		return $this->error_info;
	}


}