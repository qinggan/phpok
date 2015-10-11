<?php
/*****************************************************************************************
	文件： express/zjs/index.php
	备注： 获取数据
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月07日 15时45分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$express || !$rs || !$rs['code']){
	return false;
}
$ext = ($express['ext'] && is_string($express['ext'])) ? unserialize($express['ext']) : array();
$rdm1 ="0000" ;
$rdm2 ="0000" ;
$clientFlag = $ext['logisticProviderID'];
$xml ="<BatchQueryRequest><logisticProviderID>".$ext['logisticProviderID']."</logisticProviderID>";
$xml.= "<orders><order><mailNo>".$rs['code']."</mailNo></order></orders></BatchQueryRequest>";
$strSeed = $ext['keyseed'];//客户密钥
$strConst = $ext['fixed_string'];//常量值
$str = $rdm1.$clientFlag.$xml.$strSeed.$strConst.$rdm2;
$str = mb_convert_encoding($str, "UTF-8", "GBK"); 
$strVerifyData=$rdm1.substr(md5($str),7,21).$rdm2;//生成密钥
$postdata='clientFlag='.$clientFlag.'&xml='.($xml).'&verifyData='.$strVerifyData;

$ch = curl_init(); //创建一个curl
// 2. 设置选项，包括URL
curl_setopt($ch, CURLOPT_URL, "http://edi.zjs.com.cn/svst/tracking.asmx/Get");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
// 3. 执行并获取HTML文档内容
$output = curl_exec($ch);
if(!$output){
	$this->json('远程获取数据失败');
}
$curl_info = curl_getinfo($ch);
if($curl_info['http_code'] != '200'){
	$this->json('远程获取数据失败');
}
curl_close($ch);
$output=str_replace(array('&lt;', '&gt;'), array('<','>'),$output);
$xmlinfo = $this->lib('xml')->read($output,false);
if(!$xmlinfo){
	$output=substr($output,79);
	$output=str_replace('</string>','',$output);
	$this->json($output);
}
$logisticProviderID=$xmlinfo['logisticProviderID'];//客户标识
$orders=$xmlinfo['orders'];
$order=$orders['order'];
$steps=$order['steps'];
$step=$steps['step'];
$mailNo=$order['mailNo'];//运单号
$orderStatus=$order['orderStatus'];//当前订单状态，订单状态值：GOT 物流公司已经取件、SIGNED 订单已经签收、FAILED 订单签收失败
$statusTime=$order['statusTime'];//当前状态时间
$tmplist = array();
if($step && is_array($step)){
	foreach($step as $key=>$val){
		$tmp = array('time'=>$val['acceptTime'],'content'=>$val['acceptAddress']);
		$tmplist[] = $tmp;
	}
}
$is_end = false;
if($orderStatus=="SIGNED"){
	$is_end = true;
	$last['time'] = $statusTime;
	$last['content'] = '订单已经签收，签收人是'.$order['signinPer'];
	$tmplist[] = $last;
	return $tmplist;
}elseif($orderStatus == 'GOT'){
	$tmp = array('time'=>$statusTime,'content'=>'物流公司已取件');
	$tmplist[] = $tmp;
}elseif($orderStatus == 'FAILED'){
	$tmp = array('time'=>$statusTime,'content'=>'订单签收失败，原因说明：'.$order['error']);
	$tmplist[] = $tmp;
}else{
	$tmp = array('time'=>date("Y-m-d H:i:s",$this->time),'content'=>'查无结果！请检查运单号是否正确，若正确，原因可能为：物流公司还没有录入信息，请4小时后再查询');
	$tmplist[] = $tmp;
}
return array('is_end'=>$is_end,'content'=>$tmplist,'status'=>true);
