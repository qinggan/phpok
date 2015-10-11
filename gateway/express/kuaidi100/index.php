<?php
/*****************************************************************************************
	文件： express/kuaidi100/index.php
	备注： 快递100查询接口
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月19日 18时05分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$express || !$rs || !$rs['code']){
	return false;
}
$ext = ($express['ext'] && is_string($express['ext'])) ? unserialize($express['ext']) : array();
$postdata = "id=".$ext['kd_id'];
$postdata.= "&com=".$ext['kd_com']."&nu=".$rs['code'];
$postdata.= "&valicode=1";
$ch = curl_init(); //创建一个curl
curl_setopt($ch, CURLOPT_URL, 'http://api.kuaidi100.com/api');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$output = curl_exec($ch);
if(!$output){
	$this->json('远程获取数据失败');
}
$curl_info = curl_getinfo($ch);
if($curl_info['http_code'] != '200'){
	$this->json('远程获取数据失败');
}
curl_close($ch);
$tmpinfo = $this->lib('json')->decode($output);
if(!$tmpinfo){
	$this->json('检索异常');
}
if(!$tmpinfo['status'] || $tmpinfo['status'] == 2){
	$this->json($tmpinfo['message']);
}
$array = array('is_end'=>false);
//0：在途，即货物处于运输过程中；
//1：揽件，货物已由快递公司揽收并且产生了第一条跟踪信息；
//2：疑难，货物寄送过程出了问题；
//3：签收，收件人已签收；
//4：退签，即货物由于用户拒签、超区等原因退回，而且发件人已经签收；
//5：派件，即快递正在进行同城派件；
//6：退回，货物正处于退回发件人的途中；
$tmp = array(3,4,6);
if($tmpinfo['state'] && in_array($tmpinfo['state'],$tmp)){
	$array['is_end'] = true;
}

if($tmpinfo['data']){
	$array['content'] = array();
	foreach($tmpinfo['data'] as $key=>$value){
		$tmp = array('time'=>$value['time'],'content'=>$value['context']);
		$array['content'][] = $tmp;
	}
}
$array['status'] = true;
return $array;
?>