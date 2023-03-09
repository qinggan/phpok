<?php
/**
 *
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html
 * @时间 2021年3月1日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
 * 核心魔术方法，此项可实现类，方法的自动加载，PHPOK里的Control，Model及Plugin都继承了这个类
**/
class _init_auto
{
	public function __construct()
	{
		//
	}

	public function __destruct()
	{
		//
	}

	public function lib($class='',$param='')
	{
		return $GLOBALS['app']->lib($class,$param);
	}

	public function model($class='',$param='')
	{
		return $GLOBALS['app']->model($class,$param);
	}

	/**
	 * 魔术方法之方法重载
	 * @参数 $method $GLOBALS['app']下的方法，如果存在，直接调用，不存在，通过分析动态加载lib或是model
	 * @参数 $param 传递过来的变量
	**/
	public function __call($method,$param)
	{
		if($method && method_exists($GLOBALS['app'],$method)){
			return call_user_func_array(array($GLOBALS['app'],$method),$param);
		}else{
			$GLOBALS['app']->error('方法：'.$method.' 不存在');
		}
	}

	/**
	 * 属性重载，读取不可访问属性的值时，尝试通过这里重载
	 * @参数 $id $GLOBALS['app']下的属性
	**/
	public function __get($id)
	{
		$engine_list = array('db','session','cache');
		if(in_array($id,$engine_list)){
			return $GLOBALS['app']->engine($id);
		}
		$lst = explode("_",$id);
		if(isset($lst[1]) && $lst[1] == "model"){
			return $GLOBALS['app']->model($lst[0]);
		}elseif(isset($lst[1]) && $lst[1] == "lib"){
			return $GLOBALS['app']->lib($lst[0]);
		}
		return isset($GLOBALS['app']->$id) ? $GLOBALS['app']->$id : false;
	}

	/**
	 * 属性重载，当对不可访问属性调用
	 * @参数 $id $GLOBALS['app']下的属性
	**/
	public function __isset($id)
	{
		return $this->__get($id);
	}
}

/**
 * PHPOK控制器，里面大部分函数将通过Global功能调用核心引挈
**/
class phpok_control extends _init_auto
{
	public function control($id='',$app_id='')
	{
		if(!$id){
			parent::__construct();
			$this->init_authorization();
			return true;
		}
		return $GLOBALS['app']->control($id,$app_id);
	}

