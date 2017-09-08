<?php
/**
 * 下拉菜单管理器，支持无限级别下拉菜单
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年01月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("opt");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 读取全部组信息
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('opt')->group_all();
		if($rslist){
			foreach($rslist as $key=>$value){
				$tmp = $this->model('opt')->opt_count("group_id='".$value['id']."' AND parent_id=0");
				$value['_export'] = false;
				if($tmp){
					$value['_export'] = true;
				}
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("opt_group");
	}

	public function group_set_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if($id){
			$this->assign('id',$id);
			$rs = $this->model('opt')->group_one($id);
			$this->assign('rs',$rs);
		}
		$this->view('opt_group_set');
	}

	/**
	 * 存储选项组
	**/
	public function group_save_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('组名称不能为空'));
		}
		$link_symbol = $this->get('link_symbol');
		$data = array('title'=>$title,'link_symbol'=>$link_symbol);
		$id = $this->get("id","int");
		$this->model('opt')->group_save($data,$id);
		$this->success();
	}

	/**
	 * 删除选项组，同时删除选项组下的数据
	**/
	public function group_del_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定选项组'));
		}
		$this->model('opt')->group_del($id);
		exit("ok");
	}

	/**
	 * 取得指定选项组下的内容，支持分页
	**/
	public function list_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$pid = $this->get("pid","int");
		$group_id = $this->get("group_id","int");
		if(!$group_id && !$pid){
			$this->error(P_Lang('未指定选项组'),$this->url("opt"));
		}
		if($pid){
			$p_rs = $this->model('opt')->opt_one($pid);
			if(!$p_rs){
				$this->error(P_Lang('操作异常，请检查'),$this->url("opt"));
			}
			$group_id = $p_rs["group_id"];
			$list[0] = $p_rs;
			if($p_rs["parent_id"]){
				$this->model('opt')->opt_parent($list,$p_rs["parent_id"]);
			}
			krsort($list);
			$this->assign("lead_list",$list);
			$this->assign("p_rs",$p_rs);
			$this->assign("pid",$pid);
		}
		$this->assign("group_id",$group_id);
		$rs = $this->model('opt')->group_one($group_id);
		$psize = 50;
		$pageid = $this->get($this->config["pageid"],"int");
		$offset = $pageid ? ($pageid-1) * $psize : 0;
		$pageurl = $this->url("opt","list");
		$keywords = $this->get("keywords");
		$condition = "group_id='".$group_id."'";
		if($pid){
			$condition .= " AND parent_id='".$pid."' ";
			$pageurl .= "&pid=".$pid;
		}else{
			$pageurl .= "&group_id=".$group_id;
			$condition .= " AND parent_id='0' ";
		}
		if($keywords){
			$condition .= " AND (title LIKE '%".$keywords."%' OR val LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$rslist = $this->model('opt')->opt_list($condition,$offset,$psize);
		$_export = false;
		if($rslist){
			foreach($rslist as $key=>$value){
				$tmp = $this->model('opt')->opt_count("group_id='".$group_id."' AND parent_id='".$value['id']."'");
				$value['_export'] = false;
				if($tmp){
					$value['_export'] = true;
				}
				$rslist[$key] = $value;
			}
			$_export = true;
		}
		$this->assign("_export",$_export);
		$total = $this->model('opt')->opt_count($condition);
		if($total > $psize){
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("total",$total);
		$this->assign("psize",$psize);
		$this->assign("pageid",$pageid);
		$this->assign("pageurl",$pageurl);
		$this->assign("rslist",$rslist);
		$this->assign("rs",$rs);
		$this->view("opt_list");
	}

	/**
	 * 添加选项内容
	**/
	public function add_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$group_id = $this->get("group_id","int");
		if(!$group_id){
			exit(P_Lang('未指定选项组'));
		}
		$title = $this->get("title");
		$val = $this->get("val");
		$pid = $this->get("pid","int");
		$taxis = $this->get("taxis","int");
		if(!$title || $val == ""){
			exit(P_Lang('显示或值不能为空'));
		}
		$chk_exists = $this->model('opt')->chk_val($group_id,$val,$pid);
		if($chk_exists){
			exit(P_Lang('值已存在不允许重复创建'));
		}
		$array = array("group_id"=>$group_id,"parent_id"=>$pid,"title"=>$title,"val"=>$val,"taxis"=>$taxis);
		$this->model('opt')->opt_save($array);
		exit("ok");
	}

	/**
	 * 更新值选项信息
	**/
	public function edit_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定要编辑的ID'));
		}
		$rs = $this->model('opt')->opt_one($id);
		if(!$rs){
			exit(P_Lang('没有此项内容'));
		}
		$pid = $rs["parent_id"];
		$title = $this->get("title");
		$val = $this->get("val");
		$taxis = $this->get("taxis","int");
		if(!$title || $val == ""){
			exit(P_Lang('显示或值不能为空'));
		}
		$chk_exists = $this->model('opt')->chk_val($rs["group_id"],$val,$pid,$id);
		if($chk_exists){
			exit(P_Lang('值已存在不允许重复'));
		}
		$array = array("title"=>$title,"val"=>$val,"taxis"=>$taxis);
		$this->model('opt')->opt_save($array,$id);
		exit("ok");
	}

	/**
	 * 删除选项内容
	**/
	public function del_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定要编辑的ID'));
		}
		$rs = $this->model('opt')->opt_one($id);
		if(!$rs){
			exit(P_Lang('没有此项内容'));
		}
		$list[0] = $rs;
		$this->model('opt')->opt_son($list,$id);
		foreach($list AS $key=>$value){
			$this->model('opt')->opt_del($value["id"]);
		}
		exit("ok");
	}

	/**
	 * 导入数据上传界面
	**/
	public function import_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if($id){
			$this->assign('id',$id);
			$rs = $this->model('opt')->group_one($id);
			if(!$rs){
				$this->error(P_Lang('组信息不存在'));
			}
			$this->assign('rs',$rs);
		}
		$pid = $this->get('pid','int');
		if($pid){
			$this->assign('pid',$pid);
			$info = $this->model('opt')->opt_one($pid);
			if($info){
				$this->assign('info',$info);
			}
		}
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$this->view('opt_import');
	}

	/**
	 * 导入数据操作
	**/
	public function import_data_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		$pid = $this->get('pid','int');
		$zipfile = $this->get('zipfile');
		if(!$zipfile){
			$this->error(P_Lang('没有ZIP文件'));
		}
		if(!file_exists($this->dir_root.$zipfile)){
			$this->error(P_Lang('ZIP文件不存在'));
		}
		$tmpdir = $this->dir_cache.$this->session->val('admin_id').'_'.$this->time;
		$this->lib('file')->make($tmpdir);
		$this->lib('phpzip')->unzip($this->dir_root.$zipfile,$tmpdir);
		$flist = $this->lib('file')->ls($tmpdir);
		if(!$flist){
			$this->error(P_Lang('没有文件'));
		}
		$file = current($flist);
		if(strtolower(substr($file,-4)) != '.xml'){
			$this->error(P_Lang('压缩包有异常，不是XML文件'));
		}
		$data = $this->lib('xml')->read($file);
		$this->lib('file')->rm($tmpdir,'folder');
		$this->lib('file')->rm($this->dir_root.$zipfile);
		if(!$id && $data['title']){
			$id = $this->model('opt')->group_save($data['title']);
		}
		if(!$id){
			$this->error('导入失败');
		}
		$data = $data['data'];
		$this->_import($data,$id,$pid);
		$this->success();
	}

	private function _import($data,$id,$pid=0)
	{
		if($data['info']['val']){
			$tmplist = array();
			$tmp = false;
			foreach($data['info'] as $key=>$value){
				if(is_numeric($key)){
					$tmplist[$key] = $value;
				}else{
					$tmp[$key] = $value;
				}
			}
			$data['info'] = array();
			$data['info'][] = $tmp;
			foreach($tmplist as $key=>$value){
				$data['info'][] = $value;
			}
		}
		foreach($data['info'] as $key=>$value){
			$tmp = array('val'=>$value['val']);
			$tmp['title'] = $value['title'] ? $value['title'] : $value['val'];
			$tmp['taxis'] = $value['taxis'] ? $value['taxis'] : ($key+1)*5;
			$tmp['parent_id'] = $pid;
			$tmp['group_id'] = $id;
			$insert_id = $this->model('opt')->opt_save($tmp);
			if($insert_id && $value['sublist'] && $value['sublist']['info']){
				$this->_import($value['sublist'],$id,$insert_id);
			}
		}
	}

	/**
	 * 导出数据
	**/
	public function export_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('项目组ID未指定'),$this->url('opt'));
		}
		$pid = $this->get('pid','int');
		$sub = $this->get('sub','int');
		$rs = $this->model('opt')->group_one($id);
		if(!$rs){
			$this->error(P_Lang('组信息不存在'),$this->url('opt'));
		}
		$rslist = $this->model('opt')->opt_all("group_id=".$id." AND parent_id=".$pid);
		if(!$rslist){
			$this->error(P_Lang('没有选项内容数据'),$this->url('opt'));
		}
		$tmpfile = 'opt_'.$this->session->val('admin_id').'_'.$id.'.xml';
		$data  = '<root>'."\n";
		if(!$pid && $sub){
			$data .= "\t".'<title><![CDATA['.$rs['title'].']]></title>'."\n";
		}
		$data .= "\t".'<data>'."\n";
		$this->_export($data,$rslist,$id,"\t\t",($sub ? true : false));
		$data .= "\t".'</data>'."\n";
		$data .= '</root>';
		$this->lib('file')->vim($data,$this->dir_cache.$tmpfile);
		$zipfile = $this->dir_cache.md5($tmpfile).'.zip';
		$this->lib('phpzip')->set_root($this->dir_cache);
		$this->lib('phpzip')->zip($this->dir_cache.$tmpfile,$zipfile);
		$title = $rs['title'];
		if($pid){
			$info = $this->model('opt')->opt_one($pid);
			if($info){
				$title .= '-'.($info['title'] ? $info['title'] : $info['val']);
			}
		}
		$this->lib('file')->rm($this->dir_cache.$tmpfile);
		$this->lib('file')->download($zipfile,$title);
	}

	private function _export(&$data,$rslist,$gid,$space='',$readsublist=true)
	{
		foreach($rslist as $key=>$value){
			$title = $value['title'] ? $value['title'] : ($value['val'] ? $value['val'] : '0');
			$val = $value['val'] ? $value['val'] : '0';
			$taxis = $value['taxis'] ? $value['taxis'] : 255;
			$data .= $space.'<info>'."\n";
			$data .= $space."\t".'<title><![CDATA['.$value['title'].']]></title>'."\n";
			$data .= $space."\t".'<val><![CDATA['.$value['val'].']]></val>'."\n";
			$data .= $space."\t".'<taxis><![CDATA['.$value['taxis'].']]></taxis>'."\n";
			if($readsublist){
				$tmplist = $this->model('opt')->opt_all("group_id='".$gid."' AND parent_id='".$value['id']."'");
				if($tmplist){
					$data .= $space."\t".'<sublist>'."\n";
					$this->_export($data,$tmplist,$gid,$space."\t\t");
					$data .= $space."\t".'</sublist>'."\n";
				}
			}
			$data .= $space.'</info>'."\n";
		}
	}
}