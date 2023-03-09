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

include_once "phar://".dirname(__FILE__).'/aliyun.phar/aliyun-php-sdk-core/Config.php';
include_once "phar://".dirname(__FILE__).'/aliyun.phar/aliyun-oss-php-sdk/autoload.php';

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

use Sts\Request\V20150401 as sts;

use OSS\OssClient;
use OSS\Core\OssException;

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

	private $oss_client;
	private $oss_bucket;

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
		$iClientProfile = DefaultProfile::getProfile($this->regoin_id, $this->access_key,$this->access_secret);
		$this->client = new DefaultAcsClient($iClientProfile);
		return $this->client;
	}

	/**
	 * 上传视频文件
	 * @参数 $filename 文件名
	**/
	public function create_upload_video($filename)
	{
		if(!$filename){
			return false;
		}
		$tmp = explode(".",$filename);
		$total = count($tmp);
		$ext = $tmp[($total-1)];
		$title = str_replace('.'.$ext,'',$filename);
		$request = new vod\CreateUploadVideoRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setTitle($title);
		$request->setFileName($filename);
		$request->setFileSize(0);
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

	public function create_upload_image($filename)
	{
		if(!$filename){
			return false;
		}
		$tmp = explode(".",$filename);
		$total = count($tmp);
		$ext = $tmp[($total-1)];
		$title = str_replace('.'.$ext,'',$filename);
		$request = new vod\CreateUploadImageRequest();
		$request->setImageType('default');
		$request->setImageExt($ext);
		$request->setAcceptFormat('JSON');
		$request->setTitle($title);
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

	public function image_info($imageid)
	{
		if(!$imageid){
			return false;
		}
		$request = new vod\GetImageInfoRequest();
		$request->setAcceptFormat('JSON');
		$request->setImageId($imageid);
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

	public function image_delete($imageid,$type="ImageId")
	{
		$request = new vod\DeleteImageRequest();
		//根据ImageURL删除图片文件
		if(!in_array($type,array('VideoId','ImageId','ImageURL'))){
			$type = 'ImageId';
		}
		$request->setDeleteImageType($type);
		if($type == 'VideoId'){
			$request->setVideoId($imageid);
		}elseif($type == 'ImageURL'){
			$request->setImageURLs($imageid);
		}else{
			$request->setImageIds($imageid);
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

	public function live_push_forbid($stream)
	{
		//
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

	public function video_mezzanine($videoid,$type = 'cdn')
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\GetMezzanineInfoRequest();
		$request->setAcceptFormat('JSON');
		$request->setAuthTimeout(3600*24);
		$request->setVideoId($videoid);
		$request->setOutputType($type);
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
	 * 取得多个视频信息
	 * @参数 $videoid 视频ID
	**/
	public function video_infos($vids)
	{
		if(!$vids){
			return false;
		}
		if(is_array($vids)){
			$vids = implode(",",$vids);
		}
		$request = new vod\GetVideoInfosRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setVideoIds($vids);
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
	 * 获取源片信息
	**/
	public function video_base($videoid)
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\GetMezzanineInfoRequest();
		$request->setAcceptFormat('JSON');
		$request->setAuthTimeout(3600*24);
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

	public function play_url($videoid,$type='cdn')
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\GetPlayInfoRequest();
		$request->setAcceptFormat('JSON');
		$request->setAuthTimeout(3600*24);
		$request->setVideoId($videoid);
		$request->setOutputType($type);
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

	public function sts($role,$session_name='default',$expiretime=3600)
	{
		try{
			$request = new sts\AssumeRoleRequest();
			$request->setRoleSessionName($session_name);
			$request->setRoleArn($role);
			$request->setDurationSeconds($expiretime);
			$response = $this->client->getAcsResponse($request);
		}catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
        $rows = array();
        $rows['access_id'] = $response->Credentials->AccessKeyId;
        $rows['access_secret'] = $response->Credentials->AccessKeySecret;
        $rows['expiration'] = $response->Credentials->Expiration;
        $rows['token'] = $response->Credentials->SecurityToken;
        return $this->success($rows);
	}
	
	public function oss_client($access_key='',$access_secret='',$end_point='')
	{
		if($access_key){
			$this->access_key($access_key);
		}
		if($access_secret){
			$this->access_secret($access_secret);
		}
		if($end_point){
			$this->end_point($end_point);
		}
		try {
			$ossClient = new OssClient($this->access_key, $this->access_secret, $this->end_point, false);
		} catch (OssException $e) {
			return $this->error($e->getMessage());
		}
		$ossClient->setUseSSL(false);
		$this->oss_client = $ossClient;
		return $this->success();
	}

	public function oss_bucket($name='')
	{
		if($name){
			$this->oss_bucket = $name;
		}
		return $this->oss_bucket;
	}

	public function oss_delete($object)
	{
		if(!$this->oss_client){
			$this->oss_client();
		}
		try {
			$this->oss_client->deleteObject($this->oss_bucket, $object);
		} catch (OssException $e) {
			return $this->error($e->getMessage());
		}
		return $this->success();
	}

	public function oss_ico($object,$id,$ext)
	{
		if(!$this->oss_client){
			$this->oss_client();
		}
		$style = "image/resize,m_fill,w_200,h_200";
		$save_object = "res/_cache/".date("Ym/d/")."_".$id.".".$ext;
		$process = $style.'|sys/saveas,o_'.$this->oss_encode($save_object).',b_'.$this->oss_encode($this->oss_bucket);
		try {
			$info = $this->oss_client->processObject($this->oss_bucket, $object, $process);
		} catch (OssException $e) {
			return $this->error($e->getMessage());
		}
		return $this->success($save_object);
	}

	/**
	 * 对象文件数据编码
	**/
	public function oss_encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 * 检测对象文件是否存在
	**/
	public function oss_chk($object)
	{
		if(!$this->oss_client){
			$this->oss_client();
		}
		try {
			$exist = $this->oss_client->doesObjectExist($this->oss_bucket, $object);
		} catch (OssException $e) {
			return $this->error($e->getMessage());
		}
		return true;
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
