<?php
/**
 * 接口应用_用于平台用户内部沟通交流，同样适用于APP互动交流，发留言有副本，接收人员也有一份副本
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年12月25日 22时25分
**/
namespace phpok\app\control\pm;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		//$info = "";
		//$this->error($info);
		$this->success();
	}

	public function read_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('未会员不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('pm')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('短消息不存在'));
		}
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您没有权限操作此消息信息'));
		}
		$data = array('isread'=>1,'readtime'=>$this->time);
		$this->model('pm')->save($data,$id);
		$this->success();
	}

	public function all_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('未会员不能执行此操作'));
		}
		$this->model('pm')->set_read_all($this->session->val('user_id'));
		$this->success();
	}
}
