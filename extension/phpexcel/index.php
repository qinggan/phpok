<?php
/**
 * phpexcel 类操作
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年12月30日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
require_once 'phar://'.EXTENSION . 'phpexcel/phpexcel.phar';
require_once 'phar://'.EXTENSION . 'phpexcel/phpexcel.phar/PHPExcel/IOFactory.php';
class MyReadFilter implements PHPExcel_Reader_IReadFilter
{
    public function readCell($column, $row, $worksheetName = '') {
        // Read title row and rows 20 - 30
        if ($row == 1 || ($row >= 20 && $row <= 30)) {
            return true;
        }

        return false;
    }
}
class phpexcel_lib
{
	public function __construct()
	{
		//PHP版
		//require_once 'phar://'.EXTENSION . 'phpexcel/phpexcel.phar';
		//require_once 'phar://'.EXTENSION . 'phpexcel/phpexcel.phar/PHPExcel/IOFactory.php';
	}

	public function getTitle($file)
	{
		$basefile = basename($file);
		$tmp = explode(".",$basefile);
		$ext = $tmp[count($tmp)-1];
		$filetype = $ext == "xlsx" ? "Excel2007" : "Excel5";
		$cacheMethod = \PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '8MB');
		\PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		$objReader = \PHPExcel_IOFactory::createReader($filetype);
		$objReader->setReadDataOnly(true);
		$objReader->setReadFilter(new MyReadFilter());
		$objPHPExcel = $objReader->load($file);
		$currentSheet = $objPHPExcel->getSheet(0);
		$allColumn = $currentSheet->getHighestColumn();
		$allRow = $currentSheet->getHighestRow();
		$m = 0;
		$idlist = array();
		$t_i = \PHPExcel_Cell::columnIndexFromString($allColumn);
		for($i = 0;$i<$t_i;$i++){
			$str = "";
			$m = $i+65;
			$tmp_i = intval($m/91);
			if($tmp_i){
				$tm = chr($tmp_i+64);
				$str .= $tm;
				$str .= chr($m%91+65);
			}else{
				$str = chr($m);
			}
			$t = $str."1";
			$idlist[$str] = $currentSheet->getCell($t)->getValue();
		}
		if(!$idlist || count($idlist)<1){
			return false;
		}
		return $idlist;
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
