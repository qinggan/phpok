<?php
/**
 * JSON 编码解码操作
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年11月14日
**/

class json_lib
{
	/**
	 * 将数组转成JSON数据
	 * @参数 $var 要转换的数据
	 * @参数 $unicode 是否转换中文等非字母数据
	 * @参数 $pretty 设置为true时表示优雅输出，可视效果
	**/
	public function encode($var,$unicode=false,$pretty=false)
	{
		if(version_compare(PHP_VERSION, '5.4.0', '<')){
			return json_encode($var);
		}
		if(!$unicode){
			if($pretty){
				return json_encode($var,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
			}
			return json_encode($var,JSON_UNESCAPED_UNICODE);
		}else{
			if($pretty){
				return json_encode($var,JSON_PRETTY_PRINT);
			}
			return json_encode($var);
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
		if($this->json_validate($str)){
			return json_decode($str,$is_array);
		}
		return false;
	}

	public function json_validate($string) {
        if (is_string($string)) {
            @json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    public function is_json($string)
    {
	    return $this->json_validate($string);
    }
}