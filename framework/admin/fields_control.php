<?php
/**
 * 常用字段管理器
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_control extends phpok_control
{
	private $form_list;
	private $field_list;
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->form_list = $this->model('form')->form_all(true);
		$this->field_list = $this->model('form')->field_all(true);
		$this->format_list = $this->model('form')->format_all(true);
		$this->assign("field_list",$this->field_list);
		$this->assign("form_list",$this->form_list);
		$this->assign("format_list",$this->format_list);
		$this->popedom = appfile_popedom("fields");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 取得全部常用字段列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		//读取全部字段
		$rslist = $this->model('fields')->get_all($condition);
		if($rslist){
			foreach($rslist AS $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("fields_index");
	}

	/**
	 * 添加字段
	**/
	public function set_f()
	{
		$id = $this->get("id");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('fields')->get_one($id);
			if($rs["ext"]){
				if(is_string($rs['ext'])){
					$ext = unserialize($rs['ext']);
					$rs['ext'] = $ext;
				}
				if($rs['ext'] && is_array($rs['ext'])){
					foreach($rs['ext'] as $key=>$value){
						$rs[$key] = $value;
					}
				}
			}
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$opt_list = $this->model('opt')->group_all();
		$this->assign("opt_list",$opt_list);
		$this->view("fields_set");
	}

	public function set1_f()
	{
		$area = array("module");
		if(!$this->popedom["add"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		# 取得选项列表
		$opt_list = $this->model('opt')->group_all();
		$this->assign("opt_list",$opt_list);
		//取得复选框
		$arealist = $this->lib('xml')->read($this->dir_root.'data/xml/fields_area.xml');
		$this->assign("arealist",$arealist);
		$this->assign("area",$area);
		$this->view("fields_set_open");
	}

	/**
	 * 保存表单信息
	**/
	public function save_f()
	{
		$id = $this->get('id','system');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$identifier = $id;
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$identifier = $this->get('identifier');
			if(!$identifier){
				$this->error(P_Lang('字段标识不能为空'));
			}
			$identifier = strtolower($identifier);
			if(!preg_match("/^[a-z][a-z0-9\_]+$/u",$identifier)){
				$this->error(P_Lang('字段标识不符合系统要求，限字母、数字及下划线且必须是字母开头'));
			}
		}
		$title = $this->get("title");
		$note = $this->get("note");
		$field_type = $this->get("field_type");
		$form_type = $this->get("form_type");
		$form_style = $this->get("form_style");
		$format = $this->get("format");
		$content = $this->get("content");
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id){
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value){
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox"){
					$value = $val[0];
					$ext[$value] = $this->get($value,"checkbox");
				}else{
					$value = $val[0];
					$ext[$value] = $this->get($value);
				}
			}
		}
		$array = array();
		$array["title"] = $title;
		$array["identifier"] = $identifier;
		$array["field_type"] = $field_type;
		$array["note"] = $note;
		$array["form_type"] = $form_type;
		$array["form_style"] = $form_style;
		$array["format"] = $format;
		$array["content"] = $content;
		$array["ext"] = $ext;
		$this->model('fields')->save($array);
		$this->success();
	}

	public function save1_f()
	{
		if(!$this->popedom["add"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$title = $this->get("title");
		$note = $this->get("note");
		$identifier = $this->get("identifier");
		if(!$identifier)
		{
			error(P_Lang('字段标识不能为空'),"","error");
		}
		$identifier = strtolower($identifier);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier))
		{
			error(P_Lang('字段标识不符合系统要求，限字母、数字及下划线且必须是字母开头'),"","error");
		}
		$chk = $this->model('fields')->is_has_sign($identifier);
		if($chk)
		{
			error(P_Lang('字段标识已经存在'),"","error");
		}
		$field_type = $this->get("field_type");
		$form_type = $this->get("form_type");
		$form_style = $this->get("form_style");
		$format = $this->get("format");
		$content = $this->get("content");
		$taxis = $this->get("taxis","int");
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id)
		{
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value)
			{
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox")
				{
					$value = $val[0];
					$ext[$value] = $this->get($value,"checkbox");
				}
				else
				{
					$value = $val[0];
					$ext[$value] = $this->get($value);
				}
			}
		}		
		$myext = ($ext && count($ext)>0) ? serialize($ext) : "";
		$array = array();
		$array["title"] = $title;
		if(!$id)
		{
			$array["identifier"] = $identifier;
		}
		$array["field_type"] = $field_type;
		$array["note"] = $note;
		$array["form_type"] = $form_type;
		$array["form_style"] = $form_style;
		$array["format"] = $format;
		$array["content"] = $content;
		$array["taxis"] = $taxis;
		$array["ext"] = $myext;
		$area = $this->get("area","checkbox");
		$array["area"] = ($area && is_array($area)) ? implode(",",$area) : "";
		$this->model('fields')->save($array);
		$html = P_Lang('系统会在2秒后关闭窗口，').'<a href="javascript:parent.window.location.reload();void(0);">'.P_Lang('您可以点这里关闭窗口').'</a>';
		$html.= '<script type="text/javascript">'."\n";
		$html.= 'window.setTimeout(\'parent.window.location.reload()\',2000)'."\n";
		$html.= "\n".'</script>';
		error_open(P_Lang('字段创建成功'),"ok",$html);
	}

	private function chk_identifier($identifier)
	{
		$error_url = $this->url("fields","set");
		if(!$identifier){
			$this->error(P_Lang('字段标识不能为空'),$error_url);
		}
		$identifier = strtolower($identifier);
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier)){
			$this->error(P_Lang('字段标识不符合系统要求，限字母、数字及下划线且必须是字母开头'),$error_url);
		}
		$chk = $this->model('fields')->is_has_sign($identifier);
		if($chk){
			error(P_Lang('字段标识已经存在'),$error_url);
		}
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定字段ID'));
		}
		$this->model('fields')->delete($id);
		$this->success();
	}

	public function cateset_f()
	{
		$ids = $this->get('ids');
		if(!$ids){
			$this->json(P_Lang('未指定要操作的字段ID'));
		}
		$idlist = explode(",",$ids);
		foreach($idlist as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($idlist[$key]);
			}
		}
		$ids = implode(",",$idlist);
		if(!$ids){
			$this->json(P_Lang('没有要操作的字段ID'));
		}
		$action = $this->get('pl_act');
		if(!$action){
			$action = 'add';
		}
		$cateid = $this->get('cateid');
		if(!$cateid){
			$this->json(P_Lang('未指定分类'));
		}
		$rslist = $this->model('fields')->get_all("id IN(".$ids.")");
		if(!$rslist){
			$this->json(P_Lang('数据不存在'));
		}
		foreach($rslist as $key=>$value){
			if(!$value['area']){
				if($action == 'add'){
					$this->model('fields')->save(array('area'=>$cateid),$value['id']);
					continue;
				}
				continue;
			}
			if($value['area'] && $value['area'] == $cateid && $action != 'add'){
				$this->model('fields')->save(array('area'=>''),$value['id']);
				continue;
			}
			$tmp = explode(",",$value['area']);
			$tmp[] = $cateid;
			$tmp = array_unique($tmp);
			if($action != 'add'){
				$tmpid = array_search($cateid,$tmp);
				unset($tmp[$tmpid]);
			}
			$area = implode(",",$tmp);
			$this->model('fields')->save(array('area'=>$area),$value['id']);
		}
		$this->json(true);
	}

	public function config_f()
	{
		$id = $this->get("id");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$eid = $this->get("eid");
		if($eid){
			$rs = $this->model('fields')->get_one($eid);
			if($rs && $rs['ext'] && is_array($rs['ext'])){
				foreach($rs['ext'] as $key=>$value){
					$rs[$key] = $value;
				}
			}
			$this->assign("rs",$rs);
		}
		$this->lib('form')->config($id);
	}
}