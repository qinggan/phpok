<?php
/**
 * PHPExcel项目数据导入导出
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月24日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_phpexcel extends phpok_plugin
{
	private $path;
	private $close_button = '<input type="button" value="关闭" onclick="$.dialog.close()">';
	private $errtip = false;
	public function __construct()
	{
		parent::plugin();
		$this->path = str_replace('\\','/',dirname(__FILE__)).'/';
	}

	public function html_list_action_body()
	{
		$pid = $this->tpl->val('pid');
		if(!$pid){
			return false;
		}
		$page_rs = $this->tpl->val('rs');
		if(!$page_rs){
			return false;
		}
		if(!$page_rs['module']){
			return false;
		}
		$this->_show('button.html');
	}

	public function html_user_index_body()
	{
		$this->_show('button_user.html');
	}

	public function in_data($rs)
	{
		$pid = $this->get('pid');
		if(!$pid){
			$this->error("未指定要导入的项目ID");
		}
		$catelist = $this->model('res')->cate_all();
		if($catelist){
			foreach($catelist as $key=>$value){
				if(!$value['filetypes']){
					unset($catelist[$key]);
					continue;
				}
				$tmp = explode(",",$value['filetypes']);
				if(!in_array('xls',$tmp) && !in_array('xlxs',$tmp)){
					unset($catelist[$key]);
					continue;
				}
			}
		}
		$this->assign("catelist",$catelist);
		$this->assign("pid",$pid);
		$this->assign("rs",$rs);
		//读取最新的50个附件
		$condition = " ext IN('xls','xlsx')";
		$rslist = $this->model('res')->get_list($condition,0,30);
		$this->assign("rslist",$rslist);
		$this->_view('in_data.html');
	}

	public function import_user()
	{
		$catelist = $this->model('res')->cate_all();
		if($catelist){
			foreach($catelist as $key=>$value){
				if(!$value['filetypes']){
					unset($catelist[$key]);
					continue;
				}
				$tmp = explode(",",$value['filetypes']);
				if(!in_array('xls',$tmp) && !in_array('xlxs',$tmp)){
					unset($catelist[$key]);
					continue;
				}
			}
		}
		$this->assign("catelist",$catelist);
		$condition = " ext IN('xls','xlsx')";
		$rslist = $this->model('res')->get_list($condition,0,30);
		$this->assign("rslist",$rslist);
		$this->_view('import_user.html');
	}

	public function import_user_setting()
	{
		$this->lib('phpexcel');
		ini_set('memory_limit', '1024M');
		$file = $this->get("file");
		if(!$file){
			$this->error("未指定要导入的附件");
		}
		$res = $this->model('res')->get_one($file);
		if(!$res){
			$this->error("附件信息不存在");
		}
		if(!file_exists($this->dir_root.$res["filename"])){
			$this->error("附件信息不存在");
		}
		$filetype = $res["ext"] == "xlsx" ? "Excel2007" : "Excel5";
		$objReader = PHPExcel_IOFactory::createReader($filetype);
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($this->dir_root.$res["filename"]);
		$currentSheet = $objPHPExcel->getSheet(0);
		$allColumn = $currentSheet->getHighestColumn();
		$allRow = $currentSheet->getHighestRow();
		//取得第一行字段
		$m = 0;
		$idlist = array();
		$t_i = PHPExcel_Cell::columnIndexFromString($allColumn);
		for($i = 0;$i< $t_i;$i++){
			$str = "";
			$m = $i+65;
			$tmp_i = intval($m/91);
			if($tmp_i){
				$tm = chr($tmp_i+64);
				$str .= $tm;
				$str .= chr($m%91+65);
			}else{
				$str = chr($m);
			}
			$t = $str."1";
			$idlist[$t] = $currentSheet->getCell($t)->getValue();
			//解决第一个字符为'的数字问题
			if(substr($idlist[$t],0,1) == "'"){
				$idlist[$t] = substr($idlist[$t],1);
			}
		}
		$this->assign("idlist",$idlist);

		$flist = array('id'=>'用户ID',"group_id"=>'用户组ID',"_group"=>"用户组名称");
		$flist['user'] = "用户账号";
		$flist['pass'] = '用户密码'; //32位长度加密后的
		$flist['status'] = '状态';//设置【正常】，【是】，【1】这些词表示status=1，其他为0
		$flist['regtime'] = '注册时间';//年月日格式
		$flist['email'] = '邮箱';
		$flist['mobile'] = '手机号';
		$flist['code'] = '验证串';
		$flist['avatar'] = '用户头像';//仅仅只是一条链接
		$mlist = $this->model('user')->fields_all();
		if($mlist){
			foreach($mlist as $key=>$value){
				$flist[$value['identifier']] = $value['title'];
			}
		}

		$this->assign("flist",$flist);
		$this->assign("file",$file);
		$this->_view('import_user_setting.html');
	}

	private function to_chr($i)
	{
		$str = "";
		$t = intval(($i-65)/65);
		if($t>0){
			$str .= "A";
			$str .= chr( ($i-65) );
			return $str;
		}else{
			return chr($i);
		}
	}

	private function to_ord($str)
	{
		if(!$str){
			return false;
		}
		$list = str_split($str);
		$i = 0;
		foreach($list AS $key=>$value){
			$i+=ord($value);
		}
		return $i;
	}

	public function data_import()
	{
		$this->lib('form');
		$this->lib('phpexcel');
		ini_set('memory_limit', '1024M');
		$pid = $this->get("pid");
		if(!$pid){
			$this->error("未指定要导入的项目ID");
		}
		$this->assign("pid",$pid);
		$file = $this->get("file");
		if(!$file){
			$this->error("未指定要导入的附件");
		}
		$rs = $this->_info();
		$this->assign("rs",$rs);
		$res = $this->model('res')->get_one($file);
		if(!$res){
			$this->error("附件信息不存在");
		}
		$resfile = $this->dir_root.$res["filename"];
		if(!file_exists($resfile)){
			$this->error("附件信息不存在");
		}
		$this->assign('res',$res);
		$idlist = $this->lib('phpexcel')->getTitle($resfile);
		if(!$idlist){
			$this->error('没有找到文件头，请检查');
		}
		$this->assign("idlist",$idlist);
		$project_rs = $this->model('project')->get_one($pid);
		if(!$project_rs['module']){
			$this->error('项目未绑定模块，不支持导入');
		}
		$p_title = $project_rs["alias_title"] ? $project_rs["alias_title"] : "主题";
		$flist = array('id'=>'主键ID',"title"=>$p_title,"dateline"=>"发布时间");
		if($project_rs['is_biz']){
			$flist['price'] = '价格';
			$flist['unit'] = '单位';
			$flist['weight'] = '重量';
			$flist['volume'] = '体积';
		}
		if($project_rs["module"]){
			$extlist  = $this->model('module')->fields_all($project_rs["module"],"identifier");
			if($extlist){
				foreach($extlist as $key=>$value){
					$flist[$key] = $value["title"];
				}
			}
		}
		$flist['dateline'] = '发布时间';
		$flist['hits'] = '查看次数';
		$flist["hits"] = "查看次数";
		$flist["_cate_id"] = "分类ID";
		$flist["seo_title"] = "SEO标题";
		$flist["seo_keywords"] = "SEO关键字";
		$flist["seo_desc"] = "SEO描述";
		$flist["tag"] = "Tag标签";
		$this->assign("flist",$flist);
		$site_id = $project_rs["site_id"];
		if($project_rs["cate"]){
			$catelist = $this->model('cate')->get_all($site_id,1,$project_rs["cate"]);
			if($catelist){
				$this->assign("catelist",$catelist);
			}
		}
		$this->assign("file",$file);
		$this->_view('data_import.html');
	}

	public function import_end()
	{
		$this->lib('form');
		$this->lib('phpexcel');
		ini_set('memory_limit', '1024M');
		@set_time_limit(0);
		$pid = $this->get("pid");
		if(!$pid){
			$this->error("未指定要导入的项目ID");
		}
		$file = $this->get("file");
		if(!$file){
			$this->error("未指定要导入的附件");
		}
		$rs = $this->model('plugin')->get_one($this->id);
		$res = $this->model('res')->get_one($file);
		if(!$res){
			$this->error("附件信息不存在");
		}
		if(!file_exists($this->dir_root.$res["filename"])){
			$this->error("附件信息不存在");
		}
		$filetype = $res["ext"] == "xlsx" ? "Excel2007" : "Excel5";
		$objReader = PHPExcel_IOFactory::createReader($filetype);
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($this->dir_root.$res["filename"]);
		$currentSheet = $objPHPExcel->getSheet(0);
		$allColumn = $currentSheet->getHighestColumn();
		$allRow = $currentSheet->getHighestRow();
		//取得第一行字段
		$rslist = array();
		$idlist = array();
		$t_i = PHPExcel_Cell::columnIndexFromString($allColumn);
		for($i = 0;$i< $t_i;$i++){
			$str = "";
			$m = $i+65;
			$tmp_i = intval($m/91);
			if($tmp_i){
				$tm = chr($tmp_i+64);
				$str .= $tm;
				$str .= chr($m%91+65);
			}else{
				$str = chr($m);
			}
			$t = $str."1";
			$idlist[$t] = $currentSheet->getCell($t)->getValue();
			if(substr($idlist[$t],0,1) == "'"){
				$idlist[$t] = substr($idlist[$t],1);
			}
		}
		$project_rs = $this->model('project')->get_one($pid);
		$main_f = array("id","title","sort","dateline","hits","seo_title","seo_keywords","seo_desc","tag","_cate_id");
		if($project_rs['is_biz']){
			$biz_f = array('price','weight','unit','currency_id','volume','is_virtual');
		}
		$ext_f = array();
		if($project_rs["module"]){
			$extlist  = $this->model('module')->fields_all($project_rs["module"],"identifier");
			$module = $this->model('module')->get_one($project_rs['module']);
		}
		//导入主表及副表数据
		$cate_id = $this->get("cate_id","int");
		$status = $this->get("status","int");
		$hidden = $this->get("hidden","int");
		$link = $this->get('link');
		if(!$link){
			$link = array();
		}
		$is_only = $this->get("is_only");
		$only = "";
		if($is_only){
			$only = $link[$is_only];
		}
		$m = 0;
		$onlylist = array();
		$repeat = 0;
		for($currentRow = 2;$currentRow<=$allRow;$currentRow++){
			$main_data = $ext_data = $biz_data = array();
			$main_data["cate_id"] = $ext_data["cate_id"] = $cate_id;
			$main_data["project_id"] = $ext_data["project_id"] = $pid;
			$main_data["module_id"] = $project_rs["module"];
			$main_data["site_id"] = $ext_data["site_id"] = $project_rs["site_id"];
			$main_data["status"] = $status;
			$main_data["hidden"] = $hidden;
			foreach($idlist as $key=>$value){
				$address = substr($key,0,-1).$currentRow;
				$k = $key;
				$val_data = $currentSheet->getCell($address)->getValue();
				$tmp_title = $idlist[substr($key,0,-1).'1'];
				if(strpos($tmp_title,'日期') !== false){
					$val_data = $this->lib('phpexcel')->excelTime($val_data);
				}
				if(substr($val_data,0,1) == "'"){
					$val_data = substr($val_data,1);
				}
				if($link[$k] && in_array($link[$k],$main_f)){
					if($link[$k] == "_cate_id"){
						$main_data["cate_id"] = $val_data;
						$ext_data["cate_id"] = $val_data;
					}else{
						$main_data[$link[$k]] = $val_data;
					}
				} elseif($link[$k] && $project_rs['is_biz'] && in_array($link[$k],$biz_f)){
					$biz_data[$link[$k]] = $val_data;
				}else{
					if(strpos($tmp_title,':') !== false){
						$tmp = explode(':',$tmp_title);
						if($link[$k]){
							if($ext_data[$link[$k]]){
								$ext_data[$link[$k]]['title'][] = $tmp[1];
								$ext_data[$link[$k]]['content'][] = $val_data;
							}else{
								$ext_data[$link[$k]] = array('title'=>array($tmp[1]),'content'=>array($val_data));
							}
						}
					}else{
						if($link[$k]){
							if($ext_data[$link[$k]]){
								$ext_data[$link[$k]] .= "|".$val_data;
							}else{
								$ext_data[$link[$k]] = $val_data;
							}
						}
					}
				}
			}
			//如果主题为空，则跳过
			if(!$main_data["title"]){
				continue;
			}
			if($main_data["dateline"]){
				$main_data["dateline"] = strtotime($main_data["dateline"]);
			}else{
				$main_data["dateline"] = $this->time;
			}
			if($only && !$main_data[$only] && !$ext_data[$only]){
				continue;
			}
			foreach($extlist as $k=>$v){
				$tmp = $v['ext'] ? unserialize($v['ext']) : array();
				if($v['form_type'] == 'param' && $ext_data[$v['identifier']] && is_array($ext_data[$v['identifier']])){
					$ext_data[$v['identifier']] = serialize($ext_data[$v['identifier']]);
				}
			}
			$check = false;
			if($only && $only != 'id'){
				if($main_data[$only]){
					$check = $this->model('list')->main_only_check($only,$main_data[$only],$project_rs["site_id"],$pid,$project_rs["module"]);
				}else{
					$check = $this->model('list')->ext_only_check($only,$ext_data[$only],$project_rs["module"],$module['mtype'],$pid);
				}
			}
			if($check){
				//记录
				$tmp = array_merge($main_data,$ext_data);
				$onlylist[] = $tmp;
				unset($tmp);
				continue;
			}
			if($main_data['id']){
				$insert_id = $main_data['id'];
				unset($main_data['id']);
				$this->model('list')->save($main_data,$insert_id);
			}else{
				$insert_id = $this->model('list')->save($main_data);
			}
			if($insert_id){
				$ext_data["id"] = $insert_id;
				$this->model('list')->save_ext($ext_data,$project_rs["module"]);
				if($biz_data && count($biz_data)>0 && $project_rs['is_biz']){
					$sql = "SELECT id FROM ".$this->db->prefix."list_biz WHERE id='".$insert_id."'";
					$tmp = $this->db->get_one($sql);
					if(!$tmp){
						$biz_data['id'] = $insert_id;
						$this->model('list')->biz_save($biz_data);
					}else{
						$this->db->update_array($biz_data,"list_biz",array('id'=>$insert_id));
					}
				}
			}else{
				//删除主表数据
				$this->model('list')->delete($insert_id,$project_rs["module"]);
			}
			$m ++;
		}
		$filename = false;
		if($onlylist && count($onlylist)>0){
			$repeat = count($onlylist);
			//保存重复数据
			$p_title = $project_rs["alias_title"] ? $project_rs["alias_title"] : "主题";
			$flist = array("title"=>$p_title,"sort"=>"排序","dateline"=>"发布时间","hits"=>"查看次数","_cate_id"=>"分类ID","seo_title"=>"SEO标题","seo_keywords"=>"SEO关键字","seo_desc"=>"SEO描述","tag"=>"Tag标签");
			if($project_rs["module"] && $extlist){
				foreach($extlist AS $key=>$value){
					$flist[$key] = $value["title"];
				}
			}
			$titlelist = array();
			foreach($link as $key=>$value){
				$tmpid = str_replace(array('0','1','2','3','4','5','6','7','8','9'),'',$key);
				if($flist[$value]){
					$titlelist[$tmpid] = array('title'=>$flist[$value],'id'=>$value);
				}
			}
			$filename = $this->create_excel($onlylist,$titlelist);
		}
		$tip = '';
		if($filename && $repeat){
			$tip = '，数据中有重复数据 <span class="red"><b>'.$repeat.'</b></span> 条，<a href="'.$this->config['url'].'_data/'.$filename.'" target="_blank">请点此下载</a>';
		}
		$this->success("数据导入成功".$tip);
	}

	//导出数据
	public function out_data()
	{
		$pid = $this->get("pid");
		if(!$pid){
			$this->error("未指定要导入的项目ID");
		}
		$project_rs = $this->model('project')->get_one($pid);
		$this->assign("project_rs",$project_rs);
		if($project_rs["cate"]){
			$catelist = $this->model('cate')->get_all($project_rs["site_id"],1,$project_rs["cate"]);
			if($catelist){
				$this->assign("catelist",$catelist);
			}
		}
		$this->assign("pid",$pid);
		$rs = $this->_info();
		$this->assign("rs",$rs);
		$extlist  = $this->model('module')->fields_all($project_rs["module"],"identifier");
		$this->assign("extlist",$extlist);
		$this->_view('out_data.html');
	}

	private function create_excel($data,$title='')
	{
		$this->lib('form');
		$this->lib('phpexcel');
		if(!$data){
			return false;
		}
		$phpexcel = new PHPExcel();
		$row = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,AA,AB,AC,AD,AE,AF,AG,AH,AI,AJ,AK,AL,AM,AN,AO,AP,AQ,AR,AS,AT,AU,AV,AW,AX,AY,AZ";
		$row_array = explode(",",$row);
		$filename = 'repeat';
		$num = 1;
		$idlist = array();
		if($title && is_array($title)){
			$num = 2;
			$i = 0;
			foreach($title AS $key=>$value){
				$char = $key;
				$idlist[$value['id']] = $key;
				$phpexcel->getActiveSheet()->getColumnDimension($char)->setWidth("18");
				$phpexcel->getActiveSheet()->getStyle($char."1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$phpexcel->getActiveSheet()->getStyle($char."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$phpexcel->getActiveSheet()->setCellValueExplicit($char."1",$value['title'],PHPExcel_Cell_DataType::TYPE_STRING);
				$i++;
			}
		}
		$i=0;
		foreach($data AS $key=>$value){
			$m = $i+$num;
			foreach($value as $k=>$val){
				if($k == 'dateline' && $val){
					$val = date("Y-m-d H:i:s",$val);
				}
				if(is_array($val)){
					continue;
				}
				$char_num = $idlist[$k];
				if(!$char_num){
					continue;
				}
				$char = $char_num.''.$m;
				$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setWrapText(true);
				$phpexcel->getActiveSheet()->setCellValueExplicit($char,$val,PHPExcel_Cell_DataType::TYPE_STRING);
			}
			$i++;
		}
		$phpexcel->createSheet();
		$XLS_W = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
		$XLS_W->save($this->dir_data.''.$filename.".xls");
		return $filename.".xls";
	}

	public function export_data()
	{
		$this->lib('form');
		$this->lib('phpexcel');
		$pid = $this->get("pid");
		if(!$pid){
			$this->error("未指定要导入的项目ID");
		}
		$project_rs = $this->model('project')->get_one($pid);
		$mid = $project_rs["module"];
		$m_rs = $this->model('module')->get_one($mid);
		$this->assign("project_rs",$project_rs);
		$site_id = $project_rs["site_id"];
		$ext = isset($_POST["ext"]) ? $_POST["ext"] : array();
		$status = $this->get("status","int");
		$condition  = "l.status='".$status."'";
		$condition .= " AND l.project_id='".$pid."' AND l.module_id='".$mid."' AND l.site_id='".$site_id."' ";
		$hidden = $this->get("hidden","int");
		$condition .= " AND l.hidden='".$hidden."'";
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$cate_rs = $this->model('cate')->get_one($cate_id);
			if($cate_rs){
				$catelist = array($cate_rs);
				$this->model('cate')->get_sublist($catelist,$cate_id);
				$cate_id_list = array();
				foreach($catelist AS $key=>$value){
					$cate_id_list[] = $value["id"];
				}
				$cate_string = implode(",",$cate_id_list);
				$condition .= " AND l.cate_id IN(".$cate_string.")";
			}
		}
		$offset = $this->get("offset","int");
		if($offset && $offset>0){
			$condition .= " AND l.id<=".intval($offset)." ";
		}
		$psize = $this->get("psize","int");
		if(!$psize){
			$psize = "1000";
		}
		if($project_rs['is_biz']){
			$this->model('list')->is_biz(true);
		}
		$rslist = $this->model('list')->get_list($mid,$condition,0,$psize,'l.id DESC');
		if(!$rslist){
			$this->error("没有获取有效数据");
		}
		$first_list = array("title"=>($project_rs['alias_title'] ? $project_rs['alias_title'] : '主题'),"dateline"=>"添加时间","tag"=>"标签","seo_title"=>"SEO主题","seo_keywords"=>"SEO",'id'=>'ID');
		$first_list["seo_desc"] = "SEO描述";
		$first_list["url"] = "网址";
		if($project_rs['is_biz']){
			$first_list['price'] = '价格';
			$first_list['unit'] = '单位';
			$first_list['weight'] = '重量';
			$first_list['volume'] = '体积';
		}
		$extlist  = $this->model('module')->fields_all($project_rs["module"],"identifier");
		foreach($extlist AS $key=>$value){
			$first_list[$key] = $value["title"];
		}
		@set_time_limit(0);#[设置防止超时]
		$phpexcel = new PHPExcel();
		$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        if (!\PHPExcel_Settings::setCacheStorageMethod($cacheMethod)) {
            $this->error($cacheMethod . " 缓存方法不可用");
        }
		$row = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,AA,AB,AC,AD,AE,AF,AG,AH,AI,AJ,AK,AL,AM,AN,AO,AP,AQ,AR,AS,AT,AU,AV,AW,AX,AY,AZ";
		$filename = date("Ymd-His");
		$idlist = $ext;
		$row_array = explode(",",$row);
		$width_array = array();
		$ifpic = false;
		$list = $tmplist = array();
		$ext2 = $this->get('ext2');
		foreach($idlist as $key=>$value){
			$char = $row_array[$key];
			$phpexcel->getActiveSheet()->getColumnDimension($char)->setWidth("18");
			if($ext2 && $ext2[$value] && $ext2[$value] == 'image'){
				$phpexcel->getActiveSheet()->getColumnDimension($char)->setWidth("30");
			}
			$list[$char] = $value;
			$val = $first_list[$value];
			$phpexcel->getActiveSheet()->getStyle($char."1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle($char."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$phpexcel->getActiveSheet()->setCellValueExplicit($char."1",$val,PHPExcel_Cell_DataType::TYPE_STRING);
		}
		//现在存储内容数据
		$i=0;
		foreach($rslist as $key=>$value){
			$m = $i+2;
			foreach($list as $k=>$v){
				$val = $value[$v];
				if(is_array($val)){
					if($val["filename"]){
						$val = $val['filename'];
					}
					if(isset($val['info']) && $val['info']){
						$val = $val['info'];
					}
					if(isset($val['val']) && $val['val']){
						$val = $val['val'];
					}
					if(is_array($val)){
						$val = $val['title'];
					}
				}
				if($v == "dateline"){
					$val = date("Y-m-d H:i:s",$val);
				}
				if($v == "url"){
					$val = $this->url."index.php?id=".$value["identifier"];
				}
				if($ext2 && $ext2[$v] && $ext2[$v] == 'date'){
					$val = date('Y-m-d',$val);
				}
				if($ext2 && $ext2[$v] && $ext2[$v] == 'dateline'){
					$val = date('Y-m-d H:i:s',$val);
				}
				$char = $k."".$m;
				$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setWrapText(true);
				if($val && $ext2 && $ext2[$v] && $ext2[$v] == 'image' && file_exists($this->dir_root.$val)){
					$phpexcel->getActiveSheet()->getRowDimension($m)->setRowHeight(80);
					$objDrawing = new PHPExcel_Worksheet_Drawing();
		            $objDrawing->setPath($this->dir_root.$val);
		            $objDrawing->setHeight(80);//照片高度
		            $objDrawing->setCoordinates($char);
		            $objDrawing->setOffsetX(12);
		            $objDrawing->setOffsetY(12);
		            $objDrawing->setWorksheet($phpexcel->getActiveSheet());
				}else{
					$phpexcel->getActiveSheet()->setCellValueExplicit($char,$val,PHPExcel_Cell_DataType::TYPE_STRING);
				}
			}
			$i++;
		}
		$phpexcel->createSheet();
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$XLS_W = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
		$XLS_W->save('php://output');
	}
}