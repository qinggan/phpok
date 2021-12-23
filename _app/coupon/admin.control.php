<?php
/**
 * 后台管理_适用于整个PHPOK5平台的优惠系统
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年01月02日 15时35分
**/
namespace phpok\app\control\coupon;
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
		$this->popedom = appfile_popedom('coupon');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有权限查看优惠方案'));
		}
		$keywords = $this->get('keywords');
		$pageurl = $this->url('coupon');
		$condition = '1=1';
		if($keywords){
			if($keywords['title']){
				$condition .= " AND title LIKE '%".$keywords['title']."%' ";
				$pageurl .= "&keywords[title]=".rawurlencode($keywords['title']);
			}
			if($keywords['code']){
				$condition .= " AND code LIKE '%".$keywords['code']."%' ";
				$pageurl .= "&keywords[code]=".rawurlencode($keywords['code']);
			}
			if($keywords['status']){
				$condition .= " AND status='".($keywords['status'] == 1 ? 1 : 0)."' ";
				$pageurl .= "&keywords[status]=".rawurlencode($keywords['status']);
			}
			if($keywords['startdate']){
				$condition .= " AND startdate>='".strtotime($keywords['startdate'])."' ";
				$pageurl .= "&keywords[startdate]=".rawurlencode($keywords['startdate']);
			}
			if($keywords['stopdate']){
				$condition .= " AND stopdate<='".strtotime($keywords['stopdate'])."' ";
				$pageurl .= "&keywords[stopdate]=".rawurlencode($keywords['stopdate']);
			}
			if($keywords['types']){
				$condition .= " AND types='".$keywords['types']."' ";
				$pageurl .= "&keywords[types]=".rawurlencode($keywords['types']);
			}
			$this->assign('keywords',$keywords);
		}
		$total = $this->model('coupon')->get_total($condition);
		if($total){
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$psize = $this->config['psize'] ? $this->config['psize'] : 20;
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('coupon')->get_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageurl',$pageurl);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->display('admin_index');
	}

	public function set_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限添加优惠方案'));
			}
		}else{
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限编辑优惠方案'));
			}
			$rs = $this->model('coupon')->get_one($id);
			if(!$rs){
				$this->error(P_Lang('优惠方案信息不存在'));
			}
			if($rs['startdate']){
				$rs['startdate'] = date("Y-m-d",$rs['startdate']);
			}
			if($rs['stopdate']){
				$rs['stopdate'] = date("Y-m-d",$rs['stopdate']);
			}
			$rs['time_start'] = $rs['time_start'] ? date("H:i:s",$rs['time_start']) : '';
			$rs['time_stop'] = $rs['time_stop'] ? date("H:i:s",$rs['time_stop']) : '';
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		$condition = "module>0 AND is_biz>0 AND status=1";
		$plist = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',$condition);
		if(!$plist){
			$this->error(P_Lang('系统没有找到带电商的项目，请先开启电商功能'));
		}
		$this->assign('plist',$plist);
		//读取分类
		if($id && $rs && $rs['pid']){
			$project = $this->model('project')->get_one($rs['pid'],false);
			if($project['cate']){
				$catelist = $this->model('cate')->get_all($project["site_id"],0,$project["cate"]);
				$catelist = $this->model('cate')->cate_option_list($catelist);
				$this->assign('catelist',$catelist);
			}
		}
		//读取用户组
		$grouplist = $this->model('usergroup')->get_all("status=1 AND is_guest=0");
		if($grouplist){
			$this->assign('grouplist',$grouplist);
		}
		//查看指定的用户
		$str ="is_multiple=1&note=".P_Lang('指定可以使用优惠方案的用户，与用户组共用');
		$users_html = form_edit('users',$rs['users'],'user',$str);
		$this->assign('users_html',$users_html);
		$freq_list = $this->model('coupon')->freq_list();
		$this->assign('freq_list',$freq_list);
		$edit_content = form_edit('content',$rs['content'],'editor','height=300&btns[image]=1');
		$this->assign('edit_content',$edit_content);
		//读洲，国家
		$zonelist = $this->model('worlds')->get_all("pid=0");
		$countrylist = array();
		foreach($zonelist as $key=>$value){
			$tmplist = $this->model('worlds')->get_all("pid=".$value['id']);
			if($tmplist){
				$countrylist[] = array("id"=>$value['id'],"name"=>$value['name'],'name_en'=>$value['name_en'],'rslist'=>$tmplist);
			}
		}
		$this->assign('countrylist',$countrylist);
		if($rs && $rs['country_id']){
			$country = $this->model('worlds')->get_one($rs['country_id']);
			$this->assign('country',$country);
		}
		$this->display('admin_coupon_set');
	}
	
	public function save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限添加优惠方案'));
			}
		}else{
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限编辑优惠方案'));
			}
		}
		$data = array();
		$data['title'] = $this->get('title');
		if(!$data['title']){
			$this->error(P_Lang('优惠方案名称不能为空'));
		}
		$data['code'] = $this->get('code');
		if(!$data['code']){
			$this->error(P_Lang('优惠方案不能为空'));
		}
		$check = $this->model('coupon')->check($data['code'],$id);
		if($check){
			$this->error(P_Lang('优惠方案已被使用，请换一个'));
		}
		$data['pic1'] = $this->get('pic1');
		$data['pic2'] = $this->get('pic2');
		$data['pic3'] = $this->get('pic3');
		$data['country_id'] = $this->get('country_id');
		$data['times'] = $this->get('times','int');
		$startdate = $this->get('startdate');
		if(!$startdate){
			$this->error(P_Lang('请选择优惠方案启用时间'));
		}
		$data['startdate'] = strtotime($startdate);
		$stopdate = $this->get('stopdate');
		if(!$stopdate){
			$this->error(P_Lang('请选择优惠方案结束时间'));
		}
		$data['stopdate'] = strtotime($stopdate);
		if($data['stopdate'] < $data['startdate']){
			$this->error(P_Lang('结束时间不能小于开始时间'));
		}
		if(!$id && $data['stopdate'] <= $this->time){
			$this->error(P_Lang('结束时间不能小于当前时间'));
		}
		$data['types'] = $this->get('types');
		$data['user_groupid'] = $this->get('user_groupid','int');
		$data['users'] = $this->get('users');
		$data['pid'] = $this->get('pid','int');
		$data['cateid'] = $this->get('cateid','int');
		$data['tids'] = $this->get('tids');
		if($data['types'] == 'list' && !$data['pid'] && !$data['tids']){
			$this->error(P_Lang('基于产品的优惠方案模式请选择项目或指定产品ID'));
		}
		$data['is_multiple'] = $this->get('is_multiple','int');
		$data['discount_val'] = $this->get('discount_val','float');
		if($data['discount_val'] < 0.001){
			$this->error(P_Lang('优惠值小于0.001，系统判断为无优惠，请重新设置'));
		}
		$data['discount_type'] = $this->get('discount_type','int');
		$data['min_price'] = $this->get('min_price','float');
		if(!$id){
			$data['site_id'] = $this->session->val('admin_site_id');
			$data['dateline'] = $this->time;
		}
		$data['status'] = $this->get('status','int');
		$data['taxis'] = $this->get('taxis','int');
		$data['time_start'] = $this->get('time_start','time');
		$data['time_stop'] = $this->get('time_stop','time');
		$data['is_vouch'] = $this->get('is_vouch','int');
		$data['freq'] = $this->get('freq');
		$data['content'] = $this->get('content','html');
		$this->model('coupon')->save($data,$id);
		$this->success();
	}

	public function check_f()
	{
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('优惠方案不能为空'));
		}
		$id = $this->get('id','int');
		$check = $this->model('coupon')->check($code,$id);
		if($check){
			$this->error(P_Lang('优惠方案已被使用，请换一个'));
		}
		$this->success();
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除优惠方案的权限'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要删除的优惠方案ID'));
		}
		$this->model('coupon')->delete($id);
		$this->success();
	}

	public function o_delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除权限'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定要删除的优惠券');
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($list[$key]);
				continue;
			}
		}
		if(!$list || count($list)<1){
			$this->error('未指定要删除的优惠券');
		}
		$id = implode(",",$list);
		$sql = "DELETE FROM ".$this->db->prefix."coupon_history WHERE id IN(".$id.")";
		$this->db->query($sql);
		$this->success();
	}

	public function status_f()
	{
		if(!$this->popedom["status"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('没有指定ID'));
		}
		$rs = $this->model('coupon')->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->model('coupon')->set_status($id,$status);
		$this->success($status);
	}

	public function taxis_f()
	{
		if(!$this->popedom["modify"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('没有指定ID'));
		}
		$taxis = $this->get('taxis','int');
		$data = array('taxis'=>$taxis);
		$this->model('coupon')->save($data,$id);
		$this->success();
	}

	public function u_clear_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除权限'));
		}
		$sql = "DELETE FROM ".$this->db->prefix."coupon_user WHERE stopdate<=".$this->time;
		$this->db->query($sql);
		$this->success();
	}

	public function u_delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除权限'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定要删除的优惠券');
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($list[$key]);
				continue;
			}
		}
		if(!$list || count($list)<1){
			$this->error('未指定要删除的优惠券');
		}
		$id = implode(",",$list);
		$sql = "DELETE FROM ".$this->db->prefix."coupon_user WHERE id IN(".$id.")";
		$this->db->query($sql);
		$this->success();
	}

	public function used_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有权限查看优惠方案'));
		}
		$keywords = $this->get('keywords');
		$pageurl = $this->url('coupon','used');
		$condition = "c.site_id='".$this->site['id']."'";
		if($keywords){
			if($keywords['title']){
				$condition .= " AND c.title LIKE '%".$keywords['title']."%' ";
				$pageurl .= "&keywords[title]=".rawurlencode($keywords['title']);
			}
			if($keywords['code']){
				$condition .= " AND c.code LIKE '%".$keywords['code']."%' ";
				$pageurl .= "&keywords[code]=".rawurlencode($keywords['code']);
			}
			if($keywords['startdate']){
				$condition .= " AND h.dateline>='".strtotime($keywords['startdate'])."' ";
				$pageurl .= "&keywords[startdate]=".rawurlencode($keywords['startdate']);
			}
			if($keywords['stopdate']){
				$condition .= " AND h.dateline<='".strtotime($keywords['stopdate'])."' ";
				$pageurl .= "&keywords[stopdate]=".rawurlencode($keywords['stopdate']);
			}
			$this->assign('keywords',$keywords);
		}
		$total = $this->model('coupon')->history_total($condition);
		if($total){
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$psize = $this->config['psize'] ? $this->config['psize'] : 20;
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('coupon')->history_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageurl',$pageurl);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->display('admin_history');
	}

	public function users_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有权限查看优惠方案'));
		}
		$keywords = $this->get('keywords');
		$pageurl = $this->url('coupon','used');
		$condition = "c.site_id='".$this->site['id']."'";
		if($keywords){
			if($keywords['title']){
				$condition .= " AND c.title LIKE '%".$keywords['title']."%' ";
				$pageurl .= "&keywords[title]=".rawurlencode($keywords['title']);
			}
			if($keywords['code']){
				$condition .= " AND c.code LIKE '%".$keywords['code']."%' ";
				$pageurl .= "&keywords[code]=".rawurlencode($keywords['code']);
			}
			$keytype = $keywords['type'];
			if(!$keytype){
				$keytype = 'dateline';
			}
			if(!in_array($keytype,array('dateline','startdate','stopdate'))){
				$keytype = 'dateline';
			}
			if($keywords['startdate']){
				$condition .= " AND h.".$keytype.">='".strtotime($keywords['startdate'])."' ";
				$pageurl .= "&keywords[startdate]=".rawurlencode($keywords['startdate']);
			}
			if($keywords['stopdate']){
				$condition .= " AND h.".$keytype."<='".strtotime($keywords['stopdate'])."' ";
				$pageurl .= "&keywords[stopdate]=".rawurlencode($keywords['stopdate']);
			}
			$this->assign('keywords',$keywords);
		}
		$total = $this->model('coupon')->received_total($condition);
		if($total){
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$psize = $this->config['psize'] ? $this->config['psize'] : 20;
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('coupon')->received_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageurl',$pageurl);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->display('admin_users');
	}
}
