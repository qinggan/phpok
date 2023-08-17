<?php
/**
 * 回复内容管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年07月31日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class reply_control extends phpok_control
{
	var $popedom;
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("reply");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 取得网站全部评论
	 * @参数 status 状态，1为已审核，2为未审核，0或空为全部
	 * @参数 keywords 关键字，要检索的关键字
	 * @参数 pageid 分页ID
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$pageurl = $this->url("reply");
		$status = $this->get("status","int");
		$condition = "(r.admin_id=0 OR r.admin_id>0 AND r.parent_id=0) ";
		if($status){
			$n_status = $status == 1 ? "1" : "0";
			$condition .= "AND r.status=".$n_status." ";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		//关键字
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= "AND (r.title LIKE '%".$keywords."%' OR r.content LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_total($condition);
		if($total){
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('reply')->get_all($condition,$offset,$psize);
			if(!isset($rslist)){
				$rslist = array();
			}
			$ids = array_keys($rslist);
			if(isset($ids)){
				//输出评论的点赞信息
				$clicklist = $this->model('click')->get_all($ids,'reply');
				if(isset($clicklist)){
					foreach($rslist as $key=>$value){
						if(!isset($clicklist[$value['id']])){
							continue;
						}
						$value['click_list'] = $clicklist[$value['id']];
						$rslist[$key] = $value;
					}
				}
			}
			
			$this->assign("rslist",$rslist);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("total",$total);
		$this->view("reply_index");
	}

	/**
	 * 读取某个主题下的全部评论
	 * @参数 tid 主题ID
	 * @参数 status 状态，1已审核，2未审核，0或空表示全部
	 * @参数 keywords 关键字，检索评论关键字
	 * @参数 pageid 分页ID
	**/
	public function list_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$tid = $this->get("tid","int");
		if(!$tid){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('list')->get_one($tid);
		$this->assign("rs",$rs);
		$pageurl = $this->url("reply","list","tid=".$tid);
		$status = $this->get('status',"int");
		$condition = "tid='".$tid."' AND parent_id=0";
		if($status){
			$n_status = $status == 1 ? "1" : "0";
			$condition .= " AND r.status='".$n_status."'";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= " AND (r.content LIKE '%".$keywords."%' OR r.title LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_total($condition);
		if(!$total){
			$this->model('log')->add(P_Lang('访问评论列表 #{0},【{1}】',array($tid,$rs['title'])));
			$this->view('list_comment');
		}
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('reply')->get_list($condition,$offset,$psize,"id DESC");
		if(!$rslist){
			$this->model('log')->add(P_Lang('访问评论列表 #{0},【{1}】',array($tid,$rs['title'])));
			$this->view('list_comment');
		}
		$uidlist = array();
		$ids = array();
		foreach($rslist as $key=>$value){
			$ids[] = $value['id'];
			if($value["uid"]){
				$uidlist[] = $value["uid"];
			}
		}
		$clicklist = $this->model('click')->get_all($ids,'reply');
		if(isset($clicklist)){
			foreach($rslist as $key=>$value){
				if(!isset($clicklist[$value['id']])){
					continue;
				}
				$value['click_list'] = $clicklist[$value['id']];
				$rslist[$key] = $value;
			}
		}

		$condition = "tid='".$tid."' AND parent_id IN(".implode(",",$ids).")";
		$sublist = $this->model('reply')->get_list($condition,0,0);
		if($sublist){
			foreach($sublist as $key=>$value){
				if($value["uid"]){
					$uidlist[] = $value["uid"];
				}
				$rslist[$value["parent_id"]]["sublist"][$value["id"]] = $value;
			}
		}
		
		if($uidlist && count($uidlist)>0){
			$uidlist = array_unique($uidlist);
			$ulist = $this->model('user')->get_all_from_uid(implode(",",$uidlist),'id');
			if(!$ulist){
				$ulist = array();
			}
			foreach($rslist as $key=>$value){
				if($value["uid"]){
					$value["uid"] = $ulist[$value["uid"]];
				}
				if($value["sublist"]){
					foreach($value["sublist"] AS $k=>$v){
						if($v){
							$v["uid"] = $ulist[$v["uid"]];
						}
						$value["sublist"][$k] = $v;
					}
				}
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		if($total>$psize){
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量').' (total)/(psize) '.P_Lang('页码').' (num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("total",$total);
		$this->model('log')->add(P_Lang('访问评论列表 #{0}，【{1}】，第 {2} 页',array($tid,$rs['title'],$pid)));
		$this->view('list_comment');
	}

	/**
	 * 变更评论的状态
	 * @参数 status 要变更的状态
	 * @参数 id 评论ID
	**/
	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		$status = $rs['status'] ? 0 : 1;
		$array = array("status"=>$status);
		$this->model('reply')->save($array,$id);
		if($status && $rs['tid'] && $rs['uid'] && ($rs['vtype'] == 'title' || $rs['vtype'] == 'order')){
			$this->model('wealth')->add_integral($rs['tid'],$rs['uid'],'comment',P_Lang('管理员审核评论#{id}',array('id'=>$id)));
		}
		$this->success($status);
	}

	/**
	 * 批量处理评论的状态
	 * @参数 status 要变更的状态
	 * @参数 id 评论ID
	**/
	public function status_pl_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$status = $this->get('status','int');
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$array = array("status"=>$status);
			$this->model('reply')->save($array,$value);
			if($status && $rs['tid'] && $rs['uid'] && ($rs['vtype'] == 'title' || $rs['vtype'] == 'order')){
				$this->model('wealth')->add_integral($rs['tid'],$rs['uid'],'comment',P_Lang('管理员审核评论#{id}',array('id'=>$id)));
			}
		}
		$this->success();
	}

	/**
	 * 删除回复
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$this->model('reply')->delete($value);
		}
		$this->success();
	}

	/**
	 * 新增评论
	 * @参数 tid 主题ID
	 * @返回 页面
	**/
	public function add_f()
	{
		if(!$this->popedom["modify"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$tid = $this->get('tid','int');
		$type = $this->get('type');
		if(!$tid || !$type){
			$this->error(P_Lang('未指定ID'));
		}
		if($type != 'title' && $type != 'order'){
			$this->error(P_Lang('不支持此功能，请检查'));
		}
		$title_rs = $this->model('list')->get_one($tid);
		$this->assign("title_rs",$title_rs);
		$this->assign('tid',$tid);
		$this->assign('type',$type);
		$edit_content = form_edit('content','','editor','width=680&height=180');
		$this->assign('edit_content',$edit_content);
		$this->assign('res_content',form_edit('pictures','','upload','is_multiple=1'));
		$this->assign('edit_user',form_edit('user_id','','user'));
		$this->view("reply_add");
	}

	/**
	 * 编辑评论内容
	 * @参数 id 评论ID
	**/
	public function edit_f()
	{
		if(!$this->popedom["modify"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('数据记录不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		if($rs['tid'] && in_array($rs['vtype'],array('title','order'))){
			$title_rs = $this->model('list')->get_one($rs["tid"]);
			$this->assign("title_rs",$title_rs);
		}
		$edit_content = form_edit('content',$rs['content'],'editor','width=680&height=180');
		$this->assign('edit_content',$edit_content);
		$this->assign('res_content',form_edit('pictures',$rs['res'],'upload','is_multiple=1'));
		$this->view("reply_content");
	}

	/**
	 * 保存评论
	 * @参数 id 评论ID
	 * @参数 star 星数，最多5，最少为0
	 * @参数 content 评论内容
	 * @参数 status 状态
	**/
	public function edit_save_f()
	{
		if(!$this->popedom["modify"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		$array = array();
		$array["star"] = $this->get("star","int");
		$array["content"] = $this->get("content",'html');
		$array["status"] = $this->get("status","int");
		$array['res'] = $this->get('pictures');
		$array['vouch'] = $this->get('vouch','int');
		if($id){
			$rs = $this->model('reply')->get_one($id);
			if(!$rs){
				$this->error(P_Lang('数据记录不存在'));
			}
			$this->model('reply')->save($array,$id);
			$this->model('log')->add(P_Lang('修改评论，评论ID #{0}_{1}',array($id,$rs['title'])));
			if($array["status"] && $rs['tid'] && $rs['uid'] && !$rs['status']){
				$this->model('wealth')->add_integral($rs['tid'],$rs['uid'],'comment',P_Lang('管理员编辑评论#{id}',array('id'=>$rs['id'])));
				$this->model('log')->add(P_Lang('审核评论ID #{0} 通过，增加积分操作',$id));
			}
		}else{
			$tid = $this->get('tid','int');
			if(!$tid){
				$this->error(P_Lang('未指定ID'));
			}
			$array['tid'] = $tid;
			$array['vtype'] = $this->get('type');
			$array['title'] = $this->get('title');
			$array['uid'] = $this->get('user_id','int');
			$array['admin_id'] = $this->session->val('admin_id');
			$array['addtime'] = $this->time;
			$this->model('reply')->save($array);
			$this->model('log')->add(P_Lang('新增评论，主题ID #{0}_{1}',array($tid,$array['title'])));
		}
		$this->success();
	}

	/**
	 * 管理员回复评论
	 * @参数 id 评论ID
	**/
	public function adm_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('数据记录不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$rslist = $this->model('reply')->adm_reply($id);
		$this->assign('rslist',$rslist);
		if($rs['tid'] && in_array($rs['vtype'],array('title','order'))){
			$title_rs = $this->model('list')->get_one($rs["tid"]);
			$this->assign("title_rs",$title_rs);
		}
		$edit_content = form_edit('content','','editor','width=680&height=300');
		$this->assign('edit_content',$edit_content);
		$this->view("reply_adm");
	}

	public function adm_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$array = array();
		$array["content"] = $this->get("content","html");
		$array["addtime"] = $this->time;
		$array['admin_id'] = $this->session->val('admin_id');
		$array['parent_id'] = $id;
		$array['status'] = 1;
		$this->model('reply')->save($array);
		$this->success();
	}
}