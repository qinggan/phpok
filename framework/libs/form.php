<?php
/**
 * 表单选项管理器
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2018年01月20日
 * @更新 2023年4月21日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class form_lib
{
	//表单对象
	public $cls;
	public $appid = 'www';
	private $dir_form;
	private $ext_form;

	//构造函数
	public function __construct()
	{
		$this->dir_form = $GLOBALS['app']->dir_phpok.'form/';
		$this->ext_form = $GLOBALS['app']->dir_extension.'ext-form/';
		$appid = isset($GLOBALS['app']->appid) ? $GLOBALS['app']->appid : false;
		$this->appid = $appid == 'admin' ? 'admin' : 'www';
	}

	public function appid($appid='www')
	{
		$this->appid = $appid;
	}

	/**
	 * 获取对象
	 * @参数 $name 表单名称
	**/
	public function cls($name)
	{
		$class_name = $name.'_form';
		if(isset($this->$class_name)){
			return $this->$class_name;
		}
		$phpfile = false;
		if(file_exists($this->ext_form.$name.'/form.php')){
			$phpfile = $this->ext_form.$name.'/form.php';
		}
		if(!$phpfile && file_exists($this->dir_form.$class_name.'.php')){
			$phpfile = $this->dir_form.$class_name.'.php';
		}
		if(!$phpfile){
			return false;
		}
		include_once($phpfile);
		$this->$class_name = new $class_name();
		return $this->$class_name;
	}

	private function _obj($rs)
	{
		if(!$rs || !$rs['form_type']){
			return false;
		}
		return $this->cls($rs['form_type']);
	}

	/**
	 * 表单配置信息
	 * @参数 $id 表单类型名
	**/
	public function config($id)
	{
		$obj = $this->cls($id);
		if(!$obj){
			return false;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_config',$mlist)){
			$obj->phpok_config();
			exit;
		}
		if(in_array('config',$mlist)){
			$obj->config();
			exit;
		}
		exit(P_Lang('文件异常'));
	}

	public function ext($id)
	{
		global $app;
		$obj = $this->cls($id);
		if(!$obj){
			return false;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_ext',$mlist)){
			$ext = $obj->phpok_ext();
		}else{
			$ext_form_id = $app->get("ext_form_id");
			$ext = array();
			if($ext_form_id){
				$list = explode(",",$ext_form_id);
				foreach($list as $key=>$value){
					$val = explode(':',$value);
					if($val[1] && $val[1] == "checkbox"){
						$value = $val[0];
						$ext[$value] = $app->get($value,"checkbox");
					}else{
						$value = $val[0];
						$ext[$value] = $app->get($value,'html_js');
					}
				}
			}
		}
		return $ext;
	}

	/**
	 * 格式化表单信息
	 * @参数 $rs 要格式化的内容
	**/
	public function format($rs)
	{
		$obj = $this->_obj($rs);
		if(!$obj){
			return $rs;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_format',$mlist)){
			$info = $obj->phpok_format($rs,$this->appid);
			$rs['html'] = $info;
			return $rs;
		}
		if(in_array('format',$mlist)){
			$info = $obj->format($rs);
			$rs['html'] = $info;
			return $rs;
		}
		return $rs;
	}

	/**
	 * 获取内容信息
	 * @参数 $rs 数组，字段属性
	 * @返回 
	 * @更新时间 
	**/
	public function get($rs)
	{
		$obj = $this->_obj($rs);
		if(!$obj){
			return false;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_get',$mlist)){
			return $obj->phpok_get($rs,$this->appid);
		}
		if(in_array('get',$mlist)){
			return $obj->get($rs);
		}
		return $GLOBALS['app']->get($rs['identifier'],$rs['format']);
	}

	/**
	 * 输出内容信息
	 * @参数 $rs 内容
	 * @参数 $value 值
	**/
	public function show($rs,$value='')
	{
		if(!$rs){
			return $value;
		}
		if($value != ''){
			$rs['content'] = $value;
		}
		$obj = $this->_obj($rs);
		if(!$obj){
			return $value;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_show',$mlist)){
			return $obj->phpok_show($rs,$this->appid);
		}
		if(in_array('show',$mlist)){
			if(!$value) $value = $rs['content'];
			return $obj->show($rs,$value);
		}
		return $value;
	}

	public function ajax($id,$type)
	{
		$obj = $this->cls($type);
		if(!$obj){
			return $value;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_ajax',$mlist)){
			return $obj->phpok_ajax($id,$this->appid);
		}
		if(in_array('ajax',$mlist)){
			return $obj->ajax($id,$this->appid);
		}
		return false;
	}


	//弹出窗口，用于创建字段
	public function open_form_setting($saveurl)
	{
		if(!$saveurl) return false;
		$GLOBALS['app']->assign('saveUrl',$saveurl);
		//读取格式化类型
		$field_list = $GLOBALS['app']->model('form')->field_all();
		$form_list = $GLOBALS['app']->model('form')->form_all();
		$format_list = $GLOBALS['app']->model('form')->format_all();
		$GLOBALS['app']->assign('fields',$field_list);
		$GLOBALS['app']->assign('formats',$format_list);
		$GLOBALS['app']->assign('forms',$form_list);
		//创建字段
		$GLOBALS['app']->view("field_create");
	}

	//格式化值，对应的表单内容
	public function info($val,$rs)
	{
		if($val == '' || !$rs || !is_array($rs)){
			return $val;
		}
		//如果只是普通的文本框
		if($rs['form_type'] == 'text' || $rs['form_type'] == 'password'){
			return $val;
		}
		//如果是代码编辑器 或是 文本区
		if($rs['form_type'] == 'code_editor' || $rs['form_type'] == 'textarea'){
			return $val;
		}
		//如果是编辑器
		if($rs['form_type'] == 'editor'){
			return $GLOBALS['app']->lib('ubb')->to_html($val);
		}
		//如果是单选框
		if($rs['form_type'] == 'radio'){
			if(!$rs["option_list"]) $rs['option_list'] = 'default:0';
			$opt_list = explode(":",$rs["option_list"]);
			$rslist = opt_rslist($opt_list[0],$opt_list[1],$rs['ext_select']);
			//如果内容为空，则返回空信息
			if(!$rslist){
				return false;
			}
			foreach($rslist as $key=>$value){
				//
			}
		}
		return $val;
	}

	/**
	 * 按需装载CSS和JS文件
	 * @参数 $rs 要加载的对象
	**/
	public function cssjs($rs='')
	{
		if($rs && is_array($rs)){
			$obj = $this->_obj($rs);
			if(!$obj){
				return false;
			}
			$mlist = get_class_methods($obj);
			if(in_array('cssjs',$mlist)){
				$obj->cssjs();
			}
			return true;
		}
		$list = $GLOBALS['app']->model('form')->form_all();
		foreach($list as $key=>$value){
			$obj = $this->_obj(array('form_type'=>$key));
			$mlist = get_class_methods($obj);
			if(in_array('cssjs',$mlist)){
				$obj->cssjs();
			}
		}
		return true;
	}
}