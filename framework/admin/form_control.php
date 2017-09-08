<?php
/**
 * 自定义表单的字段异步处理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	public function config_f()
	{
		$id = $this->get("id");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$eid = $this->get("eid");
		$etype = $this->get("etype");
		if(!$etype){
			$etype = "ext";
		}
		if($eid) {
			if($etype == "fields"){
				$rs = $this->model('fields')->get_one($eid);
				if($rs && $rs['ext'] && is_array($rs['ext'])){
					foreach($rs['ext'] as $key=>$value){
						$rs[$key] = $value;
					}
				}
			}elseif($etype == "module"){
				$rs = $this->model('module')->field_one($eid);
			}elseif($etype == "user"){
				$rs = $this->model('user')->field_one($eid);
			}else{
				$rs = $this->model('ext')->get_one($eid);
			}
			if($rs["ext"] && is_string($rs['ext'])){
				$ext = unserialize($rs["ext"]);
				if(!$ext){
					$ext = array();
				}
				foreach($ext AS $key=>$value){
					$rs[$key] = $value;
				}
			}
			$this->assign("rs",$rs);
		}
		$this->lib('form')->config($id);
	}
}