<?php
/**
 * 物流平台管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年4月30日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

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
			$this->error(P_Lang('您没有权限执行此操作'));
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
				$this->error(P_Lang('您没有权限执行此操作'));
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
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$code = $this->get('code');
			if(!$code){
				$this->error(P_Lang('未指定接口引挈'));
			}
			$this->assign('code',$code);
		}
		//扩展信息
		$extlist = $this->model('express')->code_one($code);
		$this->assign('extlist',$extlist);
		$this->lib('form')->cssjs();
		$this->view('express_set');
	}

	/**
	 * 保存物流接口信息
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		$array = array();
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('express')->get_one($id);
			$code = $rs['code'];
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$code = $this->get('code');
			if(!$code){
				$this->error('未指定接口引挈');
			}
			$array['code'] = $code;
			$array['site_id'] = $_SESSION['admin_site_id'];
		}
		$array['title'] = $this->get('title');
		if(!$array['title']){
			$this->error(P_Lang('请填写物流快递名称'));
		}
		$array['company'] = $this->get('company');
		$array['homepage'] = $this->get('homepage');
		$array['logo'] = $this->get('logo');
		$array['content'] = $this->get('content','html');
		$array['rate']  = $this->get('rate','int');
		$codeinfo = $this->model('express')->code_one($code);
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
			$array['ext'] = serialize($ext);
		}
		$this->model('express')->save($array,$id);
		$this->success();
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
		$this->model('express')->delete($id);
		$this->success();
	}
}