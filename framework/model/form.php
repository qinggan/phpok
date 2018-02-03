<?php
/**
 * 表单选择器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年01月20日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_model_base extends phpok_model
{
	private $info = array();
	public function __construct()
	{
		parent::model();
		$this->info = $this->lib('xml')->read($this->dir_phpok.'system.xml');
	}

	/**
	 * 表单类型
	 * @参数 $note 是否显示备注，默认为 false
	**/
	public function form_all($note = false)
	{
		if($this->info['form']){
			if($note){
				return $this->info['form'];
			}
			$list = array();
			foreach($this->info['form'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	/**
	 * 格式化方式
	 * @参数 $note 是否显示备注，默认为 false
	**/
	public function format_all($note = false)
	{
		if($this->info['format']){
			if($note){
				return $this->info['format'];
			}
			$list = array();
			foreach($this->info['format'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	/**
	 * 字段类型
	 * @参数 $note 是否显示备注，默认为 false
	**/
	public function field_all($note = false)
	{
		if($this->info['field']){
			if($note){
				return $this->info['field'];
			}
			$list = array();
			foreach($this->info['field'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	//读取表单下的子项目信息
	public function project_sublist($pid)
	{
		$sql = "SELECT id as val,title FROM ".$this->db->prefix."project WHERE parent_id=".intval($pid)." AND status=1 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}
}