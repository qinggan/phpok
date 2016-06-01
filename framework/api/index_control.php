<?php
/***********************************************************
	Filename: {phpok}/api/index_control.php
	Note	: API接口默认接入
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年10月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		if(!$this->site['api_code']){
			$this->json(P_Lang("系统未启用接口功能"));
		}
		$this->json(true);
	}

	public function token_f()
	{
		if(!$this->site['api_code']){
			$this->error(P_Lang("系统未配置接口功能"));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定数据调用标识'));
		}
		$rs = $this->model('call')->get_rs($id,$this->site['id']);
		if(!$rs || !$rs['status']){
			$this->error(P_Lang('标识不存在或未启用'));
		}
		if(!$rs['is_api']){
			$this->error(P_Lang('未启用远程接入'));
		}
		if($rs['type_id'] == 'sql' && !$this->config['api_remote_sql']){
			$this->error(P_Lang('系统未开放远程调用SQL操作，需要在配置文件启用api_remote_sql值设为true'));
		}
		$param = array();
		$pid = $this->get('pid','int');
		if($pid){
			$param['pid'] = $pid;
		}else{
			$project = $this->get('project','system');
			if($project){
				$tmp = $this->model('project')->simple_project_from_identifier($project,$this->site['id']);
				if($tmp && $tmp['id']){
					$param['pid'] = $tmp['id'];
				}
			}
		}
		//判断是否有参数分类
		$cateid = $this->get('cateid','int');
		if($cateid){
			$param['cateid'] = $cateid;
		}else{
			$cate = $this->get('cate','system');
			if($cate){
				$cate_rs = $this->model('cate')->get_one($cate,'identifier',false);
				if($cate_rs && $cate_rs['status']){
					$param['cateid'] = $cate_rs['id'];
				}
			}
		}
		//判断是否有指定sqlinfo
		$sqlinfo = $this->get('sql');
		if($sqlinfo){
			$sqlinfo = str_replace(array('&#39;','&quot;','&apos;','&#34;'),array("'",'"',"'",'"'),$sqlinfo);
			$param['sqlinfo'] = $sqlinfo;
		}
		//判断是否要指定会员ID
		$uid = $this->get('uid','int');
		if($uid){
			$param['user_id'] = $uid;
		}else{
			$user = $this->get('user');
			if($user){
				$user_rs = $this->model('user')->get_one($user,'user',false,false);
				if($user_rs && $user_rs['status'] == 1){
					$param['user_id'] = $user_rs['id'];
				}
			}
		}
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			foreach($ext as $key=>$value){
				if($key == 'sqlext' && $value){
					$value = str_replace(array('&#39;','&quot;','&apos;','&#34;'),array("'",'"',"'",'"'),$value);
				}
				$param[$key] = $value;
			}
		}
		$this->lib('token')->keyid($this->site['api_code']);
		$array = array('id'=>$id,'param'=>$param);
		$token = $this->lib('token')->encode($array);
		$this->success($token);
	}

	public function phpok_f()
	{
		if(!$this->site['api_code']){
			$this->json(P_Lang("系统未启用接口功能"));
		}
		$token = $this->get("token");
		if(!$token){
			$this->json(P_Lang("接口数据异常"));
		}
		$this->lib('token')->keyid($this->site['api_code']);
		$info = $this->lib('token')->decode($token);
		if(!$info){
			$this->json(P_Lang('信息为空'));
		}
		$id = $info['id'];
		if(!$id){
			$this->json(P_Lang('未指定数据调用中心ID'));
		}
		$param = $info['param'];
		if($param){
			if(is_string($param)){
				$pm = array();
				parse_str($param,$pm);
				$param = $pm;
				unset($pm);
			}
		}else{
			$param = array();
		}
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			foreach($ext as $key=>$value){
				if(!$value){
					continue;
				}
				if($key == 'sqlext' && $value){
					$value = str_replace(array('&#39;','&quot;','&apos;','&#34;'),array("'",'"',"'",'"'),$value);
				}
				$param[$key] = $value;
			}
		}
		$list = $this->call->phpok($id,$param);
		if(!$list){
			$this->json(P_Lang("没有获取到数据"));
		}
		$tpl = $this->get("tpl");
		if($tpl && $this->tpl->check_exists($tpl)){
			$this->assign("rslist",$list);
			$info = $this->fetch($tpl);
			$this->json($info,true);
		}
		$this->json($list,true);
	}
}