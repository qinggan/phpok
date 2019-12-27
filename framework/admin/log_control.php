<?php
/**
 * 日志管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年05月07日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class log_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$keywords = $this->get('keywords');
		$pageurl = $this->url('log');
		$condition = '1=1';
		if($keywords){
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
			$condition .= " AND l.note LIKE '%".$keywords."%'";
		}
		$adminer = $this->get('adminer');
		if($adminer){
			$pageurl .= "&adminer=".rawurlencode($adminer);
			$this->assign('adminer',$adminer);
			$condition .= " AND a.account='".$adminer."'";
		}
		$user = $this->get('user');
		if($user){
			$pageurl .= "&user=".rawurlencode($user);
			$this->assign('user',$user);
			$condition .= " AND a.user='".$user."'";
		}
		$position = $this->get('position');
		if(!$position){
			$position = 'admin';
		}
		$pageurl .= "&position=".$position;
		$this->assign('position',$position);
		$condition .= " AND l.app_id='".$position."'";
		$start_time = $this->get('start_time');
		if($start_time){
			$pageurl .= "&start_time=".rawurlencode($start_time);
			$this->assign('start_time',$start_time);
			$condition .= " AND l.dateline>=".strtotime($start_time);
		}
		$stop_time = $this->get('stop_time');
		if($stop_time){
			$pageurl .= "&stop_time=".rawurlencode($stop_time);
			$this->assign('stop_time',$stop_time);
			$condition .= " AND l.dateline<=".(strtotime($stop_time) + 24 * 3600);
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		}
		$pageurl .= "&psize=".rawurlencode($psize);
		$this->assign('psize',$psize);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$total = $this->model('log')->get_count($condition);
		if($total>0){
			$rslist = $this->model('log')->get_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
		}
		$date30=date('Y-m-d',($this->time - 30*24*3600));
		$date7=date('Y-m-d',($this->time - 7*24*3600));
		$this->assign('date30',$date30);
		$this->assign('date7',$date7);
		$this->view('log_index');
	}

	public function delete_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('只有系统管理员才有此权限'));
		}
		$id = $this->get('id','int');
		$ids = $this->get('ids');
		$date = $this->get('date','int');
		if(!$id && !$ids && !$date){
			$this->error(P_Lang('参数不完整！'));
		}
		if($id){
			$condition = "id='".$id."'";
			$tip = P_Lang('删除日志#{id}',array('id'=>$id));
		}
		if($ids){
			$lst = explode(",",$ids);
			foreach($lst as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($lst[$key]);
					continue;
				}
				$lst[$key] = intval($value);
			}
			$ids = implode(",",$lst);
			if(!$ids){
				$this->error(P_Lang('未指定要删除的日志'));
			}
			$condition = "id IN(".$ids.")";
			$tip = P_Lang('删除日志#{id}',array('id'=>$ids));
		}
		if($date){
			$time = strtotime(date("Y-m-d",$this->time)) - $date*24*60*60;
			$condition = "dateline<".$time;
			$tip = P_Lang('删除{date}天前的日志',array('date'=>$date));
		}
		$this->model('log')->delete($condition);
		$this->model('log')->save($tip);
		$this->success();
	}
}
