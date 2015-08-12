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
		echo '1<br />';
		echo $this->lib('phpzip')->unzip($this->dir_root.'data/tmp.zip','data/update/');
		echo 'ok';
	}
}

?>