	/**
	 * 身份认证接口
	**/
	public function init_authorization()
	{
		$client_ip = $this->lib('common')->ip();
		//基于浏览器生成的 MD5 认证
		if(!$this->session()->val('api_code')){
			$api_code = $this->lib('common')->str_rand(10);
			$this->session()->assign('api_code',$api_code);
			$chkdata = array();
			$chkdata['ip'] = $client_ip;
			$chkdata['api_code'] = $api_code;
			$chkdata['time'] = $this->time;
			$chkdata['session_id'] = $this->session()->sessid();
			ksort($chkdata);
			$md5 = md5(serialize($chkdata)).$chkdata['time'];
			$authinfo = base64_encode('md5:'.$md5);
		}else{
			$api_code = $this->session()->val('api_code');
			$chkdata = array();
			$chkdata['ip'] = $client_ip;
			$chkdata['api_code'] = $api_code;
			$chkdata['time'] = $this->time;
			$chkdata['session_id'] = $this->session()->sessid();
			ksort($chkdata);
			$md5 = md5(serialize($chkdata)).$chkdata['time'];
			$authinfo = base64_encode('md5:'.$md5);
			$tmpinfo = serialize($chkdata);
		}
		$this->assign('AUTHORIZATION',$authinfo);
		if($this->app_id == 'admin' || !$this->is_ajax || !$this->config['api_auth']){
			return false;
		}
		$not_auth = array('index','login','register','js','logout','vcode','plugin','token','task','call','usercp');
		if(in_array($this->ctrl,$not_auth)){
			return true;
		}

		$cache_id = $this->cache()->id('api-code-rsa');
		$info = $this->cache()->get($cache_id);
		if(!$info){
			$info = $this->model('config')->get_all($this->site['id']);
		}
		if(!$info){
			$this->error(P_Lang('未配置权限验证方式'));
		}
		$tmp = $_SERVER['HTTP_AUTHORIZATION'] ? $_SERVER['HTTP_AUTHORIZATION'] : $_SERVER['HTTP_TOKEN'];
		if(!$tmp){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证信息不存在'));
		}
		$tmps = explode(" ",$tmp);
		if(!$tmps[1]){
			$token = $tmp;
		}else{
			$token = $tmps[1];
		}
		list($type, $string) = explode(':', base64_decode($token));
		$typelist = array('md5','code','rsa');
		if(!in_array($type,$typelist)){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证信息异常'));
		}

		if($type == 'md5'){
			$chkdata = array();
			$chkdata['ip'] = $client_ip;
			$chkdata['api_code'] = $this->session()->val('api_code');
			$chkdata['time'] = intval(substr($string,-10));
			$chkdata['session_id'] = $this->session()->sessid();
			$tmpinfo = serialize($chkdata);
			ksort($chkdata);
			$chkinfo = md5(serialize($chkdata)).$chkdata['time'];
			if($chkinfo != $string){
				$this->error(P_Lang('HTTP_AUTHORIZATION 认证信息失败'));
			}
			if($chkdata['time'] < ($this->time - 3600)){
				$this->error(P_Lang('HTTP_AUTHORIZATION 认证相差超过一小时'));
			}
			$this->_api_code = $chkdata['api_code'];
			return true;
		}
		if($type == 'code'){
			$this->lib('token')->etype('api_code');
			$this->lib('token')->keyid($info['api_code']);
			$this->lib('token')->expiry(24*60*60);
			$decode_data = $this->lib('token')->decode($string);
			if(!$decode_data || !is_array($decode_data)){
				$this->error(P_Lang('HTTP_AUTHORIZATION 认证失败'));
			}
			if(!$decode_data['session_id']){
				$this->error(P_Lang('HTTP_AUTHORIZATION 认证失败，Session 丢失'));
			}
			if($this->session()->sessid() != $decode_data['session_id']){
				$this->session()->comment($decode_data['session_id']);
			}

			if($decode_data['ip'] != $chkdata['ip']){
				$this->error(P_Lang('HTTP_AUTHORIZATION 认证异常，IP不一致'));
			}
			if($decode_data['time'] < ($this->time - 3600)){
				$this->error(P_Lang('HTTP_AUTHORIZATION 认证相差超过一小时'));
			}
			if(isset($decode_data['user_id']) && $decode_data['user_id'] != $this->session()->val('user_id')){
				$this->error(P_Lang('HTTP_AUTHORIZATION 认证失败，用户ID不一致'));
			}
			$this->_api_code = $decode_data['api_code'];
			return true;
		}
		$this->lib("token")->etype("public_key");
		$this->lib('token')->public_key($info['public_key']);
		$this->lib('token')->private_key($info['private_key']);
		$this->lib('token')->expiry(24*60*60);
		$decode_data = $this->lib('token')->decode($string);
		if(!$decode_data || !is_array($decode_data)){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证失败'));
		}
		if(!$decode_data['session_id']){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证失败，Session 丢失'));
		}
		if($this->session()->sessid() != $decode_data['session_id']){
			$this->session()->comment($decode_data['session_id']);
		}
		if($decode_data['ip'] != $chkdata['ip']){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证异常，IP不一致'));
		}
		if($decode_data['time'] < ($this->time - 3600)){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证相差超过一小时'));
		}
		if(isset($decode_data['user_id']) && $decode_data['user_id'] != $this->session()->val('user_id')){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证失败，用户ID不一致'));
		}
		$this->_api_code = $decode_data['api_code'];
		return true;
	}

}

/**
 * Model根类，继承了_into_auto类，支持直接调用核心引挈里的信息
**/
class phpok_model extends _init_auto
{
	/**
	 * 站点ID，所有的Model类都可以直接用这个
	**/
	public $site_id = 0;

	/**
	 * 缓冲区，用于即时缓存信息，同一条SQL多次请求时直接从缓冲区获取，注意需要手动更新数据
	**/
	protected $_buffer = array();

