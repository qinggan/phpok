<?php
/**
 * 文本框表单配置器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年01月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class text_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 此项限制后台使用
	**/
	public function phpok_config()
	{
		$rslist = $this->model("project")->get_all_project($this->session->val('admin_site_id'));
		if($rslist){
			$p_list = $m_list = array();
			foreach($rslist as $key=>$value){
				if(!$value["parent_id"]){
					$p_list[] = $value;
				}
				if($value["module"]){
					$m_list[] = $value;
				}
			}
			if($p_list && count($p_list)>0){
				$this->assign("project_list",$p_list);
			}
			if($m_list && count($m_list)>0){
				$this->assign("title_list",$m_list);
			}
		}
		$this->view($this->dir_phpok.'form/html/text_admin.html','abs-file');
	}

	/**
	 * 读取扩展字段
	**/
	public function phpok_ext()
	{
		$data = array();
		$data['form_btn'] = $this->get('form_btn');
		$data['btn_name'] = $this->get('btn_name');
		$data['ext_format'] = $this->get('ext_format');
		$data['ext_quick_words'] = $this->get('ext_quick_words');
		$data['ext_quick_type'] = $this->get('ext_quick_type');
		$data['ext_include_3'] = $this->get('ext_include_3');
		$ext_field = $this->get('ext_field');
		if($ext_field && is_array($ext_field)){
			$list = array_chunk($ext_field,2);
			$tmp = array();
			foreach($list as $key=>$value)
			{
				if($value[0] == '' || $value[1] == ''){
					continue;
				}
				$tmp[] = implode(":",$value);
			}
			$data['ext_field'] = implode(",",$tmp);
		}
		$data['ext_onlyone'] = $this->get('ext_onlyone');
		$data['ext_layout'] = $this->get('ext_layout');
		return $data;
	}

	/**
	 * 格式化内容
	 * @参数 $rs 数组，字段属性（对应module_fields里的一条记录属性信息）
	 * @参数 $appid 入口，默认是admin
	**/
	public function phpok_format($rs,$appid='admin')
	{
		if($appid == 'admin'){
			return $this->_format_admin($rs);
		}else{
			return $this->_format_default($rs);
		}
	}

	/**
	 * 获取数据
	 * @参数 $rs 数组，字段属性（对应module_fields里的一条记录属性信息）
	 * @参数 $appid 入口，默认是admin
	**/
	public function phpok_get($rs,$appid='admin')
	{
		if(!$rs){
			return false;
		}
		$array = array('int','intval','float','floatval','html','html_js','time','safe');
		if(in_array($rs['format'],$array)){
			return $this->get($rs['identifier'],$rs['format']);
		}
		$info = $this->get($rs['identifier'],'html');
		if($info){
			$info = strip_tags($info);
		}
		return $info;
	}

	/**
	 * 输出显示的内容
	 * @参数 $rs 数组，字段属性（对应module_fields里的一条记录属性信息）
	 * @参数 $appid 入口，默认是admin
	**/
	public function phpok_show($rs,$appid='admin')
	{
		if(!$rs || !$rs['content']){
			return '';
		}
		//判断如果有UBB代码
		if(strpos($rs['content'],'[') !== false && strpos($rs['content'],']') !== false){
			$rs['content'] = $this->lib('ubb')->to_html($rs['content'],false);
		}
		if($appid == 'admin'){
			if($rs['format'] == 'time'){
				$format = $rs['form_btn'] == 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
				return date($format,$rs['content']);
			}
		}
		return $rs['content'];
	}

	private function _format_admin($rs)
	{
		$_laydate = false;
		if($rs['format'] == 'time'){
			$format = $rs['form_btn'] == "datetime" ? "Y-m-d H:i:m" : "Y-m-d";
			$time = $rs['content'] ? $rs['content'] : $this->time;
			$rs['content'] = date($format,$time);
		}
		if($rs['form_btn'] == 'color'){
			$this->addjs('js/jscolor/jscolor.js');
		}
		if($rs['form_btn'] && in_array($rs['form_btn'],array('date','datetime','time','year','month'))){
			$this->addjs('js/laydate/laydate.js');
			$_laydate = true;
			$tmp = array('date'=>P_Lang('日期'),'datetime'=>P_Lang('日期时间'),'time'=>P_Lang('时间'),'year'=>P_Lang('年份'),'month'=>P_Lang('年月'));
			$this->assign('_laydate_button',$tmp[$rs['form_btn']]);
		}
		if($rs['form_style']){
			$rs['form_style'] = $this->lib('common')->css_format($rs['form_style']);
		}
		if($rs['form_btn'] == 'user'){
			$css = $rs['form_style'] ? $rs['form_style'].';background:#EFEFEF;cursor:default;' : 'background:#EFEFEF;cursor:default;';
			$rs['form_style'] = $this->lib('common')->css_format($css);
		}
		if($rs['form_btn'] && strpos($rs['form_btn'],'title:') !== false){
			$tmp = explode(":",$rs['form_btn']);
			if($tmp[0] == 'title' && $tmp[1]){
				$rs['form_btn_pid'] = $tmp[1];
				$t = $this->model('project')->get_one($tmp[1],false);
				$rs['extitle'] = $t['title'];
				if($rs['btn_name']){
					$rs['extitle'] = $rs['btn_name'];
				}
			}
		}
		if($rs['ext_quick_words'] && trim($rs['ext_quick_words'])){
			$tmp = explode("\n",trim($rs['ext_quick_words']));
			foreach($tmp as $key=>$value){
				if(!$value || !trim($value)){
					unset($tmp[$key]);
					continue;
				}
				if(strpos($value,'|') !== false){
					$tmp2 = explode("|",$value);
					if(!$tmp2[1]){
						$tmp2[1] = $tmp2[0];
					}
					$tmp[$key] = array('id'=>$tmp2[0],'show'=>$tmp2[1]);
				}elseif(strpos($value,':') !== false){
					$tmp2 = explode(":",$value);
					if(!$tmp2[1]){
						$tmp2[1] = $tmp2[0];
					}
					$tmp[$key] = array('id'=>$tmp2[0],'show'=>$tmp2[1]);
				}else{
					$tmp[$key] = array('id'=>trim($value),'show'=>trim($value));
				}
			}
			$rs['ext_quick_words'] = $tmp;
		}
		$this->assign('_rs',$rs);
		$this->assign('_laydate',$_laydate);
		return $this->fetch($this->dir_phpok."form/html/text_admin_tpl.html",'abs-file');
	}

	private function _format_default($rs)
	{
		$_laydate = false;
		if($rs['form_btn'] == 'color'){
			$this->addjs('js/jscolor/jscolor.js');
		}
		if($rs['form_btn'] && in_array($rs['form_btn'],array('date','datetime','time','year','month'))){
			$this->addjs('static/admin/layui/layui.all.js');
			$this->addjs('js/laydate/laydate.js');
			$_laydate = true;
			$tmp = array('date'=>P_Lang('日期'),'datetime'=>P_Lang('日期时间'),'time'=>P_Lang('时间'),'year'=>P_Lang('年份'),'month'=>P_Lang('年月'));
			$this->assign('_laydate_button',$tmp[$rs['form_btn']]);
		}
		if($rs['form_style']){
			$rs['form_style'] = $this->lib('common')->css_format($rs['form_style']);
		}
		if($rs['format'] == 'time'){
			$format = $rs['form_btn'] == "datetime" ? "Y-m-d H:i" : "Y-m-d";
			$time = $rs['content'] ? $rs['content'] : $this->time;
			$rs['content'] = date($format,$time);
		}
		if($rs['ext_quick_words'] && trim($rs['ext_quick_words'])){
			$tmp = explode("\n",trim($rs['ext_quick_words']));
			foreach($tmp as $key=>$value){
				if(!$value || !trim($value)){
					unset($tmp[$key]);
					continue;
				}
				if(strpos($value,'|') !== false){
					$tmp2 = explode("|",$value);
					if(!$tmp2[1]){
						$tmp2[1] = $tmp2[0];
					}
					$tmp[$key] = array('id'=>$tmp2[0],'show'=>$tmp2[1]);
				}elseif(strpos($value,':') !== false){
					$tmp2 = explode(":",$value);
					if(!$tmp2[1]){
						$tmp2[1] = $tmp2[0];
					}
					$tmp[$key] = array('id'=>$tmp2[0],'show'=>$tmp2[1]);
				}else{
					$tmp[$key] = array('id'=>trim($value),'show'=>trim($value));
				}
				
			}
			$rs['ext_quick_words'] = $tmp;
		}
		$this->assign("_rs",$rs);
		$this->assign('_laydate',$_laydate);
		return $this->fetch($this->dir_phpok."form/html/text_www_tpl.html",'abs-file');
	}
}