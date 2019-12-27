<?php
/**
 * 模板控制器
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月29日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tpl_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("tpl");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 模板方案
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('tpl')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("tpl_index");
	}

	/**
	 * 添加或修改风格信息
	**/
	public function set_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('tpl'));
		}
		$id = $this->get("id","int");
		if($id){
			$rs = $this->model('tpl')->get_one($id);
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}
		$this->view("tpl_set");
	}

	/**
	 * 保存模板信息
	**/
	public function save_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('tpl'));
		}
		$id = $this->get("id","int");
		$error_url = $this->url("tpl","set");
		if($id) $error_url .= '&id='.$id;
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('名称不能为空'),$error_url);
		}
		$folder = $this->get("folder");
		if(!$folder){
			$this->error(P_Lang('文件夹目录名不能为空'),$error_url);
		}
		$ext = $this->get("ext");
		if(!$ext){
			$this->error(P_Lang('后缀不允许为空'),$error_url);
		}
		$array = array("title"=>$title,"folder"=>$folder,"ext"=>$ext);
		$array["folder_change"] = $this->get("folder_change");
		$array["author"] = $this->get("author");
		$array['phpfolder'] = $this->get('phpfolder');
		$array["refresh_auto"] = $this->get("refresh_auto","checkbox");
		$array["refresh"] = $this->get("refresh","checkbox");
		$this->model('tpl')->save($array,$id);
		$this->success(P_Lang('风格方案配置成功'),$this->url("tpl"));
	}

	/**
	 * 通过Ajax删除风格方案配置
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('tpl')->delete($id);
		$this->success();
	}

	//模板弹出选择器
	public function open_f()
	{
		$id = $this->get("id");
		if(!$id){
			$id = "tpl";
		}
		$config = $this->model('site')->get_one($this->session->val('admin_site_id'));
		if(!$config){
			$this->error(P_Lang('站点信息不存在'));
		}
		if(!$config['tpl_id']){
			$this->error(P_Lang('站点尚未设置默认风格，请先设置好'));
		}
		$tpl_id = $this->get('tpl_id','int');
		if(!$tpl_id){
			$tpl_id = $config["tpl_id"];
		}
		$rs = $this->model('tpl')->get_one($tpl_id);
		if(!$rs){
			$this->error(P_Lang('风格文件信息不存在'));
		}
		if(!$rs["ext"]){
			$rs["ext"] = "html";
		}
		$this->assign("site_rs",$config);
		$this->assign("rs",$rs);
		//绑定目录
		$tpl_dir = $this->dir_root."tpl/".$rs["folder"].'/';
		$tpl_list = $this->lib('file')->ls($tpl_dir);
		$ext_length = strlen($rs["ext"]);
		if(!$tpl_list){
			$tpl_list = array();
		}
		$myurl = $this->url("tpl","open",'tpl_id='.$tpl_id);
		$rslist = false;
		foreach($tpl_list AS $key=>$value){
			$bname = $this->lib('string')->to_utf8(basename($value));
			if(is_dir($value) || substr($bname,-$ext_length) != $rs["ext"]){
				continue;
			}
			$date = date("Y-m-d H:i:s",filemtime($value));
			$tplid = substr($bname,0,-($ext_length+1));
			$type = "html";
			$rslist[] = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>$type,"url"=>$url,"tplid"=>$tplid);
		}
		$this->assign("rslist",$rslist);
		$this->assign("id",$id);
		//取得模板风格全部列表
		$tplist = $this->model('tpl')->get_all();
		if($tplist){
			foreach($tplist as $key=>$value){
				if(!file_exists($this->dir_root.'tpl/'.$value['folder'])){
					unset($tplist[$key]);
					continue;
				}
			}
			$this->assign('tplist',$tplist);
			$this->assign('tpl_id',$tpl_id);
		}
		$this->view("tpl_open");	
	}
}