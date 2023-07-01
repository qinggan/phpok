<?php
/**
 * API 安全接口
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2020年1月1日
**/

class apisafe_model_base extends phpok_model
{
	private $code = '';
	private $error_info = '';
	public function __construct()
	{
		parent::model();
	}

	public function code($code='')
	{
		if($code && !is_bool($code)){
			$this->code = $code;
		}
		return $this->code;
	}

	public function error_info($info='')
	{
		if($info && !is_bool($info)){
			$this->error_info = $info;
		}
		return $this->error_info;
	}

	public function create($string='')
	{
		if(!$string){
			return false;
		}
		$list = explode(",",$string);
		sort($list);
		$isok = true;
		foreach($list as $key=>$value){
			if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
				$isok = false;
				break;
			}
		}
		if(!$isok){
			return false;
		}
		$sign = md5($this->code.",".implode(",",$list));
		return $sign;
	}

	public function check()
	{
		$safecode = $this->get("_safecode");
		if(!$safecode){
			$this->error_info(P_Lang('没有安全串'));
			return false;
		}
		
		$keys = array();
		if(isset($_POST)){
			foreach($_POST as $k=>$v){
				if($k && !is_array($k) && $k != '_safecode' && $k != "_safeid"){
					$keys[] = $k;
				}
				if($k && is_array($k)){
					foreach($k as $kk=>$vv){
						if($kk && !is_array($kk)){
							$keys[] = $kk;
						}
					}
				}
			}
		}
		if(isset($_GET)){
			foreach($_GET as $k=>$v){
				if($k && !is_array($k) && $k != '_safecode' && $k != "_safeid"){
					$keys[] = $k;
				}
				if($k && is_array($k)){
					foreach($k as $kk=>$vv){
						if($kk && !is_array($kk)){
							$keys[] = $kk;
						}
					}
				}
			}
		}
		$keys = array_unique($keys);
		sort($keys);
		$chkcode = md5($this->code.",".implode(",",$keys));
		if($chkcode != $safecode){
			$this->error_info(P_Lang('验证不通过'));
			return false;
		}
		return true;
	}
}
