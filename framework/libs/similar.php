<?php
/**
 * 文本相似度匹配，可用于防范垃圾群发
 * 特别说明，此方法源自网络别人写好的Class，目前没有搜到是谁第一个写的
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年1月7日
**/

class similar_lib
{
	var $str1;
	var $str2;
	var $c = array();

	/*
		返回串一和串二的最长公共子序列
	*/
	public function getLCS($str1, $str2, $len1 = 0, $len2 = 0) {
		$this->str1 = $str1;
		$this->str2 = $str2;
		if ($len1 == 0){
			$len1 = strlen($str1);
		}
		if ($len2 == 0){
			$len2 = strlen($str2);
		}
		$this->initC($len1, $len2);
		return $this->printLCS($this->c, $len1 - 1, $len2 - 1);
	}
	
	/*
	 * 返回两个串的相似度
	*/
	public function ssim($str1='', $str2='')
	{
		if(!$str1 || !$str2){
			return false;
		}
		$str1 = strip_tags($str1);
		$str2 = strip_tags($str2);
		if(!$str1 || !$str2){
			return false;
		}
		$len1 = strlen($str1);
		$len2 = strlen($str2);
		$len = strlen($this->getLCS($str1, $str2, $len1, $len2));
		return round(($len * 2 / ($len1 + $len2))*100,2);
	}
	
	private function initC($len1, $len2)
	{
		for ($i = 0; $i < $len1; $i++){
			$this->c[$i][0] = 0;
		}
		for ($j = 0; $j < $len2; $j++){
			$this->c[0][$j] = 0;
		}
		for ($i = 1; $i < $len1; $i++) {
			for ($j = 1; $j < $len2; $j++) {
				if ($this->str1[$i] == $this->str2[$j]) {
					$this->c[$i][$j] = $this->c[$i - 1][$j - 1] + 1;
				} else if ($this->c[$i - 1][$j] >= $this->c[$i][$j - 1]) {
					$this->c[$i][$j] = $this->c[$i - 1][$j];
				} else {
					$this->c[$i][$j] = $this->c[$i][$j - 1];
				}
			}
		}
	}
	
	private function printLCS($c, $i, $j) 
	{
		if ($i == 0 || $j == 0) {
			if ($this->str1[$i] == $this->str2[$j]) return $this->str2[$j];
			else return "";
		}
		if ($this->str1[$i] == $this->str2[$j]) {
			return $this->printLCS($this->c, $i - 1, $j - 1).$this->str2[$j];
		} else if ($this->c[$i - 1][$j] >= $this->c[$i][$j - 1]) {
			return $this->printLCS($this->c, $i - 1, $j);
		} else {
			return $this->printLCS($this->c, $i, $j - 1);
		}
	}
} 

/**
 * 在 PHPOK 里调用方法 $this->lib('similar')->ssim('文本一','文本二');
 * 返回的值如果一般都是小于100，建议设置高于70为同样的贴子
**/