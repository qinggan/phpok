<?php
/**
 * 数据调用
 * @作者 qinggan <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年1月29日
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
				$data = $this->format($data,'safe_text');
			}
		}else{
			$tmplist = explode(",",$data);
			$data = array();
			foreach($tmplist as $key=>$value){
				$data[$value] = array();
			}
		}
		//基于接口禁用SQL
		$is_error = false;
		foreach($data as $key=>$value){
			if(isset($value['type_id'])){
				$is_error = true;
				break;
			}
		}
		if($is_error){
			$this->error(P_Lang('系统禁止改写类型参数'));
		}

		$call_all = $this->model('call')->all($this->site['id'],'identifier');
		$is_ok = false;
		$rslist = array();
		foreach($data as $key=>$value){
			//检查系统是否有开放SQL调用
			if($call_all && $call_all[$key] && $call_all[$key]['type_id'] == 'sql' && !$this->config['api_remote_sql']){
				$fid = $value['_alias'] ? $value['_alias'] : $key;
				$rslist[$fid] = array('status'=>0,'info'=>P_Lang('禁止远程调用SQL执行，请检查'));
				continue;
			}
			//明文传输将禁用sqlext和sqlinfo
			if(isset($value['sqlext'])){
				unset($value['sqlext']);
			}
			if(isset($value['sqlinfo'])){
				unset($value['sqlinfo']);
			}
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

	/**
	 * 仅限后台管理
	**/
	public function admin_preview_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$code = $this->get('code','system');
		if(!$code){
			$this->error('未指定调用');
		}
		$rs = $this->model('design')->get_one($code,'code');
		$param = $this->get('param','html');
		if($param){
			$param = $this->_param($param);
		}
		$tplfile = $this->get('tplfile');
		if($tplfile){
			$ext = substr($tplfile,-5);
			$ext = strtolower($ext);
			if($ext != '.html'){
				$tplfile .= '.html';
			}
			if(!file_exists($this->dir_root.$tplfile)){
				$this->error('模板文件不存在');
			}
		}else{
			$tplfile = $this->dir_data.'design/'.$rs['code'].'.html';
			if(!file_exists($tplfile)){
				$this->error('模板文件不存在');
			}
		}
		$calldata = $this->get('calldata');
		if(!$calldata){
			if($rs['ext'] && $rs['ext']['calldata']){
				$calldata = $rs['ext']['calldata'];
			}
		}
		if($calldata){
			$list = phpok($calldata,$param);
			$this->assign('info',$list);
			$replace = $this->get('param_replace','html');
			if($replace){
				$tpl_content = $this->lib('file')->cat($this->dir_root.$tplfile);
				$tmplist = explode("\n",$replace);
				foreach($tmplist as $key=>$value){
					$value = trim($value);
					if(!$value){
						continue;
					}
					$tmp = explode("=",$value);
					if(!$tmp[0] || !$tmp[1]){
						continue;
					}
					$tmp[0] = trim($tmp[0]);
					$tmp[1] = trim($tmp[1]);
					if(!$tmp[0] || !$tmp[1]){
						continue;
					}
					$tpl_content = str_replace($tmp[0],$tmp[1],$tpl_content);
				}
				$content = $this->fetch($tpl_content,'content');
			}else{
				$content = $this->fetch($tplfile,'abs-file');
			}
		}
		$tmp = explode("/",$tplfile);
		$preview_file = $this->dir_data.'design/preview.html';
		$tplcontent = $this->lib('file')->cat($preview_file);
		$tplcontent = str_replace('{content}',$content,$tplcontent);
		$tplcontent = str_replace('{iframe_id}',$id,$tplcontent);
		echo $tplcontent;
		exit;
	}

	private function _param($str)
	{
		if(!$str){
			return false;
		}
		$dt = array();
		$dt['ext'] = array();
		$old = array('&amp;lt;','&amp;gt;','&amp;quot;','&amp;apos;','&lt;','&gt;','&quot;','&apos;');
		$new = array('<','>','"',"'",'<','>','"',"'");
		$param = str_replace($old,$new,$str);
		$list = explode("\n",$param);
		foreach($list as $key=>$value){
			$tmp = explode("=",$value);
			if($tmp[0] && $tmp[1] != ''){
				$length = strlen($tmp[0]);
				$e = substr($value,($length+1));
				if(strpos($tmp[0],'ext[') === false){
					$dt[$tmp[0]] = $e;
				}else{
					$tmp_id = str_replace(array('ext[',']'),'',$tmp[0]);
					$dt['ext'][$tmp_id] = $e;
				}
			}
		}
		if(count($dt['ext'])<1){
			unset($dt['ext']);
		}
		return $dt;
	}
}
