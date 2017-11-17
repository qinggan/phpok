<?php
/**
 * 插件中心
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年10月07日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 读取已安装的全部插件
	 * @参数 $status 为1进，表示只读取正在使用的插件
	**/
	public function get_all($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins ";
		if($status){
			$sql .= "WHERE status=1 ";
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,'id');
	}

	/**
	 * 取得全部的插件列表
	**/
	public function dir_list()
	{
		$folder = $this->dir_root."plugins/";
		//读取列表
		$handle = opendir($folder);
		$list = array();
		while(false !== ($file = readdir($handle))){
			if(substr($file,0,1) != "." && is_dir($folder.$file)){
				$list[] = $file;
			}
		}
		closedir($handle);
		return $list;
	}

	/**
	 * 读取插件基本数据
	 * @参数 $id 插件标识
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 读取插件配置文件XML数据，仅在安装时有效
	 * @参数 $id 插件标识
	**/
	public function get_xml($id)
	{
		$folder = $this->dir_root."plugins/".$id."/";
		if(!is_dir($folder)){
			return false;
		}
		$rs = array();
		if(file_exists($folder."config.xml")){
			$rs = $this->lib('xml')->read($folder.'config.xml');
		}
		$rs["id"] = $id;
		$rs["path"] = $folder;
		return $rs;
	}

	/**
	 * 保存安装的数据
	 * @参数 $data 插件数据
	**/
	public function install_save($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$this->db->insert_array($data,'plugins','replace');
		$sql = "SELECT id FROM ".$this->db->prefix."plugins WHERE id='".$data['id']."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['id'];
	}

	/**
	 * 更新插件扩展数据
	 * @参数 $id 插件标识
	 * @参数 $info 插件扩展数据
	**/
	public function update_param($id,$info='')
	{
		if($info && is_array($info)){
			$info = serialize($info);
		}
		$sql = "UPDATE ".$this->db->prefix."plugins SET param='".$info."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新插件信息
	 * @参数 $data 插件基本数据
	 * @参数 $id 插件标识
	**/
	public function update_plugin($data,$id)
	{
		if(!$data || !$id || !is_array($data)){
			return false;
		}
		$this->db->update_array($data,'plugins',array('id'=>$id));
	}

	/**
	 * 删除插件
	 * @参数 $id 插件标识
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."plugins WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新插件状态
	 * @参数 $id 插件标识
	 * @参数 $status 状态，1使用，0未使用
	**/
	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."plugins SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}