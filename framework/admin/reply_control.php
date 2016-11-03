<?php
/**
 * 回复内容管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
		$condition = "l.replydate>0";
		if($status){
			$n_status = $status == 1 ? "1" : "0";
			$condition .= " AND l.id IN(SELECT DISTINCT tid FROM ".$this->db->prefix."reply WHERE status='".$n_status."')";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		//关键字
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= " AND (l.title LIKE '%".$keywords."%' OR l.id IN(SELECT DISTINCT tid FROM ".$this->db->prefix."reply WHERE content LIKE '%".$keywords."%' OR adm_content LIKE '%".$keywords."%')) ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_all_total($condition);
		if($total>0){
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('reply')->get_all($condition,$offset,$psize,"id");
			$this->assign("rslist",$rslist);
			if($total>$psize){
				$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
				$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
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
			$condition .= " AND status='".$n_status."'";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= " AND (content LIKE '%".$keywords."%' OR adm_content LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_total($condition);
		if(!$total){
			$this->error(P_Lang('没有评论内容'));
		}
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('reply')->get_list($condition,$offset,$psize,"id");
		if(!$rslist){
			$this->error(P_Lang('没有找到评论内容'));
		}
		$uidlist = array();
		foreach($rslist AS $key=>$value){
			if($value["uid"]){
				$uidlist[] = $value["uid"];
			}
		}
		$idlist = array_keys($rslist);
		$condition = "tid='".$tid."' AND parent_id IN(".implode(",",$idlist).")";
		$sublist = $this->model('reply')->get_list($condition,0,0);
		if($sublist){
			foreach($sublist AS $key=>$value){
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
			foreach($rslist AS $key=>$value){
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
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("total",$total);
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
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$status = $this->get("status","int");
		$status = $status ? 1 : 0;
		$array = array("status"=>$status);
		$this->model('reply')->save($array,$id);
		$this->json("OK",true);
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
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('reply')->delete($id);
		$this->json(true);
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
		if(!$id) error_open(P_Lang('未指定ID'));
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			error_open(P_Lang('数据记录不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$title_rs = $this->model('list')->get_one($rs["tid"]);
		$this->assign("title_rs",$title_rs);
		$edit_content = form_edit('content',$rs['content'],'editor','width=680&height=180');
		$this->assign('edit_content',$edit_content);
		$this->view("reply_content");
	}

	/**
	 * 保存编辑的评论
	 * @参数 id 评论ID
	 * @参数 star 星数，最多5，最少为0
	 * @参数 content 评论内容
	 * @参数 status 状态
	**/
	public function edit_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$array = array();
		$array["star"] = $this->get("star","int");
		$array["content"] = $this->get("content",'html');
		$array["status"] = $this->get("status","int");
		$this->model('reply')->save($array,$id);
		$this->json(true);
	}

	/**
	 * 管理员回复评论
	 * @参数 id 评论ID
	**/
	public function adm_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			error(P_Lang('数据记录不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$title_rs = $this->model('list')->get_one($rs["tid"]);
		$this->assign("title_rs",$title_rs);
		$edit_content = form_edit('content',$rs['adm_content'],'editor','width=680&height=180&etype=simple&btn_image=1');
		$this->assign('edit_content',$edit_content);
		$this->view("reply_adm");
	}

	public function adm_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$array = array();
		$array["adm_content"] = $this->get("content","html");
		$array["adm_time"] = $this->time;
		$this->model('reply')->save($array,$id);
		$this->json(true);
	}
	
}
?>