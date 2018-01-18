<?php
//$tmp = array();
//$tmp['demo2']['demo3']['demo4'] = array();
//$ok = $tmp;
//$tmp = array();
//$tmp['demo5'] = $ok;
//echo "<pre>".print_r(json_encode($tmp),true)."</pre>";
/*$string = 'demo1.demo2.demo3.demo4';
$list = explode(".",$string);
krsort($list);
$tmp = array();
$i=0;
$total = count($list);
foreach($list as $key=>$value){
	if($i<1){
		$tmp[$value] = '123';
	}else{
		
		if(($i+1) == $total){
			$_SESSION[$value] = $tmp;
		}else{
			$ok = array();
			$ok[$value] = $tmp;
			$tmp = $ok;
		}
		//$tmp[$value] = $tmp;
	}
	$i++;
}
echo "<pre>".print_r($_SESSION,true)."</pre>";*/


