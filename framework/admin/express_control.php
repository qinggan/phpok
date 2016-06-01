<?php
/*****************************************************************************************
	文件： {phpok}/admin/express_control.php
	备注： 物流平台管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月05日 16时44分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class express_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('express');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('express')->get_all();
		if($rslist){
			foreach($rslist as $key=>$value){
				$value['url'] = $value['homepage'];
				if($value['url'] && substr($value['url'],0,7) != 'http://' && substr($value['url'],0,8) != 'https://'){
					$value['url'] = 'http://'.$value['url'];
				}
				if($value['homepage'] && (substr($value['homepage'],0,7) == 'http://' || substr($value['homepage'],0,8) == 'https://')){
					$value['homepage'] = parse_url($value['homepage'],PHP_URL_HOST);
				}
				$rslist[$key] = $value;
			}
		}
		$this->assign('rslist',$rslist);
		$codelist = $this->model('express')->code_all();
		$this->assign('codelist',$codelist);
		$this->view('express_index');
	}

	public function set_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom["modify"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('express'),'error');
			}
			$rs = $this->model('express')->get_one($id);
			if($rs['ext']){
				$rs['ext'] = unserialize($rs['ext']);
			}
			$this->assign('rs',$rs);
			$this->assign('id',$id);
			$this->assign('code',$rs['code']);
			$code = $rs['code'];
		}else{
			if(!$this->popedom["add"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('express'),'error');
			}
			$code = $this->get('code');
			if(!$code){
				error(P_Lang('未指定接口引挈'),$this->url('express'),'error');
			}
			$this->assign('code',$code);
		}
		//扩展信息
		$extlist = $this->model('express')->code_one($code);
		$this->assign('extlist',$extlist);
		$this->lib('form')->cssjs();
		$this->view('express_set');
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		$array = array();
		if($id){
			if(!$this->popedom['modify']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('express')->get_one($id);
			$code = $rs['code'];
		}else{
			if(!$this->popedom['add']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
			$code = $this->get('code');
			if(!$code){
				$this->json('未指定接口引挈');
			}
			$array['code'] = $code;
			$array['site_id'] = $_SESSION['admin_site_id'];
		}
		$array['title'] = $this->get('title');
		if(!$array['title']){
			$this->json(P_Lang('请填写物流快递名称'));
		}
		$array['company'] = $this->get('company');
		$array['homepage'] = $this->get('homepage');
		$array['rate']  = $this->get('rate','int');
		$codeinfo = $this->model('express')->code_one($code);
		if($codeinfo['code'] && is_array($codeinfo['code'])){
			$ext = array();
			foreach($codeinfo['code'] AS $key=>$value){
				if($value['type'] != 'checkbox'){
					$ext[$key] = $this->get($code."_".$key);
				}else{
					$tmp = array();
					foreach($value['option'] AS $k=>$v){
						$tmp_name = $code."_".$k;
						if(isset($_POST[$tmp_name])){
							$tmp[] = $k;
						}
					}
					$ext[$key] = implode(",",$tmp);
				}
			}
			$array['ext'] = serialize($ext);
		}
		$this->model('express')->save($array,$id);
		$this->json(true);
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('express')->delete($id);
		$this->json(true);
	}
}

?>