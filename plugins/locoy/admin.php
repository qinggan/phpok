<?php
/*****************************************************************************************
	文件： plugins/locoy/admin.php
	备注： 后台操作插件
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月16日 21时49分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_locoy extends phpok_plugin
{
	public $me;
	private $thumbfile = 'thumbfile';
	private $thumb = 'thumb';
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
		if($this->me['param'] && $this->me['param']['locoy_thumbfile']){
			$this->thumbfile = $this->me['param']['locoy_thumbfile'];
		}
		if($this->me['param'] && $this->me['param']['locoy_thumb']){
			$this->thumb = $this->me['param']['locoy_thumb'];
		}
	}

	public function ap_list_ok_after($data)
	{
		$id = $data['id'];
		$project = $data['project'];
		$file = $this->get($this->thumbfile);
		if(!$file){
			return false;
		}
		$content_img = $this->lib("html")->get_content($file);
		if(!$content_img){
			return false;
		}
		$ext = strtolower(substr($file,-3));
		if($ext != 'jpg' && $ext != 'png' && $ext != 'gif'){
			return false;
		}
		$fileid = md5($file);
		$filename = $fileid.'.'.$ext;
		//取得附件配置
		$cate_rs = $this->model("res")->cate_default();
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$this->time);
		}
		if(!file_exists($folder)){
			$this->lib("file")->make($folder);
		}
		if(substr($folder,-1) != "/"){
			$folder .= "/";
		}
		if(substr($folder,0,1) == "/"){
			$folder = substr($folder,1);
		}
		if($folder){
			$folder = str_replace("//","/",$folder);
		}
		$save_folder = $this->dir_root.$folder;
		$this->lib("file")->save_pic($content_img,$save_folder.$filename);
		$array = array();
		$array["cate_id"] = $cate_rs["id"];
		$array["folder"] = $folder;
		$array["name"] = basename($file);
		$array["ext"] = $ext;
		$array["filename"] = $folder.$filename;
		$array["addtime"] = $this->time;
		$array["title"] = str_replace(".".$ext,"",basename($file));
		$img_ext = getimagesize($save_folder.$filename);
		$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
		$array["attr"] = serialize($my_ext);
		$insert_id = $this->model("res")->save($array);
		$ico = $this->lib("gd")->thumb($array["filename"],$insert_id);
		if(!$ico){
			$ico = "images/filetype-large/".$ext.".jpg";
			if(!file_exists($ico)){
				$ico = "images/filetype-large/unknow.jpg";
			}
		}else{
			$ico = $folder.$ico;
		}
		$tmp = array();
		$tmp["ico"] = $ico;
		$this->model("res")->save($tmp,$insert_id);
		$this->model('res')->gd_update($insert_id);
		//更新记录
		$this->model('list')->update_ext(array($this->thumb=>$insert_id),$project['module'],$id);
		return true;
	}
}

?>