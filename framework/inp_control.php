<?php
/**
 * 自定义表单数据获取接口
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2019年9月4日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class inp_control extends phpok_control
{
	var $field_list;
	var $format_list;
	public function __construct()
	{
		parent::control();
	}

	//取得表单数据
	public function index_f()
	{
		$this->config('is_ajax',true);
		if(!$this->session->val('admin_id')){
			$this->error(P_Lang('仅限后台接入'));
		}
		$type = $this->get("type");
		$content = $this->get("content");
		if($type == "title" && $content){
			$this->get_title_list($content);
		}elseif($type == "user" && $content){
			$this->get_user_list($content);
		}
		$this->success();
	}

	public function form_f()
	{
		$type = $this->get('type','system');
		if(!$type){
			$this->error('未指定表单类型');
		}
		$exec = $this->get('exec','system');
		if(!$exec){
			$this->error('未指定执行方法');
		}
		$obj = $this->lib('form')->cls($type);
		$obj->$exec();
	}

	public function xml_f()
	{
		$this->config('is_ajax',true);
		$file = $this->get('file',"system");
		if(!$file){
			$this->error(P_Lang('未指定XML文件'));
		}
		if(!file_exists($this->dir_data.'xml/'.$file.'.xml')){
			$this->error(P_Lang('XML文件不存在'));
		}
		$info = $this->lib('xml')->read($this->dir_data.'xml/'.$file.'.xml');
		$this->success($info);
	}

	private function get_title_list($content)
	{
		$content = explode(",",$content);
		$list = array();
		foreach($content as $key=>$value){
			$value = intval($value);
			if($value){
				$list[] = $value;
			}
		}
		$list = array_unique($list);
		$content = implode(",",$list);
		if(!$content){
			$this->error(P_Lang('未指定ID'));
		}
		$condition = "l.id IN(".$content.")";
		$rslist = $this->model("list")->get_all($condition,0,0);
		if(!$rslist){
			$this->error(P_Lang('没有主题信息'));
		}
		$extprice = $this->get('extprice','int');
		$field = $this->get('field');
		if($extprice && $field){
			$fid = 0;
			$tmp = $this->model('list')->call_one($extprice);
			if($tmp && $tmp['module_id']){
				$f = $this->model('module')->fields_all($tmp['module_id'],'identifier');
				if($f && $f[$field]){
					$fid = $f['id'];
				}
			}
			if($extprice && $fid){
				$extprice_list = $this->model('list')->extprice($extprice,$fid);
				if($extprice_list){
					foreach($rslist as $key=>$value){
						if($extprice_list[$value['id']]){
							$value = array_merge($value,$extprice_list[$value['id']]);
							$rslist[$key] = $value;
						}
					}
				}
			}
		}
		$this->success($rslist);
	}

	private function get_user_list($content)
	{
		$content = explode(",",$content);
		$list = array();
		foreach($content as $key=>$value){
			$value = intval($value);
			if($value){
				$list[] = $value;
			}
		}
		$list = array_unique($list);
		$content = implode(",",$list);
		if(!$content){
			$this->error(P_Lang('暂无内容'));
		}
		$condition = "u.id IN(".$content.")";
		$rslist = $this->model("user")->get_list($condition,0,999);
		if($rslist){
			$this->success($rslist);
		}
		$this->error(P_Lang('没有数据信息'));
	}

	/**
	 * 取得主题列表
	 * @参数 pageid 页码
	 * @参数 identifier 表单标识，对应输出的变量是$input
	 * @参数 multi 是否多选，1为多选，其他为单选
	 * @参数 project_id 项目ID
	**/
	public function title_f()
	{
		if(!$this->session->val('admin_id')){
			$this->error(P_Lang('仅限后台管理员有此功能'));
		}
		$psize = $this->config["psize"];
		if(!$psize){
			$psize = 30;
		}
		$pageid = $this->config["pageid"] ? $this->config["pageid"] : "pageid";
		$pageid = $this->get($pageid,"int");
		if(!$pageid || $pageid<1){
			$pageid=1;
		}
		$offset = ($pageid-1) * $psize;
		$input = $this->get("identifier");
		if(!$input){
			$this->error("未指定表单ID");
		}
		$multi = $this->get("multi","int");
		$pageurl = $this->url("inp","title","identifier=".rawurlencode($input));
		if($multi){
			$pageurl .= "&multi=1";
		}
		$project_id = $this->get("project_id");
		if(!$project_id){
			$this->error(P_Lang('未指定项目ID'));
		}
		if(!$this->session->val('admin_id') && !$this->session->val('user_id')){
			$this->error(P_Lang('游客不支持这里获取数据'));
		}
		$tmp = explode(",",$project_id);
		$lst = array();
		foreach($tmp as $key=>$value){
			$value = intval($value);
			if($value){
				$lst[] = $value;
			}
		}
		$lst = array_unique($lst);
		$project_list = array();
		foreach($lst as $key=>$value){
			$tmp = $this->model('project')->get_one($value);
			$project_list[] = $tmp;
		}
		$this->assign('project_list',$project_list);
		$project_id = implode(",",$lst);
		if(!$project_id){
			$this->error("指定项目异常");
		}
		$pageurl .="&project_id=".rawurlencode($project_id);
		$formurl = $pageurl;
		$pid = $this->get('pid','int');
		if($pid){
			$pageurl .= "&pid=".$pid;
			$this->assign('pid',$pid);
			$condition = "l.project_id='".$pid."'";
			$project = $this->model('project')->get_one($pid,false);
		}else{
			$condition = "l.project_id IN(".$project_id.")";
			if(is_numeric($project_id)){
				$project = $this->model('project')->get_one($project_id,false);
			}
		}
		if(!$this->session->val('admin_id')){
			$condition .= " AND l.user_id='".$this->session->val('user_id')."'";
		}
		$keywords = $this->get('keywords');
		if($keywords){
			$pageurl .= "&keywords=".rawurlencode($keywords);
			if($project && $project['module']){
				$module = $this->model('module')->get_one($project['module']);
				$flist = $this->model('fields')->flist($project['module'],'identifier');
				if($flist){
					$tbl = $module['mtype'] ? $this->db->prefix.$module['id'] : $this->db->prefix."list_".$module['id'];
					$tmpsql = "SELECT id FROM ".$tbl." WHERE project_id='".$project['id']."' ";
					$tmpsql_c = array();
					foreach($flist as $k=>$v){
						if($v['search'] == 1){
							$tmpsql_c[] = " ".$v['identifier']."='".$keywords."' ";
						}elseif($v['search'] == 2){
							$tmpsql_c[] = " ".$v['identifier']." LIKE '%".str_replace(' ','%',$keywords)."%' ";
						}
					}
					if($tmpsql_c && $tmpsql_c>0){
						$tmpsql .= " AND (".implode(" OR ",$tmpsql_c).") ";
						$condition .= " AND (l.id IN(".$tmpsql.") OR l.title LIKE '%".$keywords."%') ";
					}else{
						$condition .= " AND l.title LIKE '%".$keywords."%' ";
					}
				}else{
					$condition .= " AND l.title LIKE '%".$keywords."%' ";
				}
			}else{
				$condition .= " AND l.title LIKE '%".$keywords."%' ";
			}
			$this->assign('keywords',$keywords);
		}
		if($flist && $keywords){
			$total = $this->model('list')->arc_count($project['module'],$condition);
		}else{
			$total = $this->model('list')->get_all_total($condition);
		}
		if($total){
			if($flist && $keywords && $project){
				$field = "l.*";
				foreach($flist as $key=>$value){
					$field .= ",ext.".$value['identifier'];
				}
				$rslist = $this->model('list')->arc_all($project,$condition,$field,$offset,$psize);
				$layout = array();
				if($rslist && $flist && $keywords){
					$ks = array_keys($flist);
					foreach($rslist as $key=>$value){
						foreach($value as $k=>$v){
							if($flist[$k]['search'] && $flist[$k]['field_type'] == 'varchar' && in_array($k,$ks) && strpos($v,$keywords) !== false){
								$layout[$k] = $flist[$k]['title'];
							}
						}
					}
				}
				if($layout){
					$this->assign('layout',$layout);
				}
			}else{
				$rslist = $this->model('list')->get_all($condition,$offset,$psize);
			}
			$this->assign("total",$total);
			$this->assign("rslist",$rslist);
			$string = "home=".P_Lang('首页')."&prev=".P_Lang('上一页')."&next=".P_Lang('下一页')."&last=".P_Lang('尾页')."&half=5&add=(total)/(psize)&always=1";
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("multi",$multi);
		$this->assign("input",$input);
		$this->assign('formurl',$formurl);
		$this->view("inp_title");
	}
}