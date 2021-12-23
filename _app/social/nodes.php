<?php
/**
 * 接入节点_针对社交信息增加的一些服务，如关注，粉丝，黑名单等功能
 * @作者 phpok.com <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @许可 www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年07月16日 10时13分
**/
namespace phpok\app\social;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class nodes_phpok extends \_init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function admin_before()
	{
		//公共管理后台数据未执行前操作
	}

	public function admin_after()
	{
		//公共管理后台数据执行后未输出前
	}

	public function www_before()
	{
		//前台未执行前
	}

	public function www_after()
	{
		//数据执行后未输出前
		$me = $this->tpl->val('me');
		if($me && $me['id']){
			$idol = $this->model('social')->idol_count($me['id']);
			$fans = $this->model('social')->fans_count($me['id']);
			$me['social'] = array('idol'=>$idol,'fans'=>$fans);
			$this->assign('me',$me);
			$this->data('me',$me);
		}
	}

	public function www_user_index_after()
	{
		$user_rs = $this->tpl->val('user_rs');
		$uid = $user_rs['id'];
		//个人主页装修
		$homepage = $this->model('social')->homepage($uid);
		$this->assign('homepage',$homepage);
		if($homepage && $homepage['tags']){
			$homepage['tags'] = str_replace(array("、","/","，",'|',' '),',',$homepage['tags']);
			$taglist = explode(",",$homepage['tags']);
			$this->assign('taglist',$taglist);
		}
		//显示我关注的及粉丝数
		$idol = $this->model('social')->idol_count($uid);
		$fans = $this->model('social')->fans_count($uid);
		$social = array();
		$social['idol'] = $idol;
		$social['fans'] = $fans;
		//是否已关注
		if($this->session->val('user_id') && $this->session->val('user_id') != $user_rs['id']){
			$m = $this->model('social')->links_info($this->session->val('user_id'),$user_rs['id']);
			if($m && $m['is_black']){
				$this->error(P_Lang('您已拉黑该用户，不能查看信息'));
			}
			$n = $this->model('social')->links_info($user_rs['id'],$this->session->val('user_id'));
			if($n && $n['is_black']){
				$this->error(P_Lang('您没有权限查看'),$this->url('index'));
			}
			$social['is_idol'] = ($m && $m['is_idol']) ? true : false;
			$social['is_fans'] = ($n && $n['is_idol']) ? true : false;
		}
		$this->assign('social',$social);
	}

	public function PHPOK_arclist()
	{
		//这里开始编写PHP代码
		$rslist = $this->data('rslist');
		if(!$rslist){
			return false;
		}
		$uids = array();
		foreach($rslist as $key=>$value){
			if($value['user_id']){
				$uids[] = $value['user_id'];
			}
		}
		if(!$uids){
			return false;
		}
		$uids = array_unique($uids);
		$fans = $this->model('social')->fans_count($uids);
		$idol = $this->model('social')->idol_count($uids);
		$relation = array();
		if($this->session->val('user_id')){
			$relation = $this->model('social')->attr($uids,$this->session->val('user_id'));
		}
		foreach($rslist as $key=>$value){
			if($value['user'] && $value['user_id']){
				$value['user']['social'] = array('idol'=>0,'fans'=>0,'is_idol'=>false,'is_fans'=>false,'is_black'=>false);
				if($fans && $fans[$value['user_id']]){
					$value['user']['social']['fans'] = $fans[$value['user_id']]['total'];
				}
				if($idol && $idol[$value['user_id']]){
					$value['user']['social']['idol'] = $idol[$value['user_id']]['total'];
				}
				if($relation && $relation[$value['user_id']]){
					$value['user']['social']['is_idol'] = $relation[$value['user_id']]['is_idol'];
					$value['user']['social']['is_fans'] = $relation[$value['user_id']]['is_fans'];
					$value['user']['social']['is_black'] = $relation[$value['user_id']]['is_black'];
				}
			}
			$rslist[$key] = $value;
		}
		$this->data('rslist',$rslist);
	}

	public function PHPOK_arc()
	{
		$arc = $this->data('arc');
		if(!$arc || !$arc['user_id'] || !$arc['user']){
			return false;
		}
		$fans = $this->model('social')->fans_count($arc['user_id']);
		$idol = $this->model('social')->idol_count($arc['user_id']);
		$relation = array();
		if($this->session->val('user_id')){
			$relation = $this->model('social')->attr($uids,$this->session->val('user_id'));
		}
		$arc['user']['social'] = array('idol'=>0,'fans'=>0,'is_idol'=>false,'is_fans'=>false,'is_black'=>false);
		if($fans){
			$arc['user']['social']['fans'] = $fans;
		}
		if($idol){
			$arc['user']['social']['idol'] = $idol;
		}
		if($relation && $relation[$value['user_id']]){
			$arc['user']['social']['is_idol'] = $relation[$value['user_id']]['is_idol'];
			$arc['user']['social']['is_fans'] = $relation[$value['user_id']]['is_fans'];
			$arc['user']['social']['is_black'] = $relation[$value['user_id']]['is_black'];
		}
		$this->data('arc',$arc);
	}

	public function PHPOK_project()
	{
		//这里开始编写PHP代码
	}

	public function PHPOK_catelist()
	{
		//这里开始编写PHP代码
	}

	public function PHPOK_cate()
	{
		//这里开始编写PHP代码
	}

	/**
	 * 删除主题时触发删除这个应用事件
	 * @参数 $id 主题ID
	 * @返回 true 
	**/
	public function system_admin_title_delete($id)
	{
		//这里开始编写PHP代码
		return true;
	}

	/**
	 * 更新或添加保存完主题后触发动作
	 * @参数 $id 主题ID
	 * @参数 $project 项目信息，数组
	 * @返回 true 
	**/
	public function system_admin_title_success($id,$project)
	{
		//这里开始编写PHP代码
		return true;
	}

	/**
	 * 初始化站点信息接口，无参数，需要通过data来获取信息
	**/
	public function system_init_site()
	{
		$site_rs = $this->data("site_rs");
		//这里开始编写PHP代码
		$this->data("site_rs",$site_rs);
		return true;
	}

}
