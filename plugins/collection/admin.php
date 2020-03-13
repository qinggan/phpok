<?php
/**
 * 采集器<后台应用>
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月05日
**/

class admin_collection extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}

	/**
	 * 采集器
	**/
	public function manage()
	{
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$sql = "SELECT count(id) FROM ".$this->db->prefix."collection";
		$total = $this->db->count($sql);
		if($total>0){
			$pageurl = $this->url('plugin','index','id='.$this->me['id'].'&exec=manage');
			$sql = "SELECT c.*,cate.title c_title,p.title p_title FROM ".$this->db->prefix."collection c ";
			$sql.= " LEFT JOIN ".$this->db->prefix."project p ON(c.project_id=p.id) ";
			$sql.= " LEFT JOIN ".$this->db->prefix."cate cate ON(c.cateid=cate.id) ";
			$sql.= " ORDER BY c.id DESC LIMIT ".$offset.",".$psize;
			$rslist = $this->db->get_all($sql);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
			$this->assign('pageid',$pageid);
			$this->assign('psize',$psize);
			$this->assign('pageurl',$pageurl);
			$this->assign('rslist',$rslist);
			$this->assign('total',$total);
		}
		$projectlist = $this->model('project')->get_all($_SESSION['admin_site_id'],'id','module>0');
		$this->assign('projectlist',$projectlist);
		$this->_view('manage.html');
	}

	public function collection_set()
	{
		$tid = $this->get('tid','int');
		if($tid){
			$sql = "SELECT * FROM ".$this->db->prefix."collection WHERE id='".$tid."'";
			$rs = $this->db->get_one($sql);
			$this->assign('rs',$rs);
			$this->assign('tid',$tid);
		}
		$projectlist = $this->model('project')->get_all($_SESSION['admin_site_id'],'id','module>0');
		$this->assign('plist',$projectlist);
		$this->_view('collection_set.html');
	}

	public function catelist()
	{
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->json('未指定目标ID');
		}
		$rs = $this->model('project')->get_one($pid,false);
		if(!$rs){
			$this->json('项目不存在');
		}
		if(!$rs['module']){
			$this->json('项目未绑定模块');
		}
		if(!$rs['cate']){
			$this->json(true);
		}
		$catelist = $this->model('cate')->get_all($_SESSION['admin_site_id'],1,$rs['cate']);
		$catelist = $this->model('cate')->cate_option_list($catelist);
		$this->json($catelist,true);
	}

	public function collection_setok()
	{
		$tid = $this->get('tid','int');
		$array = array();
		$array['title'] = $this->get('title');
		if(!$array['title']){
			$this->json('主题不能为空');
		}
		$array['linkurl'] = $this->get('linkurl');
		if(!$array['linkurl']){
			$this->json('采集的网站不能为空');
		}
		$array['url_charset'] = $this->get('url_charset');
		$array['project_id'] = $this->get('project_id','int');
		if(!$array['project_id']){
			$this->json('发布目标未设定');
		}
		$array['cateid'] = $this->get('cateid','int');
		$array['listurl'] = $this->get('listurl');
		if(!$array['listurl']){
			$this->json('未指定列表地址');
		}
		$array['list_tags_start'] = $this->get('list_tags_start','html_js');
		$array['list_tags_end'] = $this->get('list_tags_end','html_js');
		$array['url_tags'] = $this->get('url_tags');
		$array['url_not_tags'] = $this->get('url_not_tags');
		$array['is_gzip'] = $this->get('is_gzip','int');
		$array['is_proxy'] = $this->get('is_proxy','int');
		if($array['is_proxy']){
			$array['proxy_service'] = $this->get('proxy_service');
			$array['proxy_user'] = $this->get('proxy_user');
			$array['proxy_pass'] = $this->get('proxy_pass');
		}
		if($tid){
			$this->db->update_array($array,'collection',array('id'=>$tid));
		}else{
			$myid = $this->db->insert_array($array,'collection');
			//保存字段
			if(!$myid){
				$this->json('保存数据失败');
			}
			$this->fields_all_add($myid,$array['project_id']);
		}
		$this->json(true);
	}

	public function collection_copy()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->json('未指定ID');
		}
		$pid = $this->get('project_id','int');
		if(!$pid){
			$this->json('未指定目标项目');
		}
		$cateid = $this->get('cateid','int');
		$sql = "SELECT * FROM ".$this->db->prefix."collection WHERE id='".$tid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			$this->json('数据不存在');
		}
		unset($rs['id']);
		$old_project_id = $rs['project_id'];
		$rs['project_id'] = $pid;
		$rs['cateid'] = $cateid;
		foreach($rs as $key=>$value){
			$rs[$key] = addslashes($value);
		}
		$sql = "INSERT INTO ".$this->db->prefix."collection(title,linkurl,url_charset,project_id,cateid,listurl,list_tags_start,list_tags_end,url_tags,url_not_tags,is_gzip,is_proxy,proxy_service,proxy_user,proxy_pass) VALUES('".$rs['title']."','".$rs['linkurl']."','".$rs['url_charset']."','".$rs['project_id']."','".$rs['cateid']."','".$rs['listurl']."','".$rs['list_tags_start']."','".$rs['list_tags_end']."','".$rs['url_tags']."','".$rs['url_not_tags']."','".$rs['is_gzip']."','".$rs['is_proxy']."','".$rs['proxy_service']."','".$rs['proxy_user']."','".$rs['proxy_pass']."')";
		$insert_id = $this->db->insert($sql);
		if($insert_id){
			if($old_project_id == $rs['project_id']){
				$sql = "SELECT * FROM ".$this->db->prefix."collection_tags WHERE cid='".$tid."'";
				$tmplist = $this->db->get_all($sql);
				if($tmplist){
					foreach($tmplist as $key=>$value){
						foreach($value as $k=>$v){
							$value[$k] = addslashes($v);
						}
						unset($value['id']);
						$value['cid'] = $insert_id;
						$this->db->insert_array($value,'collection_tags');
					}
				}
			}else{
				$this->fields_all_add($insert_id,$pid);
			}
		}
		$this->json(true);
	}

	public function collection_del()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->json('未指定ID');
		}
		$sql = "DELETE FROM ".$this->db->prefix."collection WHERE id='".$tid."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."collection_tags WHERE cid='".$tid."'";
		$this->db->query($sql);
		$sql = "SELECT id FROM ".$this->db->prefix."collection_list WHERE cid='".$tid."'";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."collection_format WHERE lid='".$value['id']."'";
				$this->db->query($sql);
			}
			$sql = "DELETE FROM ".$this->db->prefix."collection_list WHERE cid='".$tid."'";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."collection_files WHERE cid='".$tid."'";
		$this->db->query($sql);
		//删除采集到的图片信息
		$this->lib('file')->rm($this->dir_root.'res/tmp'.$tid,'folder');
		$this->json(true);
	}

	public function collection_list()
	{
		$tid = $this->get('tid','int');
		$condition = "1=1";
		$pageurl = $this->url('plugin','exec','id=collection&exec=collection_list');
		if($tid){
			$condition = "l.cid='".$tid."'";
			$pageurl .= "&tid=".$tid;
			$this->assign('tid',$tid);
			$sql = "SELECT * FROM ".$this->db->prefix."collection WHERE id='".$tid."'";
			$rs = $this->db->get_one($sql);
			$this->assign('rs',$rs);
		}
		$status = $this->get('status','int');
		if($status){
			$pageurl .= "&status=".$status;
			if($status >= 3){
				$condition .= " AND l.status=0 ";
			}else{
				$condition .= " AND l.status=".$status." ";
			}
		}
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."collection_list l WHERE ".$condition;
		$total = $this->db->count($sql);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1)*$psize;
		if($total>0){
			$sql = "SELECT l.*,c.title FROM ".$this->db->prefix."collection_list l ";
			$sql.= "LEFT JOIN ".$this->db->prefix."collection c ON(l.cid=c.id) ";
			$sql.= "WHERE ".$condition." ORDER BY id DESC LIMIT ".$offset.",".$psize;
			$rslist = $this->db->get_all($sql);
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('psize',$psize);
			$this->assign('pageurl',$pageurl);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
		}
		$this->echo_tpl('collection_list.html');
	}

	public function collection_tags_list()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			error("未指定ID",$this->url('plugin','exec','id=collection&exec=manage'),'error');
		}
		$rs = $this->db->get_one("SELECT * FROM ".$this->db->prefix."collection WHERE id='".$tid."'");
		$this->assign('rs',$rs);
		$rslist = $this->db->get_all("SELECT * FROM ".$this->db->prefix."collection_tags WHERE cid='".$tid."' ORDER BY id ASC");
		$this->assign('rslist',$rslist);
		$this->echo_tpl('tags.html');
	}

	public function field_set()
	{
		$fid = $this->get('fid','int');
		if($fid){
			$sql = "SELECT * FROM ".$this->db->prefix."collection_tags WHERE id='".$fid."'";
			$rs = $this->db->get_one($sql);
			if(!$rs){
				$this->error('数据异常，信息不存在');
			}
			$tid = $rs['cid'];
			$this->assign('fid',$fid);
			$this->assign('rs',$rs);
		}else{
			$tid = $this->get('tid','int');
		}
		if(!$tid){
			$this->error('未指定TID');
		}
		$this->assign('tid',$tid);
		$collection = $this->db->get_one("SELECT * FROM ".$this->db->prefix."collection WHERE id='".$tid."'");
		$this->assign('info',$collection);
		$this->echo_tpl('field_set.html');
	}

	public function field_save()
	{
		$fid = $this->get('fid','int');
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->json('未指定Tid');
		}
		$array = array('cid'=>$tid);
		$array['title'] = $this->get('title');
		if(!$array['title']){
			$this->json('标签名不能为空');
		}
		$array['identifier'] = $this->get('identifier');
		if(!$array['identifier']){
			$this->json('变量名不能为空');
		}
		$sql = "SELECT id FROM ".$this->db->prefix."collection_tags WHERE identifier='".$array['identifier']."' AND cid='".$tid."'";
		if($fid){
			$sql .= " AND id != '".$fid."'";
		}
		$chk = $this->db->get_one($sql);
		if($chk){
			$this->json('变量名已经存在');
		}
		$array['tags_type'] = $this->get('tags_type');
		$array['rules'] = $this->get('rules');
		$array['rules_start'] = $this->get('rules_start','html_js');
		$array['rules_end'] = $this->get('rules_end','html_js');
		$array['del'] = $this->get('del','html_js');
		$array['del_url'] = $this->get('del_url','checkbox');
		$array['del_font'] = $this->get('del_font','checkbox');
		$array['del_table'] = $this->get('del_table','checkbox');
		$array['del_span'] = $this->get('del_span','checkbox');
		$array['del_bold'] = $this->get('del_bold','checkbox');
		$array['del_html'] = $this->get('del_html','checkbox');
		$array['suburl_start'] = $this->get('suburl_start','html_js');
		$array['suburl_end'] = $this->get('suburl_end','html_js');
		$array['post_save'] = $this->get('post_save');
		$array['translate'] = $this->get('translate');
		$array['re1'] = $this->get('re1');
		if($fid){
			$this->db->update_array($array,'collection_tags',array('id'=>$fid));
		}else{
			$this->db->insert_array($array,'collection_tags');
		}
		$this->json(true);
	}

	public function field_del()
	{
		$fid = $this->get('fid','int');
		if(!$fid){
			$this->json('未指定ID');
		}
		$sql = "DELETE FROM ".$this->db->prefix."collection_format WHERE fid='".$fid."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."collection_tags WHERE id='".$fid."'";
		$this->db->query($sql);
		$this->json(true);
	}

	public function collection_test()
	{
		$tid = $this->get('tid');
		if(!$tid){
			$this->error('未指定Tid');
		}
		$this->assign('tid',$tid);
		$this->echo_tpl('testing.html');
	}

	private function fields_all_add($myid=0,$project_id=0)
	{
		$flist = $this->model('fields')->tbl_fields('list');
		$notsave = array('id','site_id','parent_id','cate_id','project_id','module_id','tpl','attr','replydate','user_id','sort','identifier','style','integral');
		$alias = array('dateline'=>'发布时间','lastdate'=>'最后修改时间','status'=>'状态','hidden'=>'隐藏');
		$alias["hits"] = "查看次数";
		$alias["seo_title"] = "SEO标题";
		$alias["seo_keywords"] = "SEO关键字";
		$alias["seo_desc"] = "SEO描述";
		$alias["tag"] = "Tag标签";
		foreach($flist as $key=>$value){
			if(in_array($value,$notsave)){
				continue;
			}
			$data = array('cid'=>$myid);
			if($value == 'title'){
				$data['title'] = '主题';
				$data["identifier"] = "title";
				$data["tags_type"] = "var";
				$data["rules_start"] = "<title>";
				$data["rules_end"] = "</title>";
				$data["del_html"] = 1;
				$this->db->insert_array($data,'collection_tags');
				continue;
			}
			$data["title"] = $alias[$value] ? $alias[$value] : $value;
			$data["identifier"] = $value;
			$data["tags_type"] = "var";
			if($value == "status"){
				$data["rules"] = "1";
				$data["tags_type"] = "string";
			}elseif($value == 'hidden'){
				$data['rules'] = '0';
				$data["tags_type"] = "string";
			}
			$this->db->insert_array($data,'collection_tags');
		}
		$project = $this->model('project')->get_one($project_id,false);
		if($project['is_biz']){
			$data = array('cid'=>$myid);
			$data['title'] = '价格';
			$data["identifier"] = "price";
			$data["tags_type"] = "var";
			$data["rules_start"] = "<div>";
			$data["rules_end"] = "</div>";
			$data["del_html"] = 1;
			$this->db->insert_array($data,'collection_tags');
			$data = array('cid'=>$myid);
			$data['title'] = '价格单位';
			$data["identifier"] = "currency_id";
			$data["tags_type"] = "string";
			$data["rules"] = "1";
			$this->db->insert_array($data,'collection_tags');
			$data = array('cid'=>$myid);
			$data['title'] = '重量';
			$data["identifier"] = "weight";
			$data["tags_type"] = "string";
			$data["rules"] = "0";
			$this->db->insert_array($data,'collection_tags');
			$data = array('cid'=>$myid);
			$data['title'] = '体积';
			$data["identifier"] = "volume";
			$data["tags_type"] = "string";
			$data["rules"] = "0";
			$this->db->insert_array($data,'collection_tags');
			$data = array('cid'=>$myid);
			$data['title'] = '计量单位';
			$data["identifier"] = "unit";
			$data["tags_type"] = "string";
			$this->db->insert_array($data,'collection_tags');
		}
		if($project['module']){
			$flist = $this->model('module')->fields_all($project['module']);
			if($flist){
				foreach($flist as $key=>$value){
					$data = array('cid'=>$myid);
					$data['identifier'] = $value['identifier'];
					$data['tags_type'] = 'var';
					$data['rules_start'] = '<div>';
					$data["rules_end"] = "</div>";
					$data['title'] = $value['title'];
					$this->db->insert_array($data,'collection_tags');
				}
			}
		}
	}

	//采集网址
	public function collection_url()
	{
		$url = $this->get('listurl');
		if(!$url){
			$this->json('未指定要采集的网址');
		}
		$tid = $this->get('tid');
		if(!$tid){
			$this->json('未指定Tid');
		}
		$info = $this->cj_url($url,$tid);
		$this->json($info['content'],$info['status']);
	}

	public function url2()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->json('未指定采集规则ID');
		}
		$listurl = $this->get('listurl');
		if(!$listurl){
			$this->json('未指定要采集的网址');
		}
		$info = $this->cj_url($listurl,$tid);
		if($info['status'] && $info['content']){
			$i = 1;
			foreach($info['content'] as $key=>$value){
				$value['url'] = trim($value['url']);
				if(!$value['url']){
					continue;
				}
				$value['url'] = str_replace("&amp;","&",$value['url']);
				$sql = "SELECT id FROM ".$this->db->prefix."collection_list WHERE url='".$value['url']."' AND cid='".$tid."'";
				$tmp = $this->db->get_one($sql);
				if($tmp){
					continue;
				}
				$sql = "INSERT INTO ".$this->db->prefix."collection_list(cid,url,status,postdate) VALUES('".$tid."','".$value['url']."',0,'".$this->time."')";
				$this->db->query($sql);
				$i++;
			}
			$this->json('网址：<span class="red">'.$listurl.'</span> 采集到网址数量：<span class="red b">'.$i.'</span> 条',true);
		}
		$this->json('网址：<span class="red">'.$listurl.'</span> 采集列表数据为空');
	}

	public function content2()
	{
		$tid = $this->get('tid','int');
		$cid = $this->get('cid','int');
		if(!$cid){
			$this->json('未指定采集项目ID');
		}
		$rs = $this->db->get_one("SELECT * FROM ".$this->db->prefix."collection WHERE id='".$cid."'");
		if(!$rs){
			$this->json('项目不存在');
		}
		if($tid){
			$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE id='".$tid."'";
			$info = $this->db->get_one($sql);
			if(!$info){
				$this->json('要采集的主题信息不在，请检查');
			}
		}else{
			$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE cid='".$cid."' AND status=0 ORDER BY id ASC LIMIT 1";
			$info = $this->db->get_one($sql);
			if(!$info){
				$this->json('end',true);
			}
		}
		
		$array = $this->cj_content($info['url'],$cid);
		if(!$array['status']){
			$this->json($array['content']);
		}
		if(!$array['content']){
			$this->json('内容为空');
		}
		foreach($array['content'] as $key=>$value){
			if($value['keytype'] == 'string'){
				continue;
			}
			if(!$value['content']){
				continue;
			}
			$value['content'] = trim($value['content']);
			if(!$value['content']){
				continue;
			}
			$value['content'] = addslashes($value['content']);
			$data = array('lid'=>$info['id'],'fid'=>$value['id'],'content'=>$value['content']);
			$chk = $this->db->get_one("SELECT id FROM ".$this->db->prefix."collection_format WHERE lid='".$info['id']."' AND fid='".$value['id']."'");
			if($chk){
				$this->db->update_array(array('content'=>$value['content']),'collection_format',array('id'=>$chk['id']));
				$format_id = $chk['id'];
			}else{
				$format_id = $this->db->insert_array($data,'collection_format');
			}
			if($value['content'] && $value['id'] && $info['id'] && $rs){
				$this->file_save($info["id"],$value['id'],$value["content"],$rs);
			}
		}
		$sql = "UPDATE ".$this->db->prefix."collection_list SET status=1 WHERE id='".$info['id']."'";
		$this->db->query($sql);
		$tip = '网址：<span class="red">'.$info['url'].'</span> 数据采集完毕，请稍候，正在执行下一动作…';
		$this->json($tip,true);
	}

	private function file_save($lid='',$fid='',$content='',$rs='')
	{
		if(!$lid || !$fid || !$content || !$rs){
			return false;
		}
		$content = stripslashes($content);
		$tmp = str_replace("<img",'',$content);
		if($tmp == $content){
			return false;
		}
		$cid = $rs['id'];
		$save_path = $this->dir_root.'res/tmp'.$cid.'/';
		$this->lib('file')->make($save_path);//创建存储目录
		if(!file_exists($save_path)){
			$save_path = $this->dir_root."res/tmp/";
		}
		$this->lib('curl')->is_gzip($rs['is_gzip']);
		$this->lib('curl')->is_proxy($rs['is_proxy']);
		if($rs['is_proxy'] && $rs['proxy_service']){
			$tmp = explode(":",$rs['proxy_service']);
			if(!$tmp[1]){
				$tmp[1] = 80;
			}
			$this->lib('curl')->set_proxy($tmp[0],$tmp[1],$rs["proxy_user"],$rs["proxy_pass"]);
		}
		preg_match_all("/<img.+src=(\"|\'){0,1}(.+)(\"|\'| |>){1}/isU",$content,$matches);
		$picurl = array();
		if(!$matches[0] || !is_array($matches[0])){
			return false;
		}
		foreach($matches[0] AS $k=>$v){
			$mypic_url = str_replace('"',"",$matches[2][$k]);
			if(substr($mypic_url,-1) == "/"){
				$mypic_url = substr($mypic_url,0,-1);
			}
			$picurl[] = $mypic_url;
		}
		$picurl = array_unique($picurl);
		phpok_log($picurl);
		foreach($picurl as $key=>$value){
			$value = strtolower($value);
			if(strpos($value,'?') !== false){
				if(version_compare(PHP_VERSION,'5.3.0', '<')){
					$tmp = strstr($value,'?');
					$value = str_replace($tmp,'',$value);
				}else{
					$value = strstr($value,'?',true);
				}
			}
			$ext = substr($value,-3);
			$ext2 = substr($value,-4);
			if(!in_array($ext,array('jpg','png','gif')) && $ext2 != 'jpeg'){
				unset($picurl[$key]);
			}
		}
		foreach($picurl as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$sql = "SELECT * FROM ".$this->db->prefix."collection_files WHERE lid='".$lid."' AND fid='".$fid."' AND srcurl='".$value."'";
			$chk_rs = $this->db->get_one($sql);
			if($chk_rs && $chk_rs['newurl']){
				continue;
			}
			$imgurl = $value;
			if(substr($imgurl,0,7) != 'http://' && substr($imgurl,0,8) != 'https://'){
				$imgurl = $rs['linkurl'].$value;
			}
			$img = $this->lib('curl')->get_content($imgurl);
			if(strlen($img)<1){
				continue;
			}
			$value2 = $value;
			if(strpos($value,'?') !== false){
				if(version_compare(PHP_VERSION,'5.3.0', '<')){
					$tmp = strstr($value,'?');
					$value2 = str_replace($tmp,'',$value);
				}else{
					$value2 = strstr($value,'?',true);
				}
			}
			$ext = strtolower(substr($value2,-3)) == 'peg' ? 'jpg' : strtolower(substr($value2,-3));
			$tmp_array = array();
			$tmp_array["cid"] = $cid;
			$tmp_array["lid"] = $lid;
			$tmp_array["fid"] = $fid;
			$tmp_array["srcurl"] = $value;
			$tmp_array["ext"] = $ext;
			$filename = $this->time."_".$key."_".rand(100,999).".".$ext;
			$this->lib('file')->save_pic($img,$save_path.$filename);
			$tmp_array["newurl"] = str_replace($this->dir_root,"",$save_path.$filename);
			if($chk_rs){
				$this->db->update_array($tmp_array,'collection_files',array('id'=>$chk_rs['id']));
			}else{
				$this->db->insert_array($tmp_array,'collection_files');
			}
			usleep(500000);//沉睡500毫秒
		}
		return true;
	}


	public function collection_content()
	{
		$url = $this->get('msgurl');
		if(!$url){
			$this->json('未指定要采集的网址');
		}
		$tid = $this->get('tid');
		if(!$tid){
			$this->json('未指定Tid');
		}
		$info = $this->cj_content($url,$tid);
		$this->json($info['content'],$info['status']);
	}

	private function cj_content($msgurl,$id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return array('content'=>'内容项目不存在');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection_tags WHERE cid='".$id."' ORDER BY id ASC";
		$rslist = $this->db->get_all($sql,'identifier');
		$this->lib('curl')->is_gzip($rs['is_gzip']);
		$this->lib('curl')->is_proxy($rs['is_proxy']);
		if($rs['is_proxy'] && $rs['proxy_service']){
			$tmp = explode(":",$rs['proxy_service']);
			if(!$tmp[1]){
				$tmp[1] = 80;
			}
			$this->lib('curl')->set_proxy($tmp[0],$tmp[1],$rs["proxy_user"],$rs["proxy_pass"]);
		}
		$content = $this->lib('curl')->get_content($msgurl);
		if($rs['url_charset'] != "utf-8"){
			$content = $this->lib('string')->charset($content,$rs['url_charset'],"utf-8");
		}
		$list_array = array();
		$key_i = 0;
		foreach($rslist as $key=>$value){
			if($value["suburl_start"] && $value["suburl_end"]){
				$content_array = array();
				$content_array[] = $content;
				$url_list = $this->get_sub_list($value["suburl_start"],$value["suburl_end"],$content);
				if($url_list){
					foreach($url_list as $k=>$v){
						$tmp_content = $this->lib('curl')->get_content($v);
						if(!$tmp_content){
							continue;
						}
						if($rs['url_charset'] != "utf-8"){
							$tmp_content = $this->lib('string')->charset($tmp_content,$rs['url_charset'],"utf-8");
						}
						$content_array[] = $tmp_content;
						unset($tmp_content);
					}
				}
				
				$content_array = array_unique($content_array);
				$msg = $this->format_content($content_array,$value,$rs["linkurl"]);
			}else{
				$msg = $this->format_content($content,$value,$rs["linkurl"]);
			}
			$list_array[$key_i]["identifier"] = $value["identifier"];
			$list_array[$key_i]["keytype"] = $value["tags_type"];
			$list_array[$key_i]["title"] = $value["title"];
			$list_array[$key_i]["content"] = $msg;
			$list_array[$key_i]['id'] = $value['id'];
			$key_i++;
		}
		return array('status'=>true,'content'=>$list_array);
	}

	private function format_content($content,$rs,$siteurl="")
	{
		if($rs["tags_type"] && $rs["tags_type"] == "string"){
			return $rs["rules"];
		}
		if(!$content || !$rs){
			return false;
		}
		if($rs["del"]){
			$rs["del"] = str_replace("\r","",$rs["del"]);
		}
		$array = is_array($content) ? $content : array($content);
		$msg = array();
		foreach($array AS $key=>$value){
			$value = $this->tags_split($value,$rs["rules_start"],"start");
			$value = $this->tags_split($value,$rs["rules_end"],"end");
			if(!$value){
				continue;
			}
			
			$value = preg_replace("/<form(.*)>(.*)<\/form>/isU","\\2",$value);
			$value = preg_replace("/<input(.*)>/isU","",$value);
			$value = preg_replace("/<textarea(.*)>(.*)<\/textarea>/isU","",$value);
			$value = preg_replace("/<select(.*)>(.*)<\/select>/isU","",$value);
			$value = preg_replace("/<scrip(.*)>(.*)<\/script>/isU","",$value);
			$value = preg_replace("/<iframe(.*)>(.*)<\/iframe>/isU","",$value);
			$value = preg_replace("/<style(.*)>(.*)<\/style>/isU","",$value);
			if($rs["del_html"] && $value){
				$value = preg_replace("/<(.*)>/isU","",$value);
			}else{
				if($rs["del_url"] && $value){
					$value = preg_replace("/<a(.*)>(.*)<\/a>/isU","\\2",$value);
				}
				if($rs["del_font"] && $value){
					$value = preg_replace("/<font(.*)>(.*)<\/font>/isU","\\2",$value);
				}
				if($rs["del_table"] && $value){
					$value = preg_replace("/<table(.*)>(.*)<\/table>/isU","\\2",$value);
					$value = preg_replace("/<tr(.*)>(.*)<\/tr>/isU","\\2",$value);
					$value = preg_replace("/<td(.*)>(.*)<\/td>/isU","\\2",$value);
					$value = preg_replace("/<thead(.*)>(.*)<\/thead>/isU","\\2",$value);
					$value = preg_replace("/<tbody(.*)>(.*)<\/tbody>/isU","\\2",$value);
					$value = preg_replace("/<tfoot(.*)>(.*)<\/tfoot>/isU","\\2",$value);
					$value = preg_replace("/<th(.*)>(.*)<\/\th>/isU","\\2",$value);
				}
				if($rs["del_span"] && $value){
					$value = preg_replace("/<span(.*)>(.*)<\/span>/isU","\\2",$value);
				}
				if($rs["del_bold"] && $value){
					$value = preg_replace("/<strong(.*)>(.*)<\/strong>/isU","\\2",$value);
					$value = preg_replace("/<b(.*)>(.*)<\/b>/isU","\\2",$value);
				}
			}
			if(!$value){
				continue;
			}
			
			if($rs["del"] && trim($rs['del'])){
				$rs['del'] = trim($rs['del']);
				$rs["del"] = str_replace("[&amp;]","&",$rs["del"]);
				$rs["del"] = str_replace("[&]","&",$rs["del"]);
				$rs['del'] = str_replace("\r","",$rs['del']);
				$rs['del'] = str_replace("\t","",$rs['del']);
				$del_array = explode("\n",$rs["del"]);
				foreach($del_array As $k=>$v){
					if(strpos($v,"[:phpok:]") !== false){
						$tmp = explode("[:phpok:]",$v);
						$t1 = $tmp[0] ? $tmp[0] : ' ';
						$t2 = $tmp[1] ? $tmp[1] : " ";
						if(strpos($t1,'(*)') === false){
							$value = str_replace($t1,$t2,$value);
						}else{
							$t1 = $this->safe_code($t1);
							$value = preg_replace("/".$t1."/is",$t2,$value);
						}
					}else{
						if(strpos($v,'(*)') !== false){
							$v = $this->safe_code($v);
							$value = preg_replace("/".$v."/is","",$value);
						}else{
							if(!$v){
								$v = ' ';
							}
							$value = str_replace($v,'',$value);
						}
					}
				}
			}
			if(!$value){
				continue;
			}
			if($siteurl){
				if(substr($siteurl,-1) != "/") $siteurl .= "/";
				preg_match_all("/<img(.*)src=(.*)[ |>]/isU",$value,$matches);
				$picurl = array();
				foreach($matches[0] AS $k=>$v){
					$mypic_url = str_replace(array('"',"'"),"",$matches[2][$k]);
					if(substr($mypic_url,-1) == "/"){
						$mypic_url = substr($mypic_url,0,-1);
					}
					$picurl[] = str_replace(array('"',"'"),"",$mypic_url);
				}
				$picurl = array_unique($picurl);
				$new_picurl = array();
				foreach($picurl AS $k=>$v){
					if(strtolower(substr($v,0,7)) != "http://" && strtolower(substr($v,0,8)) != "https://"){
						$new_picurl[$k] = $siteurl.$v;
					}else{
						$new_picurl[$k] = $v;
					}
				}
				$value = str_replace($picurl,$new_picurl,$value);
			}
			$value = str_replace(array("  ","&nbsp;","&amp;nbsp;"),"",$value);
			$msg[] = $value;
		}
		if($msg && count($msg)>0){
			$msg = implode("<br />",$msg);
			if($msg && $rs['post_save'] == 'safe_cut'){
				$msg = phpok_cut($msg,80);
			}
			if($rs['translate']){
				$trans = $rs['translate'] == 1 ? $this->fanyi($msg) : $this->fanyi2($msg);
				if(!$trans || $trans == $msg){
					return $msg;
				}
				if($rs['re1']){
					return $trans.'<br /><br />'.$msg;
				}else{
					return $trans;
				}
			}
		}
		if($msg && $rs['post_save'] == 'safe_cut'){
			$msg = phpok_cut($msg,80);
		}
		return $msg;
	}

	//取得子页采集
	private function get_sub_list($start,$end,$content)
	{
		$content = str_replace(array("\r","\n","\t"),"",$content);
		$start = str_replace(array("\r","\n","\t"),"",$start);
		$start = str_replace(array("(*)","/"),array(".*?","\/"),$start);
		$tmp_array = preg_split("/".$start."/is",$content);
		if(count($tmp_array)<2){
			return false;
		}
		unset($tmp_array);
		$content = $this->tags_split($content,$start,"start");
		$content = $this->tags_split($content,$end,"end");
		preg_match_all("/<a(.*)href=[\"|'|](.*)[\"|'|](.*)>(.+)<\/a>/isU",$content,$matches);
		unset($tmp_content);
		$url_list = array();
		foreach($matches[0] AS $k=>$v){
			$url = $matches[2][$k];
			if(!$url){
				continue;
			}
			if(strtolower(substr($url,0,7)) != "http://" && strtolower(substr($url,0,8)) != "https://"){
				if(substr($rs["linkurl"],-1) != "/") $rs["linkurl"] .= "/";
				$url = $rs["linkurl"].$url;
			}
			$url_list[] = $url;
		}
		$url_list = array_unique($url_list);
		if(count($url_list)>0){
			return $url_list;
		}else{
			return false;
		}
	}
	


	//url是网址
	//id是指应的采集ID
	private function cj_url($url,$id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return array('content'=>'内容项目不存在');
		}
		//判断是否
		$this->lib('curl')->is_gzip($rs['is_gzip']);
		$this->lib('curl')->is_proxy($rs['is_proxy']);
		if($rs['is_proxy'] && $rs['proxy_service']){
			$tmp = explode(":",$rs['proxy_service']);
			if(!$tmp[1]){
				$tmp[1] = 80;
			}
			$this->lib('curl')->set_proxy($tmp[0],$tmp[1],$rs["proxy_user"],$rs["proxy_pass"]);
		}
		$content = $this->lib('curl')->get_content($url);
		if($rs["url_charset"] != "utf-8"){
			$content = $this->lib('string')->charset($content,$rs['url_charset'],'utf-8');
		}
		if($rs["list_tags_start"]){
			$content = $this->tags_split($content,$rs["list_tags_start"],"start");
		}
		if($rs["list_tags_end"]){
			$content = $this->tags_split($content,$rs["list_tags_end"],"end");
		}
		if(substr($rs["linkurl"],-1) != "/"){
			$rs["linkurl"] .= "/";
		}
		preg_match_all("/<a(.*)href=([^>]+)>(.+)<\/a>/isU",$content,$matches);
		$array["status"] = true;
		$list_array = array();
		$i = 0;
		$list_url = $matches[2] ? $matches[2] : array();
		$domain_rs = parse_url($rs['linkurl']);
		$tmplist = array();
		$http_type = parse_url($rs['linkurl'],PHP_URL_SCHEME);
		foreach($list_url as $key=>$value){
			if(!$value){
				continue;
			}
			$tmp_url_list = explode(" ",$value);
			$url = $tmp_url_list[0];
			if(!$url){
				continue;
			}
			$url = str_replace(array("'",'"'),"",$url);
			if(!$url){
				continue;
			}

			if($rs["url_tags"]){
				$tmp_array = explode("|",$rs["url_tags"]);
				$ok = false;
				foreach($tmp_array as $k=>$v){
					if(strpos($url,$v) !== false){
						$ok = true;
					}
				}
				if($ok == false){
					continue;
				}
			}
			if($rs['url_not_tags']){
				$tmp_array = explode("|",$rs["url_not_tags"]);
				$ok = true;
				foreach($tmp_array as $k=>$v){
					if(strpos($url,$v) !== false){
						$ok = false;
					}
				}
				if($ok == false){
					continue;
				}
			}
			$tmp_str = strtolower(substr($url,0,7));
			
			if($tmp_str != "http://" && $tmp_str != "https:/"){
				if(substr($url,0,2) == '//'){
					$url = $http_type.':'.$url;
				}else{
					if(substr($url,0,1) == "/"){
						$url = substr($url,1);
					}
					$url = $rs["linkurl"].$url;
				}
			}
			$url = str_replace("&amp;","&",$url);
			$parse = parse_url($url);
			if(strtolower($parse['host']) == strtolower($domain_rs['host']) && !in_array($url,$tmplist)){
				$tmplist[] = $url;
				$list_array[$i]["url"] = $url;
				$i++;
			}
		}
		$array["content"] = $list_array;
		return $array;
	}

	private function tags_split($content,$tag,$type="start")
	{
		if(!$content || !$tag){
			return false;
		}
		$content = str_replace(array("\r","\n","\t"),"",$content);
		$tag = $this->safe_code($tag);
		$tmp_array = preg_split("/".$tag."/is",$content);
		if($type == "start"){
			$tmp_count = count($tmp_array);
			if($tmp_count>1){
				$content = "";
				for($i=0;$i<$tmp_count;$i++){
					if($i>0){
						$content .= $tmp_array[$i];
					}
				}
			}
		}else{
			$content = $tmp_array[0];
		}
		return $content;
	}

	private function safe_code($tag)
	{
		if(!$tag){
			return false;
		}
		$tag = str_replace("[&]","&",$tag);
		$tag = str_replace('[space]',"\s+",$tag);
		$old = array("\r","\n","\t","/","|","[","]",".","?",'"',"'");
		$new = array("","","","\/","\|","\[","\]","\.","\?",'\"',"\'");
		$tag = str_replace($old,$new,$tag);
		$tag = str_replace('(*)',"(.*?)",$tag);
		return $tag;
	}

	public function info_edit()
	{
		$lid = $this->get('lid','int');
		if(!$lid){
			$this->error('未指定ID');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE id='".$lid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			$this->error('内容不存在');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection_tags WHERE cid='".$rs['cid']."' AND tags_type='var' ORDER BY id ASC";
		$taglist = $this->db->get_all($sql,'id');
		if(!$taglist){
			$this->error('没有可用标签');
		}
		$infolist = $this->db->get_all("SELECT * FROM ".$this->db->prefix."collection_format WHERE lid='".$lid."'",'fid');
		if($infolist){
			foreach($taglist as $key=>$value){
				$value['content'] = $infolist[$key]['content'];
				if(strlen($value['content'])>240){
					$value['_type'] = 'code';
				}else{
					$value['_type'] = 'input';
				}
				$taglist[$key] = $value;
			}
		}
		$this->assign('rslist',$taglist);
		$this->assign('lid',$lid);
		$this->echo_tpl('info_edit.html');
	}

	public function edit_save()
	{
		$lid = $this->get('lid','int');
		if(!$lid){
			$this->json('未指定ID');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE id='".$lid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			$this->json('内容不存在');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection_tags WHERE cid='".$rs['cid']."' AND tags_type='var' ORDER BY id ASC";
		$taglist = $this->db->get_all($sql,'id');
		if(!$taglist){
			$this->json('没有可用标签');
		}
		foreach($taglist as $key=>$value){
			$tmp = $this->get($value['identifier'],'html_js');
			if(!$tmp){
				$sql = "DELETE FROM ".$this->db->prefix."collection_format WHERE lid='".$lid."' AND fid='".$value['id']."'";
				$this->db->query($sql);
			}else{
				$sql = "SELECT id FROM ".$this->db->prefix."collection_format WHERE lid='".$lid."' AND fid='".$value['id']."'";
				$chk = $this->db->get_one($sql);
				if($chk){
					$sql = "UPDATE ".$this->db->prefix."collection_format SET content='".$tmp."' WHERE id='".$chk['id']."'";
					$this->db->query($sql);
				}else{
					$array = array('lid'=>$lid,'fid'=>$value['id'],'content'=>$tmp);
					$this->db->insert_array($array,'collection_format');
				}
			}
		}
		$this->json(true);
	}

	public function info_delete()
	{
		$lid = $this->get('lid','int');
		if(!$lid){
			$this->json('未指定ID');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE id='".$lid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			$this->json('数据不存在');
		}
		$sql = "DELETE FROM ".$this->db->prefix."collection_format WHERE lid='".$lid."'";
		$this->db->query($sql);
		$sql = "SELECT * FROM ".$this->db->prefix."collection_files WHERE lid='".$lid."'";
		$list = $this->db->get_all($sql);
		if($list){
			foreach($list as $key=>$value){
				if($value['newurl'] && file_exists($this->dir_root.$value['newurl'])){
					$this->lib('file')->rm($this->dir_root.$value['newurl']);
				}
			}
			$this->db->query("DELETE FROM ".$this->db->prefix."collection_files WHERE lid='".$lid."'");
		}
		$this->db->query("DELETE FROM ".$this->db->prefix."collection_list WHERE id='".$lid."'");
		$this->json(true);
	}

	//初始化采集的数据
	public function re_content()
	{
		$ids = $this->get('tid');
		if(!$ids){
			$this->json('未指定ID');
		}
		$list = explode(",",$ids);
		foreach($list as $key=>$value){
			if(!$value || !intval($value)){
				unset($list[$key]);
			}
		}
		$ids = implode(",",$list);
		$sql = "DELETE FROM ".$this->db->prefix."collection_format WHERE lid IN(".$ids.")";
		$this->db->query($sql);
		$sql = "SELECT * FROM ".$this->db->prefix."collection_files WHERE lid IN(".$ids.")";
		$list = $this->db->get_all($sql);
		if($list){
			foreach($list as $key=>$value){
				if($value['newurl'] && file_exists($this->dir_root.$value['newurl'])){
					$this->lib('file')->rm($this->dir_root.$value['newurl']);
				}
			}
			$this->db->query("DELETE FROM ".$this->db->prefix."collection_files WHERE lid IN(".$ids.")");
		}
		//标注为未采集
		$sql = "UPDATE ".$this->db->prefix."collection_list SET status=0 WHERE id IN(".$ids.")";
		$this->db->query($sql);
		$this->json(true);
	}

	public function clear_post()
	{
		$ids = $this->get('lid');
		if(!$ids){
			$this->json('未指定ID');
		}
		$list = explode(",",$ids);
		foreach($list as $key=>$value){
			if(!$value || !intval($value)){
				unset($list[$key]);
			}
		}
		$ids = implode(",",$list);
		$sql = "UPDATE ".$this->db->prefix."collection_list SET status=1 WHERE id IN(".$ids.")";
		$this->db->query($sql);
		$this->json(true);
	}

	public function clear_post2()
	{
		$ids = $this->get('lid');
		if(!$ids){
			$this->json('未指定ID');
		}
		$list = explode(",",$ids);
		foreach($list as $key=>$value){
			if(!$value || !intval($value)){
				unset($list[$key]);
			}
		}
		$ids = implode(",",$list);
		$sql = "UPDATE ".$this->db->prefix."collection_list SET status=2 WHERE id IN(".$ids.")";
		$this->db->query($sql);
		$this->json(true);
	}

	public function post_save()
	{
		$pageurl = $this->url('plugin','exec','id=collection&exec=post_save');
		$numid = $this->get('numid','int');
		$lid = $this->get('lid');
		if(!$lid){
			$cid = $this->get('cid');
			if(!$cid){
				$this->error('未指定要发布的主题',$this->url('plugin','exec','id=collection&exec=manage'));
			}
			$list = explode(",",$cid);
			foreach($list as $key=>$value){
				if(!$value || !intval($value)){
					unset($list[$key]);
				}
			}
			$cid = implode(",",$list);
			$pageurl .= "&cid=".rawurlencode($cid);
			$sql = "SELECT id FROM ".$this->db->prefix."collection_list WHERE cid IN(".$cid.") AND status=1 ORDER BY id ASC LIMIT 0,1";
			$tmp_rs = $this->db->get_one($sql);
			if(!$tmp_rs){
				$this->success('数据已发布完成，请到网站平台上检查数据是否发布完整');
			}
			$id = $tmp_rs['id'];
		}else{
			$list = explode(",",$lid);
			foreach($list as $key=>$value){
				if(!$value || !intval($value)){
					unset($list[$key]);
				}
			}
			$lid = implode(",",$list);
			$pageurl .= "&lid=".rawurlencode($lid);
			$list = explode(",",$lid);
			if(!$list[$numid]){
				$this->success('数据已发布完成，请到网站平台上检查数据是否发布完整');
			}
			$id = $list[$numid];
		}
		$rs = $this->db->get_one("SELECT * FROM ".$this->db->prefix."collection_list WHERE id='".$id."'");
		if(!$rs){
			$nextid = $numid+1;
			$this->error('数据不存在，跳过执行，进入下一步',$pageurl."&numid=".$nextid);
		}
		if($rs['status'] == 2){
			$nextid = $numid+1;
			$this->error('数据已发布，跳过执行，进入下一步',$pageurl."&numid=".$nextid);
		}
		if(!$rs['status']){
			$nextid = $numid+1;
			$this->error('数据还未采集，跳过执行，进入下一步',$pageurl."&numid=".$nextid);
		}
		//读取保存发布的字段
		$sql = "SELECT * FROM ".$this->db->prefix."collection_tags WHERE cid='".$rs['cid']."' ORDER BY id ASC";
		$taglist = $this->db->get_all($sql,'id');
		if(!$taglist){
			$nextid = $numid+1;
			$this->error('没有定义要发布的字段，跳过，进入下一步',$pageurl."&numid=".$nextid);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection WHERE id='".$rs['cid']."'";
		$info = $this->db->get_one($sql);
		if(!$info){
			$nextid = $numid+1;
			$this->error('采集项目不存在，跳过，进入下一步',$pageurl."&numid=".$nextid);
		}
		$project = $this->db->get_one("SELECT * FROM ".$this->db->prefix."project WHERE id='".$info['project_id']."'");
		if(!$project){
			$nextid = $numid+1;
			$this->error('目标存储不存在，跳过，进入下一步',$pageurl."&numid=".$nextid);
		}
		if(!$project['module']){
			$nextid = $numid+1;
			$this->error('目标模块不存在，跳过，进入下一步',$pageurl."&numid=".$nextid);
		}
		$mfields = $this->db->list_fields($this->db->prefix."list");
		unset($mfields['id'],$mfields['parent_id'],$mfields['project_id'],$mfields['cate_id']);
		$bfields = array('price','currency_id','weight','volume','unit');
		$elist = $this->model('module')->fields_all($project['module'],'identifier');
		if($elist){
			$efields = array_keys($elist);
		}
		$main = $ext = $biz = array();
		$sql = "SELECT * FROM ".$this->db->prefix."collection_format WHERE lid='".$id."'";
		$clist = $this->db->get_all($sql,'fid');
		//生
		$tag2fid = array();
		foreach($taglist as $key=>$value){
			$tag2fid[$value['identifier']] = $value['id'];
		}
		foreach($taglist as $key=>$value){
			if($value['tags_type'] == 'var'){
				$content = $this->content_format_it($clist[$value['id']]['content'],$value,$id);
			}else{
				$content = $value['rules'];
				if($content == '{time}'){
					$content = $this->time;
				}
				if($content == '{url}'){
					$content = $rs['url'];
				}
				if(strpos($content,'[') !== false){
					$content = str_replace(array('[',']'),'',$content);
					if($tag2fid[$content]){
						$content = $this->content_format_it($clist[$tag2fid[$content]]['content'],$value,$id);
					}else{
						$content = '';
					}
				}
			}
			if(!$content){
				continue;
			}
			//主表数据
			if(in_array($value['identifier'],$mfields)){
				$main[$value['identifier']] = $content;
			}
			//电商数据
			if($project['is_biz'] && in_array($value['identifier'],$bfields)){
				$biz[$value['identifier']] = $content;
			}
			//扩展表数据
			if($efields && in_array($value['identifier'],$efields)){
				$ext[$value['identifier']] = $content;
			}
		}
		$main['project_id'] = $info['project_id'];
		$main['module_id'] = $project['module'];
		$main['cate_id'] = $info['cateid'];
		$main['site_id'] = $_SESSION['admin_site_id'];
		if(!$main['title']){
			$sql = "UPDATE ".$this->db->prefix."collection_list SET status=0 WHERE id='".$id."'";
			$this->db->query($sql);
			$nextid = $numid+1;
			$this->error('数据未采集到主题（已标记未采集），跳过，进入下一步',$pageurl."&numid=".$nextid);
		}
		$insert_id = $this->model('list')->save($main);
		if(!$insert_id){
			$nextid = $numid+1;
			$this->error('数据保存失败，跳过，进入下一步',$pageurl."&numid=".$nextid);
		}
		if($project['is_biz'] && $biz){
			$biz['id'] = $insert_id;
			$this->model('list')->biz_save($biz);
		}
		$ext['id'] = $insert_id;
		$ext['project_id'] = $info['project_id'];
		$ext['cate_id'] = $info['cateid'];
		$ext['site_id'] = $_SESSION['admin_site_id'];
		$this->model('list')->save_ext($ext,$project['module']);
		//如果有分类
		if($info['cateid']){
	 		$ext_cate = array($info['cateid']);
	 		$this->model('list')->save_ext_cate($insert_id,$ext_cate);
		}
		//更新标识
		$sql = "UPDATE ".$this->db->prefix."collection_list SET status=2 WHERE id='".$id."'";
		$this->db->query($sql);
		$nextid = $numid+1;
		$this->success('主题：<span class="red">'.$main['title'].'</span>保存成功，进入下一步',$pageurl."&numid=".$nextid);
	}

	private function content_format_it($content,$tag,$lid=0)
	{
		if(!$content || !trim($content)){
			return false;
		}
		$content = trim($content);
		//变更内容里的链接
		$sql = "SELECT * FROM ".$this->db->prefix."collection_files WHERE cid='".$tag['cid']."' AND lid='".$lid."' AND fid='".$tag['id']."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			if($tag['post_save'] == 'datetime'){
				return strtotime($content);
			}
			if($tag['post_save'] == 'int'){
				return intval($content);
			}
			if($tag['post_save'] == 'float'){
				return floatval($content);
			}
			if($tag['post_save'] == 'safe'){
				$content = str_replace(array("\\","'",'"',"<",">"),array("&#92;","&#39;","&quot;","&lt;","&gt;"),$content);
				return addslashes($content);
			}
			if($tag['post_save'] == 'safe_cut'){
				return phpok_cut($content,80);
			}
			return addslashes($content);
		}
		//获取附件保存地址
		$rescate = $this->model('rescate')->get_one($this->me['param']['rescate']);
		if(!$rescate){
			$rescate = $this->model('rescate')->get_default();
		}
		if(!$rescate){
			$rescate = array('root'=>'res/','folder'=>'Ym/d/');
		}
		$folder = $rescate['root'];
		if($rescate['folder'] && $rescate['folder'] != '/'){
			$folder .= date($rescate['folder'],$this->time); 
		}
		$this->lib('file')->make($this->dir_root.$folder);
		if(!file_exists($this->dir_root.$folder)){
			$folder = 'res/';
		}
		if($tag['post_save'] == 'img'){
			$info = $rslist[0];
			if(!$info['newurl'] || !file_exists($this->dir_root.$info['newurl']) || filesize($this->dir_root.$info['newurl'])<1){
				return false;
			}
			$basename = basename($info['newurl']);
			$this->lib('file')->cp($this->dir_root.$info['newurl'],$this->dir_root.$folder.$basename);
			$array = array();
			$array["cate_id"] = $rescate['id'];
			$array["folder"] = $folder;
			$array["name"] = $basename;
			$array["ext"] = $info['ext'];
			$array["filename"] = $folder.$basename;
			$array["addtime"] = $this->time;
			$array["title"] = $basename;
			$array['admin_id'] = $_SESSION['admin_id'];
			if($info['ext'] && in_array($info["ext"],array('jpg','gif','png'))){
				$img_ext = getimagesize($this->dir_root.$folder.$basename);
				$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
				$array["attr"] = serialize($my_ext);
			}
			$insert_id = $this->model('res')->save($array);
			if(!$insert_id){
				$this->lib('file')->rm($this->dir_root.$folder.$basename);
				return false;
			}
			$this->model('res')->gd_update($insert_id);
			return $insert_id;
		}

		//替换网址
		foreach($rslist as $key=>$value){
			if(!$value['newurl'] || !file_exists($this->dir_root.$value['newurl']) || filesize($this->dir_root.$value['newurl'])<1){
				continue;
			}
			$basename = basename($value['newurl']);
			$this->lib('file')->cp($this->dir_root.$value['newurl'],$this->dir_root.$folder.$basename);
			$array = array();
			$array["cate_id"] = $rescate['id'];
			$array["folder"] = $folder;
			$array["name"] = $basename;
			$array["ext"] = $value['ext'];
			$array["filename"] = $folder.$basename;
			$array["addtime"] = $this->time;
			$array["title"] = $basename;
			$array['admin_id'] = $_SESSION['admin_id'];
			if($value['ext'] && in_array($value["ext"],array('jpg','gif','png'))){
				$img_ext = getimagesize($this->dir_root.$folder.$basename);
				$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
				$array["attr"] = serialize($my_ext);
			}
			$insert_id = $this->model('res')->save($array);
			if(!$insert_id){
				$this->lib('file')->rm($this->dir_root.$folder.$basename);
				continue;
			}
			$this->model('res')->gd_update($insert_id);
			//替换网址
			$content = str_replace($value['srcurl'],$folder.$basename,$content);
		}
		return addslashes($content);
	}

	private function fanyi($q)
	{
		if(!$q){
			return false;
		}
		$url = "http://fanyi.youdao.com/openapi.do?keyfrom=".$this->me['param']["keyfrom"];
		$url.= "&key=".$this->me['param']["keyid"]."&type=data&doctype=json&version=1.1&q=".rawurlencode($q);
		$content = $this->lib("curl")->get_content($url);
		if(!$content){
			return $q;
		}
		$rs = $this->lib("json")->decode($content);
		if($rs["errorCode"]){
			return $q;
		}
		return $rs["translation"][0];
	}

	private function fanyi2($str)
	{
		if(!$str){
			return false;
		}
		include "big2gbk.php";
    	mb_internal_encoding("UTF-8");
    	$length = mb_strlen($str);
    	$fantis = '';
	    for ($i = 0; $i <= $length; $i++) {
	        $fanti = mb_substr($str, $i, 1);
	        if($b_data && $b_data[$fanti]){
		        $fantis .= $b_data[$fanti];
	        }else{
		        $fantis .= $fanti;
	        }
	    }
	    return $fantis;
	}

	public function preview()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->error('未指定ID');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE id='".$tid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			$this->error('网址不存在');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection WHERE id='".$rs['cid']."'";
		$root = $this->db->get_one($sql);
		$this->lib('curl')->referer($root['linkurl']);
		$this->lib('curl')->is_gzip($root['is_gzip']);
		$this->lib('curl')->is_proxy($root['is_proxy']);
		if($root['is_proxy'] && $root['proxy_service']){
			$tmp = explode(":",$root['proxy_service']);
			if(!$tmp[1]){
				$tmp[1] = 80;
			}
			$this->lib('curl')->set_proxy($tmp[0],$tmp[1],$root["proxy_user"],$root["proxy_pass"]);
		}
		$content = $this->lib('curl')->get_content($rs['url']);
		//去除JS
		$content = preg_replace("/<scrip(.*)>(.*)<\/script>/isU","",$content);
		//增加baseurl
		$tmp = parse_url($rs['url']);
		$baseurl = $tmp['scheme']."://".$tmp['host'];
		if($tmp['port'] && $tmp['port'] != '80' && $tmp['port'] != '443'){
			$baseurl .= ':'.$tmp['port'];
		}
		$baseurl .= '/';
		$this->assign('baseurl',$baseurl);
		//$content = preg_replace("/<body(.*)>/isU","<base href=\"'.$root['linkurl'].'\" /><body\\1>",$content);
		$this->assign('content',$content);
		$this->_view("collection_preview.html");
	}
}