	/**
	 * 动态加载Model
	 * @参数 $id 为空用于继承父构造函数，不为空时动态加载其他model类，即实现了多个model的互相调用
	 * @参数 $check 用于检测Model是否存在
	**/
	public function model($id='',$check=false)
	{
		if(!$id){
			parent::__construct();
			if($this->app_id == 'admin' && $this->session->val('admin_site_id')){
				$this->site_id = $this->session->val('admin_site_id');
			}
			if($this->app_id != 'admin' && isset($this->site['id'])){
				$this->site_id = $this->site['id'];
			}
		}else{
			return $GLOBALS['app']->model($id,$check);
		}
	}

	/**
	 * 定义站点ID，用于实现同一个程序里有多个站点
	 * @参数 $site_id，站点ID
	**/
	public function site_id($site_id=0)
	{
		$this->site_id = $site_id;
	}

	/**
	 * 动态获取下一个排序
	 * @参数 $rs 数组或数字，为数字时返回该值+10后的数字，为数组时，尝试获取taxis或sort对应的数值，并返回+10后的数字，为空时返回10
	 * @返回 数字，下一个排序
	**/
	protected function return_next_taxis($rs='')
	{
		if($rs){
			if(is_array($rs)){
				$taxis = $rs['taxis'] ? $rs['taxis'] : $rs['sort'];
			}else{
				$taxis = $rs;
			}
			$taxis = intval($taxis);
			return intval($taxis+5);
		}else{
			return 5;
		}
	}

	/**
	 * 获取或保存缓冲区信息
	 * @参数 $sql 缓冲区标识
	 * @参数 $data 要保存的缓存信息
	**/
	protected function _buffer($sql,$data='')
	{
		$id = "sql".md5($sql);
		if(isset($data) && $data != ''){
			$this->_buffer[$id] = $data;
			return true;
		}
		if(isset($this->_buffer[$id])){
			return $this->_buffer[$id];
		}
		return false;
	}
}


/**
 * 初始化应用HTML接口类，即在插件中，也可以使用$this->model或是$this->lib等方法来获取相应的核心信息
**/
class _init_node_html extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 返回应用标识
	**/
	final public function _id()
	{
		$name = get_class($this);
		$lst = explode("\\",$name);
		return $lst[2];
	}

	/**
	 * 返回应用信息
	 * @参数 $id 应用标识，为空时尝试读取当前应用标识
	 * @返回 数组 应用基本信息
	 * @更新时间
	**/
	final public function _info($id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		$rs = $this->model('appsys')->get_one($id);
		if(!$rs){
			$rs = array('id'=>$id);
		}
		$rs['path'] = $this->dir_app.''.$id.'/';
		return $rs;
	}

	/**
	 * 返回插件输出的HTML数据，请注意，这里并没有输出，只是返回
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时返回模板内容，错误时返回false
	**/
	final public function _tpl($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if(!$file){
			return false;
		}
		return $this->tpl->fetch($file,'abs-file');
	}

	/**
	 * 输出的HTML数据到设备上，请注意，这里是输出，不是返回，同时也要注意，这里没有中止
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
	final public function _show($name,$id='')
	{
		$info = $this->_tpl($name,$id);
		if($info){
			echo $info;
		}
	}

	/**
	 * 输出的HTML数据到设备上并中断后续操作，请注意，这里是输出，有中断
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
	final public function _view($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if($file){
			$this->tpl->display($file,'abs-file');
			exit;
		}
	}

	/**
	 * 按顺序读取挑出最近的一个模板
	 * @参数 $name 模板名称，不带后缀的模板名称，相对路径，系统会依次检查这些文件是否存在，只要有一个符合要求即可<br />
	 * 1. APP应用根目录/应用标识/tpl/$name<br />
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
	private function _tplfile($name,$id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		if(substr($name,0,-5) != '.html'){
			$name .= '.html';
		}
		$list = array();
		$list[0] = $this->dir_app.''.$id.'/tpl/'.$name;
		$file = false;
		foreach($list as $key=>$value){
			if(file_exists($value)){
				$file = $value;
				break;
			}
		}
		return $file;
	}
}


/**
 * 初始化插件类，即在插件中，也可以使用$this->model或是$this->lib等方法来获取相应的核心信息
**/
class phpok_plugin extends _init_auto
{
	public function plugin()
	{
		parent::__construct();
	}

