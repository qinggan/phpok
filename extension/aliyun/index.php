<?php
/**
 * 阿里云SDK信息，请配合插件或是网关路由使用
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2017年12月23日
**/


/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

include_once dirname(__FILE__).'/aliyun-php-sdk-core/Config.php';

/**
 * 邮件推送
**/
use Dm\Request\V20151123 as Dm;

/**
 * 视频上传
**/
use vod\Request\V20170321 as vod;

/**
 * 短消息服务
**/
use Aliyun\DySDKLite as sms;

/**
 * 直播平台
**/
use live\Request\V20161101 as live;

/**
 * CDN 应用
**/
use Cdn\Request\V20180510 as cdn;

class aliyun_lib
{
	/**
	 * Access Key ID 密钥ID
	**/
	private $access_key = '';

	/**
	 * Access Key Secret 密钥加密参数
	**/
	private $access_secret = '';

	/**
	 * 服务器节点ID，默认使用 cn-hangzhou
	**/
	private $regoin_id = 'cn-hangzhou';

	/**
	 * 签名
	**/
	private $signature = '锟铻科技';

	/**
	 * 模板ID，一般适用于短信发送使用
	**/
	private $template_id = 0;

	/**
	 * 服务器节点地址
	**/
	private $end_point = ''; //节点

	/**
	 * 邮件发送账号
	**/
	private $dm_account = '';

	/**
	 * 发件人昵称
	**/
	private $dm_name = '锟铻科技';

