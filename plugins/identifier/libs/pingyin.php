<?php

/*
	[THEN CMS] (C) 2000-2010 长行.
	$Id: pinyin.class.php 001 2009-3-16 03:16:32Z 长行 $
  QQ:657653135; EMAIL:657653135@qq.com.
*/

class pingyin{

  /*
  是否将拼音文件读取到内存内，损耗少许内存,几百KB的样子，速度可以略有提升，
  */
  var $isMemoryCache = 1;

  /*
  是否只获取首字母
  */
  var $isFrist = 0;

 	/*
 	拼音矩阵文件地址
 	*/
	var $path = "py.qdb";

  /*
  内存拼音矩阵
  */
  var $MemoryCache;

  /*
   拼音文件句柄
  */
  var $handle;

	/*
	转换发生错误盒子
	*/
  var $errorMsgBox;

  /*
  转换结果
  */
  var $result;


  var $array = array();
	var $n_t = array("ā" => "a","á" => "a","ǎ" => "a","à" => "a","ɑ" => "a",
	  "ō" => "o","ó" => "o","ǒ" => "o","ò" => "o",
	  "ē" => "e","é" => "e","ě" => "e","è" => "e","ê" => "e",
	  "ī" => "i","í" => "i","ǐ" => "i","ì" => "i",
	  "ū" => "u","ú" => "u","ǔ" => "u","ù" => "u",
	  "ǖ" => "v","ǘ" => "v","ǚ" => "v","ǜ" => "v","ü" => "v"
	);

	/*
	转换入口
  @params $str 所需转换字符,$isToneMark 是否保留音标  $suffix 尾缀,默认为空格
  */
  function ChineseToPinyin($str,$isToneMark = 0,$suffix = ""){
    $this->py($str,$isToneMark,$suffix);
    return $this -> result;
  }

  function get(){
  	return $this -> result;
  }


  function py($str,$n = 0,$s = ""){
    $strLength = strlen($str);
		if($strLength == 0){ return "";  }
  	$this->result = "";
    if(is_array($str)){
      foreach($str as $key => $val){
		    $str[$key] = $this->py($val,$n,$s);
		  }
		  return;
		}

    if(empty($this->handle)){
	    if(!file_exists($this->path)){
	      $this->addOneErrorMsg(1,"拼音文件路径不存在");
	      return false;

		  }

      if(is_array($str)){
		    foreach($str as $key => $val){
		      $str[$key] = $this->py($val,$n,$s);
		    }
	    }


      if($this -> isMemoryCache){
        if(!$this->memoryCache){
    	    $this->memoryCache = file_get_contents($this->path);
		      for($i = 0 ; $i < $strLength ; $i++){
            $ord1 = ord(substr($str,$i,1));
            if($ord1 > 128){
              $ord2 = ord(substr($str, ++$i, 1));
              if(!isset($this->array[$ord1][$ord2])){
                $leng = ($ord1 - 129) * ((254 - 63) * 8 + 2) + ($ord2 - 64) * 8;
                $this->array[$ord1][$ord2] = trim(substr($this->memoryCache,$leng,8));
              }
              $strtrLen = $this->isFrist ? 1 : 8;
              $this->result .= substr($this ->array[$ord1][$ord2],0,$strtrLen).$s;
      }else{
        $this->result .= substr($str,$i,1);
      }

    }
        }
      }else{
        $this->handle = fopen($this->path,"r");
		    for($i = 0 ; $i < $strLength ; $i++){
          $ord1 = ord(substr($str,$i,1));
          if($ord1 > 128){
            $ord2 = ord(substr($str, ++$i, 1));
            if(!isset($this->array[$ord1][$ord2])){
              $leng = ($ord1 - 129) * ((254 - 63) * 8 + 2) + ($ord2 - 64) * 8;
              fseek($this -> handle,$leng);
                $this->array[$ord1][$ord2] = trim(fgets($this->handle,8));

            }
          $strtrLen = $this->isFrist ? 1 : 8;

          $this->result .= substr($this ->array[$ord1][$ord2],0,$strtrLen).$s;
        }else{ $this->result .= substr($str,$i,1); }

        }
      }

    if(!$n){ $this -> result = strtr($this -> result,$this -> n_t);}
    }
  }
   function addOneErrorMsg($No,$reason){

    $this->errorMsgBox[] = "<b>Error:</b>" . $No . "," . $reason;
  }

  function showErrorMsg(){

    foreach($this->errorMsgBox as $val){
      echo $val."\r\n\r\n</br></br>";
    }
  }

  function __destruct(){
    if(is_array($this->errorMsgBox)){
  	  $this->showErrorMsg();
  	}
  }

}

?>
