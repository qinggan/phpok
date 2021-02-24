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
		if(!$this->session->val('admin_id')){
			$this->error('仅限后台管理员使用');
		}
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$code = $this->get('code');
		if(!$code){
			$this->error('未指定调用');
		}
		$param = $this->get('param','html');
		if($param){
			$param = $this->_param($param);
		}
		$tplfile = $this->get('tplfile');
		$ext = substr($tplfile,-5);
		$ext = strtolower($ext);
		if($ext != '.html'){
			$tplfile .= '.html';
		}
		if(!file_exists($this->dir_root.$tplfile)){
			$this->error('模板文件不存在');
		}
		$list = phpok($code,$param);
		$this->assign('info',$list);
		$content = $this->fetch($tplfile,'abs-file');
		$tmp = explode("/",$tplfile);
		$preview_file = $tmp[0] == '_data' ? $this->dir_data.'design/preview.html' : $this->dir_root.'tpl/'.$tmp[1].'/design/preview.html';
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
