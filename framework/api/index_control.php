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
		$this->config('is_ajax',true);
	}

	public function index_f()
	{
		if(!$this->site['api_code']){
			$this->error(P_Lang("系统未启用接口功能"));
		}
		$data = array('ctrl_id'=>$this->config['ctrl_id']);
		$data['func_id'] = $this->config['func_id'];
		$data['site_id'] = $this->site['id'];
		$data['session_name'] = $this->session->sid();
		$data['session_val'] = $this->session->sessid();
		$data['_note'] = '历史原因，会话名称及值将会上移到与info同级，此项将在OK5.4后取消使用，请使用接口开发的注意同步更新';
		$wxAppConfig = $this->get('wxAppConfig');
		$clear_url = $this->config['url'].'wxapp/';
		if($wxAppConfig && is_file($this->dir_data.'wxappconfig.php')){
			include_once($this->dir_data.'wxappconfig.php');
			unset($wxconfig['wxapp_secret']);
			$data['wxconfig'] = $wxconfig;
		}
		$tmpinfo = $this->site;
		unset($tmpinfo['api_code']);
		$data['site'] = $tmpinfo;
		$this->success($data);
	}

	public function site_f()
	{
		$this->config('is_ajax',true);
		if(!$this->site['api_code']){
			$this->error(P_Lang("系统未启用接口功能"));
		}
		unset($this->site['api_code']);
		$id = $this->get('id');
		if(!$id){
			$id = 'title';
		}
		$data = array();
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			if($this->site[$value]){
				$data[$value] = $this->site[$value];
			}
		}
		$this->success($data);
	}

	/**
	 * 安全码生成
	 * @参数 data 要加密的字串
	 * @参数 
	 * @参数 
	**/
	public function safecode_f()
	{
		if(!$this->site['api_code']){
			$this->error(P_Lang('未设置 API 密钥'));
		}
		$data = $this->get("data");
		if(!$data){
			$this->error(P_Lang('未找到要加密的字串'));
		}
		$list = explode(",",$data);
		sort($list);
		$isok = true;
		foreach($list as $key=>$value){
			if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
				$isok = false;
				break;
			}
		}
		if(!$isok){
			$this->error(P_Lang('参数不合法'));
		}
		$code = md5($this->site['api_code'].",".implode(",",$list));
		$this->success($code);
	}

	public function token_f()
	{
		$this->config('is_ajax',true);
		if(!$this->site['api_code']){
			$this->error(P_Lang("系统未配置接口功能"));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定数据调用标识'));
		}
		$this->model('call')->site_id($this->site['id']);
		$rs = $this->model('call')->get_one($id,'identifier');
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

	public function qrcode_f()
	{
		$data = $this->get('data');
		if(!$data){
			$this->error(P_Lang('未指定生成的二维码数据'));
		}
		header("Pragma:no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate"); 
		header("Content-type: image/png");
		$this->lib('qrcode')->png($data);
	}
	
	/**
	 * 分享推荐
	**/
	public function share_f()
	{
		$uid = $this->get('uid','int');
		if(!$uid){
			$this->error(P_Lang('未指定会员'));
		}
		$rs = $this->model('user')->get_one($uid,'id',false,false);
		if(!$rs){
			$this->error(P_Lang('会员信息不存在'));
		}
		if($this->session->val('user_id') == $rs['id']){
			$this->error(P_Lang('不能给自己推荐'));
		}
		$this->session->assign('introducer',$uid);
		$this->success();
	}

	/**
	 * 价格格式化
	 * @参数 price 价格数值
	 * @参数 from 数值对应的货币
	 * @参数 to 要显示的货币
	 * @参数 symbol 是否有符号
	**/
	public function price_f()
	{
		$price = $this->get('price','float');
		if(!$price){
			$price = '0';
		}
		$from = $this->get('from','int');//当前货币ID（系统自动生成的ID）
		if(!$from){
			$from = $this->site['currency_id'];
		}
		$to = $this->get('to','int');
		if(!$to){
			$to = $this->site['currency_id'];
		}
		$symbol = $this->get('symbol','int');
		if($symbol){
			if(is_array($price)){
				$list = array();
				foreach($price as $key=>$value){
					$list[$key] = price_format($value,$from,$to);
				}
				$this->success($list);
			}
			$this->success(price_format($price,$from,$to));
		}
		if(is_array($price)){
			$list = array();
			foreach($price as $key=>$value){
				$list[$key] = price_format_val($value,$from,$to);
			}
			$this->success($list);
		}
		$this->success(price_format_val($price,$from,$to));
	}
}