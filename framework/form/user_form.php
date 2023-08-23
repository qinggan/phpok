<?php
/**
 * 用户选项
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2015年03月13日 13时06分
 * @更新 2023年8月23日
**/
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
		if($appid == 'admin'){
			return $this->fetch($this->dir_phpok.'form/html/user_admin_tpl.html','abs-file');
		}
		return $this->fetch($this->dir_phpok.'form/html/user_www_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		$user_id = $this->get($rs['identifier']);
		if(!$user_id){
			$username = $this->get('title_'.$rs['identifier']);
			if(!$username){
				return false;
			}
			if($rs['is_multiple']){
				$list = explode(",",$username);
				$list = array_unique($list);
				$ulist = array();
				foreach($list as $key=>$value){
					$value = trim($value);
					if(!$value){
						continue;
					}
					$tmp = $this->model('user')->get_one($username,'user',false,false);
					if($tmp){
						$ulist[] = $tmp['id'];
					}
				}
				$user_id = implode(",",$ulist);
			}else{
				$user = $this->model('user')->get_one($username,'user',false,false);
				if($user){
					$user_id = $user['id'];
				}
			}
		}
		return $user_id;
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs['content']){
			return false;
		}
		if($appid == 'admin'){
			return $this->_admin_show($rs);
		}
		$info = $this->_www_show($rs);
		//API 接口返回的数据中，不含手机号及密码
		if($appid == 'api'){
			if(isset($info['id'])){
				unset($info['pass'],$info['mobile']);
			}else{
				foreach($info as $key=>$value){
					unset($value['pass'],$value['mobile']);
					$info[$key] = $value;
				}
			}
		}
		return $info;
	}

	private function _www_show($rs)
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		if($rs['is_multiple']){
			if($rs['content'] && is_array($rs['content'])){
				$rs['content'] = implode(",",$rs['content']);
			}
			$condition = "u.id IN(".$rs['content'].") AND u.status=1";
			$rslist = $this->model('user')->get_list($condition,0,999);
			if(!$rslist){
				return false;
			}
			return $rslist;
		}
		$uinfo = $this->model('user')->get_one($rs['content']);
		if(!$uinfo){
			return false;
		}
		return $uinfo;
	}

	private function _admin_show($rs)
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		if($rs['is_multiple']){
			$condition = "u.id IN(".$rs['content'].") AND u.status=1";
			$rslist = $this->model('user')->get_list($condition,0,999);
			if(!$rslist){
				return false;
			}
			$uinfo = array();
			foreach($rslist as $key=>$value){
				$uinfo[] = $value['user'];
			}
			return implode(' / ',$uinfo);
		}
		$uinfo = $this->model('user')->get_one($rs['content']);
		if($uinfo){
			return $uinfo['user'];
		}
		return false;
	}
}