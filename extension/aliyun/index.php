<?php
/**
 * 阿里云SDK信息，请配合插件或是网关路由使用
 * @package phpok\extension\aliyun
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年02月27日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
include_once dirname(__FILE__).'/aliyun-php-sdk-core/Config.php';
include_once dirname(__FILE__).'/aliyun-php-sdk-mns/mns-autoloader.php';
use AliyunMNS\Client;
use AliyunMNS\Topic;
use AliyunMNS\Constants;
use AliyunMNS\Model\MailAttributes;
use AliyunMNS\Model\SmsAttributes;
use AliyunMNS\Model\BatchSmsAttributes;
use AliyunMNS\Model\MessageAttributes;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\PublishMessageRequest;

use Dm\Request\V20151123 as Dm;
use vod\Request\V20170321 as vod;
class aliyun_lib
{
	private $access_key = '';
	private $access_secret = '';
	private $access_id = '';
	private $regoin_id = 'cn-hangzhou';
	private $signature = '锟铻科技';
	private $sms_template_id = 0;
	private $end_point = ''; //节点
	private $mns_title = '';

	private $dm_account = '';
	private $dm_name = '锟铻科技';

	private $client;

	public function __construct()
	{
		//
	}

	public function mns_title($val='')
	{
		if($val){
			$this->mns_title = $val;
		}
		return $this->mns_title;
	}

	public function end_point($val='')
	{
		if($val){
			$this->end_point = $val;
		}
		return $this->end_point;
	}

	public function regoin_id($val='')
	{
		if($val){
			$this->regoin_id = $val;
		}
		return $this->regoin_id;
	}

	public function access_id($val='')
	{
		if($val){
			$this->access_id = $val;
		}
		return $this->access_id;
	}

	public function access_key($val='')
	{
		if($val){
			$this->access_key = $val;
		}
		return $this->access_key;
	}

	public function access_secret($val='')
	{
		if($val){
			$this->access_secret = $val;
		}
		return $this->access_secret;
	}

	public function signature($val='')
	{
		if($val){
			$this->signature = $val;
		}
		return $this->signature;
	}

	public function sms_template_id($val='')
	{
		if($val){
			$this->sms_template_id = $val;
		}
		return $this->sms_template_id;
	}

	public function dm_account($val='')
	{
		if($val){
			$this->dm_account = $val;
		}
		return $this->dm_account;
	}

	public function dm_name($val='')
	{
		if($val){
			$this->dm_name = $val;
		}
		return $this->dm_name;
	}

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
		$iClientProfile = DefaultProfile::getProfile($this->access_id, $this->access_key,$this->access_secret);
		$client = new DefaultAcsClient($iClientProfile);    
		$request = new Dm\SingleSendMailRequest();     
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

	public function sms($mobile,$data='')
	{
		if(!$mobile){
			return $this->error(P_Lang('未指定手机号'));
		}
		if(!$this->sms_template_id){
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

		if(!$this->mns_title){
			return $this->error(P_Lang('未配置主题'));
		}

		$this->client = new Client($this->end_point, $this->access_key,$this->access_secret);
		$topic = $this->client->getTopicRef($this->mns_title);
		$batchSmsAttributes = new BatchSmsAttributes($this->signature, $this->sms_template_id);
		$batchSmsAttributes->addReceiver($mobile,$data);
		$messageAttributes = new MessageAttributes(array($batchSmsAttributes));
		$request = new PublishMessageRequest('smsmessage', $messageAttributes);
		try{
			$res = $topic->publishMessage($request);
			return $this->success();
		}
		catch (MnsException $e){
			return $this->error($e->getMessage(),$e->getMnsErrorCode());
		}
	}

	public function client()
	{
		if(!$this->regoin_id){
			return $this->error(P_Lang('未设置 Regoin ID'));
		}
		if(!$this->access_id){
			return $this->error(P_Lang('未指定Access Key ID'));
		}
		if(!$this->access_secret){
			return $this->error(P_Lang('未指定Access Key Secret'));
		}
		if(!$this->signature){
			return $this->error(P_Lang('未配置标签'));
		}
		$iClientProfile = DefaultProfile::getProfile($this->regoin_id, $this->access_id,$this->access_secret);
		$this->client = new DefaultAcsClient($iClientProfile);
		return $this->client;
	}
	
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
