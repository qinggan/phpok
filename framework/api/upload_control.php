<?php
/*****************************************************************************************
	文件： {phpok}/api/upload_control.php
	备注： 前端附件上传接口
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月10日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class upload_control extends phpok_control
{
	private $u_id = 0; //会员ID
	private $u_name = 'guest'; //会员名字，游客使用guest
	private $is_client = false;//判断是否客户端
	function __construct()
	{
		parent::control();
		$token = $this->get('token');
		if($token){
			$this->lib('token')->keyid($this->site['api_code']);
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['user_id'] || !$info['user_name']){
				$this->json(P_Lang('您还没有登录，请先登录或注册'));
			}
			$this->u_id = $info['user_id'];
			$this->u_name = $info['user_name'];
			$this->is_client = true;
		}else{
			if($this->session->val('user_id')){
				$this->u_id = $this->session->val('user_id');
				$this->u_name = $this->session->val('user_name');
			}
		}
	}

	//存储上传的数据，游客仅能上传jpg,png,gif,jpeg附件
	//普通会员能上传的附件有：jpg,png,gif,jpeg,zip,rar,doc,xls,docx,xlsx,txt,ppt,pptx
	public function save_f()
	{
		if($this->u_id){
			if(!$this->site['upload_user']){
				$this->json(P_Lang('你没有上传权限'));
			}
		}else{
			if(!$this->site['upload_guest']){
				$this->json(P_Lang('游客没有上传权限'));
			}
		}
		$cateid = $this->get('cateid','int');
		if($cateid){
			$cate_rs = $this->model('rescate')->get_one($cateid);
		}
		if(!$cate_rs){
			$cate_rs = $this->model('rescate')->get_default();
			if(!$cate_rs){
				$this->json(P_Lang('未配置附件存储方式'));
			}
		}
		$filetypes = $this->u_id ? $cate_rs['filetypes'] : 'jpg,png,gif,rar,zip';
		$this->lib('upload')->set_type($filetypes);
		$this->lib('upload')->set_cate($cate_rs);
		$upload = $this->lib('upload')->upload('upfile');
		if(!$upload || !$upload['status']){
			$this->json(P_Lang('附件上传失败'));
		}
		if($upload['status'] != 'ok'){
			$tip = $upload['error'] ? $upload['error'] : $upload['content'];
			$this->json($tip);
		}
		$array = array();
		$array["cate_id"] = $this->lib('upload')->get_cate();
		$array["folder"] = $this->lib('upload')->get_folder();
		$array["name"] = $upload['name'];
		$array["ext"] = $upload["ext"];
		$array["filename"] = $upload['filename'];
		$array["addtime"] = $this->time;
		$array['title'] = $upload['title'];
		$array["mime_type"] = $upload['mime_type'];
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($upload['ext'],$arraylist)){
			$img_ext = getimagesize($this->dir_root.$upload['filename']);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		if(!$this->is_client){
			$array["session_id"] = $this->session->sessid();
		}
		$array['user_id'] = $this->u_id;
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($this->dir_root.$upload['filename']);
			$this->json(P_Lang('图片存储失败'));
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
	}

	/**
	 * 基于 Base64 附件上传接口
	 * @参数 cateid 分类ID，留空使用系统默认
	 * @参数 title 文件名，留空使用系统生成的文件名
	 * @参数 data 要保存的内容的 Base64 内容
	**/
	public function base64_f()
	{
		if($this->u_id){
			if(!$this->site['upload_user']){
				$this->json(P_Lang('你没有上传权限'));
			}
		}else{
			if(!$this->site['upload_guest']){
				$this->json(P_Lang('游客没有上传权限'));
			}
		}
		$cateid = $this->get('cateid','int');
		if($cateid){
			$cate_rs = $this->model('rescate')->get_one($cateid);
		}
		if(!$cate_rs){
			$cate_rs = $this->model('rescate')->get_default();
			if(!$cate_rs){
				$this->json(P_Lang('未配置附件存储方式'));
			}
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$this->time);
		}
		if(!file_exists($this->dir_root.$folder)){
			$this->lib('file')->make($this->dir_root.$folder);
		}
		//上传附件
		$data = $this->get('data');
		if(!$data){
			$this->json(P_Lang('Base64 内容不能为空'));
		}
		if(strpos($data,',') === false){
			$this->json(P_Lang('附片格式不正确'));
		}
		$tmp = explode(",",$data);
		$tmpinfo = substr($data,strlen($tmp[0]));
		$content = base64_decode($tmpinfo);
		if($content == $tmpinfo){
			$this->json(P_Lang('不是合法的附件'));
		}
		$info = explode(";",$tmp[0]);
		if(!$info[0]){
			$this->json(P_Lang('不是合法的 Base64 文件'));
		}
		$mime_type = $info[0];
		$tmp = explode("/",$mime_type);
		if(!$tmp[1]){
			$this->json(P_Lang('不是合法的 Base64 文件'));
		}
		$ext = $tmp[1];
		if($ext && $ext == 'jpeg'){
			$ext = 'jpg';
		}
		$name = $this->time.'_'.$this->u_id.'.'.$ext;
		$filetypes = $this->u_id ? $cate_rs['filetypes'] : 'jpeg,jpg,png,gif,rar,zip';
		$ft_list = explode(",",$filetypes);
		if(!in_array($ext,$ft_list)){
			$this->json(P_Lang('您不能上传 {ext} 格式的附件',array('ext'=>$ext)));
		}
		//保存文件
		$this->lib('file')->save_pic($content,$this->dir_root.$folder.$name);
		if(!file_exists($this->dir_root.$folder.$name)){
			$this->json(P_Lang('文件保存失败，请检查'));
		}
		$array = array();
		$array["cate_id"] = $cate_rs['id'];
		$array["folder"] = $folder;
		$array["name"] = $name;
		$array["ext"] = $ext;
		$array["filename"] = $folder.$name;
		$array["addtime"] = $this->time;
		$array['title'] = $this->get('title');
		if(!$array['title']){
			$array['title'] = $name;
		}
		$array["mime_type"] = $mime_type;
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($ext,$arraylist)){
			$img_ext = getimagesize($this->dir_root.$array['filename']);
			if(!$img_ext || !$img_ext[0] || !$img_ext[1]){
				$this->lib('file')->rm($this->dir_root.$array['filename']);
				$this->json(P_Lang('文件异常，无法获取宽高，请检查'));
			}
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		if(!$this->is_client){
			$array["session_id"] = $this->session->sessid();
		}
		$array['user_id'] = $this->u_id;
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($this->dir_root.$array['filename']);
			$this->json(P_Lang('图片存储失败'));
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
	}
}