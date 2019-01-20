<?php
/**
 * 附件常见动作操作
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年03月30日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class res_action_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->model("res");
	}

	public function download_f()
	{
		$file = $this->get("file");
		$id = $this->get("id");
		if(!$id && !$file){
			$this->error(P_Lang('未指定ID'));
		}
		if($id){
			$rs = $this->model('res')->get_one($id);
			$file = $rs["filename"];
			$title = $rs["title"].".".$rs["ext"];
		}else{
			$title = basename($file);
		}
		if(!$file){
			$this->error(P_Lang('未指定附件'));
		}
		if(substr($file,0,7) != "http://" && substr($file,0,8) != "https://"){
			$file = $this->dir_root.$file;
			if(!file_exists($file)){
				$this->error(P_Lang('附件不存在'));
			}
		}
		$this->lib('file')->download($file,$title);
	}

	public function view_f()
	{
		$file = $this->get("file");
		$id = $this->get("id");
		if(!$id && !$file){
			$this->error(P_Lang('未指定附件'));
		}
		if($id){
			$rs = $this->model('res')->get_one($id,true);
		}else{
			$rs = array();
			$rs["title"] = basename($file);
			$rs["filename"] = $file;
		}
		$this->assign("rs",$rs);
		$this->view("res_action_view");
	}

	public function video_f()
	{
		$file = $this->get("file");
		$id = $this->get("id");
		if(!$id && !$file){
			$this->error(P_Lang('未指定ID'));
		}
		if($id){
			$rs = $this->model('res')->get_one($id);
			$file = $rs["filename"];
		}
		$ext = substr($file,-4);
		$ext = strtolower($ext);
		if($ext != '.mp4' && $ext != 'webp'){
			$this->error(P_Lang('系统暂时只支持MP4/WebP视频预览'));
		}
		$this->assign("file",$file);
		$this->view("res_action_video");
	}

	public function preview_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定附件'));
		}
		$rs = $this->model('res')->get_one($id,true);
		$type = "files";
		$picture = array('jpg','gif','png','jpeg','tiff','svg');
		$video = array('mp4','mpeg','avi','mov','mpg','qt','ram','rm','dat','asf','wmv','wma');

		if(in_array($rs['ext'],$picture)){
			$type = 'picture';
		}
		if(in_array($rs['ext'],$video)){
			$type = 'video';
		}
		$this->assign("type",$type);
		$this->assign("rs",$rs);
		$is_local = false;
		if($this->model('res')->is_local($rs['filename'])){
			$is_local = true;
		}
		$this->assign('file_is_local',$is_local);
		$this->view("res_action_preview");
	}
}