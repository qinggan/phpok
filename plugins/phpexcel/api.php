<?php
/**
 * PHPExcel项目数据导入导出
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年6月24日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class api_phpexcel extends phpok_plugin
{
	private $path;
	private $errtip = false;
	public function __construct()
	{
		parent::plugin();
		$this->path = str_replace('\\','/',dirname(__FILE__)).'/';
	}

	public function import_user()
	{
		$this->_admin();
		$this->lib('form');
		$this->lib('phpexcel');
		ini_set('memory_limit', '1024M');
		@set_time_limit(0);
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
			if(substr($idlist[$t],0,1) == "'") $idlist[$t] = substr($idlist[$t],1);
		}
		$main_f = array("id","group_id","user","pass","status","regtime","email","mobile","code","avatar","_group");
		$ext_f = array();
		$elist = $this->model('user')->fields_all();
		if($elist){
			foreach($elist as $key=>$value){
				$ext_f[] = $value['identifier'];
			}
		}
		//导入主表及副表数据
		$link = $this->get('link');
		if(!$link){
			$link = array();
		}
		$only = $this->get("is_only");
		$m = 0;
		$onlylist = array();
		$repeat = 0;
		$group = $this->model('usergroup')->get_default();
		for($currentRow = 2;$currentRow<=$allRow;$currentRow++){
			$main_data = $ext_data = array();
			foreach($idlist as $key=>$value){
				$address = substr($key,0,-1).$currentRow;
				$k = $key;
				$obj = $currentSheet->getCell($address);
				$val_data = $obj->getFormattedValue();
				if($obj->getDataType() == PHPExcel_Cell_DataType::TYPE_NUMERIC){
					$cellstyleformat = $obj->getParent()->getStyle( $obj->getCoordinate() )->getNumberFormat();
					$formatcode= $cellstyleformat->getFormatCode();
					if(preg_match('/^(\[\$[A-Z]*-[0-9A-F]*\])*[hmsdy]/i', $formatcode)){
						$val_data = gmdate("Y-m-d h:i:s", PHPExcel_Shared_Date::ExcelToPHP($val_data));
					}
				}
				$tmp_title = $idlist[substr($key,0,-1).'1'];
				if(strpos($tmp_title,'日期') !== false){
					$val_data = $this->lib('phpexcel')->excelTime($val_data);
				}
				if(substr($val_data,0,1) == "'") $val_data = substr($val_data,1);
				if($link[$k] && in_array($link[$k],$main_f)){
					if($link[$k] == "_group"){
						$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE title='".$val_data."'";
						$tmp = $this->db->get_one($sql);
						if($tmp){
							$main_data['group_id'] = $tmp['id'];
						}
					}elseif($link[$k] == 'status'){
						$tmp = array('正常','是','1','yes','ok');
						if($val_data && in_array($val_data,$tmp)){
							$main_data['status'] = 1;
						}else{
							$main_data['status'] = 0;
						}
					}elseif($link[$k] == 'pass'){
						if($val_data){
							if(strlen($val_data) == 32){
								$main_data['pass'] = $val_data;
							}else{
								$main_data['pass'] = password_create($val_data);
							}
						}else{
							$main_data['pass'] = password_create("123456");
						}
					}elseif($link[$k] == 'regtime'){
						if($val_data){
							$val_data = $this->lib('phpexcel')->excelTime($val_data);
							$main_data['regtime'] = strtotime($val_data);
						}else{
							$main_data['regtime'] = $this->time;
						}
					}else{
						if($val_data != ''){
							$main_data[$link[$k]] = $val_data;
						}
					}
				}else{
					if($link[$k]){
						$ext_data[$link[$k]] = $val_data;
					}
				}
			}
			//如果账号为空，则跳过
			if(!$main_data["user"]){
				$m++;
				continue;
			}
			$save_act = true;
			if($only && is_array($only) && count($only)>0){
				foreach($only as $k=>$v){
					if($main_data[$v] && $v != 'id'){
						$sql = "SELECT * FROM ".$this->db->prefix."user WHERE ".$v."='".$main_data[$v]."'";
						$tmp = $this->db->get_one($sql);
						if($tmp){
							$save_act = false;
							break;
						}
					}
					if($ext_data[$v] && $v != 'id'){
						$sql = "SELECT * FROM ".$this->db->prefix."user_ext WHERE ".$v."='".$ext_data[$v]."'";
						$tmp = $this->db->get_one($sql);
						if($tmp){
							$save_act = false;
							break;
						}
					}
				}
			}
			if(!$save_act){
				$m++;
				continue;
			}
			if(!$main_data['regtime']){
				$main_data['regtime'] = $this->time;
			}
			if(!$main_data['group_id']){
				$main_data['group_id'] = $group['id'];
			}
			if($main_data['id']){
				$uid = $main_data['id'];
				unset($main_data['id']);
				$this->db->update($main_data,'user',array('id'=>$uid));
				$sql = "SELECT * FROM ".$this->db->prefix."user_ext WHERE id='".$uid."'";
				$tmp = $this->db->get_one($sql);
				if($tmp && $ext_data){
					$this->db->update($ext_data,'user_ext',array('id'=>$uid));
				}
				if(!$tmp){
					$ext_data['id'] = $uid;
					$this->db->insert($ext_data,'user_ext');
				}
			}else{
				$insert_id = $this->db->insert($main_data,'user');
				$ext_data['id'] = $insert_id;
				$this->db->insert($ext_data,'user_ext');
			}
			$m++;
		}
		$this->success("数据导入成功");
	}

	private function _admin()
	{
		if(!$this->session->val('admin_id')){
			$this->error('非管理员不能执行此操作');
		}
	}
}