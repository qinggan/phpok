<?php
/*****************************************************************************************
	文件： {phpok}/form/user_form.php
	备注： 会员选项
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月13日 13时06分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/user_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if($rs["is_multiple"]){
			if(is_array($rs['content'])){
				$content = implode(',',array_keys($rs['content']));
			}else{
				$content = $rs['content'];
			}
		}else{
			$content = $rs['content'];
		}
		$this->assign('_rs_content',$content);
		$this->assign('_rs',$rs);
		return $this->fetch($this->dir_phpok.'form/html/user_admin_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier']);
	}

	public function phpok_show($rs,$appid="admin")
	{
		$ext = array();
		if($rs['ext']){
			$ext = is_string($rs['ext']) ? unserialize($rs['ext']) : $rs['ext'];
		}
		if($appid == 'admin'){
			if($ext['is_multiple']){
				$condition = "u.id IN(".$rs['content'].") AND status=1";
				$rslist = $this->model('user')->get_list($condition,0,999);
				if(!$rslist){
					return false;
				}
				$uinfo = array();
				foreach($rslist as $key=>$value){
					$uinfo = $value['user'];
				}
				return implode('/',$uinfo);
			}else{
				$uinfo = $this->model('user')->get_one($rs['content']);
				if($uinfo){
					return $uinfo['user'];
				}
				return false;
			}
		}else{
			if($ext['is_multiple']){
				$rslist = $this->model('user')->get_list($condition,0,999);
				if(!$rslist){
					return false;
				}
				return $rslist;
			}else{
				$uinfo = $this->model('user')->get_one($rs['content']);
				if(!$uinfo){
					return false;
				}
				return $uinfo;
			}
		}
	}
}
?>