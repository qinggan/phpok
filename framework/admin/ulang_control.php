<?php
/**
 * 语言包提取工具
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2020年8月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ulang_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$id = $this->get('id');
		if(!$id){
			$id = 'admin';
		}
		$this->assign('id',$id);
		$langid = $this->get('langid');
		if(!$langid){
			$langid = 'en_US';
		}
		$this->assign('langid',$langid);
		//语言包
		$langlist = $this->model('lang')->get_list();
		if($langlist){
			foreach($langlist as $key=>$value){
				if($key == 'zh_CN' || $key == 'cn' || $key == 'default'){
					unset($langlist[$key]);
				}
			}
		}
		$this->assign('langlist',$langlist);
		$list = array();
		$file = $this->dir_data.'json/language_'.$id.'.json';
		if(file_exists($file)){
			$list = $this->lib('json')->decode($this->lib('file')->cat($file));
		}
		$this->assign('rslist',$list);
		$this->view('ulang_index');
	}

	//加载语言本地库
	public function reload_f()
	{
		$tmp = array('admin','www','api');
		foreach($tmp as $key=>$value){
			$list = $this->file2content($value);
			$tmplist = array();
			foreach($list as $k=>$v){
				if(substr($v,-4) == 'html'){
					preg_match_all("/\{lang([^\\)\(}]+)[\}|\|]/isU",$v,$matches);
				}else{
					preg_match_all("/P_Lang\([\"']{1}(.+)[\"']/isU",$v,$matches);
				}
				if(!$matches || !$matches[1]){
					continue;
				}
				foreach($matches[1] as $kk=>$vv){
					if(strpos($vv,'.$') !== false){
						continue;
					}
					$code = md5($vv);
					$tmplist[$code] = $vv;
				}
			}
			$tmp = $this->lib("json")->encode($tmplist,false,true);
			$file = $this->dir_data."/json/language_".$value.".json";
			$this->lib('file')->vim($tmp,$file);
		}
		$this->success('语言包文件更新成功');
	}

	private function file2content($type="admin")
	{
		if($type == 'admin'){
			$list = $this->lib('file')->ls($this->dir_phpok.'admin/');
			$list2 = $this->lib('file')->ls($this->dir_phpok.'view/');
			$list = array_merge($list,$list2);
			foreach($list as $key=>$value){
				$tmp = $this->lib('file')->cat($value);
				yield $tmp;
			}
		}else{
			$list = $this->lib('file')->ls($this->dir_phpok.$type.'/');
			foreach($list as $key=>$value){
				$tmp = $this->lib('file')->cat($value);
				yield $tmp;
			}
		}
	}

	public function tophp_f()
	{
		$list = $this->lib('file')->ls($this->dir_phpok.'view/');
		$fopen = fopen($this->dir_root.'langs/lang2.php','wb');
		fwrite($fopen,'<?php'."\n");
		foreach($list as $key=>$value){
			$handle = fopen($value,'rb');
			if($handle) {
				while(($buffer = fgets($handle,4096)) !== false){
					preg_match_all("/\{lang([^\}]+)\}/isU",$buffer,$matches);
					if($matches && $matches[1]){
						foreach($matches[1] as $k=>$v){
							$v = str_replace(array("'",'"'),'',$v);
							$lst = explode("|",$v);
							$param = array();
							if($lst[1]){
								$tmp = explode(",",$lst[1]);
								foreach($tmp as $key2=>$value2){
									$tmp2 = explode(":",$value2);
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