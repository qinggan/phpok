<?php
/**
 * 模板相关操作
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月30日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tpl_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 获取模板信息
	 * @参数 $id 模板ID，数字
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tpl WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得全部风格列表，不限站点
	**/
	public function get_all($pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tpl ORDER BY id DESC";
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 存储或添加风格信息
	 * @参数 $data 数组，保存模板参数信息
	 * @参数 $id 主键ID，留空或为0表示添加新模板记录
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"tpl",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"tpl");
		}
	}

	/**
	 * 删除模板记录
	 * @参数 $id 主键ID
	**/
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."tpl WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function tpl_info($id,$is_mobile=false)
	{
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		$tpl_rs = array('id'=>$rs['id'],'dir_root'=>$this->dir_root);
		$tpl_rs["dir_tpl"] = $rs["folder"] ? "tpl/".$rs["folder"]."/" : "tpl/www/";
		$tpl_rs["dir_cache"] = $this->dir_data."tpl_www/";
		$tpl_rs["dir_php"] = $rs['phpfolder'] ? $this->dir_root.$rs['phpfolder'].'/' : $this->dir_root.'phpinc/';
		if($rs["folder_change"]){
			$tpl_rs["path_change"] = $rs["folder_change"];
		}
		$tpl_rs["refresh_auto"] = $rs["refresh_auto"] ? true : false;
		$tpl_rs["refresh"] = $rs["refresh"] ? true : false;
		$tpl_rs["tpl_ext"] = $rs["ext"] ? $rs["ext"] : "html";
		if($is_mobile){
			$tpl_rs["id"] = $rs["id"]."_mobile";
			$tplfolder = $rs["folder"] ? $rs["folder"]."_mobile" : "www_mobile";
			if(!file_exists($this->dir_root."tpl/".$tplfolder)){
				$tplfolder = $rs["folder"] ? $rs["folder"] : "www";
			}
			$tpl_rs["dir_tpl"] = "tpl/".$tplfolder."/";
		}
		$tpl_rs['langid'] = $this->session->val($this->app_id.'_lang_id');
		return $tpl_rs;
	}

	public function all_files()
	{
		$list = $this->get_all();
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$filelist = $this->files($value);
			if($filelist){
				$rslist = array_merge($rslist,$filelist);
			}
		}
		return $rslist;
	}

	/**
	 * 读取文件列表，过滤掉文件夹和block_开头的文件
	 * @参数 $rs 数组或数字，模板基础数据
	**/
	public function files($rs=0)
	{
		if($rs && is_numeric($rs)){
			$rs = $this->get_one($rs);
		}
		if(!$rs){
			return false;
		}
		if(!$rs['ext']){
			$rs['ext'] = 'html';
		}
		$ext_length = strlen($rs["ext"]);
		$list = $this->lib('file')->ls($this->dir_root.'tpl/'.$rs['folder'].'/');
		if(!$list){
			return false;
		}
		$tmplist = false;
		if(file_exists($this->dir_data.'xml/tpl_'.$rs['id'].'.xml')){
			$tmplist = $this->lib('xml')->read($this->dir_data.'xml/tpl_'.$rs['id'].'.xml');
		}
		$rslist = false;
		foreach($list as $key=>$value){
			$bname = $this->lib('string')->to_utf8(basename($value));
			if(is_dir($value) || substr($bname,-$ext_length) != $rs["ext"] || substr($bname,0,6) == 'block_'){
				continue;
			}
			$tplid = substr($bname,0,-($ext_length+1));
			$tmpid = $tplid;
			if(is_numeric(substr($tplid,0,1))){
				$tmpid = 'ok'.$tplid;
			}
			$title = $rs['title'].':'.$bname;
			if($tmplist && $tmplist[$tmpid]){
				$title = $rs['title'].':'.$tmplist[$tmpid].':'.$bname;
			}
			$rslist[$tmpid.'-'.$rs['id']] = array("title"=>$title,"id"=>$rs['id'].':'.$tplid);
		}
		return $rslist;
	}
}