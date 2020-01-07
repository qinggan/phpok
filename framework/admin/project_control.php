<?php
/**
 * 项目管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月19日
**/


if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class project_control extends phpok_control
{
	/**
	 * 权限
	**/
	private $popedom;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("project");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 项目列表，展示全部项目，包括启用，未启用，隐藏的，普通管理员要求有查看权限（project:list）
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('project')->get_all_project($this->session->val('admin_site_id'));
		$this->assign("rslist",$rslist);
		$this->view("project_index");
	}

	/**
	 * 添加或编辑项目基础配置信息，普通管理员要有配置权限（project:set）
	 * @参数 id 项目ID，数值，ID为空表示添加项目，不为0表示编辑这个ID下的项目
	**/
	public function set_f()
	{
		if(!$this->popedom["set"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id","int");
		$idstring = "";
		if($id){
			$this->assign("id",$id);
			$rs = $this->model('project')->get_one($id);
			if(!$rs['ico']){
				$rs['ico'] = 'images/ico/default.png';
			}
			$this->assign("rs",$rs);
			$ext_module = "project-".$id;
		}else{
			$rs = array();
			$ext_module = "add-project";
			$parent_id = $this->get("pid");
			$rs["parent_id"] = $parent_id;
			$rs['taxis'] = $this->model('project')->project_next_sort($parent_id);
			$rs['ico'] = 'images/ico/default.png';
			$this->assign("rs",$rs);
		}
		$parent_list = $this->model('project')->get_all($this->session->val('admiin_site_id'),0);
		$this->assign("parent_list",$parent_list);
		$this->assign("ext_module",$ext_module);
		$forbid = array("id","identifier");
		$forbid_list = $this->model('ext')->fields("project");
		$forbid = array_merge($forbid,$forbid_list);
		$forbid = array_unique($forbid);
		$this->assign("ext_idstring",implode(",",$forbid));
		$module_list = $this->model('module')->get_all();
		$this->assign("module_list",$module_list);
		$catelist = $this->model('cate')->root_catelist($this->session->val('admin_site_id'));
		$this->assign("catelist",$catelist);
		$currency_list = $this->model('currency')->get_list();
		$this->assign('currency_list',$currency_list);
		$emailtpl = $this->model('email')->simple_list($this->session->val('admin_site_id'));
		if($emailtpl){
			foreach($emailtpl as $key=>$value){
				if(substr($value['identifier'],0,4) == 'sms_'){
					unset($emailtpl[$key]);
				}
			}
			$this->assign("emailtpl",$emailtpl);
		}
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$gid = $c_rs["id"];
		unset($c_rs);
		$popedom_list = $this->model('popedom')->get_all("pid=0 AND gid='".$gid."'",false,false);
		$this->assign("popedom_list",$popedom_list);
		if($id){
			$popedom_list2 = $this->model('popedom')->get_all("pid='".$id."' AND gid='".$gid."'",false,false);
			if($popedom_list2){
				$m_plist = array();
				foreach($popedom_list2 AS $key=>$value){
					$m_plist[] = $value["identifier"];
				}
				$this->assign("popedom_list2",$m_plist);
			}
		}
		$note_content = form_edit('admin_note',$rs['admin_note'],"editor","btn[image]=1&height=180");
		$this->assign('note_content',$note_content);
		
		$grouplist = $this->model('usergroup')->get_all("status=1");
		if($grouplist){
			foreach($grouplist as $key=>$value){
				$tmp_popedom = array('read'=>false,'post'=>false,'reply'=>false,'post1'=>false,'reply1'=>false);
				$tmp = $value['popedom'] ? unserialize($value['popedom']) : false;
				if($tmp && $tmp[$this->session->val('admin_site_id')]){
					$tmp = $tmp[$this->session->val('admin_site_id')];
					$tmp = explode(",",$tmp);
					foreach($tmp_popedom as $k=>$v){
						if($id && in_array($k.':'.$id,$tmp)){
							$tmp_popedom[$k] = true;
						}else{
							if(!$id && $k == 'read'){
								$tmp_popedom[$k] = true;
							}
						}
					}
				}
				$value['popedom'] = $tmp_popedom;
				$grouplist[$key] = $value;
			}
		}
		$this->assign('grouplist',$grouplist);
		$freight = $this->model('freight')->get_all();
		$this->assign('freight',$freight);

		$tag_config = $this->model('tag')->config();
		$this->assign('tag_config',$tag_config);
		$this->view("project_set");
	}

	/**
	 * 项目属性扩展，即扩展项目自身字段配置，要操作此项要求普通管理员有配置权限（project:set）
	 * @参数 id，项目ID，不能为空或0
	**/
	public function content_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url("project"));
		}
		$this->assign("id",$id);
		$rs = $this->model('project')->get_one($id);
		$this->assign("rs",$rs);
		$ext_module = "project-".$id;
		$this->assign("ext_module",$ext_module);
		$extlist = $this->model('ext')->ext_all($ext_module);
		if($extlist){
			$tmp = false;
			foreach($extlist AS $key=>$value){
				if($value["ext"]){
					$ext = unserialize($value["ext"]);
					foreach($ext AS $k=>$v){
						$value[$k] = $v;
					}
				}
				$tmp[] = $this->lib('form')->format($value);
				$this->lib('form')->cssjs($value);
			}
			$this->assign('extlist',$tmp);
		}
		$this->view("project_content");
	}

	/**
	 * 取得模块的扩展字段
	 * @参数 id 模块ID
	 * @返回 Json数据
	 * @更新时间 2016年07月21日
	**/
	public function mfields_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rslist = $this->model('module')->fields_all($id);
		if(!$rslist){
			$this->success();
		}
		$list = array();
		foreach($rslist AS $key=>$value){
			$type = "text";
			if($value["field_type"] != "longtext" && $value["field_type"] != "longblob" && $value["field_type"] != "text"){
				$type = "varchar";
			}
			$list[] = array("id"=>$value["id"],"identifier"=>$value["identifier"],"title"=>$value["title"],'type'=>$type);
		}
		$this->success($list);
	}

	/**
	 * 保存项目信息
	 * @参数 id 项目ID，为0或空时表示添加
	 * @参数 title 项目名称
	 * @参数 module 模块ID，为0表示不绑定模块
	 * @参数 cate 分类ID，为0表示不绑定分类，此项仅限module不为0时有效
	 * @参数 cate_multiple 是否支持多分类，仅限绑定分类后才有效
	 * @参数 tpl_index 自定义封面模板
	 * @参数 tpl_list 自定义列表模板
	 * @参数 tpl_content 自定义内容模板
	 * @参数 taxis 项目排序，值范围是0-255，越小越往前靠
	 * @参数 parent_id 父级项目ID，为0表示当前为父级项目
	 * @参数 nick_title 项目别名，此项主要是给管理员使用，前台无效
	 * @参数 alias_title 主题别名
	 * @参数 alias_note 主题备注
	 * @参数 psize 每个项目显示多少主题，此项影响前台布局，仅在module不为0时有效
	 * @参数 ico 项目图标，仅限后台使用
	 * @参数 orderby 项目主题排序，仅在module不为0时有效
	 * @参数 lock 是否锁定，对应数据表的 status，选中表示锁定，未选中表示开放
	 * @参数 hidden 是否隐藏，对应数据表的hidden，选中表示隐藏，未选中表示显示
	 * @参数 seo_title 项目SEO标题，此项为空将会调用全局的SEO标题
	 * @参数 seo_keywords 项目SEO关键字，此项为空将会调用全局的SEO关键字
	 * @参数 seo_desc 项目SEO描述，此项为空将会调用全局的SEO描述
	 * @参数 subtopics 是否启用子主题，即该主题存在简单的父子关系，主要常用于导航
	 * @参数 is_search 是否支持搜索，禁用后前台将无法搜索该项目下的主题信息，仅限module不为0时有效
	 * @参数 is_tag 是否启用自定义标签，启用于允许用户针对主题设置标签
	 * @参数 is_biz 是否启用电商，启用后需要配置相应的货币，运费等功能
	 * @参数 currency_id 货币ID
	 * @参数 admin_note 管理员备注，仅限后台使用
	 * @参数 post_status 是否启用发布功能，启用后您需要配置相应的发布模板及发布权限
	 * @参数 comment_status 是否启用评论，启用后需要配置前台权限
	 * @参数 post_tpl 发布模板，未定义将使用 标识_post 来替代，如果找不到，将会报错
	 * @参数 etpl_admin 发布通知管理员的邮件模板
	 * @参数 etpl_user 发布通知会员的邮件模板
	 * @参数 etpl_comment_admin 评论通知管理员
	 * @参数 etpl_comment_user 评论通知会员
	 * @参数 is_attr 是否启用主题属性，主题属性配置在 _data/xml/attr.xml 里
	 * @参数 is_userid 主题是否绑定会员
	 * @参数 is_tpl_content 是否允许主题单独绑定模板
	 * @参数 is_seo 是否启用主题自定义SEO，未启用将使用 分类SEO > 项目SEO > 全局SEO
	 * @参数 is_identifier 是否启用自定义标识
	 * @参数 tag 项目标签，这里设置后，在添加主题如果启用标签而未配置标签，将会偿试从这里获取
	 * @参数 biz_attr 是否启用电商产品属性功能，启用后，电商商品支持自定义属性以实现价格浮动
	 * @参数 freight 运费模板，为0表示不使用运费
	 * @参数 _popedom 管理员权限
	 * @参数 read,post,reply,post1,reply1 前台权限，分别表示：查看，发布，评论，发布免审核，评论免审核
	**/
	public function save_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		$module = $this->get("module","int");
		$cate = $this->get("cate","int");
		if($cate){
			$cate_multiple = $this->get('cate_multiple','int');
		}else{
			$cate_multiple = 0;
		}
		$tpl_index = $this->get("tpl_index");
		$tpl_list = $this->get("tpl_list");
		$tpl_content = $this->get("tpl_content");
		$taxis = $this->get("taxis","int");
		if(!$title){
			$this->error(P_Lang('名称不能为空'));
		}
		$check_rs = $this->check_identifier($identifier,$id,$this->session->val('admin_site_id'));
		if($check_rs != "ok"){
			$this->error($check_rs);
		}
		$array = array();
		if(!$id){
			$array["site_id"] = $this->session->val('admin_site_id');
		}
		if($module){
			$m_rs = $this->model('module')->get_one($module);
			if($m_rs['mtype']){
				$array["orderby"] = $this->get("orderby2");
				$array["psize"] = $this->get("psize2","int");
				$array['psize_api'] = $this->get('psize2_api','int');
			}else{
				$array["orderby"] = $this->get("orderby");
				$array["psize"] = $this->get("psize","int");
				$array['psize_api'] = $this->get('psize_api','int');
				$array["alias_title"] = $this->get("alias_title");
				$array["alias_note"] = $this->get("alias_note");
			}
		}
		$array["parent_id"] = $this->get("parent_id","int");
		$array["module"] = $module;
		$array["cate"] = $cate;
		$array['cate_multiple'] = $cate_multiple;
		$array["title"] = $title;
		$array["nick_title"] = $this->get("nick_title");
		$array["taxis"] = $taxis;
		$array["tpl_index"] = $tpl_index;
		$array["tpl_list"] = $tpl_list;
		$array["tpl_content"] = $tpl_content;
		$array["ico"] = $this->get("ico");
		$array["status"] = $this->get("lock","checkbox") ? 0 : 1;
		$array["hidden"] = $this->get("hidden","checkbox");
		$array["identifier"] = $identifier;
		$array["seo_title"] = $this->get("seo_title");
		$array["seo_keywords"] = $this->get("seo_keywords");
		$array["seo_desc"] = $this->get("seo_desc");
		$array["subtopics"] = $this->get("subtopics",'checkbox');
		$array["is_search"] = $this->get("is_search",'checkbox');
		$array["is_tag"] = $this->get("is_tag",'int');
		$array["is_biz"] = $this->get("is_biz",'checkbox');
		$array["currency_id"] = $this->get("currency_id",'int');
		$array["admin_note"] = $this->get("admin_note","html");
		$array['post_status'] = $this->get('post_status','checkbox');
		$array['comment_status'] = $this->get('comment_status','checkbox');
		$array['is_front'] = $this->get('is_front','checkbox');
		$array['is_api'] = $this->get('is_api','checkbox');
		$array['post_tpl'] = $this->get('post_tpl');
		$array['etpl_admin'] = $this->get('etpl_admin');
		$array['etpl_user'] = $this->get('etpl_user');
		$array['etpl_comment_admin'] = $this->get('etpl_comment_admin');
		$array['etpl_comment_user'] = $this->get('etpl_comment_user');
		$array['is_attr'] = $this->get('is_attr','checkbox');
		$array['is_userid'] = $this->get('is_userid','int');
		$array['is_tpl_content'] = $this->get('is_tpl_content','int');
		$array['is_seo'] = $this->get('is_seo','int');
		$array['is_identifier'] = $this->get('is_identifier','int');
		$array['tag'] = $this->get('tag');
		$array['biz_attr'] = $this->get('biz_attr');
		$array['freight'] = $this->get('freight');
		$array['list_fields'] = $this->get('list_fields');
		$array['style'] = $this->get('style');
		$array['limit_similar'] = $this->get('limit_similar','int');
		$array['limit_times'] = $this->get('limit_times','int');
		$ok_url = $this->url("project");
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$gid = $c_rs["id"];
		unset($c_rs);
		if($id){
			$action = $this->model('project')->save($array,$id);
			if(!$action){
				$this->error(P_Lang('编辑失败'));
			}
			$rs = $this->model('project')->get_one($id);
			$popedom = $this->get("_popedom","int");
			if($popedom && is_array($popedom)){
				$str = implode(",",$popedom);
				$tlist = array();
				$newlist = $this->model('popedom')->get_all("id IN(".$str.")",false,false);
				if($newlist){
					foreach($newlist AS $key=>$value){
						$tmp_condition = "pid='".$id."' AND gid='".$gid."' AND identifier='".$value["identifier"]."'";
						$tmp = $this->model('popedom')->get_one_condition($tmp_condition);
						if(!$tmp){
							$tmp_value = $value;
							unset($tmp_value["id"]);
							$tmp_value["pid"] = $id;
							$this->model('popedom')->save($tmp_value);
						}
						$tlist[] = $value["identifier"];
					}
					$alist = $this->model('popedom')->get_all("gid='".$gid."' AND pid='".$id."'",false,false);
					if($alist){
						foreach($alist AS $key=>$value){
							if(!in_array($value["identifier"],$tlist)){
								$this->model('popedom')->delete($value["id"]);
							}
						}
					}
				}
			}
		}else{
			$id = $this->model('project')->save($array);
			if(!$id){
				$this->error(P_Lang('添加失败'));
			}
			$popedom = $this->get("_popedom","int");
			if($popedom && is_array($popedom)){
				$str = implode(",",$popedom);
				$newlist = $this->model('popedom')->get_all("id IN(".$str.")",false,false);
				if($newlist){
					foreach($newlist AS $key=>$value){
						$tmp_condition = "pid='".$id."' AND gid='".$gid."' AND identifier='".$value["identifier"]."'";
						$tmp = $this->model('popedom')->get_one_condition($tmp_condition);
						if(!$tmp){
							$tmp_value = $value;
							unset($tmp_value["id"]);
							$tmp_value["pid"] = $id;
							$this->model('popedom')->save($tmp_value);
						}
					}
				}
			}
		}
		$this->_save_user_group($id);
		$this->_save_tag($id);
		$this->success();
	}

	/**
	 * 更新项目Tag标签
	 * @参数 $id，项目ID
	 * @返回 true
	**/
	private function _save_tag($id)
	{
		$rs = $this->model('project')->get_one($id,false);
		$this->model('tag')->update_tag($rs['tag'],'p'.$id);
		return true;
	}

	/**
	 * 更新前台会员及游客权限，更新每个项目对应的前台会员或游客的权限
	 * @参数 $id，项目ID
	 * @返回 true或false
	**/
	private function _save_user_group($id)
	{
		$grouplist = $this->model('usergroup')->get_all("status=1");
		if(!$grouplist){
			return false;
		}
		$tmp_popedom = array('read','post','reply','post1','reply1');
		foreach($grouplist as $key=>$value){
			$tmp = false;
			$plist = $value['popedom'] ? unserialize($value['popedom']) : false;
			if($plist && $plist[$this->session->val('admin_site_id')]){
				$tmp = $plist[$this->session->val('admin_site_id')];
				$tmp = explode(",",$tmp);
			}
			foreach($tmp_popedom as $k=>$v){
				$checked = $this->get("p_".$v."_".$value['id'],'checkbox');
				if($checked){
					$tmp[] = $v.":".$id;
				}else{
					foreach((array)$tmp as $kk=>$vv){
						if($vv == $v.":".$id){
							unset($tmp[$kk]);
						}
					}
				}
			}
			if($tmp){
				$tmp = array_unique($tmp);
				$tmp = implode(",",$tmp);
				$plist[$this->session->val('admin_site_id')] = $tmp;
			}else{
				$plist[$this->session->val('admin_site_id')] = array();
			}
			$this->model('usergroup')->save(array('popedom'=>serialize($plist)),$value['id']);
		}
		return true;
	}

	/**
	 * 项目扩展字段保存
	 * @参数 id，项目ID，此项不能为空
	 * @参数 title，项目名称
	**/
	public function content_save_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('名称不能为空'));
		}
		$array = array("title"=>$title);
		$this->model('project')->save($array,$id);
		ext_save("project-".$id);
		$this->success();
	}

	/**
	 * 检测标识串是否被使用了
	 * @参数 $sign 检测的标识
	 * @参数 $id 忽略的项目ID，用于编辑时跳过自身
	 * @参数 $site_id 站点ID
	 * @返回 ok是表示检测通过，其他字符表示检测不通过
	**/
	private function check_identifier($sign,$id=0,$site_id=0)
	{
		if(!$sign){
			return P_Lang('标识串不能为空');
		}
		$sign = strtolower($sign);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-\.]+/",$sign)){
			return P_Lang("标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头");
		}
		if(!$site_id){
			$site_id = $this->session->val('admin_site_id');
		}
		$rs = $this->model('id')->check_id($sign,$site_id,$id);
		if($rs){
			return P_Lang('标识符已被使用');
		}
		return 'ok';
	}

	/**
	 * 删除项目操作，要求普通管理员有配置权限（project:set）
	 * @参数 id 项目ID
	 * @返回 json字串
	**/
	public function delete_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		//判断是否有子项目
		$list = $this->model('project')->get_son($id);
		if($list){
			$this->json(P_Lang('已存在子项目，请移除子项目'));
		}
		$rs = $this->model('project')->get_one($id,false);
		if(!$rs){
			$this->json(P_Lang('项目信息不存在'));
		}
		$this->model('project')->delete_project($id);
		$this->model('tag')->stat_delete('p'.$id,"title_id");
		$this->json(true);
	}

	/**
	 * 更新项目状态，要求普通管理员有配置权限（project:set）
	 * @参数 id 项目ID
	 * @返回 json字串
	**/
	public function status_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		$status = $this->get("status","int");
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$this->model('project')->status($value,$status);
		}
		$this->success();
	}

	/**
	 * 项目排序
	 * @参数 id 项目ID
	 * @返回 json字串
	**/
	public function sort_f()
	{
		$sort = $this->get('sort');
		if(!$sort || !is_array($sort)){
			$this->json(P_Lang('更新排序失败'));
		}
		foreach($sort AS $key=>$value){
			$key = intval($key);
			$value = intval($value);
			$this->model('project')->update_taxis($key,$value);
		}
		$this->json(true);
	}

	/**
	 * 取得全部分类下的根分类
	**/
	public function rootcate_f()
	{
		$catelist = $this->model('cate')->root_catelist($this->session->val('admin_site_id'));
		$this->json($catelist,true);
	}

	/**
	 * 项目复制操作
	 * @参数 id 要复制的项目ID
	**/
	public function copy_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定项目ID'));
		}
		$rs = $this->model('project')->get_one($id,false);
		if(!$rs){
			$this->json(P_Lang('项目不存在'));
		}
		//自定义标识串
		$identifier = $rs['identifier'].$_SESSION['admin_id'].$this->time;
		$array = array();
		$array['site_id'] = $_SESSION["admin_site_id"];
		$array["parent_id"] = $rs['parent_id'];
		$array["module"] = $rs['module'];
		$array["cate"] = $cate;
		$array["title"] = $rs['title'];
		$array["nick_title"] = $rs['nick_title'];
		$array["alias_title"] = $rs['alias_title'];
		$array["alias_note"] = $rs['alias_note'];
		$array["psize"] = $rs['psize'];
		$array["taxis"] = $rs['taxis'];
		if($rs['module']){
			$array["tpl_index"] = $rs['tpl_index'];
			$array["tpl_list"] = $rs['tpl_list'] ? $rs['tpl_list'] : $rs['identifier'].'_list';
			$array["tpl_content"] = $rs['tpl_content'] ? $rs['tpl_content'] : $rs['identifier'].'_content';
		}else{
			$array["tpl_index"] = $rs['tpl_index'];
			$array["tpl_list"] = $rs['tpl_list'];
			$array["tpl_content"] = $rs['tpl_content'];
			if(!$array['tpl_list'] && !$array['tpl_content'] && !$array['tpl_index']){
				$array['tpl_index'] = $rs['identifier'].'_page';
			}
		}
		$array["ico"] = $rs['ico'];
		$array["orderby"] = $rs['orderby'];
		$array["status"] = $rs['status'];
		$array["hidden"] = $rs['hidden'];
		$array["identifier"] = $identifier;
		$array["subtopics"] = $rs['subtopics'];
		$array["is_search"] = $rs['is_search'];
		$array["is_tag"] = $rs['is_tag'];
		$array["is_biz"] = $rs['is_biz'];
		$array["currency_id"] = $rs['currency_id'];
		$array['post_status'] = $rs['post_status'];
		$array['comment_status'] = $rs['comment_status'];
		$array['post_tpl'] = $rs['post_tpl'];
		$array['etpl_admin'] = $rs['etpl_admin'];
		$array['etpl_user'] = $rs['etpl_user'];
		$array['etpl_comment_admin'] = $rs['etpl_comment_admin'];
		$array['etpl_comment_user'] = $rs['etpl_comment_user'];
		$array['is_attr'] = $rs['is_attr'];
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$gid = $c_rs["id"];
		$nid = $this->model('project')->save($array);
		if(!$nid){
			$this->json(P_Lang('复制项目失败'));
		}
		//配置后台权限
		$popedom_list = $this->model('popedom')->get_all("pid=0 AND gid='".$gid."'",false,false);
		if($popedom_list){
			foreach($popedom_list as $key=>$value){
				$tmp_array = array('gid'=>$gid,'pid'=>$nid,'title'=>$value['title'],'identifier'=>$value['identifier']);
				$tmp_array['taxis'] = $value['taxis'];
				$this->model('popedom')->save($tmp_array);
			}
		}
		//存储前台权限
		$grouplist = $this->model('usergroup')->get_all("status=1");
		if($grouplist){
			$tmp_popedom = array('read','post','reply','post1','reply1');
			foreach($grouplist as $key=>$value){
				$tmp = array();
				$plist = $value['popedom'] ? unserialize($value['popedom']) : false;
				if($plist && $plist[$this->session->val('admin_site_id')]){
					$tmp = $plist[$this->session->val('admin_site_id')];
					$tmp = explode(",",$tmp);
				}
				foreach($tmp_popedom as $k=>$v){
					$tmp[] = $v.":".$nid;
				}
				$tmp = array_unique($tmp);
				$tmp = implode(",",$tmp);
				$plist[$this->session->val('admin_site_id')] = $tmp;
				$this->model('usergroup')->save(array('popedom'=>serialize($plist)),$value['id']);
			}
		}
		$this->json(true);
	}

	/**
	 * 项目导出
	 * @参数 id，项目ID
	**/
	public function export_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定项目ID'),$this->url('project'),'error');
		}
		$rs = $this->model('project')->get_one($id,false);
		if(!$rs){
			$this->error(P_Lang('项目不存在'),$this->url('project'),'error');
		}
		unset($rs['id'],$rs['parent_id'],$rs['site_id']);
		foreach($rs as $key=>$value){
			if($value == ''){
				unset($rs[$key]);
			}
		}
		if($rs['module']){
			$module = $this->model('module')->get_one($rs['module']);
			unset($module['id']);
			$module_list = $this->model('module')->fields_all($rs['module'],'identifier');
			if($module_list){
				$tmplist = array();
				foreach($module_list as $key=>$value){
					unset($value['id'],$value['module_id']);
					if($value['ext']){
						$value['ext'] = unserialize($value['ext']);
					}
					$tmplist[$key] = $value;
				}
				$module['_fields'] = $tmplist;
			}
			$rs['_module'] = $module;
			unset($rs['module']);
		}
		//扩展字段
		$extlist = $this->model('ext')->ext_all('project-'.$id,false);
		if($extlist){
			$tmplist = array();
			foreach($extlist as $key=>$value){
				unset($value['id'],$value['module']);
				if($value['ext']){
					$value['ext'] = unserialize($value['ext']);
				}
				$tmplist[$value['identifier']] = $value;
			}
			$rs['_ext'] = $tmplist;
		}
		$tmpfile = $this->dir_cache.'project.xml';
		$this->lib('xml')->save($rs,$tmpfile);
		$this->lib('phpzip')->set_root($this->dir_cache);
		$zipfile = $this->dir_cache.$this->time.'.zip';
		$this->lib('phpzip')->zip($tmpfile,$zipfile);
		$this->lib('file')->rm($tmpfile);
		//下载zipfile
		$this->lib('file')->download($zipfile,$rs['title']);
	}

	/**
	 * 项目导入
	 * @变量 zipfile 指定的ZIP文件地址
	**/
	public function import_f()
	{
		$zipfile = $this->get('zipfile');
		if(!$zipfile){
			$this->lib('form')->cssjs(array('form_type'=>'upload'));
			$this->addjs('js/webuploader/admin.upload.js');
			$this->view('project_import');
		}
		if(strpos($zipfile,'..') !== false){
			$this->error(P_Lang('不支持带..上级路径'));
		}
		if(!file_exists($this->dir_root.$zipfile)){
			$this->error(P_Lang('ZIP文件不存在'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$zipfile,$this->dir_cache);
		if(!file_exists($this->dir_cache.'project.xml')){
			$this->error(P_Lang('导入项目失败，请检查解压缩是否成功'));
		}
		$rs = $info = $this->lib('xml')->read($this->dir_cache.'project.xml',true);
		if(!$rs){
			$this->error(P_Lang('XML内容解析异常'));
		}
		$tmp = $rs;
		if(isset($tmp['_module'])){
			unset($tmp['_module']);
		}
		if(isset($tmp['_ext'])){
			unset($tmp['_ext']);
		}
		$tmp['site_id'] = $this->session->val('admin_site_id');
		$tmp['identifier'] = 'i'.$this->time;
		
		$insert_id = $this->model('project')->save($tmp);
		if(!$insert_id){
			$this->error(P_Lang('项目导入失败，保存项目基本信息错误'));
		}
		
		if($rs['_ext']){
			foreach($rs['_ext'] as $key=>$value){
				if($value['ext']){
					$value['ext'] = serialize($value['ext']);
				}
				$value['ftype'] = 'project-'.$insert_id;
				$this->model('ext')->save($value);
			}
		}
		if($rs['_module']){
			$tmp2 = $rs['_module'];
			if(isset($tmp2['_fields'])){
				unset($tmp2['_fields']);
			}
			$mid = $this->model('module')->save($tmp2);
			if(!$mid){
				$this->model('project')->delete_project($insert_id);
				$this->error(P_Lang('项目导入失败：模块创建失败'));
			}
			$this->model('module')->create_tbl($mid);
			$tbl_exists = $this->model('module')->chk_tbl_exists($mid,$tmp2['mtype'],$tmp2['tbl']);
			if(!$tbl_exists){
				$this->model('module')->delete($mid);
				$this->model('project')->delete_project($insert_id);
				$this->error(P_Lang('创建模块表失败'));
			}
			if(isset($rs['_module']['_fields']) && $rs['_module']['_fields']){
				foreach($rs['_module']['_fields'] as $key=>$value){
					$value['ftype'] = $mid;
					$tmpid = $this->model('module')->fields_save($value);
					if($tmpid){
						$this->model('module')->create_fields($tmpid);
					}
				}
			}
			//更新项目和模块之间的关系
			$array = array('module'=>$mid);
			$this->model('project')->update($array,$insert_id);
		}
		$this->lib('file')->rm($this->dir_cache.'project.xml');
		$this->lib('file')->rm($this->dir_cache.$zipfile);
		$this->success();
	}

	public function hidden_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您没有权限执行状态操作'));
		}
		$id = $this->get('id');
		$hidden = $this->get('hidden','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if($hidden>1){
			$hidden = 1;
		}
		if($hidden < 0){
			$hidden = 0;
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				continue;
			}
			$this->model('project')->set_hidden(intval($value),$hidden);
		}
		$this->success();
	}

	public function icolist_f()
	{
		$icolist = $this->lib('file')->ls('images/ico/');
		if(!file_exists($this->dir_root.'res/ico/')){
			$this->lib('file')->make($this->dir_root.'res/ico/');
		}
		$tmplist = $this->lib('file')->ls('res/ico/');
		if($tmplist){
			$icolist = array_merge($icolist,$tmplist);
		}
		$this->assign('icolist',$icolist);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$this->view('project_icolist');
	}
}