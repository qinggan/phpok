<?php
/**
 * 主题内容管理
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月22日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_model extends list_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function update_sort($id,$sort=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET sort='".$sort."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function biz_attr_save($data)
	{
		return $this->db->insert_array($data,'list_attr');
	}

	public function biz_attr_update($data,$id)
	{
		$aids = array();
		foreach($data as $key=>$value){
			$aids[] = $value['aid'];
		}
		$aids = array_unique($aids);
		$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid='".$id."' AND aid IN(".implode(",",$aids).")";
		$this->db->query($sql);
		foreach($data as $key=>$value){
			$value['tid'] = $id;
			$this->db->insert_array($value,'list_attr');
		}
		return true;
	}

	public function biz_attr_delete($tid,$aid=0)
	{
		$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid='".$tid."'";
		if($aid){
			$sql .= " AND aid='".$aid."'";
		}
		return $this->db->query($sql);
	}

	public function biz_all($ids='')
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_biz WHERE id IN(".$ids.")";
		return $this->db->get_all($sql,'id');
	}


	public function ext_catelist($id)
	{
		$sql = "SELECT cate_id FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[] = $value['cate_id'];
		}
		return $rslist;
	}

	public function admin_list_rs($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list_admin WHERE tid='".$id."'";
		return $this->db->get_one($sql);
	}

	//复制一个主题
	public function copy_id($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		unset($rs["id"]);
		$rs['dateline'] = $this->time;
		$insert_id = $this->db->insert_array($rs,"list");
		if(!$insert_id){
			return false;
		}
		if($rs["module_id"]){
			$m_id = $rs["module_id"];
			$sql = "SELECT * FROM ".$this->db->prefix."list_".$m_id." WHERE id='".$id."'";
			$ext_rs = $this->db->get_one($sql);
			if($ext_rs){
				$ext_rs["id"] = $insert_id;
				$this->save_ext($ext_rs,$m_id);
			}
		}
		//绑定扩展分类
		$sql = "SELECT * FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$catelist = $this->db->get_all($sql);
		if($catelist){
			foreach($catelist as $key=>$value){
				$tmp = array('id'=>$insert_id,'cate_id'=>$value['cate_id']);
				$this->db->insert_array($tmp,'list_cate','replace');
			}
		}
		//绑定价格
		$sql = "SELECT * FROM ".$this->db->prefix."list_biz WHERE id='".$id."'";
		$tmp = $this->db->get_one($sql);
		if($tmp){
			$tmp['id'] = $insert_id;
			$this->db->insert_array($tmp,'list_biz','replace');
		}
		//绑定属性
		$sql = "SELECT * FROM ".$this->db->prefix."list_attr WHERE tid='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$value['tid'] = $insert_id;
				unset($value['id']);
				$this->db->insert_array($value,'list_attr','replace');
			}
		}
		return $insert_id;
	}

	public function list_cate_add($cateid,$tid)
	{
		$sql = "SELECT p.cate_multiple FROM ".$this->db->prefix."list l ";
		$sql.= "LEFT JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) ";
		$sql.= "WHERE l.id='".$tid."'";
		$rs = $this->db->get_one($sql);
		$multiple = false;
		if($rs && $rs['cate_multiple']){
			$multiple = true;
		}
		if(!$multiple){
			$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$tid."'";
			$this->db->query($sql);
		}
		$sql = "REPLACE INTO ".$this->db->prefix."list_cate(id,cate_id) VALUES('".$tid."','".$cateid."')";
		return $this->db->query($sql);
	}

	public function list_cate_delete($cateid,$id)
	{
		$sql = "SELECT cate_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if($rs && $rs['cate_id'] == $cateid){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$id."' AND cate_id='".$cateid."'";
		$this->db->query($sql);
		return true;
	}

	

	public function catelist($ids)
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_cate WHERE id IN(".$ids.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['id']][] = $value['cate_id'];
		}
		return $rslist;
	}

	public function fields_all($mid=0,$id=0,$ext='')
	{
		$f1 = $this->db->list_fields('list');
		$f2 = $this->db->list_fields('list_attr');
		$f3 = $this->db->list_fields('list_biz');
		$f4 = $this->db->list_fields('list_cate');
		$fields = array_merge($f1,$f2,$f3,$f4);
		if($mid){
			$f5 = $this->db->list_fields("list_".$mid);
			$fields = array_merge($fields,$f5);
		}
		if($id){
			$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE ftype='list-".$id."'";
			$f6 = $this->db->get_all($sql);
			if($f6){
				$tmp = array();
				foreach($f6 as $key=>$value){
					$tmp[] = $value['identifier'];
				}
				$fields = array_merge($fields,$tmp);
			}
		}
		if($ext && is_array($ext)){
			$f7 = array();
			foreach($ext as $key=>$value){
				$f7[] = $key;
			}
			$fields = array_merge($fields,$f7);
		}
		return $fields;
	}

	/**
	 * 独立表列表数据
	 * @参数 $mid 模块ID
	 * @参数 $condition 查询条件
	 * @参数 $offset 起始位置
	 * @参数 $psize 查询数量
	 * @参数 $orderby 排序
	**/
	public function single_list($mid,$condition='',$offset=0,$psize=30,$orderby='',$field='*')
	{
		$sql = "SELECT ".$field." FROM ".$this->db->prefix.$mid." ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if(!$orderby){
			$orderby = 'id DESC';
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)>0){
			$sql .= " LIMIT ".intval($offset).",".intval($psize);
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$m_rs = $this->lib('ext')->module_fields($mid);
		if($m_rs){
			foreach($rslist as $key=>$value){
				foreach($value as $k=>$v){
					if($m_rs[$k]){
						$value[$k] = $this->lib('ext')->content_format($m_rs[$k],$v);
					}
				}
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}


	/**
	 * 获取主题列表
	 * @参数 $mid，模块ID，数值
	 * @参数 $condition，查询条件
	 * @参数 $offset，查询起始位置，默认是0
	 * @参数 $psize，查询条数，默认是0，表示不限制
	 * @参数 $orderby，排序
	 * @返回 数组，查询结果集，扩展字段内容已经格式化
	**/
	public function get_list($mid,$condition="",$offset=0,$psize=0,$orderby="")
	{
		if(!$mid){
			return false;
		}
		if(!$orderby){
			$orderby = " l.sort DESC,l.dateline DESC,l.id DESC ";
		}
		$fields_list = $this->db->list_fields('list');
		$field = "l.id";
		if($this->is_user || ($condition && strpos($condition,'u.') !== false) || strpos($orderby,'u.') !== false){
			$field .= ",u.user _user";
		}
		foreach($fields_list as $key=>$value){
			if($value == 'id' || !$value){
				continue;
			}
			$field .= ",l.".$value;
		}
		$module = $this->model('module')->get_one($mid);
		if($module && $module['layout']){
			$tmp = explode(",",$module['layout']);
			$field_ext = $this->ext_fields($mid,'ext',"identifier IN('".implode("','",$tmp)."')");
			if($field_ext){
				$field .= ",".$field_ext;
			}
		}
		
		if($this->is_biz || ($condition && strpos($condition,'b.') !== false) || strpos($orderby,'b.') !== false){
			$field.= ",b.price,b.currency_id,b.weight,b.volume,b.unit";
		}
		$linksql = " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id AND l.project_id=ext.project_id) ";
		if($this->is_user || ($condition && strpos($condition,'u.') !== false) || strpos($orderby,'u.') !== false){
			$linksql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id AND u.status=1) ";
		}
		if($this->is_biz || ($condition && strpos($condition,'b.') !== false) || strpos($orderby,'b.') !== false){
			$linksql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
		}
		if($this->multiple_cate || ($condition && strpos($condition,'lc.') !== false) || strpos($orderby,'lc.') !== false){
			$linksql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		}
		$sql  = "SELECT ".$field." FROM ".$this->db->prefix."list l ".$linksql;
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY ".$orderby;
		if($psize && is_numeric($psize) && intval($psize)){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$cid_list = array();
		foreach($rslist as $key=>$value){
			$cid_list[$value["cate_id"]] = $value["cate_id"];
		}
		$m_rs = $this->lib('ext')->module_fields($mid);
		if($m_rs){
			foreach($rslist as $key=>$value){
				foreach($value as $k=>$v){
					if($m_rs[$k]){
						$value[$k] = $this->lib('ext')->content_format($m_rs[$k],$v);
					}
				}
				$rslist[$key] = $value;
			}
		}
		$cid_string = implode(",",$cid_list);
		if($cid_string){
			$catelist = $this->lib('ext')->cate_list($cid_string);
			foreach($rslist AS $key=>$value){
				if($value["cate_id"]){
					$value["cate_id"] = $catelist[$value["cate_id"]];
					$rslist[$key] = $value;
				}
			}
		}
		return $rslist;
	}
}
