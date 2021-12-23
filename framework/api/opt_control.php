<?php
/***********************************************************
	Filename: {phpok}/api/opt_control.php
	Note	: OPT选项功能前后台数据读取
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月21日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_control extends phpok_control
{
	private $symbol = '|';
	public function __construct()
	{
		parent::control();
	}

	//获取
	public function index_f()
	{
		$val = $this->get("val");
		$group_id = $this->get("group_id",'int');
		if(!$group_id){
			exit(P_Lang('没有指定选项组'));
		}
		$group_rs = $this->model('opt')->group_one($group_id);
		if($group_rs && $group_rs['link_symbol']){
			$this->symbol = $group_rs['link_symbol'];
		}
		$identifier = $this->get("identifier");
		if(!$identifier){
			exit(P_Lang('未定义变量'));
		}
		$rootlist = $this->model('opt')->opt_all('group_id='.$group_id.' AND parent_id=0');
		if(!$rootlist){
			exit(P_Lang('没有内容选项'));
		}
		if(!$val){
			$html  = '<ul class="select"><li>';
			$html .= $this->_html_select($rootlist,$identifier,$group_id,true);
			$html .= '</li></ul>';
			exit($html);
		}
		$list = explode($this->symbol,$val);
		$count = count($list);
		$htmlist = array();
		$parent_id = 0;
		for($i=0;$i<=$count;$i++){
			$tmplist = $this->model('opt')->opt_all('group_id='.$group_id.' AND parent_id='.$parent_id);
			if($tmplist){
				$first = array();
				for($m=0;$m<$i;$m++){
					$first[] = $list[$m];
				}
				$first = implode($this->symbol,$first);
				//检测是否有子项
				$sub = false;
				foreach($tmplist as $key=>$value){
					if($value['val'] == $list[$i]){
						$sub = $value['id'];
						break;
					}
				}
				if($sub){
					$parent_id = $sub;
					$tmplist2 = $this->model('opt')->opt_all('group_id='.$group_id.' AND parent_id='.$sub);
					if($tmplist2){
						$sub = true;
					}else{
						$sub = false;
					}
				}
				if($sub){
					$htmlist[] = $this->_html_select($tmplist,$identifier,$group_id,$list[$i],$first,false);
				}else{
					$htmlist[] = $this->_html_select($tmplist,$identifier,$group_id,$list[$i],$first,true);
				}
			}
		}
		if(count($htmlist) == 1){
			$htmlist[0] = $this->_html_select($rootlist,$identifier,$group_id,$list[0],true);
		}
		$html  = '<ul class="select">';
		foreach($htmlist as $key=>$value){
			$html .= '<li>'.$value.'</li>';
		}
		$html .= '</ul>';
		exit($html);
	}

	public function list_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID信息'));
		}
		$list = explode(",",$id);
		$rslist = array();
		foreach($list as $key=>$value){
			$rootlist = $this->model('opt')->opt_all('group_id='.$value.' AND parent_id=0');
			if($rootlist){
				$rslist[$value] = $rootlist;
			}
		}
		$this->success($rslist);
	}

	public function cate_f()
	{
		$this->symbol = ",";
		$val = $this->get("val");
		$group_id = $this->get("group_id",'int');
		if(!$group_id){
			exit(P_Lang('没有指定选项组'));
		}
		$cate_rs = $this->model('cate')->get_one($group_id);
		if(!$cate_rs){
			exit(P_Lang('根分类信息不存在'));
		}
		$identifier = $this->get("identifier");
		if(!$identifier){
			exit(P_Lang('未定义变量'));
		}
		$rootlist = $this->model('cate')->get_sonlist($cate_rs['id'],true);
		if(!$rootlist){
			exit(P_Lang('没有内容选项'));
		}
		if(!$val){
			$html  = '<ul class="select"><li>';
			$html .= $this->_html_select_cate($rootlist,$identifier,$group_id,true);
			$html .= '</li></ul>';
			exit($html);
		}
		$list = explode($this->symbol,$val);
		$count = count($list);
		$htmlist = array();
		$parent_id = $group_id;
		for($i=0;$i<=$count;$i++){
			$tmplist = $this->model('cate')->get_sonlist($parent_id,true);
			if($tmplist){
				$first = array();
				for($m=0;$m<$i;$m++){
					$first[] = $list[$m];
				}
				$first = implode($this->symbol,$first);
				//检测是否有子项
				$sub = false;
				foreach($tmplist as $key=>$value){
					if($value['id'] == $list[$i]){
						$sub = $value['id'];
						break;
					}
				}
				if($sub){
					$parent_id = $sub;
					$tmplist2 = $this->model('cate')->get_sonlist($sub,true);
					if($tmplist2){
						$sub = true;
					}else{
						$sub = false;
					}
				}
				if($sub){
					$htmlist[] = $this->_html_select_cate($tmplist,$identifier,$group_id,$list[$i],$first,false);
				}else{
					$htmlist[] = $this->_html_select_cate($tmplist,$identifier,$group_id,$list[$i],$first,true);
				}
			}
		}
		if(count($htmlist) == 1){
			$htmlist[0] = $this->_html_select_cate($rootlist,$identifier,$group_id,$list[0],true);
		}
		$html  = '<ul class="select">';
		foreach($htmlist as $key=>$value){
			$html .= '<li>'.$value.'</li>';
		}
		$html .= '</ul>';
		exit($html);
	}

	private function _html_select_cate($rslist,$identifier,$group_id,$selected='',$first='',$in_name=false){
		if(is_bool($selected)){
			$in_name = $selected;
			$selected = '';
		}
		if(is_bool($first)){
			$in_name = $first;
			$first = '';
		}
		$html  = '<select lay-ignore class="select form_select form_select_'.$identifier.'" ';
		if($in_name){
			$html .= 'name="'.$identifier.'" id="'.$identifier.'" ';
		}
		$html .= 'onchange="$.phpok_form_select.change('.$group_id.',\''.$identifier.'\',this.value,\'cate\')">';
		$html .= '<option value="'.$first.'">'.P_Lang('请选择…').'</option>';
		foreach($rslist as $key=>$value){
			$tmp = $first ?  $first.$this->symbol.$value['id'] : $value['id'];
			$html .= '<option value="'.$tmp.'"';
			if($selected && $selected == $value['id']){
				$html .= ' selected';
			}
			$html .= '>'.$value["title"]."</option>";
		}
		$html .= "</select>";
		return $html;
	}

	private function _html_select($rslist,$identifier,$group_id,$selected='',$first='',$in_name=false)
	{
		if(is_bool($selected)){
			$in_name = $selected;
			$selected = '';
		}
		if(is_bool($first)){
			$in_name = $first;
			$first = '';
		}
		$html  = '<select lay-ignore class="select form_select form_select_'.$identifier.'" ';
		if($in_name){
			$html .= 'name="'.$identifier.'" id="'.$identifier.'" ';
		}
		$html .= 'onchange="$.phpok_form_select.change('.$group_id.',\''.$identifier.'\',this.value)">';
		$html .= '<option value="'.$first.'">'.P_Lang('请选择…').'</option>';
		foreach($rslist as $key=>$value){
			$tmp = $first ?  $first.$this->symbol.$value['val'] : $value['val'];
			$html .= '<option value="'.$tmp.'"';
			if($selected && $selected == $value['val']){
				$html .= ' selected';
			}
			$html .= '>'.$value["title"]."</option>";
		}
		$html .= "</select>";
		return $html;
	}

	private function _check_sonlist($parent_id,$group_id)
	{
		$son = false;
		foreach($rslist as $key=>$value){
			if($value['parent_id'] == $parent_id){
				$son = true;
				break;
			}
		}
		return $son;
	}

	private function ajax_admin_opt_tmp_list(&$tmp_array,$list,$pid)
	{
		if($pid){
			$tmp_all = $list[$pid];
			$tmp_array[] = $tmp_all["val"];
			$this->ajax_admin_opt_tmp_list($tmp_array,$list,$tmp_all["parent_id"]);
		}
	}
}