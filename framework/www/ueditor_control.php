<?php
/***********************************************************
	Filename: {phpok}/admin/ueditor_control.php
	Note	: Ueditor 编辑器中涉及到上传的操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年06月26日 19时04分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ueditor_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	private function load_config()
	{
		$config = $this->lib('file')->cat($this->dir_root.'data/config.json');
		$config = preg_replace("/\/\*[\s\S]+?\*\//","",$config);
		$config = $this->lib('json')->decode($config);
		$config['imageCompressEnable'] = false;
		//获取默认存储路径
		$cate_rs = $this->model('res')->cate_one();
		if(!$cate_rs || $cate_rs['root'] == '/' || !$cate_rs['root'])
		{
			$cate_rs["id"] = 0;
			$cate_rs["root"] = "res/";
			$cate_rs["folder"] = "/";
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/")
		{
			$folder .= date($cate_rs["folder"],$this->time);
		}
		if(!file_exists($folder))
		{
			$this->lib('file')->make($folder);
		}
		//如果还是没有检测到文件夹，则回到默认目录
		if(!file_exists($folder))
		{
			$folder = $cate_rs['root'];
		}
		if(substr($folder,-1) != "/") $folder .= "/";
		if(substr($folder,0,1) == "/") $folder = substr($folder,1);
		if($folder)
		{
			$folder = str_replace("//","/",$folder);
		}
		$rooturl = $this->root_url();
		$config['imagePathFormat'] = $folder;
		$config['imageUrlPrefix'] = $rooturl;
		$config['scrawlPathFormat'] = $folder;
		$config['scrawUrlPrefix'] = $rooturl;
		$config['snapscreenPathFormat'] = $folder;
		$config['snapscreenUrlPrefix'] = $rooturl;
		$tmp = array('localhost','127.0.0.1','img.baidu.com',$_SERVER['SERVER_NAME']);
		$config['catcherLocalDomain'] = array_unique($tmp);
		$config['catcherPathFormat'] = $folder;
		$config['catcherUrlPrefix'] = $rooturl;
		$config['videoPathFormat'] = $folder;
		$config['videoUrlPrefix'] = $rooturl;
		$config['filePathFormat'] = $folder;
		$config['fileUrlPrefix'] = $rooturl;
		$config['imageManagerUrlPrefix'] = $rooturl;
		$config['videoManagerUrlPrefix'] = $rooturl;
		$config['fileManagerUrlPrefix'] = $rooturl;
		$config['cateid'] = $cate_rs['id'];
		foreach($config as $key=>$value)
		{
			if(substr($key,0,5) == 'scraw')
			{
				unset($config[$key]);
			}
		}
		return $config;
	}

	//停止运行
	private function _stop($info,$data='')
	{
		if(!$data)
		{
			$data = array();
		}
		$data['state'] = ($info && !is_bool($info)) ? $info : 'SUCCESS';
		if($data['state'] != 'SUCCESS')
		{
			$data['list'] = array();
		}
		exit($this->lib('json')->encode($data));
	}

	public function index_f()
	{
		$action = $this->get('action');
		if(!$action){
			$this->_stop(P_Lang('未指定请求方式'));
		}
		$action_array = array('config','uploadimage','uploadvideo','uploadfile','listimage','listfile','listvideo','catchimage');
		if(!in_array($action,$action_array)){
			$this->_stop(P_Lang('请求参数不正确'));
		}
		if(!$this->site['upload_guest'] && !$_SESSION['user_id']){
			$this->_stop(P_Lang(P_Lang('系统已禁止游客上传，请联系管理员')));
		}
		if(!$this->site['upload_user'] && $_SESSION['user_id']){
			$this->_stop(P_Lang(P_Lang('系统已禁止会员上传，请联系管理员')));
		}
		$action_name = 'u_'.$action;
		$this->$action_name();
	}

	//图片本地化
	private function u_catchimage()
	{
		$this->_stop(P_Lang('前端禁用图片本地化'));
	}

	//读取视频列表
	private function u_listvideo()
	{
		$config = $this->load_config();
		$offset = $this->get('start','int');
		$psize = $this->get('size','int');
		$type = $config['videoManagerAllowFiles'];
		$type = implode("|",$type);
		$type = str_replace(".","",$type);
		$condition = "res.ext IN('".str_replace('|',"','",$type)."') ";
		if($_SESSION['user_id'])
		{
			$condition .= " AND res.user_id='".$_SESSION['user_id']."' ";
		}
		else
		{
			$condition .= " AND session_id='".$this->session->sessid()."' ";
		}
		$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize,false);
		if(!$rslist)
		{
			$this->_stop(P_Lang('视频内容为空'));
		}
		$piclist = array();
		foreach($rslist as $key=>$value)
		{
			$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'title'=>$value['title']);
			$piclist[] = $tmp;
		}
		$data = array('list'=>$piclist,'start'=>$offset,'size'=>$psize);
		$this->_stop(true,$data);
	}

	//文件管理工具
	private function u_listfile()
	{
		$offset = $this->get('start','int');
		$psize = $this->get('size','int');
		if($_SESSION['user_id'])
		{
			$condition = " res.user_id='".$_SESSION['user_id']."' ";
		}
		else
		{
			$condition = " session_id='".$this->session->sessid()."' ";
		}
		$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize,false);
		if(!$rslist)
		{
			$this->_stop(P_Lang('附件内容为空'));
		}
		$piclist = array();
		foreach($rslist as $key=>$value)
		{
			$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'title'=>$value['title']);
			$piclist[] = $tmp;
		}
		$data = array('list'=>$piclist,'start'=>$offset,'size'=>$psize);
		$this->_stop(true,$data);
	}
	//图片管理工具
	private function u_listimage()
	{
		$offset = $this->get('start','int');
		$psize = $this->get('size','int');
		$condition = "res.ext IN ('gif','jpg','png','jpeg') ";
		if($_SESSION['user_id'])
		{
			$condition .= " AND res.user_id='".$_SESSION['user_id']."' ";
		}
		else
		{
			$condition .= " AND session_id='".$this->session->sess_id()."' ";
		}
		$is_gd = false;
		$gd_rs = $this->model('gd')->get_editor_default();
		if($gd_rs)
		{
			$condition .= " AND e.gd_id='".$gd_rs["id"]."' ";
			$is_gd = true;
		}
		$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize,$is_gd);
		if(!$rslist)
		{
			$this->_stop(P_Lang('图片数据内容为空'));
		}
		$piclist = array();
		foreach($rslist as $key=>$value)
		{
			$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime']);
			$piclist[] = $tmp;
		}
		$data = array('list'=>$piclist,'start'=>$offset,'size'=>$psize);
		$this->_stop(true,$data);
	}

	//附件上传
	private function u_uploadfile()
	{
		$config = $this->load_config();
		$folder = $config['filePathFormat'];
		$input_name = $config['fileFieldName'];
		$rs = $this->upload_base($input_name,$folder,$config['cateid']);
		if(!$rs || $rs['status'] != 'ok')
		{
			$this->_stop('文件上传失败');
		}
		$data = array('title'=>$rs['title'],'url'=>$rs['filename'],'original'=>$rs['title']);
		$this->_stop(true,$data);
	}
	//视频上传
	private function u_uploadvideo()
	{
		$config = $this->load_config();
		$folder = $config['videoPathFormat'];
		$input_name = $config['videoFieldName'];
		$rs = $this->upload_base($input_name,$folder,$config['cateid']);
		if(!$rs || $rs['status'] != 'ok')
		{
			$this->_stop(P_Lang('视频上传失败'));
		}
		$data = array('title'=>$rs['title'],'url'=>$rs['filename'],'original'=>$rs['title']);
		$this->_stop(true,$data);
	}

	//图片上传
	private function u_uploadimage()
	{
		$config = $this->load_config();
		$folder = $config['imagePathFormat'];
		$input_name = $config['imageFieldName'];
		$rs = $this->upload_base($input_name,$folder,$config['cateid']);
		if(!$rs || $rs['status'] != 'ok')
		{
			$this->_stop(P_Lang('图片上传失败'));
		}
		$gd_rs = $this->model('gd')->get_editor_default();
		if($gd_rs)
		{
			$ext_rs = $this->model('res')->get_gd_pic($rs['id']);
			$filename = ($ext_rs && $ext_rs[$gd_rs['identifier']]) ? $ext_rs[$gd_rs['identifier']]['filename'] : $rs['filename'];
		}
		else
		{
			$filename = $rs['filename'];
		}
		$data = array('title'=>$rs['title'],'url'=>$filename,'original'=>$rs['title']);
		$this->_stop(true,$data);
	}

	//读取配置信息
	private function u_config()
	{
		$config = $this->load_config();
		$this->_stop(true,$config);
	}

  
	//基础上传
	function upload_base($input_name='upfile',$folder='res/',$cateid=0)
	{
		//上传类型
		$typelist = $this->model('res')->type_list();
		if($typelist)
		{
			$ext = array();
			foreach($typelist as $key=>$value)
			{
				$ext[] = $value['ext'];
			}
			$ext = implode(",",$ext);
			$this->lib('upload')->set_type($ext);
		}
		$rs = $this->lib('upload')->upload($input_name);
		if($rs["status"] != "ok")
		{
			return $rs;
		}
		//存储目录
		$basename = basename($rs["filename"]);
		$save_folder = $this->dir_root.$folder;
		if($save_folder.$basename != $rs["filename"])
		{
			$this->lib('file')->mv($rs["filename"],$save_folder.$basename);
		}
		if(!file_exists($save_folder.$basename))
		{
			$this->lib('file')->rm($rs["filename"]);
			$rs = array();
			$rs["status"] = "error";
			$rs["error"] = P_Lang('图片迁移失败');
			return $rs;
		}
		$rs['title'] = $this->lib('string')->to_utf8($rs['title']);
		$array = array();
		$array["cate_id"] = $cateid;
		$array["folder"] = $folder;
		$array["name"] = $basename;
		$array["ext"] = $rs["ext"];
		$array["filename"] = $folder.$basename;
		$array["addtime"] = $this->time;
		$array["title"] = str_replace(".".$rs["ext"],"",$rs["title"]);
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$arraylist))
		{
			$img_ext = getimagesize($save_folder.$basename);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$array["session_id"] = $this->session->sessid();
		if($_SESSION['user_id'])
		{
			$array['user_id'] = $_SESSION['user_id'];
		}
		//存储图片信息
		$id = $this->model('res')->save($array);
		if(!$id)
		{
			$this->lib('file')->rm($save_folder.$basename);
			$rs = array();
			$rs["status"] = "error";
			$rs["error"] = P_Lang('图片存储失败');
			return $rs;
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$rs["status"] = "ok";
		return $rs;
	}
}
?>