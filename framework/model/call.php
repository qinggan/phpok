<?php
/**
 * 数据调用中心涉及到的SQL操作
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class call_model_base extends phpok_model
{
	public $psize = 20;
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得数据调用类型
	**/
	public function types()
	{
		$xmlfile = $this->dir_data.'xml/calltype_'.$this->site_id.'.xml';
		if(!file_exists($xmlfile)){
			$xmlfile = $this->dir_data.'xml/calltype.xml';
		}
		return $this->lib('xml')->read($xmlfile);
	}

	/**
	 * 页码
	**/
	public function psize($psize='')
	{
		if($psize && is_numeric($psize)){
			$this->psize = $psize;
		}
		return $this->psize;
	}
	
	/**
	 * 通过ID取得数据（此操作用于后台）
	 * @参数 $id 主键ID
	 * @参数 $identifier 标识，默认是id，也可以取identifier
	**/
	public function get_one($id,$identifier='id')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE ".$identifier."='".$id."'";
		if($identifier != 'id'){
			$sql .= " AND site_id='".$this->site_id."'";
		}
		return $this->db->get_one($sql);
	}


	/**
	 * 取得列表
	 * @参数 $condition 查询条件
	 * @参数 $offset 初始位置
	 * @参数 $psize 查询数量
	**/
	public function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT call.* FROM ".$this->db->prefix."phpok call WHERE call.site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql.= " ORDER BY call.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	/**
	 * 取得站点下的全部数据
	 * @参数 $site_id 站点ID
	 * @参数 $status 为1或true时表示仅查已审核的数据
	 * @参数 $pri 主键，留空使用identifier
	**/
	public function get_all($site_id=0,$status=0,$pri='identifier')
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		if(!$site_id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE site_id='".$site_id."'";
		if($status){
			$sql.= " AND status=1";
		}
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 查询数量
	 * @参数 $condition 查询条件
	**/
	public function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."phpok WHERE site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 检测标识串是否存在
	 * @参数 $identifier 标识
	**/
	public function chk_identifier($identifier)
	{
		return $this->get_one_sign($identifier);
	}

	/**
	 * 通过标识串取得调用的配置数据
	 * @参数 $identifier 标识
	**/
	public function get_one_sign($identifier)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$identifier."' AND site_id='".$this->site_id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 检测标识串是否存在
	 * @参数 $identifier 标识
	**/
	public function chksign($identifier)
	{
		return $this->get_one_sign($identifier);
	}

	/**
	 * 获取一条数据，仅获取已通过审核的数据，并对扩展数据进行合并
	 * @参数 $identifier 标识
	 * @参数 $site_id 站点ID
	**/
	public function one($identifier,$site_id=0)
	{
		if(!$identifier){
			return false;
		}
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$identifier."' AND site_id='".$site_id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$ext = unserialize($rs['ext']);
			$rs = array_merge($rs,$ext);
		}
		return $rs;
	}

	/**
	 * 取得站点下的全部数据，并对数据进行格式化
	 * @参数 $site_id 站点ID
	 * @参数 $status 为1或true时表示仅查已审核的数据
	**/
	public function all($site_id=0,$pri='')
	{
		if($site_id && !is_numeric($site_id)){
			$pri = $site_id;
			$site_id = $this->site_id;
		}
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$rslist = $this->get_all($site_id,true,$pri);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$ext = unserialize($value['ext']);
				unset($value['ext']);
				$value = array_merge($value,$ext);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}
}