	private $client;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		//
	}

	/**
	 * Access Key ID 密钥ID
	 * @参数 $val 要设定的值
	**/
	public function access_key($val='')
	{
		if($val){
			$this->access_key = $val;
		}
		return $this->access_key;
	}

	/**
	 * Access Key Secret 密钥加密参数
	 * @参数 $val 要设定的值
	**/
	public function access_secret($val='')
	{
		if($val){
			$this->access_secret = $val;
		}
		return $this->access_secret;
	}

	/**
	 * 服务器节点ID，默认使用 cn-hangzhou
	 * @参数 $val 要设定的值
	**/
	public function regoin_id($val='')
	{
		if($val){
			$this->regoin_id = $val;
		}
		return $this->regoin_id;
	}

	/**
	 * 签名
	 * @参数 $val 要设定的值
	**/
	public function signature($val='')
	{
		if($val){
			$this->signature = $val;
		}
		return $this->signature;
	}

	/**
	 * 模板ID
	 * @参数 $val 要设定的值
	**/
	public function template_id($val='')
	{
		if($val){
			$this->template_id = $val;
		}
		return $this->template_id;
	}

	/**
	 * 服务器节点地址
	 * @参数 $val 要设定的值
	**/
	public function end_point($val='')
	{
		if($val){
			$this->end_point = $val;
		}
		return $this->end_point;
	}

	/**
	 * 邮件账号
	 * @参数 $val 要设定的值
	**/
	public function dm_account($val='')
	{
		if($val){
			$this->dm_account = $val;
		}
		return $this->dm_account;
	}

	/**
	 * 发件人称呼
	 * @参数 $val 要设定的值
	**/
	public function dm_name($val='')
	{
		if($val){
			$this->dm_name = $val;
		}
		return $this->dm_name;
	}

	/**
	 * 邮件发送
	 * @参数 $title 邮件主题
	 * @参数 $content 邮件内容
	 * @参数 $mailto 目标邮箱
	**/
	public function email($title='',$content='',$mailto='')
	{
		if(!$title || !$content || !$mailto){
			return $this->error(P_Lang('参数传递不完整'));
		}
		if(!$this->access_key){
			return $this->error(P_Lang('未指定Access Key'));
		}
		if(!$this->access_secret){
			return $this->error(P_Lang('未指定Access Secret'));
		}
		if(!$this->signature){
			return $this->error(P_Lang('未配置标签'));
		}
		if(!$this->dm_account){
			return $this->error(P_Lang('未配置发信地址'));
		}
		if(!$this->dm_name){
			return $this->error(P_Lang('未配置发信人昵称'));
		}
		$iClientProfile = DefaultProfile::getProfile($this->regoin_id, $this->access_key,$this->access_secret);
		if($this->end_point && $this->regoin_id != 'cn-hangzhou'){
			$iClientProfile::addEndpoint($this->regoin_id,$this->regoin_id,"Dm",$this->end_point);
		}
		$client = new DefaultAcsClient($iClientProfile);
		$request = new Dm\SingleSendMailRequest();
		if($this->regoin_id != 'cn-hangzhou'){
			$request->setVersion("2017-06-22");
		}
		$request->setAccountName($this->dm_account);
		$request->setFromAlias($this->dm_name);
		$request->setAddressType(1);
		$request->setTagName($this->signature);
		$request->setReplyToAddress("true");
		$request->setToAddress($mailto);
		$request->setSubject($title);
		$request->setHtmlBody(stripslashes($content));
		try {
			$response = $client->getAcsResponse($request);
			return $this->success();
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 短信发送
	 * @参数 $mobile 目标手机号
	 * @参数 $data 变量参数，仅限数组
	**/
	public function sms($mobile,$data='')
	{
		if(!$mobile){
			return $this->error(P_Lang('未指定手机号'));
		}
		if(!$this->template_id){
			return $this->error(P_Lang('未指定模板ID'));
		}
		if(!$this->access_key){
			return $this->error(P_Lang('未指定Access Key'));
		}
		if(!$this->access_secret){
			return $this->error(P_Lang('未指定Access Secret'));
		}
		if(!$this->signature){
			return $this->error(P_Lang('未配置签名'));
		}

		if(!$this->end_point){
			return $this->error(P_Lang('未配置EndPoint'));
		}

		$params = array("PhoneNumbers"=>$mobile,'SignName'=>$this->signature,'TemplateCode'=>$this->template_id);
		if($data){
			$data = json_encode($data, JSON_UNESCAPED_UNICODE);
			$params['TemplateParam'] = $data;
		}
		$params['RegionId'] = $this->regoin_id;
		$params['Action'] = 'SendSms';
		$params['Version'] = '2017-05-25';
		$helper = new sms\SignatureHelper();
		$content = $helper->request($this->access_key,$this->access_secret,$this->end_point,$params);
		if(!$content){
			return $this->error('短信发送失败');
		}
		$content = (array) $content;
		if($content['Code'] == 'OK'){
			return $this->success($content);
		}
		return $this->error($content['Message'],$content['Code']);
	}

	public function client()
	{
		if(!$this->regoin_id){
			return $this->error(P_Lang('未设置 Regoin ID'));
		}
		if(!$this->access_key){
			return $this->error(P_Lang('未指定Access Key ID'));
		}
		if(!$this->access_secret){
			return $this->error(P_Lang('未指定Access Key Secret'));
		}
		if(!$this->signature){
			return $this->error(P_Lang('未配置标签'));
		}
		$iClientProfile = DefaultProfile::getProfile($this->regoin_id, $this->access_key,$this->access_secret);
		$this->client = new DefaultAcsClient($iClientProfile);
		return $this->client;
	}

	/**
	 * 上传视频文件
	 * @参数 $filename 文件名
	 * @参数 $title 标题
	 * @参数 $thumb 缩略图
	 * @参数 $note 摘要
	 * @参数 $tag 标签
	**/
	public function create_upload_video($filename,$title='',$thumb='',$note='',$tag='')
	{
		if(!$filename){
			return false;
		}
		if(!$title){
			$tmp = explode(".",$filename);
			$title = $tmp[0];
			if(!$title){
				$title = $filename;
			}
		}
		$request = new vod\CreateUploadVideoRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setTitle($title);
		//视频源文件名称(必须包含扩展名)
		$request->setFileName($filename);
		//视频源文件字节数
		$request->setFileSize(0);
		if($note){
			$request->setDescription($note);
		}
		if($thumb){
			$request->setCoverURL($thumb);
		}
		//$request->setIP("127.0.0.1");
		if($tag){
			$request->setTags($tag);
		}
		$request->setCateId(0);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 视频刷新
	 * @参数 $videoid 视频ID
	**/
	public function refresh_upload_video($videoid)
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\RefreshUploadVideoRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setVideoId($videoid);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 取得视频信息
	 * @参数 $videoid 视频ID
	**/
	public function video_delete($videoid)
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\DeleteVideoRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setVideoIds($videoid);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 取得视频信息
	 * @参数 $videoid 视频ID
	**/
	public function video_info($videoid)
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\GetVideoInfoRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setVideoId($videoid);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 视频列表
	 * @参数 $pageid 页码
	 * @参数 $psize 每次查询数量
	 * @参数 $starttime 开始时间
	 * @参数 $endtime 结束时间
	**/
	public function video_list($pageid=1,$psize=30,$starttime='',$endtime='')
	{
		$request = new vod\GetVideoListRequest();
		$request->setPageNo($pageid);
		$request->setPageSize($psize);
		if($starttime){
			if(substr($starttime,-1) != 'Z'){
				$starttime .= 'Z';
			}
			$request->setStartTime($starttime);
		}
		if($endtime){
			if(substr($endtime,-1) != 'Z'){
				$endtime .= 'Z';
			}
			$request->setEndTime($endtime);
		}
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	public function play_auth($videoid)
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\GetVideoPlayAuthRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setVideoId($videoid);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 错误返回
	 * @参数 $error 错误内容
	 * @参数 $errid 错误ID
	 * @返回 数组
	**/
	private function error($error='',$errid=0)
	{
		if(!$error){
			$error = '异常';
		}
		$array = array('status'=>false,'error'=>$error);
		if($errid){
			$array['errid'] = $errid;
		}
		return $array;
	}

	/**
	 * 成功时返回的结果
	 * @参数 $info 返回的内容，支持字串，数组，及空
	**/
	private function success($info='')
	{
		$array = array('status'=>true);
		if($info != ''){
			$array['info'] = $info;
		}
		return $array;
	}
}
