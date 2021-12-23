<?php
/**
 * 后台管理_集成微信所有接口功能，包括公众号（mp），开放平台（op），小程序（ap）等相关服务
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年11月28日 11时26分
**/
namespace phpok\app\control\weixin;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('weixin');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$this->display('admin_index');
	}

	public function config_f()
	{
		if(!$this->popedom['config']){
			$this->error(P_Lang('您没有配置权限'));
		}
		$config = $this->model('weixin')->config_all();
		if($config){
			$this->assign('rs',$config);
		}
		$iplist = $this->model('weixin')->ip_list();
		$this->assign('iplist',$iplist);
		$this->display('admin_config');
	}

	public function config_save_f()
	{
		if(!$this->popedom['config']){
			$this->error(P_Lang('您没有配置权限'));
		}
		$config = array();
		$config['mp'] = $this->get('mp');
		$config['op'] = $this->get('op');
		$config['ap'] = $this->get('ap');
		$config['ip'] = $this->get('ip');
		$this->model('weixin')->config_save($config);
		$this->success();
	}

	/**
	 * 消息管理器
	**/
	public function message_f()
	{
		
		$this->display('admin_message');
	}

	public function mini_app_f()
	{
		$rs = $this->model('weixin')->mini_app_config();
		$text_color = form_edit('text_color',$rs['text_color'],'text','form_btn=color&ext_include_3=1');
		$this->assign('text_color',$text_color);
		$text_color_highlight = form_edit('text_color_highlight',$rs['text_color_highlight'],'text','form_btn=color&ext_include_3=1');
		$this->assign('text_color_highlight',$text_color_highlight);
		$tab_bgcolor = form_edit('tab_bgcolor',$rs['tab_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('tab_bgcolor',$tab_bgcolor);
		$top_bgcolor = form_edit('top_bgcolor',$rs['top_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('top_bgcolor',$top_bgcolor);
		$usercp_bgcolor = form_edit('usercp_bgcolor',$rs['usercp_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('usercp_bgcolor',$usercp_bgcolor);
		$usercp_txtcolor = form_edit('usercp_txtcolor',$rs['usercp_txtcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('usercp_txtcolor',$usercp_txtcolor);
		$this->assign('rs',$rs);
		$this->display("admin_mini_app_config");
	}

	public function mini_app_save_f()
	{
		$data = array();
		$data['title'] = $this->get('title');
		$data['top_bgcolor'] = $this->get('top_bgcolor');
		$data['top_txtcolor'] = $this->get('top_txtcolor');
		$data['text_color'] = $this->get('text_color');
		$data['text_color_highlight'] = $this->get('text_color_highlight');
		$data['tab_bgcolor'] = $this->get('tab_bgcolor');
		$data['tab_bordercolor'] = $this->get('tab_bordercolor');
		$data['usercp_bgcolor'] = $this->get('usercp_bgcolor');
		$data['usercp_bgimg'] = $this->get('usercp_bgimg');
		$data['usercp_txtcolor'] = $this->get('usercp_txtcolor');
		$this->model('weixin')->mini_app_save($data);
		$this->success();
	}

	public function subscribe_f()
	{
		$welcome = $this->model('weixin')->config_welcome();
		$this->assign('welcome',$welcome);
		$config = $this->model('weixin')->config_subscribe();
		$this->assign('wxconfig',$config);
		$this->display("admin_subscribe");
	}


	public function subscribe_save_f()
	{
		$welcome = $this->get('welcome','html');
		if($welcome){
			$this->model('weixin')->config_welcome($welcome);
		}else{
			$this->model('weixin')->config_welcome(true); //删除文件
		}
		$config = array();
		$config['is_del'] = $this->get('is_del','int');
		$config['account'] = $this->get('account');
		$config['password'] = $this->get('password');
		$this->model('weixin')->config_subscribe($config);
		$this->success();
	}

	/**
	 * 删除微信用户，不建议使用
	**/
	public function user_delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error('您没有权限操作');
		}
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($list[$key]);
				continue;
			}
			$list[$key] = $value;
		}
		if(!$list || count($list)<1){
			$this->error('未指定ID');
		}
		$this->model('weixin')->user_delete($list);
		$this->success();
	}

	public function userlist_f()
	{
		if(!$this->popedom['user']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$pageurl = $this->url('weixin');
		$keywords = $this->get('keywords');
		$condition = "1=1";
		//关键字
		if($keywords){
			$condition .= " AND (wx.nickname LIKE '%".$keywords."%' OR u.user LIKE '%".$keywords."%' ";
			$condition .= " OR u.email LIKE '%".$keywords."%' OR u.mobile LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		//状态
		$status = $this->get('status','int');
		if($status){
			$condition .= " AND u.status='".($status == 1 ? 1 : 0)."' ";
			$pageurl .= "&status=".$status;
			$this->assign('status',$status);
		}
		//微信性别
		$gender = $this->get('gender','int');
		if($gender){
			$condition .= " AND wx.gender='".$gender."' ";
			$pageurl .= "&gender=".$gender;
			$this->assign('gender',$gender);
		}
		$total = $this->model('weixin')->user_count($condition);
		if($total){
			$rslist = $this->model('weixin')->user_all($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageurl',$pageurl);
			$this->assign('pageid',$pageid);
			$this->assign('psize',$psize);
			$this->assign('offset',$offset);
			$this->assign('total',$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->display('admin_userlist');
	}

	public function user_lock_f()
	{
		if(!$this->popedom['user']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$user = $this->get('user');
		if(!$user){
			$this->error(P_Lang('未指定用户'));
		}
		$info = $this->model('user')->get_one($user,'user',false,false);
		if(!$info){
			$this->error(P_Lang('用户账号不存在'));
		}
		$this->model('weixin')->user_lock($id,$info['id']);
		$this->success();
	}

	public function user_unlock_f()
	{
		if(!$this->popedom['user']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($list[$key]);
				continue;
			}
			$list[$key] = $value;
		}
		if(!$list || count($list)<1){
			$this->error('未指定ID');
		}
		$this->model('weixin')->user_unlock($list);
		$this->success();
	}
}
