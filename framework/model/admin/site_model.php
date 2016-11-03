<?php
/**
 * 站点信息管理
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年09月08日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class site_model extends site_model_base
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 保存站点数据
	 * @参数 $data 数组，要保存的站点数据
	 * @参数 $id 站点ID，留空或为0表示创建新站点
	 * @返回 true/false/站点ID
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		//检查表字段
		$fields = $this->db->list_fields('site');
		$xmldata = false;
		foreach($data as $key=>$value){
			if(!in_array($key,$fields)){
				if(!$xmldata){
					$xmldata = array();
				}
				$xmldata[$key] = $value;
				unset($data[$key]);
			}
		}
		if($id){
			$this->db->update_array($data,"site",array("id"=>$id));
			if($xmldata){
				$this->lib('xml')->save($xmldata,$this->dir_root.'data/xml/site_'.$id.'.xml');
			}
			return true;
		}else{
			$insert_id = $this->db->insert_array($data,"site");
			if($insert_id && $xmldata){
				$this->lib('xml')->save($xmldata,$this->dir_root.'data/xml/site_'.$insert_id.'.xml');
			}
			return $insert_id;
		}
	}

	/**
	 * 更新域名
	 * @参数 $domain 域名
	 * @参数 $id site_domain表的主键ID
	 * @返回 true/false
	**/
	public function domain_update($domain,$id)
	{
		if(!$domain || !$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."site_domain SET domain='".$domain."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 域名添加
	 * @参数 $domain 域名
	 * @参数 $site_id 站点ID
	 * @返回 true/false
	**/
	public function domain_add($domain,$site_id)
	{
		if(!$domain || !$site_id){
			return false;
		}
		$sql = "INSERT INTO ".$this->db->prefix."site_domain(site_id,domain) VALUES('".$site_id."','".$domain."')";
		return $this->db->insert($sql);
	}

	/**
	 * 删除域名
	 * @参数 $id site_domain表的主键ID
	 * @返回 true/false
	**/
	public function domain_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."site_domain WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 存储全局变量扩展配置
	 * @参数 $data 数组，全局变量信息
	 * @参数 $id all表中的主键ID
	**/
	public function all_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}		
		if($id){
			return $this->db->update_array($data,"all",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"all");
		}
	}

	/**
	 * 删除全局扩展
	 * @参数 $id all表中的主键ID
	 * @返回 true/false
	**/
	public function ext_delete($id)
	{
		if(!$id) {
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."all WHERE id='".$id."'";
		$this->db->query($sql);
		return $this->_ext_delete('all-'.$id);
	}

	/**
	 * 删除全局扩展中的扩展模块字段，仅限私有使用，不允许外调
	 * @参数 $module 模块标识
	 * @返回 true/false
	**/
	private function _ext_delete($module='')
	{
		if(!$module){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."ext WHERE module='".$module."'";
		$rslist = $this->db->get_all($sql,"id");
		if($rslist){
			$id_array = array_keys($rslist);
			$ids = implode(",",$id_array);
			$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id IN(".$ids.")";
			$this->db->query($sql);
			$sql = "DELETE FROM ".$this->db->prefix."ext WHERE id IN(".$ids.")";
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 删除站点信息，此方法请慎用，删除操作没有任何判断及备份操作
	 * @参数 $id 站点ID
	 * @返回 true
	**/
	public function site_delete($id)
	{
		//删除站点全局扩展字段
		$sql = "SELECT id FROM ".$this->db->prefix."all WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$this->ext_delete($value['id']);
			}
		}
		//删除项目全局扩展字段
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$tmpids = array();
			foreach($tmplist as $key=>$value){
				$this->_ext_delete('project-'.$value['id']);
				$tmpids[] = 'p'.$value['id'];
			}
			$tmpids = implode("','",$tmpids);
			$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id IN('".$tmpids."')";
			$this->db->query($sql);
		}
		//删除分类下的扩展
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$tmpids = array();
			foreach($tmplist as $key=>$value){
				$this->_ext_delete('cate-'.$value['id']);
				$tmpids[] = 'c'.$value['id'];
			}
			$tmpids = implode("','",$tmpids);
			$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id IN('".$tmpids."')";
			$this->db->query($sql);
		}
		//删除尺码属性
		$sql = "SELECT id FROM ".$this->db->prefix."attr WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."attr_values WHERE aid='".$value['id']."'";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."attr WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除分类
		$sql = "DELETE FROM ".$this->db->prefix."cate WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除Email模板
		$sql = "DELETE FROM ".$this->db->prefix."email WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除物流
		$sql = "DELETE FROM ".$this->db->prefix."express WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除运费模板
		$sql = "SELECT id FROM ".$this->db->prefix."freight WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$tmpids = array();
			foreach($tmplist as $key=>$value){
				$sql = "SELECT id FROM ".$this->db->prefix."freight_zone WHERE fid='".$value['id']."'";
				$tmp = $this->db->get_all($sql,'id');
				if($tmp){
					$ids = array_keys($tmp);
					$ids = implode(",",$ids);
					$sql = "DELETE FROM ".$this->db->prefix."freight_price WHERE zid IN(".$ids.")";
					$this->db->query($sql);
				}
				$tmpids[] = $value['id'];
			}
			$tmpids = implode(",",$tmpids);
			$sql = "DELETE FROM ".$this->db->prefix."freight_zone WHERE fid IN(".$tmpids.")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."freight WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除第三方网关
		$sql = "DELETE FROM ".$this->db->prefix."gateway WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除支付组
		$sql = "SELECT id FROM ".$this->db->prefix."payment_group WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."payment WHERE gid='".$value['id']."'";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."payment_group WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除数据调用
		$sql = "DELETE FROM ".$this->db->prefix."phpok WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除项目信息
		$sql = "SELECT id,module FROM ".$this->db->prefix."project WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$tmpids = array();
			foreach($tmplist as $key=>$value){
				if($value['module']){
					$sql = "DELETE FROM ".$this->db->prefix."list_".$value['module']." WHERE site_id='".$id."'";
					$this->db->query($sql);
				}
				$tmpids[] = $value['id'];
			}
			$tmpids = implode(",",$tmpids);
			//删除相应的权限
			$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE pid IN(".$tmpids.")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."project WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除回复
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE id IN(SELECT id FROM ".$this->db->prefix."list WHERE site_id='".$id."')";
		$this->db->query($sql);
		//删除站点域名
		$sql = "DELETE FROM ".$this->db->prefix."site_domain WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除系统菜单
		$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除Tag及Tag_stat信息
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE tag_id IN(SELECT id FROM ".$this->db->prefix."tag WHERE site_id=".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."tag WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除财富操作
		$sql = "SELECT id FROM ".$this->db->prefix."wealth WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."wealth_info WHERE wid='".$value['id']."'";
				$this->db->query($sql);
				$sql = "DELETE FROM ".$this->db->prefix."wealth_log WHERE wid='".$value['id']."'";
				$this->db->query($sql);
				$sql = "DELETE FROM ".$this->db->prefix."wealth_rule WHERE wid='".$value['id']."'";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."wealth WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除扩展分类
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id IN(SELECT id FROM ".$this->db->prefix."list WHERE site_id='".$id."')";
		$this->db->query($sql);
		//删除list表中的数据
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE site_id='".$id."'";
		$this->db->query($sql);
		//删除站点信息
		$sql = "DELETE FROM ".$this->db->prefix."site WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 设置默认站点
	 * @参数 $id 要设为默认的站点ID
	 * @返回 true/false
	**/
	public function set_default($id)
	{
		if(!$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."site SET is_default=0";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."site SET is_default=1 WHERE id=".intval($id);
		$this->db->query($sql);
		return true;
	}

	/**
	 * 设置是否手机专用域名
	 * @参数 $id site_domain表中的主键ID
	 * @参数 $act 激活
	 * @返回 true/false
	**/
	public function set_mobile($id=0,$act=1)
	{
		$this->clear_mobile_domain();
		if($id && $act){
			$sql = "UPDATE ".$this->db->prefix."site_domain SET is_mobile=1 WHERE site_id=".$this->site_id." AND id='".$id."'";
			return $this->db->query($sql);
		}
		return true;
	}

	/**
	 * 清空手机专用域名
	**/
	public function clear_mobile_domain()
	{
		$sql = "UPDATE ".$this->db->prefix."site_domain SET is_mobile=0 WHERE site_id=".$this->site_id;
		return $this->db->query($sql);
	}

	/**
	 * 订单价格方案
	 * @参数 $data 数组，多维
	 * @参数 $id 唯一标识串，用于变更
	**/
	public function price_status_update($data,$id=0)
	{
		if(!$id || !$data){
			return false;
		}
		$rslist = $this->price_status_all();
		$rslist[$id] = $data;
		$file = $this->dir_root.'data/xml/price_status_'.$this->site_id.'.xml';
		$this->lib('xml')->save($rslist,$file);
		return true;
	}

	public function order_status_one($id)
	{
		$rslist = $this->order_status_all();
		if(!$rslist){
			return false;
		}
		if($rslist[$id]){
			$rs = $rslist[$id];
			$rs['id'] = $id;
			return $rs;
		}
		return false;
	}

	//更新状态
	public function order_status_update($data,$id=0)
	{
		if(!$id || !$data){
			return false;
		}
		$rslist = $this->order_status_all();
		$rslist[$id] = $data;
		$file = $this->dir_root.'data/xml/order_status_'.$this->site_id.'.xml';
		$this->lib('xml')->save($rslist,$file);
	}

	public function get_all_site()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain";
		$dlist = $this->db->get_all($sql);
		if($dlist){
			foreach($dlist as $key=>$value){
				$rslist[$value['site_id']]['dlist'][] = $value['domain'];
			}
			foreach($rslist as $key=>$value){
				if($value['dlist']){
					$value['dlist_string'] = implode(" , ",$value['dlist']);
				}
				$rslist[$key] = $value;
			}
		}
		//读别名
		$file = $this->dir_root.'data/xml/site_alias.xml';
		if(!file_exists($file)){
			return $rslist;
		}
		$tmplist = $this->lib('xml')->read($file,true);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$tmpid = substr($key,1);
				if($tmpid && $value && $rslist[$tmpid]){
					$rslist[$tmpid]['alias'] = $value;
				}
			}
		}
		return $rslist;
	}

	public function alias_save($title,$id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."site";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$file = $this->dir_root.'data/xml/site_alias.xml';
		$tmplist = $this->lib('xml')->read($file,true);
		if($tmplist){
			$tmplist['a'.$id] = $title;
		}else{
			$tmplist = array();
			$tmplist['a'.$id] = $title;
		}
		foreach($tmplist as $key=>$value){
			$tmpid = substr($key,1);
			if(!$rslist[$tmpid]){
				unset($tmplist[$key]);
			}
		}
		if(!$tmplist || count($tmplist)<1){
			return false;
		}
		return $this->lib('xml')->save($tmplist,$file);
	}
}

?>