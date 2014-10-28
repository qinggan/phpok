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
		if($token)
		{
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['user_id'] || !$info['user_name'])
			{
				$this->json(P_Lang('您还没有登录，请先登录或注册'));
			}
			$this->u_id = $info['user_id'];
			$this->u_name = $info['user_name'];
			$this->is_client = true;
		}
		else
		{
			//只有普通会员或是管理员才有上传权限
			if($_SESSION['user_id'])
			{
				$this->u_id = $_SESSION['user_id'];
				$this->u_name = $_SESSION['user_name'];
			}
		}
	}

	//存储上传的数据，游客仅能上传jpg,png,gif,jpeg附件
	//普通会员能上传的附件有：jpg,png,gif,jpeg,zip,rar,doc,xls,docx,xlsx,txt,ppt,pptx
	function save_f()
	{
		if($this->u_id)
		{
			if(!$this->site['upload_user'])
			{
				$this->json(P_Lang('你没有上传权限'));
			}
		}
		else
		{
			if(!$this->site['upload_guest'])
			{
				$this->json(P_Lang('游客没有上传权限'));
			}
		}

		$typelist = 'jpg,gif,png,jpeg,zip,rar';
		if($this->u_id)
		{
			$typelist .= ',doc,docx,wps,txt,rtf,xls,xlsx,ppt,pptx';
		}
		$this->lib('upload')->set_type($typelist);
		$cateid = $this->get('cateid','int');
		$this->lib('upload')->set_cate($cateid);
		$upload = $this->lib('upload')->upload('upfile');
		if(!$upload || !$upload['status'])
		{
			$this->json(P_Lang('附件上传失败'));
		}
		if($upload['status'] != 'ok')
		{
			$this->json($upload['content']);
		}
		$array = array();
		$array["cate_id"] = $this->lib('upload')->get_cate();
		$array["folder"] = $this->lib('upload')->get_folder();
		$array["name"] = $upload['name'];
		$array["ext"] = $upload["ext"];
		$array["filename"] = $upload['filename'];
		$array["addtime"] = $this->time;
		$array['title'] = $upload['title'];
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($upload['ext'],$arraylist))
		{
			$img_ext = getimagesize($this->dir_root.$upload['filename']);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		if(!$this->is_client)
		{
			$array["session_id"] = $this->session->sessid();
		}
		$array['user_id'] = $this->u_id;
		//存储图片信息
		$id = $this->model('res')->save($array);
		if(!$id)
		{
			$this->lib('file')->rm($this->dir_root.$upload['filename']);
			$this->json('图片存储失败');
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
	}
}