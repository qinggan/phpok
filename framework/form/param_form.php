<?php
/**
 * 自定义参数
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年12月02日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class param_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok."form/html/param_admin.html","abs-file");
	}

	public function cssjs()
	{
		$this->addjs("js/form.param.js");
		$this->addjs('js/jscolor/jscolor.js');
		$this->addjs('js/laydate/laydate.js');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if(!$rs){
			return false;
		}
		$this->cssjs();
		$pname = array();
		$pval = array();
		if($rs['p_name']){
			$rs['p_name'] = str_replace(array("\t","\r"),"",$rs['p_name']);
			$pname = explode("\n",$rs['p_name']);
			$this->assign('_pname',$pname);
		}
		if($rs['p_val'] && $rs['p_type']){
			$rs['p_val'] = str_replace(array("\t","\r"),"",$rs['p_val']);
			$tmp = explode("\n",$rs['p_val']);
			foreach($tmp as $key=>$value){
				$pval[$key] = explode(",",$value);
			}
		}
		if($rs['content']){
			$list = unserialize($rs['content']);
			$list['count'] = count($list['title']);
		}else{
			$list = array();
			if($pname){
				foreach($pname as $key=>$value){
					$tmp = array($value);
					$list['title'][] = $value;
				}
				if($pval){
					$list['content'] = $pval;
				}
				if($list['title']){
					$list['count'] = count($list['title']);
				}
			}else{
				$list = array('title'=>array(),'content'=>array());
			}
		}
		if($rs['p_type']){
			$rs['p_width'] = intval($rs['p_width']) ? intval($rs['p_width']) : '80';
		}else{
			$rs['p_width'] = intval($rs['p_width']) ? intval($rs['p_width']) : '300';
		}
		if(!$rs['p_add'] || (!$pname && $rs['p_add'] == 'no')){
			$rs['p_add'] = 'yes';
		}
		$this->assign('_rslist',$list);
		$this->assign('_rs',$rs);
		$this->assign('_ptype',$rs['p_type']);
		$this->assign('_param_edit',($rs['p_add'] == 'yes' ? true : false));
		$file = $this->dir_phpok.'form/html/param_admin_tpl.html';
		return $this->fetch($file,'abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		if(!$rs){
			return false;
		}
		$list = array();
		if($rs['p_type']){
			$list['title'] = $this->get($rs['identifier'].'_title');
			if($list['title'] && count($list['title'])>0){
				$tmp = $this->get($rs['identifier'].'_body');
				if($tmp){
					$list['content'] = array_chunk($tmp,count($list['title']));
				}
				return serialize($list);
			}
			return false;
		}else{
			$list['title'] = $this->get($rs['identifier'].'_title');
			$list['content'] = $this->get($rs['identifier'].'_body');
			return serialize($list);
		}
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		$info = is_string($rs['content']) ? unserialize($rs['content']) : $rs['content'];
		if(!$info || !$info['title'] || !is_array($info['title'])){
			return false;
		}
		if($appid == 'admin'){
			$html = '';
			$tmptitle = '';
			if($rs['p_type']){
				$html = '<table class="layui-table" style="margin:0"><thead><tr>';
				foreach($info['title'] as $key=>$value){
					$html .= '<th>'.$value.'</th>';
				}
				$html .= '</tr></thead>';
				$tmptitle = '';
				$i=0;
				foreach(($info['content'] ? $info['content'] : array()) as $key=>$value){
					$html .= '<tr>';
					foreach($info['title'] as $k=>$v){
						$html .= '<td>'.trim($value[$k]).'</td>';
						if($i<4){
							$tmptitle .= trim($value[$k]);
						}
					}
					$html .= '</tr>';
					$i++;
				}
				$html .= "</table>";
				$html = str_replace(array("\n","\r"),"",$html);
			}else{
				$tmptitle = '';
				if(!$info['title']){
					$info['title'] = array();
				}
				$i=0;
				foreach($info['title'] as $key=>$value){
					$html .= '<div>'.trim($value).'：'.trim($info['content'][$key]).'</div>';
					if($i<4){
						$tmptitle .= '<div>'.trim($value).'：'.trim($info['content'][$key]).'</div>';
					}
					$i++;
				}
				$html = str_replace(array("\n","\r"),"",$html);
			}
			if(!$html){
				$html = '无 <b>'.implode("/",$info['title']).'</b> 内容';
			}
			$rand_id = uniqid($rs['identifier'].'-');
			$html  = '<div style="display:none" id="'.$rand_id.'"><div style="width:500px;height:400px;overflow:auto;">'.$html.'</div></div>';
			$html .= '<a href="javascript:$.dialog({\'content\':document.getElementById(\''.$rand_id.'\'),\'cancel\':true,\'cancelVal\':\'关闭\',\'width\':500,\'height\':\'400\',\'resize\':false,\'padding\':\'0\',\'title\':\'预览\',\'drag\':false});void(0);">'.($tmptitle ? phpok_cut($tmptitle,80,'…') : implode("/",$info['title'])).'</a>';
			//$html = '<input type="button" value="'.implode("/",$info['title']).'" />';
			return $html;
		}else{
			if($rs['p_type']){
				return $info;
			}else{
				$list = array();
				if(!$info['title']){
					$info['title'] = array();
				}
				foreach($info['title'] as $key=>$value){
					$list[$value] = $info['content'][$key];
				}
				return $list;
			}
		}
	}
}