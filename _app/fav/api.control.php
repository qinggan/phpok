<?php
/**
 * 收藏夹相关功能接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月04日
**/
namespace phpok\app\control\fav;

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

	/**
	 * 加入收藏
	 * @参数 id 主题ID
	**/
	public function add_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要收藏的主题ID'));
		}
		$chk = $this->model('fav')->chk($id,$this->session->val('user_id'));
		if($chk){
			$this->error(P_Lang('主题已经收藏过，不能重复收藏'));
		}
		$rs = $this->call->phpok('_arc','title_id='.$id);
		if(!$rs){
			$this->error(P_Lang('内容不存在'));
		}
		$data = array('user_id'=>$this->session->val('user_id'));
		$type = ($this->config['fav'] && $this->config['fav']['thumb_id']) ? $this->config['fav']['thumb_id'] : 'thumb';
		if($rs[$type]){
			if(is_array($rs[$type])){
				$data['thumb'] = $rs[$type]['filename'];
			}else{
				$data['thumb'] = $rs[$type];
			}
		}
		$data['title'] = $rs['title'];
		$type = ($this->config['fav'] && $this->config['fav']['note_id']) ? $this->config['fav']['note_id'] : 'content';
		if($rs[$type]){
			$data['note'] = $this->lib('string')->cut($rs[$type],80,'…',false);
		}
		$data['addtime'] = $this->time;
		$data['lid'] = $id;
		$this->model('fav')->save($data);
		$this->success();
	}

	/**
	 * 删除收藏的主题
	 * @参数 id 收藏表中 qinggan_fav 里的主键ID，注意噢，不是主题ID
	 * @参数 lid 主题ID
	**/
	public function delete_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$lid = $this->get('lid','int');
			if(!$lid){
				$this->error(P_Lang('未指定ID'));
			}
			$chk = $this->model('fav')->chk($lid,$this->session->val('user_id'));
			if(!$chk){
				$this->error(P_Lang('没有找到要删除的记录'));
			}
			$id = $chk['id'];
		}
		$rs = $this->model('fav')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('数据不存在'));
		}
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您没有权限删除'));
		}
		$this->model('fav')->delete($id);
		$this->success();
	}

	/**
	 * 检测主题是否已存在
	 * @参数 $id 主题ID
	**/
	public function check_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主题ID'));
		}
		$rs = $this->model('fav')->chk($id,$_SESSION['user_id']);
		if($rs){
			$this->success($rs['id']);
		}
		$this->success(0);
	}

	/**
	 * 读取收藏列表
	 * @参数 $pageid 页码ID
	 * @参数 $psize 每页数量
	**/
	public function index_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，不能执行此操作'));
		}
		$condition = "f.user_id='".$this->session->val('user_id')."'";
		$total = $this->model('fav')->get_count($condition);
		if(!$total){
			$this->error(P_Lang('您的收藏夹还是空的噢'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('fav')->get_all($condition,$offset,$psize);
		$data = array('total'=>$total,'pageid'=>$pageid,'psize'=>$psize,'rslist'=>$rslist);
		$this->success($data);
	}

	/**
	 * 执行动作，未添加收藏时进行添加操作，已添加执行取消操作
	 * @参数 $id 主题ID
	**/
	public function act_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主题ID'));
		}
		$chk = $this->model('fav')->chk($id,$this->session->val('user_id'));
		if($chk){
			$this->model('fav')->delete($chk['id']);
			$this->success('delete');
		}
		$rs = $this->call->phpok('_arc','title_id='.$id);
		if(!$rs){
			$this->error(P_Lang('内容不存在'));
		}
		$data = array('user_id'=>$this->session->val('user_id'));
		$type = ($this->config['fav'] && $this->config['fav']['thumb_id']) ? $this->config['fav']['thumb_id'] : 'thumb';
		if($rs[$type]){
			if(is_array($rs[$type])){
				$data['thumb'] = $rs[$type]['filename'];
			}else{
				$data['thumb'] = $rs[$type];
			}
		}
		$data['title'] = $rs['title'];
		$type = ($this->config['fav'] && $this->config['fav']['note_id']) ? $this->config['fav']['note_id'] : 'content';
		if($rs[$type]){
			$data['note'] = $this->lib('string')->cut($rs[$type],80,'…',false);
		}
		$data['addtime'] = $this->time;
		$data['lid'] = $id;
		$this->model('fav')->save($data);
		$this->success('add');
	}
}
