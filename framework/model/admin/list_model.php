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
		$sql = "SELECT id,aid,vid FROM ".$this->db->prefix."list_attr WHERE tid='".$id."'";
		$rslist = $this->db->get_all($sql,'id');
		if($rslist){
			$u_list = array();
			foreach($data as $key=>$value){
				foreach($rslist as $k=>$v){
					if($v['aid'] == $value['aid'] && $v['vid'] == $value['vid']){
						$data[$key]['id'] = $v['id'];
						$u_list[] = $v['id'];
					}
				}
			}
			if($u_list){
				$u_list = array_unique($u_list);
				$all_ids = array_keys($rslist);
				$diff = array_diff($all_ids,$u_list);
			}else{
				$diff = array_keys($rslist);
			}
			if($diff){
				$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE id IN(".implode(",",$diff).")";
				$this->db->query($sql);
			}
		}
		foreach($data as $key=>$value){
			if($value['id']){
				$tmpid = $value['id'];
				unset($value['id']);
				$this->db->update_array($value,'list_attr',array('id'=>$tmpid));
				unset($tmpid);
			}else{
				$value['tid'] = $id;
				$this->db->insert_array($value,'list_attr');
			}
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

	public function pending_info($site_id=0)
	{
		$sql = "SELECT count(l.id) total,l.project_id pid FROM ".$this->db->prefix."list l ";
		$sql.= " WHERE l.status!=1 AND l.site_id='".$site_id."'";
		$sql.= " GROUP BY l.project_id";
		$rslist = $this->db->get_all($sql,'pid');
		if(!$rslist){
			return false;
		}
		$idlist = array_keys($rslist);
		$sql = "SELECT title,id,parent_id FROM ".$this->db->prefix."project WHERE id IN(".implode(",",$idlist).")";
		$tlist = $this->db->get_all($sql);
		if($tlist){
			foreach($tlist as $key=>$value){
				$rslist[$value['id']]['title'] = $value['title'];
				$rslist[$value['id']]['parent_id'] = $value['parent_id'];
			}
		}
		foreach($rslist as $key=>$value){
			if(!$value['parent_id']){
				$tmp_total = 0;
				foreach($rslist as $k=>$v){
					if($v['parent_id'] && $v['parent_id'] == $value['pid']){
						$tmp_total += $v['total'];
					}
				}
				if($tmp_total){
					$value['total'] = $value['total'] + $tmp_total;
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function status_all($site_id=0)
	{
		$sql = "SELECT count(id) as total,project_id as id FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' GROUP BY project_id";
		$rslist = $this->db->get_all($sql,'id');
		if($rslist){
			$ids = array_keys($rslist);
			$sql = "SELECT id,title FROM ".$this->db->prefix."project WHERE id IN(".implode(",",$ids).") AND status=1";
			$tmplist = $this->db->get_all($sql,'id');
			if($tmplist){
				foreach($rslist as $key=>$value){
					if(!$tmplist[$value['id']]){
						unset($rslist[$key]);
						continue;
					}
					$value['title'] = $tmplist[$value['id']]['title'];
					$rslist[$key] = $value;
				}
			}
		}
		return $rslist;
	}
}
