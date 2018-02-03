<?php
/**
 * 评论信息
 * @package phpok\api
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
		if(!$groupid)
		{
			$this->json(P_Lang('无法获取前端用户组信息'));
		}
		$this->user_groupid = $groupid;
	}

	//获取评论信息
	public function index_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定主题'));
		}
		$condition = "tid='".$id."' AND parent_id='0' ";
		$condition .= " AND (status=1 OR (status=0 AND (uid=".$_SESSION['user_id']." OR session_id='".session_id()."'))) ";
		$vouch = $this->get('vouch','int');
		if($vouch){
			$condition .= " AND vouch=1 ";
		}
		$total = $this->model('reply')->get_total($condition);
		if(!$total){
			$this->json(P_Lang('暂无评论信息'));
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
		foreach($rslist AS $key=>$value){
			if($value["uid"]){
				$userlist[] = $value["uid"];
			}
			$idlist[] = $value["id"];
		}
		//读取回复的回复
		$idstring = implode(",",$idlist);
		$condition  = " parent_id IN(".$idstring.") ";
		$condition .= " AND (status=1 OR (status=0 AND (uid=".$_SESSION['user_id']." OR session_id='".session_id()."'))) ";
		$sublist = $this->model('reply')->get_list($condition,0,0);
		if($sublist){
			$mylist = array();
			foreach($sublist AS $key=>$value){
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
			$tmplist = $this->model('user')->get_list($condition,0,0);
			if($tmplist){
				$userlist = array();
				foreach($tmplist AS $key=>$value){
					$userlist[$value["id"]] = $value;
				}
				$tmplist = "";
			}
		}
		//整理回复列表
		foreach($rslist as $key=>$value){
			if($mylist && $mylist[$value["id"]]){
				foreach($mylist[$value["id"]] AS $k=>$v){
					if($v["uid"] && $userlist){
						$v["uid"] = $userlist[$v["uid"]];
					}
					$mylist[$value["id"]][$k] = $v;
				}
				$value["sonlist"] = $mylist[$value["id"]];
			}
			if($value["uid"] && $userlist){
				$value["uid"] = $userlist[$value["uid"]];
			}
			$rslist[$key] = $value;
		}
		$pageurl = $this->url($id);
		$this->assign("rslist",$rslist);
		$this->assign("pageurl",$pageurl);
		$this->assign("pageid",$start);
		$this->assign("psize",$psize);
		$this->assign("total",$total);
		$html = $this->fetch("api_comment");
		$this->json($html,true,true,false);
	}

	/**
	 * 存储评论信息
	**/
	public function save_f()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$tid = $this->get('id','int');
		}
		if(!$tid){
			$this->json(P_Lang('未指定主题'));
		}
		$uid = $this->session->val('user_id');
		$rs = $this->model('list')->call_one($tid);
		//判断是否需要验证码
		if($this->model('site')->vcode($rs['project_id'],'comment')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->json(P_Lang('验证码填写不正确'));
			}
			$this->session->unassign('vcode');
		}
		$order_id = $this->get('order_id','int');
		if($order_id){
			if(!$uid){
				$this->json(P_Lang('非会员不能评论'));
			}
			$rs = $this->model('order')->get_one($order_id);
			if(!$rs){
				$this->json(P_Lang('订单信息不存在'));
			}
			if($rs['user_id'] != $uid){
				$this->json(P_Lang('您没有权限对此订单产品进行评论'));
			}
			$plist = $this->model('order')->product_list($order_id);
			if(!$plist){
				$this->json(P_Lang('订单中没有指定的产品'));
			}
			$check = false;
			foreach($plist as $key=>$value){
				if($value['tid'] == $tid){
					$check = true;
				}
			}
			if(!$check){
				$this->json(P_Lang('订单中没有此产品'));
			}
		}else{
			$sessid = $this->session->sessid();
			$chk = $this->model('reply')->check_time($tid,$uid,$sessid);
			if(!$chk){
				$this->json(P_Lang('30秒内同一主题只能回复一次'));
			}
			$project_rs = $this->model('project')->get_one($rs['project_id'],false);
			if(!$project_rs['comment_status']){
				$this->json(P_Lang('未启用评论功能'));
			}
		}
		$parent_id = $this->get("parent_id","int");
		if($this->session->val('user_id')){
			$content = $this->get('comment','html');
			$tmp = strip_tags($content);
			if(!$tmp){
				$this->json(P_Lang("评论内容不能为空"));
			}
		}else{
			$content = $this->get("comment");
		}
		if(!$content){
			$this->json(P_Lang("评论内容不能为空"));
		}
		$star = $this->get("star",'int');
		$array = array();
		$array["tid"] = $tid;
		$array["parent_id"] = $parent_id;
		$array["star"] = $star;
		$array["uid"] = $uid;
		$array["ip"] = $this->lib('common')->ip();
		$array["addtime"] = $this->time;
		$array["status"] = $this->model('popedom')->val($rs['project_id'],$this->user_groupid,'reply1');
		$array["session_id"] = $sessid;
		$array["content"] = $content;
		$array['order_id'] = $order_id;
		$this->model("reply")->save($array);
		$update = array("replydate"=>$this->time);
		$this->model("list")->save($update,$tid);
		//评论送积分
		if($uid && $array["status"]){
			$this->model('wealth')->add_integral($tid,$uid,'comment',P_Lang('评论：{title}',array('title'=>$rs['title'])));
		}
		$this->json(true);
	}
}