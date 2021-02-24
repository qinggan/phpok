<?php
/**
 * 评论信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class comment_control extends phpok_control
{
	private $user_groupid;
	public function __construct()
	{
		parent::control();
		$this->model('popedom')->siteid($this->site['id']);
		$groupid = $this->model('usergroup')->group_id($_SESSION['user_id']);
		if(!$groupid){
			$this->json(P_Lang('无法获取前端用户组信息'));
		}
		$this->user_groupid = $groupid;
	}

	/**
	 * 管理员删除评论，仅适用于已登录了后台
	**/
	public function admin_delete_f()
	{
		if(!$this->session->val('admin_id')){
			$this->error(P_Lang('您不是管理员，不能执行此操作'));
		}
		if(!$this->session->val('admin_rs.if_system')){
			if(!$this->model('popedom')->admin_check($this->session->val('admin_id'),'reply','delete')){
				$this->error(P_Lang('您没有权限删除'));
			}
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要删除的回复ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('回复信息不存在'));
		}
		$this->model('reply')->delete($id);
		$this->model('log')->save(P_Lang('删除回复信息，ID是[id]，主题ID是 #[tid]',array('id'=>$id,'tid'=>$rs['tid'])));
		$this->success();
	}


	/**
	 * 删除评论信息
	 * @参数 $id 要删除的评论ID
	**/
	public function delete_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要删除的回复ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('回复信息不存在'));
		}
		if(!$rs['uid'] || $rs['uid'] != $this->session->val('user_id')){
			$this->error(P_Lang('您没有权限删除此数据'));
		}
		$this->model('reply')->delete($id);
		$this->model('log')->save(P_Lang('删除回复操作'));
		$this->success();
	}

	//获取评论信息
	public function index_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主题'));
		}
		$condition = "tid='".$id."' AND parent_id='0' ";
		if($this->session->val('user_id')){
			$condition .= " AND (status=1 OR (status=0 AND (uid=".$this->session->val('user_id')." OR session_id='".$this->session->sessid()."'))) ";
		}else{
			$condition .= " AND (status=1 OR (status=0 AND session_id='".$this->session->sessid()."')) ";
		}
		
		$vouch = $this->get('vouch','int');
		if($vouch){
			$condition .= " AND vouch=1 ";
		}
		$total = $this->model('reply')->get_total($condition);
		if(!$total){
			$this->error(P_Lang('暂无评论信息'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$start = ($pageid-1) * $psize;
		$rslist = $this->model('reply')->get_list($condition,$start,$psize,"","id ASC");
		$idlist = $userlist = array();
		foreach($rslist as $key=>$value){
			if($value["uid"]){
				$userlist[] = $value["uid"];
			}
			$idlist[] = $value["id"];
		}
		//读取回复的回复
		$idstring = implode(",",$idlist);
		$condition  = " parent_id IN(".$idstring.") AND admin_id=0 ";
		if($this->session->val('user_id')){
			$condition .= " AND (status=1 OR (status=0 AND (uid=".$this->session->val('user_id')." OR session_id='".$this->session->sessid()."'))) ";
		}else{
			$condition .= " AND (status=1 OR (status=0 AND session_id='".session_id()."')) ";
		}
		
		$sublist = $this->model('reply')->get_list($condition,0,0);
		if($sublist){
			$mylist = array();
			foreach($sublist as $key=>$value){
				if($value["uid"]){
					$userlist[] = $value["uid"];
				}
				$mylist[$value["parent_id"]][] = $value;
			}
		}
		
		//获取会员信息
		if($userlist && count($userlist)>0){
			$userlist = array_unique($userlist);
			$user_idstring = implode(",",$userlist);
			$condition = "u.status='1' AND u.id IN(".$user_idstring.")";
			$userlist = $this->model('user')->get_list($condition,0,0);
		}
		//整理回复列表
		foreach($rslist as $key=>$value){
			if($mylist && $mylist[$value["id"]]){
				foreach($mylist[$value["id"]] as $k=>$v){
					$v['user'] = $this->model('user')->show_info($userlist[$v['uid']]);
					$mylist[$value["id"]][$k] = $v;
				}
				$value["sonlist"] = $mylist[$value["id"]];
			}
			$value["user"] = $this->model('user')->show_info($userlist[$value["uid"]]);
			$rslist[$key] = $value;
		}
		$data = array('total'=>$total,'pageid'=>$pageid,'psize'=>$psize,'rslist'=>$rslist);
		$this->success($data);
	}

	/**
	 * 存储评论信息
	 * @参数 vtype 评论类型，仅支持：title 主题，project 项目，cate 分类，order 订单，留空或是没有获取成功则读取主题
	 * @参数 tid 主题ID，当type为title时此项必填，当为order时， tid指为订单中的具体某个产品
	 * @参数 _chkcode 验证码，仅限评论为主题时有效，其他的评论必须是会员
	 * @参数 parent_id 父级评论ID
	 * @参数 star 评论等级，留空默认为3
	 * @参数 comment 评论内容，会员评论时支持HTML，游客仅支持文本
	 * @参数 pictures 评论的时候上传的一些图片，或附件
	 * @参数 order_id 订单ID
	**/
	public function save_f()
	{
		$this->config('is_ajax',true);
		$this->node('PHPOK_post_ok');
		$type = $this->get('vtype');
		if(!$type){
			$type = 'title';
		}
		if(!$type || !in_array($type,array('title','project','cate','order'))){
			$this->error(P_Lang('评论类型不对，请检查'));
		}
		$data = array('vtype'=>$type);
		$uid = $this->session->val('user_id');
		if($uid){
			$data['uid'] = $uid;
		}
		$user_groupid = $this->model('usergroup')->group_id($uid);
		if(!$user_groupid){
			$this->error(P_Lang('无法获取用户组信息，请检查'));
		}
		$parent_id = $this->get("parent_id","int");
		if($parent_id){
			$data['parent_id'] = $parent_id;
		}
		$data["ip"] = $this->lib('common')->ip();
		$data["addtime"] = $this->time;
		$data["star"] = $this->get('star','int');
		if(!$data['star']){
			$data['star'] = 3;
		}
		$data["session_id"] = $this->session->sessid();
		$_clearVcode = false;
		if($type == 'title'){
			$tid = $this->get('tid','int');
			if(!$tid && !$parent_id){
				$this->error(P_Lang('未指定要评论主题'));
			}
			if(!$tid && $parent_id){
				$comment = $this->model('reply')->get_one($parent_id);
				if(!$comment || !$comment['tid']){
					$this->error(P_Lang('未指定要评论主题'));
				}
				$tid = $comment['tid'];
			}
			$rs = $this->model('list')->call_one($tid);
			if(!$rs){
				$this->error(P_Lang('要评论的主题不存在'));
			}
			$project_rs = $this->model('project')->get_one($rs['project_id'],false);
			if(!$project_rs['comment_status']){
				$this->error(P_Lang('未启用评论功能'));
			}
			$data['tid'] = $rs['id'];
			$data['title'] = $rs['title'];
			if($this->model('site')->vcode($rs['project_id'],'comment')){
				$code = $this->get('_chkcode');
				if(!$code){
					$this->error(P_Lang('验证码不能为空'));
				}
				$code = md5(strtolower($code));
				if($code != $this->session->val('vcode')){
					$this->error(P_Lang('验证码填写不正确'));
				}
				$_clearVcode = true;
			}
			$data["status"] = $this->model('popedom')->val($rs['project_id'],$user_groupid,'reply1');
			$sessid = $this->session->sessid();
			$chk = $this->model('reply')->check_time($tid,$uid,$data["session_id"]);
			if(!$chk){
				$this->error(P_Lang('30秒内同一主题只能回复一次'));
			}
		}elseif($type == 'order'){
			if(!$uid){
				$this->error(P_Lang('非会员不能对订单进行评论'));
			}
			$order_id = $this->get('order_id','int');
			if(!$order_id){
				$this->error(P_Lang('未指定订单ID'));
			}
			$order = $this->model('order')->get_one($order_id);
			if(!$order){
				$this->error(P_Lang('订单信息不存在'));
			}
			if($order['user_id'] != $uid){
				$this->error(P_Lang('您没有权限对此订单产品进行评论'));
			}
			$data['order_id'] = $order_id;
			$tid = $this->get('tid','int');
			if(!$tid){
				$this->error(P_Lang('需要指定订单中的产品ID'));
			}
			$plist = $this->model('order')->product_list($order_id);
			if(!$plist){
				$this->error(P_Lang('订单中没有指定的产品'));
			}
			$check = false;
			$rs = array();
			foreach($plist as $key=>$value){
				if($value['tid'] == $tid){
					$check = true;
					$rs = $value;
					break;
				}
			}
			if(!$check){
				$this->error(P_Lang('订单中没有此产品'));
			}
			$data['title'] = '#'.$order['sn'].'_'.$rs['title'];
			$data["status"] = 0;
			$data['tid'] = $tid;
		}elseif($type == 'project'){
			if(!$uid){
				$this->error(P_Lang('非会员不能对项目进行评论'));
			}
			$tid = $this->get('tid','int');
			if(!$tid && !$parent_id){
				$this->error(P_Lang('未指定哪个项目'));
			}
			if(!$tid && $parent_id){
				$comment = $this->model('reply')->get_one($parent_id);
				if(!$comment || !$comment['tid']){
					$this->error(P_Lang('未指定要评论的项目'));
				}
				$tid = $comment['tid'];
			}
			$project_rs = $this->model('project')->get_one($tid,false);
			if(!$project_rs){
				$this->error(P_Lang('项目不存在'));
			}
			$data['title'] = $project_rs['title'];
			$data["status"] = 0;
			$data['tid'] = $tid;
		}elseif($type == 'cate'){
			if(!$uid){
				$this->error(P_Lang('非会员不能对分类进行评论'));
			}
			$tid = $this->get('tid','int');
			if(!$tid && !$parent_id){
				$this->error(P_Lang('未指定分类'));
			}
			if(!$tid && $parent_id){
				$comment = $this->model('reply')->get_one($parent_id);
				if(!$comment || !$comment['tid']){
					$this->error(P_Lang('未指定要评论的项目'));
				}
				$tid = $comment['tid'];
			}
			$cate_rs = $this->model('cate')->get_one($tid,'id',false);
			if(!$cate_rs){
				$this->error(P_Lang('分类不存在'));
			}
			$data['title'] = $cate_rs['title'];
			$data["status"] = 0;
			$data['tid'] = $tid;
		}
		$content = $uid ? $this->get('comment','html') : $this->get('comment');
		if(!$content){
			$this->error(P_Lang('评论内容不能为空'));
		}
		$data['content'] = $content;
		$data['res'] = $this->get('pictures'); //绑定附件，如果用户有上传附件，仅支持jpg,gif,png,zip,rar
		$insert_id = $this->model("reply")->save($data);
		if(!$insert_id){
			$this->error(P_Lang('评论保存失败，请联系管理员'));
		}
		if($_clearVcode){
			$this->session->unassign('vcode');
		}
		if($tid && in_array($type,array('title','order'))){
			$update = array("replydate"=>$this->time);
			$this->model("list")->save($update,$tid);
		}
		//评论送积分
		if($tid && $uid && $data["status"]){
			$this->model('wealth')->add_integral($tid,$uid,'comment',P_Lang('评论：{title}',array('title'=>$rs['title'])));
		}
		//增加通知任务
		if($project_rs && $project_rs['etpl_comment_admin'] || $project_rs['etpl_comment_user']){
			$param = 'id='.$insert_id;
			$this->model('task')->add_once('comment',$param);
		}
		$this->success();
	}
}