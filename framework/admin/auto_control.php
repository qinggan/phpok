<?php
/**
 * 自动保存数据
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月14日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class auto_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	# 存储表单
	public function index_f()
	{
		$type = $this->get("__type");
		if(!$type) $type = "list";
		$str = $_POST ? serialize($_POST) : "";
		if(!$str){
			$this->json(P_Lang('没有自动存储的表单数据'),true);
		}
		$rs = $this->model('temp')->chk($type,$_SESSION["admin_id"]);
		if($rs){
			$id = $rs["id"];
			unset($rs["id"]);
			$rs["content"] = $str;
		}else{
			$rs["content"] = $str;
			$rs["tbl"] = $type;
			$rs["admin_id"] = $_SESSION["admin_id"];
		}
		$this->model('temp')->save($rs,$id);
		$this->json(P_Lang('数据存储成功'),true);
	}

	public function read_f()
	{
		$type = $this->get("__type");
		if(!$type) $type = "list";
		$rs = $this->model('temp')->chk($type,$_SESSION["admin_id"]);
		if($rs){
			$content = unserialize($rs["content"]);
			$this->json($content,true);
		}else{
			$this->json("没有数据");
		}
	}


	/**
	 * 自动保存添加的数据
	**/
	public function list_f()
	{
		$pid = $this->get('pid');
		$uid = $this->session->val('admin_id');
		$filename = $this->dir_cache.'autosave_'.$uid.'_'.$pid.'.php';
		$this->lib('file')->rm($filename);
		$data = isset($_POST) ? serialize($_POST) : '';
		if($data){
			$this->lib('file')->vi($data,$filename);
		}
		$this->success();
	}

}