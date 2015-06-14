<?php
/***********************************************************
	Filename: phpok/phpok_tpl.php
	Note	: PHPOK模板引挈，简单实用
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2015年01月21日 20时27分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class phpok_tpl
{
	public $tpl_id = 1;
	public $dir_tpl = "tpl/";
	public $dir_cache = "data/cache/";
	public $dir_php = "./";
	public $dir_root = "./";
	public $path_change = "";
	public $refresh_auto = true;
	public $refresh = false;
	public $tpl_ext = "html";
	public $html_head = '<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?>';
	public $tpl_value;

	//构造函数
	public function __construct($config=array())
	{
		if($config["id"]) $this->tpl_id = $config["id"];
		if($config["dir_tpl"]) $this->dir_tpl = $config["dir_tpl"];
		if($config["dir_cache"]) $this->dir_cache = $config["dir_cache"];
		if($config["dir_php"]) $this->dir_php = $config["dir_php"];
		if($config["dir_root"]) $this->dir_root = $config["dir_root"];
		if($config["path_change"]) $this->path_change = $config["path_change"];
		$this->refresh_auto = $config["refresh_auto"] ? true : false;
		$this->refresh = $config["refresh"] ? true : false;
		$this->tpl_ext = $config["tpl_ext"] ? $config["tpl_ext"] : "html";
		if($this->dir_tpl && substr($this->dir_tpl,-1) != "/") $this->dir_tpl .= "/";
		if($this->dir_cache && substr($this->dir_cache,-1) != "/") $this->dir_cache .= "/";
	}

	public function assign($var,$val="")
	{
		if(!$var || (is_array($var) && $val))
		{
			return false;
		}
		if(is_array($var))
		{
			foreach($var as $key=>$value)
			{
				$this->tpl_value[$key] = $value;
			}
		}
		else
		{
			$this->tpl_value[$var] = $val;
		}
	}

	//注销变量
	public function unassign($var='')
	{
		if(!$var)
		{
			unset($this->tpl_value);
			return false;
		}
		if(!is_array($var) && $this->tpl_value[$var])
		{
			unset($this->tpl_value[$var]);
			return true;
		}
		foreach((array)$var as $key=>$value)
		{
			if($this->tpl_value[$key])
			{
				unset($this->tpl_value[$key]);
			}
		}
		return true;
	}

	public function path_change($val="")
	{
		$this->path_change = $val;
	}

	//输出编译后的模板信息
	//tpl是指模板文件，支持子目录
	//type 类型，可选值为：file,file-ext,content,msg,abs-file
	//	file，相对路径，不带后缀
	//	file-ext，相对路径，带后缀
	//	content/msg，纯模板内容
	//	abs-file，带后缀的绝对路径
	public function output($tpl,$type="file",$path_format=true)
	{
		if(!$tpl){
			$this->error(P_Lang('模板信息为空'));
		}
		$comp_id = $this->comp_id($tpl,$type);
		if(!$comp_id){
			$this->error(P_Lang('没有指定模板源'));
		}
		$this->compiling($comp_id,$tpl,$type,$path_format);
		$this->assign("session",$_SESSION);
		$this->assign("get",$_GET);
		$this->assign("post",$_POST);
		$this->assign("cookie",$_COOKIE);
		if($this->path_change){
			$tmp_path_list = explode(",",$this->path_change);
			$tmp_path_list = array_unique($tmp_path_list);
			foreach($tmp_path_list AS $key=>$value){
				$value = trim($value);
				if($value == ''){
					continue;
				}
				$this->assign("_".$value,$value);
			}
		}
		if(!$this->tpl_value || !is_array($this->tpl_value)){
			$this->tpl_value = array();
		}
		$varlist = (is_array($GLOBALS))?array_merge($GLOBALS,$this->tpl_value):$this->tpl_value;
		extract($varlist);
		include($this->dir_cache.$comp_id);
	}

	//取得内容，不直接输出，参数output
	public function fetch($tpl,$type="file",$path_format=true)
	{
		ob_start();
		$this->output($tpl,$type,$path_format);
		$msg = ob_get_contents();
		ob_end_clean();
		return $msg;
	}

	# 取得编译后的文件ID
	public function comp_id($tpl,$type="file")
	{
		$string = $this->tpl_id."_";
		if($type == "file" || $type == "file-ext"){
			$tpl = strtolower($tpl);
			$tpl = str_replace("/","_folder_",$tpl);
			$string .= $tpl;
		}elseif($type == "abs-file"){
			$string .= substr(md5($tpl),9,16);
			$string .= "_abs";
		}else{
			$string .= substr(md5($tpl),9,16);
			$string .= "_c";
		}
		$string .= ".php";
		return $string;
	}


	//显示HTML信息
	public function display($tpl,$type="file",$path_format=true)
	{
		$this->output($tpl,$type,$path_format);
		exit;
	}

	//编译模板内容，通过正则替换自己需要的
	# compiling_id，生成的编译文件ID
	# tpl，模板源文件
	# type，模板类型
	private function compiling($compiling_id,$tpl,$type="file",$path_format=true)
	{
		//判断是否刷新
		$is_refresh = false;
		if(!file_exists($this->dir_cache.$compiling_id) || $this->refresh){
			$is_refresh = true;
		}
		if($type !="file" && $type != "file-ext" && $type != "abs-file"){
			$is_refresh = true;
		}
		if(!$is_refresh && ($type == "file" || $type == "file-ext" || $type == "abs-file")){
			if($type == "file"){
				$tplfile = $this->dir_root.$this->dir_tpl.$tpl.".".$this->tpl_ext;
				if(!file_exists($tplfile) && basename($this->dir_tpl) != 'www'){
					$tplfile = $this->dir_root.'tpl/www/'.$tpl.'.html';
				}
			}elseif($type == "file-ext"){
				$tplfile = $this->dir_root.$this->dir_tpl.$tpl;
				if(!file_exists($tplfile) && basename($this->dir_tpl) != 'www'){
					$tplfile = $this->dir_root.'tpl/www/'.$tpl;
				}
			}else{
				$tplfile = $tpl;
			}
			if($this->refresh_auto){
				if(!file_exists($tplfile)){
					$this->error(P_Lang('模板文件[tplfile]不存在',array('tplfile'=>basename($tpl))));
				}
				if(filemtime($tplfile) > filemtime($this->dir_cache.$compiling_id)){
					$is_refresh = true;
				}
			}
		}
		if(!$is_refresh){
			return true;
		}
		$html_content = $this->get_content($tpl,$type);
		if($html_content){
			$php_content = $this->html_to_php($html_content,$path_format);
			file_put_contents($this->dir_cache.$compiling_id,$this->html_head.$php_content);
		}
		return true;
	}

	//前端HTML里Debug调用
	public function html_debug($info)
	{
		if(!$info || !is_array($info) || !$info[1] || !trim($info[1])) return '';
		$info = $info[1];
		$info = stripslashes(trim($info));
		$info = $this->str_format($info);
		return '<?php echo "<pre>".print_r($'.$info.',true)."</pre>";?>';
	}

	private function lang_replace($info)
	{
		if(!$info || !is_array($info) || !$info[1] || !trim($info[1])) return '';
		$info = $info[1];
		$info = trim(str_replace(array("'",'"'),'',$info));
		$lst = explode("|",$info);
		$param = false;
		if($lst[1]){
			$tmp = explode(",",$lst[1]);
			foreach($tmp as $key=>$value){
				$tmp2 = explode(":",$value);
				if(!$param){
					$param = array();
				}
				if(substr($tmp2[1],0,1) == '$'){
					$tmp2[1] = '\'.'.$tmp2[1].'.\'';
				}
				$param[$tmp2[0]] = '<span style="color:red">'.$tmp2[1].'</span>';
			}
		}
		if($param){
			$string = "array(";
			$i=0;
			foreach($param as $key=>$value){
				if($i>0){
					$string .= ",";
				}
				$string .= "'".$key."'=>'".$value."'";
				$i++;
			}
			$string .= ")";
			return '<?php echo P_Lang("'.stripslashes($lst[0]).'",'.$string.');?>';
		}else{
			return '<?php echo P_Lang("'.stripslashes($info).'");?>';
		}
	}

	//正则替换
	private function html_to_php($content,$path_format=true)
	{
		//第一步，整理模板中的路径问题
		if($this->path_change && $path_format)
		{
			$tmp_path_list = explode(",",$this->path_change);
			$tmp_path_list = array_unique($tmp_path_list);
			foreach($tmp_path_list AS $key=>$value)
			{
				$value = trim($value);
				if(!$value) continue;
				$content = str_replace($value."/",$this->dir_tpl.$value."/",$content);
			}
		}
		//正则替换内容问题
		$content = preg_replace_callback('/<!--\s+head\s+(.+)\s+-->/isU',array($this,'head_php'),$content);
		$content = preg_replace_callback('/<!--\s+plugin\s*(.*)\s+-->/isU',array($this,'plugin_php'),$content);
		$content = preg_replace('/(\{|<!--)\s*(\/if|end|\/foreach|\/for|\/while|\/loop)\s*(\}|-->)/isU','<?php } ?>',$content);
		$content = preg_replace('/(\{|<!--)\s*(else)\s*(\}|-->)/isU','<?php } else { ?>',$content);
		$content = preg_replace('/<!--\s*php\s*-->/isU','<?php',$content);
		$content = preg_replace('/<!--\s*\/\s*php\s*-->/isU','?>',$content);
		$content = preg_replace_callback('/\{debug\s+\$(.+)\}/isU',array($this,'html_debug'),$content);
		//语言包替换
		$content = preg_replace_callback('/\{lang\s*(.+)\}/isU',array($this,'lang_replace'),$content);
		//内置标签替换
		$content = preg_replace_callback('/(\{|<!--\s*)(arclist|arc|subcate|catelist|cate|project|sublist|parent|plist|fields|user|userlist)[:]*([\w\$]*)\s+(.+)(\}|\s*-->)/isU',array($this,'data_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)\/(arclist|arc|catelist|cateinfo|subcate|project|sublist)[:]*([a-zA-Z\_0-9\$]*)(\}|\s*-->)/isU',array($this,'undata_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)unset\s*(:|\(|=)\s*([^\)]+)[\)]*(\}|\}|\s*-->)/isU',array($this,'undata_php'),$content);
		//循环语法
		$content = preg_replace_callback('/(\{|<!--\s*)\$([a-zA-Z0-9_\$\[\]\'\\\"\.\-]{1,60})\s+AS\s+(.+)(\}|\s*-->)/isU',array($this,'_foreach_php_ex_doller'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)foreach\s*\(\s*(.+)\s+AS\s+(.+)\s*\)\s*(\}|\s*-->)/isU',array($this,'_foreach_php_in_doller'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)loop\s+(.+)(\}|\s*-->)/isU',array($this,'_loop_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)(while|for)\s*\(\s*(.+)\s*\)\s*(\}|\s*-->)/isU',array($this,'_for_while_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)(while|for)\s+(.+)(\}|\s*-->)/isU',array($this,'_for_while_php'),$content);
		//条件判断
		$content = preg_replace_callback('/(\{|<!--\s*)(if|else\s*if)\s*\(\s*(.+)\s*\)\s*(\}|\s*-->)/isU',array($this,'_if_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)(if|else\s*if)\s+(.+)(\}|\s*-->)/isU',array($this,'_if_php'),$content);
		//文件包含
		$content = preg_replace_callback('/(\{|<!--\s*)include\s+(.+)(\}|\s*-->)/isU',array($this,'_include_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)inc\s*(:|=)\s*(.+)(\}|\s*-->)/isU',array($this,'_inc_php'),$content);
		//单行PHP代码
		$content = preg_replace_callback('/(\{|<!--)\s*(run|php)\s*(:|\s+)\s*(.+)\s*(\}|-->)/isU',array($this,'_php_runing'),$content);
		//PHPOK4的网址写法
		$content = preg_replace_callback('/\{url\s+(.+)\/\}/isU',array($this,'_url_php'),$content);
		$content = preg_replace_callback('/\{ajaxurl\s+(.+)\/\}/isU',array($this,'_ajaxurl_php'),$content);
		//PHPOK4外调函数的写法
		$content = preg_replace_callback('/\{func\s+(.+)\}/isU',array($this,'_func_php'),$content);
		//基本变量输出
		$content = preg_replace_callback('/\{\$([a-zA-Z\_].*)\s*\}/isU',array($this,'_echo_php'),$content);
		$content = preg_replace_callback('/\{\s*(:|=|echo\s+)\s*(.+)\}/isU',array($this,'_echo_phpok3'),$content);
		$content = preg_replace('/\{#(.*)#\}/isU','\\1',$content);
		return $content;
	}

	function _echo_phpok3($info)
	{
		return $this->echo_php($info[2]);
	}

	function _echo_php($info)
	{
		return $this->echo_php('$'.$info[1]);
	}

	function _func_php($info)
	{
		return $this->func_php($info[1]);
	}

	function _ajaxurl_php($info)
	{
		return $this->ajaxurl_php($info[1]);
	}

	function _url_php($info)
	{
		return $this->url_php($info[1]);
	}

	function _php_runing($info)
	{
		return $this->php_runing($info[4]);
	}

	function _inc_php($info)
	{
		return $this->inc_php($info[3]);
	}

	function _include_php($info)
	{
		return $this->include_php($info[2]);
	}

	function _if_php($info)
	{
		return $this->if_php($info[2],$info[3]);
	}

	function _for_while_php($info)
	{
		return $this->for_while_php($info[2],$info[3]);
	}

	function _loop_php($info)
	{
		return $this->loop_php($info[2]);
	}

	function url_encode($info)
	{
		return rawurlencode($info[1]);
	}

	//更换头部信息
	function head_php($string)
	{
		if(!$string || !is_array($string) || !$string[1] || !trim($string[1])) return '';
		$string = $string[1];
		$string = stripslashes(trim($string));
		$string = $this->str_format($string);
		$string = preg_replace_callback("/[\"|']{1}(.+)[\"|']{1}/isU",array($this,'url_encode'),$string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);
		$string = str_replace(" ","&",$string);
		parse_str($string,$list);
		$tmpc = "";
		if($list)
		{
			foreach($list AS $key=>$value)
			{
				$value = substr($value,0,1) == '$' ? $value : '"'.$value.'"';
				$list[$key] = "'".$key."'=>".rawurldecode($value);
			}
			$tmpc = 'array('.implode(",",$list).')';
		}
		$tmpc = stripslashes($tmpc);
		return '<?php echo tpl_head('.$tmpc.');?>';
	}

	function php_runing($string)
	{
		if(!$string || !trim($string)) return '';
		$string = trim($string);
		$string = stripslashes($string);
		$string = $this->str_format($string,false,false);
		return '<?php '.$string.';?>';
	}

	//暂时不管参数
	function plugin_php($string)
	{
		if(!$string || !is_array($string) || !$string[1] || !trim($string[1])) return '';
		$string = $string[1];
		$string = trim($string);
		$string = str_replace(array("'",'"',"\\","/",' ',"&nbsp;"),'',$string);
		return '<?php echo $GLOBALS["app"]->plugin_html_ap("'.$string.'");?>';
	}

	function data_php($array)
	{
		$a = $array[2];
		$b = $array[3] ? $array[3] : '';
		$c = $array[4] ? $array[4] : '';
		if(!$a) return '<?php echo "";?>';
		if(!$b) $b = '$list';
		if(substr($b,0,1) != '$') $b = '$'.$b;
		$tmp_c = 'array()';
		if($c)
		{
			$c = preg_replace("/(\x20{2,})/"," ",$c);# 去除多余空格，只保留一个空格
			//处理引号里的空格
			$c = preg_replace("/[\"|']([a-zA-Z\_\-\.,]*)(\s+)([a-zA-Z\_\-\.,]*)[\"|']/isU",'\\1:_:_:-phpok-:_:_:\\3',$c);
			$c = str_replace(" ","&",$c);
			$c = $this->str_format($c);
			parse_str($c,$list);
			if($list)
			{
				$tmp = array();
				foreach($list AS $key=>$value)
				{
					if(!$value) continue;
					$t = substr($value,0,1) == '$' ? $value : '"'.$value.'"';
					$tmp[] = "'".$key."'=>".$t;
				}
				$tmp_c = "array(".implode(",",$tmp).")";
				$tmp_c = str_replace(":_:_:-phpok-:_:_:"," ",$tmp_c);
				$tmp_c = stripslashes($tmp_c);
			}
		}
		$info = '<?php '.$b."=phpok('_".$a."',".$tmp_c.");?>";
		return $info;
	}

	//注销PHP信息
	function undata_php($b="")
	{
		$b = $b[3];
		if(!$b) $b= '$list';
		if(substr($b,0,1) != '$') $b = '$'.$b;
		$b = preg_replace("/(\x20{2,})/"," ",$b);# 去除多余空格，只保留一个空格
		$b = $this->str_format($b);
		$b = str_replace(" ",",",$b);
		return '<?php unset('.$b.');?>';
	}

	function include_php($string)
	{
		$rs = $this->str_to_list($string);
		if(!$rs) return false;
		if(!$rs["tpl"] && !$rs["file"] && !$rs["php"]) return false;
		$string = "";
		foreach($rs AS $key=>$value)
		{
			if($key != "tpl" && $key != "file")
			{
				if(substr($value,0,1) != '$') $value = '"'.$value.'"';
				$string .= '<?php $'.$key.'='.$value.';?>';
				$string .= '<?php $this->assign("'.$key.'",'.$value.'); ?>';
			}
		}
		# 当存在file参数量
		if($rs['file'])
		{
			if(strtolower(substr($rs['file'],-4)) != '.php')
			{
				$rs['file'] .= '.php';
			}
			if(is_file($this->dir_php.$rs['file']))
			{
				$string .= '<?php include("'.$this->dir_php.$rs["file"].'");?>';
			}
		}
		if($rs["tpl"])
		{
			$string .= '<?php $this->output("'.$rs["tpl"].'","file"); ?>';
		}
		return $string;
	}

	function inc_php($string)
	{
		if(!$string) return false;
		$string = 'tpl="'.$string.'"';
		return $this->include_php($string);
	}

	//处理网址
	function url_php($string)
	{
		if(!$string || !trim($string)) return false;
		$string = trim($string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多余空格，只保留一个空格
		$string = str_replace(" ","&",$string);
		parse_str($string,$list);
		if(!$list || count($list)<1) return false;
		$array = array();
		foreach($list AS $key=>$value)
		{
			$value = $this->str_format($value);
			if(substr($value,0,1) != '$') $value = "'".$value."'";
			$array[] = "'".$key."'=>".$value;
		}
		return '<?php echo phpok_url(array('.implode(",",$array).'));?>';
	}

	function ajaxurl_php($string)
	{
		if(!$string || !trim($string)) return $this->return_false();
		$string = trim($string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多余空格，只保留一个空格
		parse_str($string,$list);
		if(!$list || count($list)<1) return $this->return_false();
		$url = $GLOBALS['app']->url.$GLOBALS['app']->config['www_file']."?";
		$url.= $GLOBALS['app']->config['ctrl_id']."=ajax";
		foreach($list AS $key=>$value)
		{
			$value = $this->str_format($value);
			if(substr($value,0,1) == '$')
			{
				$url .= "&".$key.'=<?php echo rawurlencode('.$value.');?>';
			}
			else
			{
				$url .= "&".$key.'='.rawurlencode($value);
			}
		}
		return $url;
	}

	function return_false()
	{
		return '<?php echo "";?>';
	}

	function func_php($string)
	{
		if(!$string) return false;
		$string = stripslashes(trim($string));
		$string = $this->str_format($string);
		$string = preg_replace_callback("/[\"|']{1}(.+)[\"|']{1}/isU",array($this,'url_encode'),$string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多余空格，只保留一个空格
		$list = explode(" ",$string);
		$func = $list[0];
		if(!$func || !function_exists($func))
		{
			return false;
		}
		$string = '<?php echo '.$func.'(';
		$newlist = array();
		foreach($list AS $key=>$value)
		{
			if($key>0)
			{
				if($value)
				{
					$value = $this->str_format($value);
					if($value)
					{
						if(substr($value,0,1) != '$')
						{
							$newlist[] = "'".rawurldecode($value)."'";
						}
						else
						{
							$newlist[] = rawurldecode($value);
						}
					}
				}
			}
		}
		$newstring = implode(",",$newlist);
		$string .= $newstring.');?>';
		return $string;
	}

	# PHP输出
	function echo_php($string)
	{
		if(!$string) return false;
		$string = trim($string);
		$string = stripslashes($string);
		$string = $this->str_format($string,false,false);
		return '<?php echo '.$string.';?>';
	}

	# While/For循环
	function for_while_php($left,$string)
	{
		if(!$string || !$left) return false;
		$string = trim($string);
		$string = stripslashes($string);
		$string = $this->str_format($string,false);
		$php = '<?php '.$left.'('.$string.'){ ?>';
		return $php;
	}

	function _foreach_php_ex_doller($array)
	{
		return $this->foreach_php('$'.$array[2],$array[3]);
	}

	function _foreach_php_in_doller($array)
	{
		return $this->foreach_php($array[2],$array[3]);
	}

	# Foreach 简单循环
	function foreach_php($from,$value)
	{
		if(!$from || !$value) return false;
		$list = explode("=>",$value);
		if($list[1])
		{
			$key = $list[0];
			$value = $list[1];
		}
		$string = 'from="'.$from.'" value="'.$value.'"';
		if($key)
		{
			$string .= ' key="'.$key.'"';
		}
		return $this->loop_php($string);
	}

	# IF 条件操作
	function if_php($left_string,$string)
	{
		if(!$string || !$left_string) return false;
		$string = trim($string);
		$string = stripslashes($string);
		# 通过正则替换文本中的.为[]
		$string = $this->str_format($string,false,false);
		if(strtolower(substr($left_string,0,4)) == "else") $left_string = '}'.$left_string;
		$php = '<?php '.$left_string.'('.$string.'){ ?>';
		return $php;
	}

	function get_loop_id($from)
	{
		$from = substr($from,1);
		$from = str_replace(array("['",'["',"']",'"]','$','-'),"_",$from);
		$from = str_replace(array("[","]"),"_",$from);
		if(substr($from,-1) != "_")
		{
			$from .= "_";
		}
		return $from."id";
	}

	# Loop循环格式化，此循环支持指定ID，可用于统计
	function loop_php($string)
	{
		$rs = $this->str_to_list($string,"key,value,from");
		if(!$rs) return false;
		if(!$rs || !is_array($rs) || count($rs)<1 || !$rs["from"]) return false;
		# 初始化循环的ID，未设置ID的用户，将取得rslist里的信息
		if(!$rs["id"]) $rs["id"] = $this->get_loop_id($rs["from"]);
		$id = $rs["id"];
		if(in_array(substr($id,0,1),array("0","1","2","3","4","5","6","7","8","9"))) $id = "phpok_".$id;
		if(substr($id,0,1) == '$') $id = substr($id,1);
		# 计算当前循环对应数量ID
		$php  = '<?php $'.$id.'["num"] = 0;';
		# 判断是否数组
		$php .= $rs["from"].'=is_array('.$rs["from"].') ? '.$rs["from"].' : array();';
		# 计算循环数据的总数
		$php .= '$'.$id.'["total"] = count('.$rs["from"].');';
		# 计算循环对应的索引ID
		if(!$rs["index"]) $rs["index"] = 0;
		$index_id = $rs["index"] - 1;
		$php .= '$'.$id.'["index"] = '.$index_id.';';
		if(!$rs["value"])
		{
			$rs["value"] = '$value';
		}
		$php .= 'foreach('.$rs["from"].' AS ';
		if($rs["key"] || $rs["item"])
		{
			if(!$rs["key"]) $rs["key"] = $rs["item"];
			$php .= $rs["key"]."=>";
		}
		$php .= $rs["value"].'){ ';
		$php .= '$'.$id.'["num"]++;';
		$php .= '$'.$id.'["index"]++;';
		$php .= ' ?>';
		return $php;
	}

	# 格式化文本，去除首尾引号，将.数组变成[]模式
	# string，要格式化的文本
	# auto_dollar，前面是否主动添加 $ 符号，默认为否
	# del_mark，是否删除引号
	function str_format($string,$auto_dollar=false,$del_mark=true)
	{
		if($string == '') return false;
		$string = stripslashes(trim($string));
		if($del_mark)
		{
			if(substr($string,0,1) == '"' || substr($string,0,1) == "'") $string = substr($string,1);
			if(substr($string,-1) == '"' || substr($string,-1) == "'") $string = substr($string,0,-1);
		}
		$string = $this->points_to_array($string);
		if($auto_dollar && substr($string,0,1) != '$')
		{
			$string = '$'.$string;
		}
		return $string;
	}

	# 点变成数组
	function points_to_array($string)
	{
		if(!$string) return false;
		//if(substr($string,0,1) != '$' && substr($string,1,1) != '$') return $string;
		for($i=0;$i<5;$i++)
		{
			$string = preg_replace('/\$([\w\[\]\>\-]+)\.([\w]+\b)/iU','$\\1[\\2]',$string);
		}
		$string = preg_replace('/\[([a-z\_][a-z0-9\_]*)\]/iU',"['\\1']",$string);
		return $string;
	}

	# 字符串格式化为数组
	function str_to_list($string,$need_dollar="")
	{
		if(!$string || !trim($string)) return false;
		$string = stripslashes(trim($string));
		$string = $this->str_format($string);
		$string = preg_replace_callback("/[\"|']{1}(.+)[\"|']{1}/isU",array($this,'url_encode'),$string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多余空格，只保留一个空格
		$list = explode(" ",$string); # 格式化为数组
		$rs = array();
		if($need_dollar && !is_array($need_dollar))
		{
			$need_dollar = explode(",",$need_dollar);
		}
		else
		{
			if(!$need_dollar) $need_dollar = array();
		}
		foreach($list AS $key=>$value)
		{
			$value = trim($value);
			if($value)
			{
				$str = explode("=",$value);
				$str_key = strtolower($str[0]);
				$str_value = $str[1];
				if($str_key && $str_value)
				{
					$str_value = rawurldecode($str_value);
					$str_value = in_array($str_key,$need_dollar) ? $this->str_format($str_value,true) : $this->str_format($str_value,false);
					$rs[$str_key] = $str_value;
				}
			}
		}
		return $rs;
	}

	//取得模板的内容
	private function get_content($tpl,$type="file")
	{
		if(!$tpl) return false;
		if($type == "content" || $type == "msg") return $tpl;
		if($type == "file")
		{
			$tplfile = $this->dir_root.$this->dir_tpl.$tpl.".".$this->tpl_ext;
		}
		elseif($type == "file-ext")
		{
			$tplfile = $this->dir_root.$this->dir_tpl.$tpl;
		}
		else
		{
			$tplfile = $tpl;
		}
		if(!file_exists($tplfile))
		{
			$this->error("模板文件：".basename($tplfile)." 不存在！");
		}
		return file_get_contents($tplfile);
	}

	private function ascii($str)
	{
		if(!$str)
		{
			return false;
		}
		$str = iconv("UTF-8", "UTF-16BE", $str);
		for ($i = 0; $i < strlen($str); $i++,$i++)
		{
			$code = ord($str{$i}) * 256 + ord($str{$i + 1});
			if ($code < 128)
			{
				$output .= chr($code);
			}
			else if($code != 65279)
			{
				$output .= "&#".$code.";";
			}
		}
		return $output;
	}

	public function error($msg)
	{
		exit($this->ascii($msg));
	}

	public function ext()
	{
		return $this->tpl_ext;
	}

	public function get_tpl($tplname,$default="default")
	{
		$tplfile = $this->dir_tpl.$tplname.".".$this->tpl_ext;
		if(file_exists($tplfile))
		{
			return $tplname;
		}
		return $default;
	}

	//检测文件是否存在
	public function check_exists($tplname,$isext=false,$ifabs=false)
	{
		$tplfile = $tplname;
		if(!$isext) $tplfile .= ".".$this->tpl_ext;
		if(!$ifabs) $tplfile = $this->dir_root.$this->dir_tpl.$tplfile;
		if(is_file($tplfile))
		{
			return true;
		}
		return false;
	}

	//检测模板文件是否存在
	public function check($tplfile)
	{
		if(!$tplfile)
		{
			return false;
		}
		$tpl_1 = $this->dir_root.$this->dir_tpl.$tplfile.".".$this->tpl_ext;
		$tpl_2 = $this->dir_root.$this->dir_tpl.$tplfile;
		$tpl_3 = $tplfile.'.'.$this->tpl_ext;
		if(is_file($tpl_1) || is_file($tpl_2) || is_file($tpl_3) || is_file($tplfile))
		{
			return true;
		}
		return false;
	}
}
