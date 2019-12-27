<?php
/**
 * 数据调用新版专用
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年11月02日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class call_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->config('is_ajax',true);
	}

	public function index_f()
	{
		$data = $this->get('data','html');
		if(!$data){
			$this->error(P_Lang('未指定参数变量'));
		}
		if(substr($data,0,1) == '{'){
			$data = $this->lib('json')->decode(stripslashes($data));
			if($data){
				$data = $this->format($data);
			}
		}else{
			$tmplist = explode(",",$data);
			$data = array();
			foreach($tmplist as $key=>$value){
				$data[$value] = array();
			}
		}
		$call_all = $this->model('call')->all($this->site['id'],'identifier');
		$is_ok = false;
		$rslist = array();
		foreach($data as $key=>$value){
			if($call_all && $call_all[$key] && $call_all[$key]['is_api']){
				$tmpValue = $value;
				$fid = $key;
				if($value['_alias']){
					unset($tmpValue['_alias']);
					$fid = $value['_alias'];
				}
				$rslist[$fid] = phpok($key,$tmpValue);
				$is_ok = true;
			}else{
				$fid = $value['_alias'] ? $value['_alias'] : $key;
				if($call_all && $call_all[$key] && !$call_all[$key]['is_api']){
					$rslist[$fid] = array('status'=>0,'info'=>P_Lang('未启用远程调用，请检查'));
				}else{
					$rslist[$fid] = array('status'=>0,'info'=>P_Lang('没有找到数据调用参数，请检查'));
				}
			}
		}
		if(!$is_ok){
			$this->error(P_Lang('未启用远程调用或没有相关调用参数，请检查'));
		}
		$this->success($rslist);
	}
}
