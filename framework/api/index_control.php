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

	public function copyright_f()
	{
		if($this->license == 'LGPL'){
			$tip = '授权模式：<span style="color:blue;font-weight:bold">LGPL 免费商用授权</span><br/>请放心使用，有问题可登录官网：<a href="https://www.phpok.com/" target="_blank">WWW.PHPOK.COM</a>';
			$this->success($tip);
		}
		if($this->license == 'PBIZ' || $this->license == 'CBIZ'){
			$url = 'http://license.phpok.com?code='.rawurlencode($this->license_code).'&domain='.rawurlencode($this->license_site);
			$this->lib('curl')->user_agent($this->lib('server')->agent());
			$t = $this->lib('curl')->get_json($url);
			if(!$t){
				$this->error('授权认证失败，请检查');
			}
			if(!$t['status']){
				$info = $t['info'] ? $t['info'] : '授权审核失败';
				$this->error($info);
			}
			if($this->license == 'PBIZ'){
				$tip  = '<span style="color:blue;font-weight:bold">个人商用授权</span>';
				$tip .= '<br/>申请时间：'.$this->license_date;
			}else{
				$tip  = '<span style="color:blue;font-weight:bold">企业商用授权（'.$this->license_name.'）</span>';
				$tip .= '<br/>申请时间：'.$this->license_date;
			}
			$tip .= '<br/>有效时间：永久';
			$this->success($tip);
		}
		$this->error('授权异常，请联系管理员');
	}

	public function index_f()
	{
		$data = array('ctrl_id'=>$this->config['ctrl_id']);
		$data['func_id'] = $this->config['func_id'];
		$data['site_id'] = $this->site['id'];
		$tmpinfo = $this->site;
		$unset_ids = array('tpl_id','_domain','meta','seo_keywords','seo_desc','seo_title');
		array_push($unset_ids,'adm_logo29','adm_logo50','adm_logo180');
		foreach($tmpinfo as $key=>$value){
			if(isset($key) && in_array($key,$unset_ids)){
				unset($tmpinfo[$key]);
			}
		}
		$list = $this->model('site')->all_list($this->site['id']);
		if($list){
			foreach($list as $key=>$value){
				if(!$value['is_api']){
					unset($tmpinfo[$value['identifier']]);
				}
			}
		}
		ksort($tmpinfo);
		$data['site'] = $tmpinfo;
		$this->success($data);
	}

	public function site_f()
	{
		$this->config('is_ajax',true);
		$api_code = $this->model('config')->get_one('api_code',$this->site['id']);
		if(!$api_code){
			$this->error(P_Lang("系统未启用接口功能"));
		}
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
		$api_code = $this->model('config')->get_one('api_code',$this->site['id']);
		if(!$api_code){
			$this->error(P_Lang("系统未启用接口功能"));
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
		$code = md5($api_code.",".implode(",",$list));
		$this->success($code);
	}

	public function token_f()
	{
		$this->config('is_ajax',true);
		$api_code = $this->model('config')->get_one('api_code',$this->site['id']);
		if(!$api_code){
			$this->error(P_Lang("系统未启用接口功能"));
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
		//判断是否有指定 sqlinfo
		$sqlinfo = $this->get('sql');
		if($sqlinfo){
			$sqlinfo = str_replace(array('&#39;','&quot;','&apos;','&#34;'),array("'",'"',"'",'"'),$sqlinfo);
			$param['sqlinfo'] = $sqlinfo;
		}
		//判断是否要指定用户ID
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
		$this->lib('token')->keyid($api_code);
		$array = array('id'=>$id,'param'=>$param);
		$token = $this->lib('token')->encode($array);
		$this->success($token);
	}

	public function phpok_f()
	{
		$api_code = $this->model('config')->get_one('api_code',$this->site['id']);
		if(!$api_code){
			$this->error(P_Lang("系统未启用接口功能"));
		}
		$token = $this->get("token");
		if(!$token){
			$this->json(P_Lang("接口数据异常"));
		}
		$this->lib('token')->keyid($api_code);
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
			$this->error(P_Lang('未指定用户'));
		}
		$rs = $this->model('user')->get_one($uid,'id',false,false);
		if(!$rs){
			$this->error(P_Lang('用户信息不存在'));
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
	
	public function phpinc_f()
	{
		$phpfile = $this->get('phpfile','system');
		if(!$phpfile){
			$this->error(P_Lang('未指定合法的 PHP 文件'));
		}
		$phpfile .= ".php";
		if(!file_exists($this->dir_root.'phpinc/'.$phpfile)){
			$this->error(P_Lang('PHP 文件不存在'));
		}
		global $app;
		include($this->dir_root.'phpinc/'.$phpfile);
	}
}