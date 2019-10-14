<?php
/**
 * phpexcel 类操作
 * @package phpok\extension
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年12月30日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class phpexcel_lib
{
	public function __construct()
	{
		require_once 'phar://' . ROOT . 'extension/phpexcel/phpexcel.phar';
		//PHP版
		//require_once ROOT . 'extension/phpexcel/phpexcel/PHPExcel.php';
	}

	public function excelTime($date, $time = false)
	{
		if(function_exists('GregorianToJD')){
			if (is_numeric( $date )) {
				$jd = GregorianToJD( 1, 1, 1970 );
				$gregorian = JDToGregorian( $jd + intval ( $date ) - 25569 );
				$date = explode( '/', $gregorian );
				$date_str = str_pad( $date [2], 4, '0', STR_PAD_LEFT )
				."-". str_pad( $date [0], 2, '0', STR_PAD_LEFT )
				."-". str_pad( $date [1], 2, '0', STR_PAD_LEFT )
				. ($time ? " 00:00:00" : '');
				return $date_str;
			}
		}else{
			$date=$date>25568 ? $date+1:25569;
			$ofs=(70 * 365 + 17+2) * 86400;
			$date = date("Y-m-d",($date * 86400) - $ofs).($time ? " 00:00:00" : '');
		}
		return $date;
	}
}
