<?php
/**
 * 网关路由接口参数运行信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月18日
**/
class gateway_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->config('is_ajax',true);
	}

	public function index_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定网关路由ID'));
		}
		$rs = $this->model('gateway')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('网关路由信息不存在'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('网关路由未启用'));
		}
		$file = $this->get('file');
		if(!$file){
			$file = 'exec';
		}
		$exec_file = $this->dir_gateway.$rs['type'].'/'.$rs['code'].'/'.$file.'.php';
		if(!is_file($exec_file)){
			$this->error(P_Lang('要运行的文件{file}不存在',array('file'=>$file)));
		}
		$this->gateway('type',$rs['type']);
		$this->gateway('param',$rs);
		$this->gateway('extinfo',$rs['ext']);
		$this->gateway($file.'.php','json');
	}

	/**
	 * 内部调用执行网关操作
	 * @参数 $id 网关ID
	 * @参数 $file 默认执行 exec.php 文件（传过来的参数不带 .php）
	**/
	public function exec_file($id,$file='exec',$post=array())
	{
		if(!$id){
			return false;
		}
		$rs = $this->model('gateway')->get_one($id);
		if(!$rs){
			return false;
		}
		if(!$rs['status']){
			return false;
		}
		$exec_file = $this->dir_gateway.$rs['type'].'/'.$rs['code'].'/'.$file.'.php';
		if(!is_file($exec_file)){
			return false;
		}
		$this->gateway('type',$rs['type']);
		$this->gateway('param',$rs);
		$this->gateway('extinfo',$rs['ext']);
		if($post && is_array($post)){
			foreach($post as $key=>$value){
				$_POST[$key] = $value;
			}
		}
		return $this->gateway($file.'.php');
	}
}
