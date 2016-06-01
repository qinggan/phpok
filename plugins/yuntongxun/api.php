<?php
/*****************************************************************************************
	文件： plugins/yuntongxun/api.php
	备注： 短信发送接口
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月22日 14时25分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class api_yuntongxun extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	public function sendsms()
	{
		$mobile = $this->get('mobile');
		if(!$mobile){
			$this->json('手机号不能为空');
		}
		$chk = $this->lib('common')->tel_check($mobile,'mobile');
		if(!$chk){
			$this->json('手机号格式不正确，请重新填写');
		}
		//检测这上手机号1分钟内是否有发送过，如果
		$time = $this->time - 60;
		$sql = "SELECT id FROM ".$this->db->prefix."plugin_yuntongxun WHERE ctime>=".$time." AND status=0 AND mobile='".$mobile."'";
		$rs = $this->db->get_one($sql);
		if($rs){
			$this->json('短信已发送，请等待，一分钟后还没有收到再重新请求');
		}
		$type = $this->get('type');
		if(!$type){
			$type = 'register';
		}
		//验证码
		$code = rand(1000,9999);
		$etime = $this->time + 300;
		$data = array('code'=>$code,'mobile'=>$mobile,'ctime'=>$this->time,'etime'=>$etime,'status'=>'0','utype'=>$type);
		$action = $this->db->insert_array($data,'plugin_yuntongxun');
		if(!$action){
			$this->json('验证短信发送失败，请再尝试一遍');
		}
		$send_status = $this->sendTemplateSMS($mobile,array($code,'5'),1);
		if(!$send_status){
			$sql = "DELETE FROM ".$this->db->prefix."plugin_yuntongxun WHERE id='".$action."'";
			$this->db->query($sql);
			$this->json('验证码发送失败');
		}
		$this->json(true);
	}

	private function sendTemplateSMS($to,$datas,$tempId=1)
	{
		include_once($this->me['path'].'yuntongxun.lib.php');
		$obj = new yuntongxun_lib($this->me['param']['ytx_sever_ip'],$this->me['param']['ytx_server_port'],$this->me['param']['ytx_soft_version']);
		$obj->setAccount($this->me['param']['ytx_account_sid'],$this->me['param']['ytx_account_token']);
		$obj->setAppId($this->me['param']['ytx_app_id']);
		$result = $obj->sendTemplateSMS($to,$datas,$tempId);
		if($result == NULL ) {
			return false;
		}
		if($result->statusCode!=0) {
			return false;
		}else{
			return true;
		}
	}

	//验证
	public function ap_register_save_before()
	{
		$tmpid = $this->me['param']['ytx_check_code'];
		if(!$tmpid){
			$tmpid = '_mobile_code';
		}
		$val = $this->get($tmpid);
		if(!$val){
			$this->json('手机验证码不能为空');
		}
		$mobile = $this->get('mobile');
		if(!$mobile){
			$this->json('手机号不能为空');
		}
		$chk = $this->lib('common')->tel_check($mobile,'mobile');
		if(!$chk){
			$this->json('手机号格式不正确，请重新填写');
		}
		$sql = "SELECT * FROM ".$this->db->prefix."plugin_yuntongxun WHERE mobile='".$mobile."' AND code='".$val."' AND status=0";
		$sql.= " AND ctime>=".($this->time-300);
		$rs = $this->db->get_one($sql);
		if(!$rs){
			$this->json('验证不通过，请检查');
		}
		if($rs['etime'] < $this->time){
			$this->json('验证码已过期，请重新获取');
		}
		return true;
	}

	public function ap_register_save_after()
	{
		$tmpid = $this->me['param']['ytx_check_code'];
		if(!$tmpid){
			$tmpid = '_mobile_code';
		}
		$val = $this->get($tmpid);
		$mobile = $this->get('mobile');
		$sql = "UPDATE ".$this->db->prefix."plugin_yuntongxun SET status=1 WHERE mobile='".$mobile."' AND code='".$val."' AND status=0";
		$this->db->query($sql);
		return true;
	}
}

?>