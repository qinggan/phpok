<?php
/**
 * 管理付款信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年09月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_model extends payment_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	//取得所支持的付款组
	function group_all($site_id=0,$status=0)
	{
		$condition = $site_id ? "site_id IN(0,".$site_id.")" : "site_id=0";
		$sql = "SELECT * FROM ".$this->db->prefix."payment_group WHERE ".$condition." ";
		if($status)
		{
			$sql .= " AND status=1 ";
		}
		$sql.= "ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,'id');		
	}

	public function get_all($condition="",$pri='')
	{
		$sql = "SELECT p.*,g.title group_title,g.is_wap group_wap FROM ".$this->db->prefix."payment p LEFT JOIN ".$this->db->prefix."payment_group g ON(p.gid=g.id) ";
		$sql.= "WHERE g.site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql.= ' ORDER BY p.taxis ASC,p.id DESC';
		return $this->db->get_all($sql,$pri);
	}

	//付款方案option
	public function opt_all($site_id=0)
	{
		$condition = $site_id ? "site_id IN(0,".$site_id.")" : "site_id=0";
		$sql = "SELECT * FROM ".$this->db->prefix."payment_group WHERE ".$condition." ";
		$sql.= " ORDER BY is_default DESC,taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist)
		{
			return false;
		}
		$ids = array_keys($rslist);
		$condition = " gid IN(".implode(",",$ids).")";
		$sql = "SELECT id,title,status,gid,currency FROM ".$this->db->prefix."payment WHERE ".$condition." ORDER BY taxis ASC,id DESC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist)
		{
			return false;
		}
		foreach($tmplist AS $key=>$value)
		{
			$rslist[$value['gid']]['paylist'][$value['id']] = $value;
		}
		return $rslist;
	}

	//获取本站系统中存储的所有支付引挈
	public function code_all()
	{
		//读取目录下的
		$handle = opendir($this->dir_root.'gateway/payment');
		$list = array();
		while(false !== ($myfile = readdir($handle))){
			if(substr($myfile,0,1) != '.' && is_dir($this->dir_root.'gateway/payment/'.$myfile)){
				$list[$myfile] = array('id'=>$myfile,'dir'=>$this->dir_root.'gateway/payment/'.$myfile);
				$tmpfile = $this->dir_root.'gateway/payment/'.$myfile.'/config.xml';
				if(is_file($tmpfile)){
					$tmp = $this->lib('xml')->read($tmpfile);
				}else{
					$tmp = array('title'=>$myfile,'code'=>'');
				}
				$list[$myfile]['title'] = $tmp['title'];
				$list[$myfile]['code'] = $tmp['code'];
			}
		}
		closedir($handle);
		return $list;
	}

	//取得当前Code信息
	public function code_one($id)
	{
		$rs = array('id'=>$id,'dir'=>$this->dir_root.'gateway/payment/'.$id);
		$xmlfile = $this->dir_root.'gateway/payment/'.$id.'/config.xml';
		if(file_exists($xmlfile)){
			$tmp = $this->lib('xml')->read($xmlfile);
		}else{
			$tmp = array('title'=>$id,'code'=>'');
		}
		$rs['code'] = $tmp['code'];
		$rs['title'] = $tmp['title'];
		return $rs;
	}

	//存储组信息
	public function groupsave($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'payment_group',array('id'=>$id));
		}
		return $this->db->insert_array($data,'payment_group');
		
	}

	public function group_set_default($id,$site_id=0)
	{
		if(!$id){
			return false;
		}
		$condition = $site_id ? 'site_id IN(0,'.$site_id.')' : 'site_id=0';
		$sql = "UPDATE ".$this->db->prefix."payment_group SET is_default=0 WHERE ".$condition;
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."payment_group SET is_default=1 WHERE id=".intval($id);
		return $this->db->query($sql);
	}

	//取得单个支付组
	public function group_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_group WHERE id=".intval($id);
		return $this->db->get_one($sql);
	}

	//删除支付组
	public function group_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."payment_group WHERE id=".intval($id);
		return $this->db->query($sql);
	}
	//取得单个支付方式
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id=".intval($id);
		return $this->db->get_one($sql);
	}

	//存储表单信息
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data))
		{
			return false;
		}
		if($id)
		{
			return $this->db->update_array($data,'payment',array('id'=>$id));
		}
		else
		{
			return $this->db->insert_array($data,'payment');
		}
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."payment WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}