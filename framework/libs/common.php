<?php
/**
 * 常用信息调用
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class common_lib
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		//
	}

	/**
	 * 取得IP地址
	**/
	public function ip()
	{
		$cip = (isset($_SERVER['HTTP_CLIENT_IP']) AND $_SERVER['HTTP_CLIENT_IP'] != "") ? $_SERVER['HTTP_CLIENT_IP'] : false;
		$rip = (isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : false;
		$fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'] != "") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;
		$ip = "0.0.0.0";
		if($cip && $rip){
			$ip = $cip;
		}elseif($rip){
			$ip = $rip;
		}elseif($cip){
			$ip = $cip;
		}elseif($fip){
			$ip = $fip;
		}

		if (strstr($ip, ',')){
			$x = explode(',', $ip);
			$ip = end($x);
		}

		if (!preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip)){
			$ip = '0.0.0.0';
		}
		return $ip;
	}

	/**
	 * CSS格式化或清理，去除无意义的CSS
	 * @参数 $info 要格式化的CSS信息
	**/
	public function css_format($info='')
	{
		if(!$info) return false;
		$list = explode(';',$info);
		$array = array();
		foreach($list AS $key=>$value){
			$ext = explode(':',$value);
			if($ext[0] && $ext[1]){
				$array[$ext[0]] = $ext[1];
			}
		}
		$info = array();
		foreach($array AS $key=>$value){
			$info[] = $key.':'.$value;
		}
		return implode(";",$info);
	}

	/**
	 * 邮箱合法性验证，此验证仅仅只是简单判断
	 * @参数 $email 邮箱
	 * @返回 true 或 false
	**/
	public function email_check($email)
	{
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex){
			return false;
		}
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if($localLen < 1 || $localLen > 64){
			return false;
		}
		if($domainLen < 1 || $domainLen > 255){
			return false;
		}
		if($local[0] == '.' || $local[$localLen-1] == '.'){
			return false;
		}
		if(preg_match('/\\.\\./', $local)){
			return false;
		}
		if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){
			return false;
		}
		if(preg_match('/\\.\\./', $domain)){
			return false;
		}
		if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local))){
			if(!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local))){
				return false;
			}
		}
		return true;
	}

	/**
	 * 身份证验证，仅限中国大陆
	 * @参数 $idcard 身份证号码
	 * @返回 true 或 false
	**/
	public function idcard_check($idcard)
	{
		if(!$idcard || strlen($idcard) != 15 && strlen($idcard) != 18){
			return false;
		}
		if(strlen($idcard) == 15){
			$idcard = $this->_idcard_15to18($idcard);
		}
		//身份
		$year = substr($idcard,6,4);
		$month = 'a'.substr($idcard,10,2);
		$day = substr($idcard,12,2);
		//日期永远不可能超过31天
		if($day>31){
			return false;
		}
		//月份永远不可能超过13，也不可能小于00
		//前面加一个字符a，让数据以字符串形式来比较
		$array = array('a01','a02','a03','a04','a05','a06','a07','a08','a09','a10','a11','a12');
		if(!in_array($month,$array)){
			return false;
		}
		//年份时间的比较，考虑服务器时间异常，及存档问题，故将比较年份适当增减范围
		//正常情况下，正确的身份证这一关是能验证通过的
		$minyear = date("Y") - 300;
		$maxyear = date("Y") + 20;
		if($year<$minyear || $year>$maxyear){
			return false;
		}
		//验证身份证是否合法
		$idcard_base = substr($idcard, 0, 17); 
		if ($this->_idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){ 
			return false; 
		}
		return true;
	}

	/**
	 * 升级15位身份证到18位
	 * @参数 $idcard 15位号码
	 * @返回 18位身份证号码
	**/
	private function _idcard_15to18($idcard)
	{
		if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){ 
			$idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9); 
		}else{ 
			$idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9); 
		}
		$idcard = $idcard . $this->_idcard_verify_number($idcard);
		return $idcard;
	}

	/**
	 * 15位身份证升级到17位后，通过规则，取得第18位数字
	 * @参数 $idcard_base 15位号码
	 * @返回 第18位号码
	**/
	private function _idcard_verify_number($idcard_base) 
	{ 
		//加权因子 
		$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); 
		//校验码对应值 
		$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); 
		$checksum = 0; 
		for($i = 0; $i < strlen($idcard_base); $i++){ 
			$checksum += substr($idcard_base, $i, 1) * $factor[$i]; 
		}
		$mod = $checksum % 11; 
		$verify_number = $verify_number_list[$mod]; 
		return $verify_number; 
	}

	/**
	 * 是否电话判断
	 * @参数 $tel 电话号码
	 * @参数 $type 类型，支持 mobile tel 和 400电话，留空只要一个符合即通过
	 * @返回 true 或 false
	**/
	public function tel_check($tel,$type='')
	{
		$regxArr = array(
			'mobile'  =>  '/^(\+?86-?)?(18|15|13|17)[0-9]{9}$/',
			'tel' =>  '/^(\+?86-?)?(010|02\d{1}|0[3-9]\d{2})-?\d{7,9}(-\d+)?$/',
			'400' =>  '/^400(-?\d{3,4}){2}$/',
		);
		if($type && isset($regxArr[$type])){
			return preg_match($regxArr[$type], $tel) ? true:false;
		}
		foreach($regxArr as $regx){
			if(preg_match($regx, $tel )){
				return true;
			}
		}
		return false;
	}

	/**
	 * 获取随机字串
	 * @参数 $length 长度，默认是10
	 * @参数 $type 类型，支持：letter 字母，number 数字，all 全部
	 * @返回 随机字符
	 * @更新时间 2016年07月26日
	**/
	public function str_rand($length=10,$type='')
	{
		$a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		if($a == 'number'){
			$a = '0123456789';
		}
		if($a == 'letter'){
			$a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		}
		$maxlength = strlen($a)-1;
		$rand_str = '';
		for($i=0;$i<$length;++$i){
			$rand_str .= $a[rand(0,$maxlength)];
		}
		return $rand_str;
	}

	/**
	 * 字节数格式化为带单位的数据，默认保留两位小数
	 * @参数 $a 要格式化的数据
	 * @参数 $ext 保留位数
	 * @参数 $min_kb 小于1KB是否直接显示1kb还是显示字节
	 * @返回 格式化后的字串
	**/
	public function num_format($a='',$ext=2,$min_kb=true)
	{
		if(!$a || $a == 0){
			return false;
		}
		if($a <= 1024){
			if($min_kb){
				$a = "1 KB";
			}else{
				return $a." B";
			}
		}elseif($a>1024 && $a<(1024*1024)){
			$a = round(($a/1024),$ext)." KB";
		}elseif($a>=(1024*1024) && $a<(1024*1024*1024)){
			$a = round(($a/(1024*1024)),$ext)." MB";
		}else{
			$a = round(($a/(1024*1024*1024)),$ext)." GB";
		}
		return $a;
	}
}
?>