<?php
/**
 * 支付管理工具，用于管理接口信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月24日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_control extends phpok_control
{
	private $popedom;
	
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("payment");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 读取所有可用的支付接口
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		//取得符合要求的全部组
		$rslist = $this->model('payment')->group_all($_SESSION['admin_site_id']);
		if($rslist){
			foreach($rslist as $key=>$value){
				$rslist[$key]['paylist'] = $this->model('payment')->get_all("p.gid=".$value['id']);
			}
			$this->assign('rslist',$rslist);
		}
		$codelist = $this->model('payment')->code_all();
		$this->assign('codelist',$codelist);
		$this->view('payment_index');
	}

	/**
	 * 设置支付组信息
	**/
	public function groupset_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom["groupedit"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('payment')->group_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}else{
			if(!$this->popedom["groupadd"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$this->view('payment_groupset');
	}

	/**
	 * 存储支付组信息，返回JSON数据
	**/
	public function groupsave_f()
	{
		$id = $this->get('id','int');
		$popedom_id = $id ? 'groupedit' : 'groupadd';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('名称不能为空'));
		}
		$data = array('site_id'=>$_SESSION['admin_site_id'],'title'=>$title);
		$data['taxis'] = $this->get('taxis','int');
		$data['status'] = $this->get('status','int');
		$data['is_wap'] = $this->get('is_wap','int');
		$insert = $this->model('payment')->groupsave($data,$id);
		if(!$insert){
			$tip = $id ? P_Lang('编辑失败') : P_Lang('添加失败');
			$this->error($tip);
		}
		if($id){
			$insert = $id;
		}
		$default = $this->get('is_default','int');
		if($default){
			$this->model('payment')->group_set_default($insert,$this->session->val('admin_site_id'));
		}
		$this->success();
	}


	/**
	 * 删除所有支付组
	 * @参数 id 支付组id
	**/
	public function groupdel_f()
	{
		if(!$this->popedom['groupdelete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rslist = $this->model('payment')->get_all("p.gid='".$id."'");
		if($rslist){
			$this->error(P_Lang('已存在支付方案，请先移除'));
		}
		$this->model('payment')->group_delete($id);
		$this->success();
	}
	
	public function set_f()
	{
		$gid = $this->get('gid','int');
		$id = $this->get('id','int');
		$code = $this->get('code');
		if($id){
			$rs = $this->model('payment')->get_one($id);
			$gid = $rs['gid'];
			$code = $rs['code'];
			if($rs['param']){
				$rs['param'] = unserialize($rs['param']);
			}
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		if(!$code){
			$this->error(P_Lang('未指定支付接口'),$this->url('payment'));
		}
		$this->assign('gid',$gid);
		$this->assign('code',$code);
		//读取支付组
		$grouplist = $this->model('payment')->group_all($_SESSION['admin_site_id']);
		$this->assign('grouplist',$grouplist);
		//扩展信息
		$extlist = $this->model('payment')->code_one($code);
		$this->assign('extlist',$extlist);
		$this->lib('form')->cssjs();
		//可使用的货币列表
		$currency_list = $this->model('currency')->get_list();
		$this->assign("currency_list",$currency_list);
		$this->lib('form')->cssjs(array('form_type'=>'editor'));
		$this->view('payment_set');
	}

	//存储支付方案
	public function save_f()
	{
		$gid = $this->get('gid','int');
		$code = $this->get('code');
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('payment')->get_one($id);
			$gid = $rs['gid'];
			$code = $rs['code'];
		}
		if(!$gid){
			$this->error(P_Lang('未指定支付组'),$this->url('payment'));
		}
		if(!$code){
			$this->error(P_Lang('未指定支付接口'),$this->url('payment'));
		}
		$error_url = $id ? $this->url('payment','set','id='.$id) : $this->url('payment','set','gid='.$gid."&code=".$code);
		$codeinfo = $this->model('payment')->code_one($code);
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('支付名称不能为空'),$error_url);
		}
		$data = array('title'=>$title,'code'=>$code,'gid'=>$gid);
		$data['currency'] = $this->get("currency");
		$data['logo1'] = $this->get('logo1');
		$data['logo2'] = $this->get('logo2');
		$data['logo3'] = $this->get('logo3');
		$data['taxis'] = $this->get('taxis','int');
		$data['status'] = $this->get('status','int');
		$data['wap'] = $this->get('wap','int');
		$data['note'] = $this->get('note','html');
		//读取扩展信息
		if($codeinfo['code'] && is_array($codeinfo['code'])){
			$ext = array();
			foreach($codeinfo['code'] as $key=>$value){
				if($value['type'] != 'checkbox'){
					$ext[$key] = $this->get($code."_".$key);
				}else{
					$tmp = array();
					foreach($value['option'] as $k=>$v){
						$tmp_name = $code."_".$k;
						if(isset($_POST[$tmp_name])){
							$tmp[] = $k;
						}
					}
					$ext[$key] = implode(",",$tmp);
				}
			}
			$data['param'] = serialize($ext);
		}
		$this->model('payment')->save($data,$id);
		$tip = $id ? P_Lang('编辑成功') : P_Lang('添加成功');
		$this->success($tip,$this->url('payment'));
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('payment')->delete($id);
		$this->success();
	}

	public function taxis_f()
	{
		$id = $this->get('id','int');
		$type = $this->get('type');
		$taxis = $this->get('taxis','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if($type == 'group'){
			$this->model('payment')->groupsave(array('taxis'=>$taxis),$id);
		}else{
			$this->model('payment')->save(array('taxis'=>$taxis),$id);
		}
		$this->success();
	}
}