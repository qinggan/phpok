<?php
/*****************************************************************************************
	文件： {phpok}/admin/ulang_control.php
	备注： 提取后台模板语言包
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年06月12日 11时02分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ulang_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$list = $this->lib('file')->ls($this->dir_phpok.'view/');
		$fopen = fopen($this->dir_root.'langs/lang2.php','wb');
		fwrite($fopen,'<?php'."\n");
		foreach($list as $key=>$value){
			$handle = fopen($value,'rb');
			if($handle) {
				$matches = false;
				while(($buffer = fgets($handle,4096)) !== false){
					preg_match_all("/\{lang([^\}]+)\}/isU",$buffer,$matches);
					if($matches && $matches[1]){
						foreach($matches[1] as $k=>$v){
							$v = str_replace(array("'",'"'),'',$v);
							$lst = explode("|",$v);
							$param = false;
							if($lst[1]){
								$tmp = explode(",",$lst[1]);
								foreach($tmp as $key2=>$value2){
									$tmp2 = explode(":",$value2);
									if(!$param){
										$param = array();
									}
									$param[$tmp2[0]] = '<span style="color:red">'.$tmp2[1].'</span>';
								}
							}
							if($param){
								$string = "array(";
								$i=0;
								foreach($param as $key2=>$value2){
									if($i>0){
										$string .= ",";
									}
									$string .= "'".$key."'=>'".$value."'";
									$i++;
								}
								$string .= ")";
								fwrite($fopen,"P_Lang('".$lst[0]."',".$string.");\n");
							}else{
								fwrite($fopen,"P_Lang('".$v."');\n");
							}
						}
					}
				}
				fclose($handle);
				echo 'Update template: '.$value."<br />\n";
			}
		}
		fclose($fopen);
		echo 'Update success.';
		echo debug_time(1,1,1,1);
	}

	public function unzip_f()
	{
		echo '<br />';
		echo $this->lib('phpzip')->unzip($this->dir_data.'tmp.zip',$this->dir_data.'update/');
		echo 'ok';
	}

	public function delete_cate_f()
	{
		$sql = "SELECT id FROM ".$this->db->prefix."cate";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->error('没有分类信息');
		}
		$clist = array();
		foreach($rslist as $key=>$value){
			$clist[] = 'cate-'.$value['id'];
			echo 'Cate '.$value['id'].'<br />';
		}
		$sql = "SELECT id,module FROM ".$this->db->prefix."fields WHERE ftype LIKE 'cate-%'";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				if(!in_array($value['module'],$clist)){
					$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$value['id']."'";
					$this->db->query($sql);
					$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$value['id']."'";
					$this->db->query($sql);
					echo 'Delete cate '.$value['id'].'<br />';
				}
			}
		}
		echo 'End<br />';
	}

	public function delete_project_f()
	{
		$sql = "SELECT id FROM ".$this->db->prefix."project";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->error('没有项目信息');
		}
		$clist = array();
		foreach($rslist as $key=>$value){
			$clist[] = 'project-'.$value['id'];
			echo 'project '.$value['id'].'<br />';
		}
		$sql = "SELECT id,module FROM ".$this->db->prefix."fields WHERE ftype LIKE 'project-%'";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				if(!in_array($value['module'],$clist)){
					$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$value['id']."'";
					$this->db->query($sql);
					$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$value['id']."'";
					$this->db->query($sql);
					echo 'Delete project '.$value['id'].'<br />';
				}
			}
		}
		echo 'End<br />';
	}

	public function delete_all_f()
	{
		$sql = "SELECT id FROM ".$this->db->prefix."all";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->error('没有项目信息');
		}
		$clist = array();
		foreach($rslist as $key=>$value){
			$clist[] = 'all-'.$value['id'];
			echo 'all '.$value['id'].'<br />';
		}
		$sql = "SELECT id,module FROM ".$this->db->prefix."fields WHERE ftype LIKE 'all-%'";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				if(!in_array($value['module'],$clist)){
					$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$value['id']."'";
					$this->db->query($sql);
					$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$value['id']."'";
					$this->db->query($sql);
					echo 'Delete all '.$value['id'].'<br />';
				}
			}
		}
		echo 'End<br />';
	}

	public function popedom_f()
	{
		$sql = "SELECT id FROM ".$this->db->prefix."project";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->error('没有项目信息');
		}
		$plist = array();
		foreach($rslist as $key=>$value){
			$plist[] = $value['id'];
			echo 'Project '.$value['id'].'<br />';
		}
		$sql = "SELECT id,pid FROM ".$this->db->prefix."popedom WHERE pid>0";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				if(!in_array($value['pid'],$plist)){
					$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE id='".$value['id']."'";
					$this->db->query($sql);
					echo 'Delete popedom '.$value['id'].'<br />';
				}
			}
		}
		echo 'End<br />';
		exit;
	}
}

?>