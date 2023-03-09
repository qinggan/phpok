<?php
/**
 * 收藏夹接口
 * @作者 phpok.com <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @许可 www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2022年4月22日
**/


namespace phpok\app\fav;
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

	/**
	 * 收藏夹总数，任意页面体现
	**/
	public function www_after()
	{
		$me = $this->tpl->val('me');
		if($me && $me['id']){
	    	$id = $this->get('id','int');
		    $chk = $this->model('fav')->chk($id,$me['id']);
            if($chk){
				$status = 1;
			}
			$total = $this->model('fav')->user_fav_count($me['id']);
		    $count = $this->model('fav')->title_fav_count($id);
			$me['fav'] = array('status'=>$status,'total'=>$total,'count'=>$count);
			$this->assign('me',$me);
			$this->data('me',$me);
		}
	}

	public function api_usercp_index_after($info)
	{
		if(!$info){
			$info = array();
		}
		$condition = "f.user_id='".$this->session->val('user_id')."'";
		$total = $this->model('fav')->get_count($condition);
		$info['fav'] = array('total'=>$total);
		$this->success($info);
	}

	/**
	 * 内容页是否包含收藏（仅限API接口）
	**/
	public function api_content_index_after($info)
	{
		if($this->session->val('user_id')){
			$chk = $this->model('fav')->chk($info['rs']['id'],$this->session->val('user_id'));
			$info['rs']['fav'] = $chk ? true : false;
		}else{
			$info['rs']['fav'] = false;
		}
		$this->success($info);
	}

	/**
	 * 内容页显示该主题是否已被会员显示（仅限WEB接口）
	**/
	public function www_content_index_after()
	{
		if($this->session->val('user_id')){
			$rs = $this->tpl->val('rs');
			$chk = $this->model('fav')->chk($rs['id'],$this->session->val('user_id'));
			$rs['fav'] = $chk ? true : false;
			$this->tpl->assign('rs',$rs);
		}
	}
}