	/**
	 * 返回插件的ID
	**/
	final public function _id()
	{
		$name = get_class($this);
		$lst = explode("_",$name);
		unset($lst[0]);
		return implode("_",$lst);
	}

	/**
	 * 返回插件信息
	 * @参数 $id 插件ID，为空时尝试读取当前插件ID
	 * @返回 数组 id插件ID，title名称，author作者，version版本，note说明，param插件扩展保存的数据，这个是一个数组，path插件路径
	 * @更新时间
	**/
	final public function _info($id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$rs = array('id'=>$id);
		}
		$rs['path'] = $this->dir_root.'plugins/'.$id.'/';
		return $rs;
	}

	/**
	 * 保存插件扩展数据，注意，这里仅保存插件的扩展数据
	 * @参数 $ext 数组，要保存的数组
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	**/
	final public function _save($ext,$id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		if(!$id){
			return false;
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			return false;
		}
		$info = ($ext && is_array($ext)) ? serialize($ext) : '';
		return $this->model('plugin')->update_param($id,$info);
	}

	/**
	 * 返回插件输出的HTML数据，请注意，这里并没有输出，只是返回
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时返回模板内容，错误时返回false
	**/
	final public function _tpl($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if(!$file){
			return false;
		}
		return $this->tpl->fetch($file,'abs-file');
	}

	/**
	 * 输出的HTML数据到设备上，请注意，这里是输出，不是返回，同时也要注意，这里没有中止
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
	final public function _show($name,$id='')
	{
		$info = $this->_tpl($name,$id);
		if($info){
			echo $info;
		}
	}

	/**
	 * 输出的HTML数据到设备上并中断后续操作，请注意，这里是输出，有中断
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
	final public function _view($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if($file){
			$this->tpl->display($file,'abs-file');
			exit;
		}
	}

	/**
	 * 按顺序读取挑出最近的一个模板
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查这些文件是否存在，只要有一个符合要求即可<br />
	 * 1. 当前模板目录/plugins/插件ID/template/$name<br />
	 * 2. 当前模板目录/plugins/插件ID/$name<br />
	 * 3. 当前模板目录/插件ID/$name<br />
	 * 4. 当前模板目录/plugins_插件ID_$name<br />
	 * 5. 当前模板目录/插件ID_$name<br />
	 * 6. 程序根目录/plugins/插件ID/template/$name<br />
	 * 7. 程序根目录/plugins/插件ID/$name
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
	private function _tplfile($name,$id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		$list = array();
		$list[0] = $this->dir_root.$this->tpl->dir_tpl.'plugins/'.$id.'/template/'.$name;
		$list[1] = $this->dir_root.$this->tpl->dir_tpl.'plugins/'.$id.'/'.$name;
		$list[2] = $this->dir_root.$this->tpl->dir_tpl.$id.'/'.$name;
		$list[3] = $this->dir_root.$this->tpl->dir_tpl.'plugins_'.$id.'_'.$name;
		$list[4] = $this->dir_root.$this->tpl->dir_tpl.$id.'_'.$name;
		$list[5] = $this->dir_root.'plugins/'.$id.'/template/'.$name;
		$list[6] = $this->dir_root.'plugins/'.$id.'/tpl/'.$name;
		$list[7] = $this->dir_root.'plugins/'.$id.'/'.$name;
		$file = false;
		foreach($list as $key=>$value){
			if(file_exists($value)){
				$file = $value;
				break;
			}
		}
		return $file;
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_id()
	**/
	protected function plugin_id()
	{
		return $this->_id();
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_info()
	**/
	protected function plugin_info($id='')
	{
		return $this->_info();
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_save()
	**/
	protected function plugin_save($ext,$id="")
	{
		return $this->_save($ext,$id);
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_tpl()
	**/
	protected function plugin_tpl($name,$id='')
	{
		return $this->_tpl($name,$id);
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_show()
	**/
	protected function show_tpl($name,$id='')
	{
		$this->_show($name,$id);
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_view()
	**/
	protected function echo_tpl($name,$id='')
	{
		$this->_view($name,$id);
	}
}
