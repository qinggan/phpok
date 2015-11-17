<?php
/***********************************************************
	Filename: plugins/phpexcel/admin.php
	Note	: PHPExcel导入导出
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_phpexcel extends phpok_plugin
{
	private $path;
	private $close_button = '<input type="button" value="关闭" onclick="$.dialog.close()">';
	function __construct()
	{
		parent::plugin();
		$this->path = str_replace('\\','/',dirname(__FILE__)).'/';
		$this->lib('form');
	}

	function html_list_action_body()
	{
		//判断是否有项目ID
		//$pid = $this->get('id','int');
		//if(!$pid) return false;
		//判断项目信息
		//$p_rs = $this->model('project')->get_one($pid,false);
		//if(!$p_rs['module'] || !$p_rs['status']) return false;
		$plugin = $this->plugin_info();
		$this->assign("plugin",$plugin);
		//echo $this->plugin_tpl('btn.html');
		echo $this->fetch($this->path."tpl/button.html","abs-file");
	}

	//执行Excel导入
	function in_data($rs)
	{
		$pid = $this->get('pid');
		if(!$pid)
		{
			error_open("未指定要导入的项目ID","error",$this->close_button);
		}
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		$this->assign("pid",$pid);
		$this->assign("rs",$rs);
		//读取最新的50个附件
		$condition = " ext IN('xls','xlsx')";
		$rslist = $this->model('res')->get_list($condition,0,30);
		$this->assign("rslist",$rslist);
		//上传excel文档
		$this->view($this->path."tpl/in_data.html","abs-file");
	}

	function to_chr($i)
	{
		$str = "";
		$t = intval(($i-65)/65);
		if($t>0)
		{
			$str .= "A";
			$str .= chr( ($i-65) );
			return $str;
		}
		else
		{
			return chr($i);
		}
	}

	function to_ord($str)
	{
		if(!$str) return false;
		$list = str_split($str);
		$i = 0;
		foreach($list AS $key=>$value)
		{
			$i+=ord($value);
		}
		return $i;
	}

	function data_import()
	{
		ini_set('memory_limit', '1024M');
		$pid = $this->get("pid");
		if(!$pid)
		{
			error_open("未指定要导入的项目ID","error",$this->close_button);
		}
		$this->assign("pid",$pid);
		$file = $this->get("file");
		if(!$file)
		{
			error_open("未指定要导入的附件","error",$this->close_button);
		}
		$rs = $this->plugin_info();
		$this->assign("rs",$rs);
		$res = $this->model('res')->get_one($file);
		if(!$res)
		{
			error_open("附件信息不存在","error",$this->close_button);
		}
		if(!is_file($this->dir_root.$res["filename"]))
		{
			error_open("附件信息不存在","error",$this->close_button);
		}
		//通过excel
		include_once $this->dir_phpok.'lib/phpexcel/PHPExcel.php';
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
		for($i = 0;$i< $t_i;$i++)
		{
			$str = "";
			$m = $i+65;
			$tmp_i = intval($m/91);
			if($tmp_i)
			{
				$tm = chr($tmp_i+64);
				$str .= $tm;
				$str .= chr($m%91+65);
			}
			else
			{
				$str = chr($m);
			}
			$t = $str."1";
			$idlist[$t] = $currentSheet->getCell($t)->getValue();
			if(substr($idlist[$t],0,1) == "'") $idlist[$t] = substr($idlist[$t],1);
		}
		$this->assign("idlist",$idlist);
		$project_rs = $this->model('project')->get_one($pid);
		$p_title = $project_rs["alias_title"] ? $project_rs["alias_title"] : "主题";
		$flist = array("title"=>$p_title,"sort"=>"排序","dateline"=>"发布时间","hits"=>"查看次数","_cate_id"=>"分类ID","seo_title"=>"SEO标题","seo_keywords"=>"SEO关键字","seo_desc"=>"SEO描述","tag"=>"Tag标签");
		//$flist = array("title"=>$p_title);
		if($project_rs["module"])
		{
			$extlist  = $this->model('module')->fields_all($project_rs["module"],"identifier");
			if($extlist)
			{
				foreach($extlist AS $key=>$value)
				{
					$flist[$key] = $value["title"];
				}
			}
		}
		$this->assign("flist",$flist);
		//echo "<pre>";
		//print_r($flist);
		$site_id = $project_rs["site_id"];
		if($project_rs["cate"])
		{
			$catelist = $this->model('cate')->get_all($site_id,1,$project_rs["cate"]);
			if($catelist)
			{
				$this->assign("catelist",$catelist);
			}
		}
		$this->assign("file",$file);
		$this->view($this->path."tpl/data_import.html","abs-file");
	}

	function import_end()
	{
		ini_set('memory_limit', '1024M');
		@set_time_limit(0);
		$pid = $this->get("pid");
		if(!$pid)
		{
			error_open("未指定要导入的项目ID","error",$this->close_button);
		}
		$file = $this->get("file");
		if(!$file)
		{
			error_open("未指定要导入的附件","error",$this->close_button);
		}
		$rs = $this->model('plugin')->get_one($this->id);
		$res = $this->model('res')->get_one($file);
		if(!$res)
		{
			error_open("附件信息不存在","error",$this->close_button);
		}
		if(!is_file($this->dir_root.$res["filename"]))
		{
			error_open("附件信息不存在","error",$this->close_button);
		}
		//通过excel
		include_once $this->path."PHPExcel.php";
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
		for($i = 0;$i< $t_i;$i++)
		{
			$str = "";
			$m = $i+65;
			$tmp_i = intval($m/91);
			if($tmp_i)
			{
				$tm = chr($tmp_i+64);
				$str .= $tm;
				$str .= chr($m%91+65);
			}
			else
			{
				$str = chr($m);
			}
			$t = $str."1";
			$idlist[$t] = $currentSheet->getCell($t)->getValue();
			if(substr($idlist[$t],0,1) == "'") $idlist[$t] = substr($idlist[$t],1);
		}
		$project_rs = $this->model('project')->get_one($pid);
		$main_f = array("title","sort","dateline","hits","seo_title","seo_keywords","seo_desc","tag","_cate_id");
		$ext_f = array();
		if($project_rs["module"])
		{
			$extlist  = $this->model('module')->fields_all($project_rs["module"],"identifier");
		}
		//导入主表及副表数据
		$cate_id = $this->get("cate_id","int");
		$status = $this->get("status","int");
		$hidden = $this->get("hidden","int");
		$link = isset($_POST["link"]) ? $_POST["link"] : array();
		if(!$link) $link = array();//
		$is_only = $this->get("is_only");
		$only = "";
		if($is_only)
		{
			$only = $link[$is_only];
		}
		$m = 0;
		for($currentRow = 2;$currentRow<=$allRow;$currentRow++)
		{
			$main_data = $ext_data = array();
			$main_data["cate_id"] = $ext_data["cate_id"] = $cate_id;
			$main_data["project_id"] = $ext_data["project_id"] = $pid;
			$main_data["module_id"] = $project_rs["module"];
			$main_data["site_id"] = $ext_data["site_id"] = $project_rs["site_id"];
			$main_data["status"] = $status;
			$main_data["hidden"] = $hidden;
			foreach($idlist AS $key=>$value)
			{
				$address = substr($key,0,-1).$currentRow;
				$k = $key;
				$val_data = $currentSheet->getCell($address)->getValue();
				if(substr($val_data,0,1) == "'") $val_data = substr($val_data,1);
				if($link[$k] && in_array($link[$k],$main_f))
				{
					if($link[$k] == "_cate_id")
					{
						$main_data["cate_id"] = $val_data;
						$ext_data["cate_id"] = $val_data;
					}
					else
					{
						$main_data[$link[$k]] = $val_data;
					}
				}
				else
				{
					if($link[$k])
					{
						$ext_data[$link[$k]] = $val_data;
					}
				}
			}
			//如果主题为空，则跳过
			if(!$main_data["title"])
			{
				continue;
			}
			if($main_data["dateline"])
			{
				$main_data["dateline"] = strtotime($main_data["dateline"]);
			}
			else
			{
				$main_data["dateline"] = $this->system_time;
			}
			if($only && !$main_data[$only] && !$ext_data[$only])
			{
				continue;
			}
			$check = false;
			if($only)
			{
				if($main_data[$only])
				{
					$check = $this->model('list')->main_only_check($only,$main_data[$only],$project_rs["site_id"],$pid,$project_rs["module"]);
				}
				else
				{
					$check = $this->model('list')->ext_only_check($only,$ext_data[$only],$project_rs["module"],$project_rs["site_id"],$pid);
				}
			}
			if($checked)
			{
				continue;
			}
			//存储主表字段
			$insert_id = $this->model('list')->save($main_data);
			if($insert_id)
			{
				$ext_data["id"] = $insert_id;
				$this->model('list')->save_ext($ext_data,$project_rs["module"]);
				//存储表ID数据
				$identifier = "content-".$insert_id;
		 		$i_array = array();
		 		$i_array["id"] = $insert_id;
		 		$i_array["site_id"] = $project_rs["site_id"];
		 		$i_array["phpok"] = $identifier;
		 		$i_array["type_id"] = "content";
		 		$this->model('id')->save($i_array);
			}
			else
			{
				//删除主表数据
				$this->model('list')->delete($insert_id,$project_rs["module"]);
			}
			$m ++;
		}
		error_open("数据导入成功","ok",$this->close_button);	
	}

	//导出数据
	function out_data()
	{
		$pid = $this->get("pid");
		if(!$pid)
		{
			error_open("未指定要导入的项目ID","error",$this->close_button);
		}
		$project_rs = $this->model('project')->get_one($pid);
		$this->assign("project_rs",$project_rs);
		$site_id = $project_rs["site_id"];
		if($project_rs["cate"])
		{
			//$catelist = array();
			$catelist = $this->model('cate')->get_all($site_id,1,$project_rs["cate"]);
			if($catelist)
			{
				$this->assign("catelist",$catelist);
			}
		}
		$this->assign("pid",$pid);
		$rs = $this->plugin_info();
		$this->assign("rs",$rs);
		$extlist  = $this->model('module')->fields_all($project_rs["module"],"identifier");
		$this->assign("extlist",$extlist);
		$this->view($this->path."tpl/out_data.html","abs-file");
	}

	function export_data()
	{
		$pid = $this->get("pid");
		if(!$pid)
		{
			error_open("未指定要导入的项目ID","error",$this->close_button);
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
		if($cate_id)
		{
			$cate_rs = $this->model('cate')->get_one($cate_id);
			if($cate_rs)
			{
				$catelist = array($cate_rs);
				$this->model('cate')->get_sublist($catelist,$cate_id);
				$cate_id_list = array();
				foreach($catelist AS $key=>$value)
				{
					$cate_id_list[] = $value["id"];
				}
				$cate_string = implode(",",$cate_id_list);
				$condition .= " l.cate_id IN(".$cate_string.")";
			}
		}
		$offset = $this->get("offset","int");
		$psize = $this->get("psize","int");
		if(!$psize)
		{
			$psize = "9999999";
		}
		$rslist = $this->model('list')->get_list($mid,$condition,$offset,$psize);
		if(!$rslist)
		{
			error_open("没有获取有效数据","error",$this->close_button);
		}
		$first_list = array("title"=>"主题","dateline"=>"添加时间","tag"=>"标签","seo_title"=>"SEO主题","seo_keywords"=>"SEO");
		$first_list["seo_desc"] = "描述";
		$first_list["url"] = "网址";
		$extlist  = $this->model('module')->fields_all($project_rs["module"],"identifier");
		foreach($extlist AS $key=>$value)
		{
			$first_list[$key] = $value["title"];
		}
		include_once $this->path."PHPExcel.php";
		@set_time_limit(0);#[设置防止超时]
		$phpexcel = new PHPExcel();
		$row = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
		$filename = date("Ymd-His");
		$idlist = $ext;
		$row_array = explode(",",$row);
		$width_array = array();
		$ifpic = false;
		$list = $tmplist = array();
		foreach($idlist AS $key=>$value)
		{
			$char = $row_array[$key];
			$phpexcel->getActiveSheet()->getColumnDimension($char)->setWidth("18");
			$list[$char] = $value;
			$val = $first_list[$value];
			$phpexcel->getActiveSheet()->getStyle($char."1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle($char."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			//$phpexcel->getActiveSheet()->getStyle($char."1")->getAlignment()->setWrapText(true);
			$phpexcel->getActiveSheet()->setCellValueExplicit($char."1",$val,PHPExcel_Cell_DataType::TYPE_STRING);
		}
		//现在存储内容数据
		$i=0;
		foreach($rslist AS $key=>$value)
		{
			$m = $i+2;
			if($ifpic) $this->set_height($m,"80");
			foreach($list AS $k=>$v)
			{
				$val = $value[$v];
				if(is_array($val))
				{
					if($val["filename"]) $val = $val['filename'];
					if($val['info']) $val = $val['info'];
					if($val['val']) $val = $val['val'];
					if(is_array($val)) $val = $val['title'];
					//$val = $val["filename"] ? $val["filename"] : $val["title"];
				}
				if($v == "dateline") $val = date("Y-m-d H:i:s",$val);
				if($v == "url") $val = "index.php?id=".$value["identifier"];
				$char = $k."".$m;
				$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				//$phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setWrapText(true);
				$phpexcel->getActiveSheet()->setCellValueExplicit($char,$val,PHPExcel_Cell_DataType::TYPE_STRING);
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
?>