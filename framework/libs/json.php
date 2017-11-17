<?php
/**
 * JSON 编码解码操作
 * @package phpok\framework\libs
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK 开源授权协议：GNU Lesser General Public License
 * @时间 2017年11月14日
**/

class json_lib
{
	public function __construct()
	{
		
	}

	/**
	 * 将数组转成JSON数据
	 * @参数 $var 要转换的数据
	 * @参数 $unicode 是否转换中文等非字母数据
	**/
	public function encode($var,$unicode=true)
	{
		if(function_exists("json_encode")){
			if(!$unicode){
				return json_encode($var,JSON_UNESCAPED_UNICODE);
			}else{
				return json_encode($var);
			}
		}
	}

	/**
	 * JSON数据转化为数组或对像
	 * @参数 $str 要解码的数据
	 * @参数 $is_array 是否转成数组，为否将转成对像
	**/
	public function decode($str,$is_array=true)
	{
		if(!$str){
			return false;
		}
		return json_decode($str,$is_array);
	}
}