<?php
/**
 * 采集器<插件安装>
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月05日
**/

class install_collection extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	//插件安装时，增加的扩展表单输出项
	public function index()
	{
		$rescatelist = $this->model('rescate')->get_all();
		$this->assign('res_catelist',$rescatelist);
		return $this->_tpl('install.html');
	}
	//插件安装时，保存扩展参数
	public function save()
	{
		$id = $this->_id();
		$this->load_sql($id);
		$ext = array();
		$ext['rescate'] = $this->get('rescate','int');
		$this->_save($ext,$id);
		//复制快捷方式
		if(file_exists($this->dir_plugin.'collection/plugin-collection-icon.xml')){
			$this->lib('file')->cp($this->dir_plugin.'collection/plugin-collection-icon.xml',$this->dir_data.'plugin-collection-icon.xml');
		}
	}

	private function load_sql($id)
	{
		if(!file_exists($this->me['path'].'collection.sql')){
			$this->model('plugin')->delete($id);
			$this->error('安装失败，缺少SQL文件',$this->url('plugin'));
		}
		$sql = file_get_contents($this->me['path'].'collection.sql');
		$sql = str_replace("\r","\n",$sql);
		if($this->db->prefix != 'qinggan_'){
			$sql = str_replace("qinggan_",$this->db->prefix,$sql);
		}
		$ret = array();
		$num = 0;
		foreach(explode(";\n", trim($sql)) as $query){
			$queries = explode("\n", trim($query));
			foreach($queries as $query){
				$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
			}
			$num++;
		}
		foreach($ret as $query){
			$query = trim($query);
			if($query){
				$this->db->query($query);
			}
		}
		return true;
	}
}