<?php
require('../secache/secache.php');
$cache = new secache;
$cache->workat('cachedata');

function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$begin_time = microtime_float();

for($i=0;$i<1000;$i++){

    $key = md5($i); //You must *HASH* it by your self
    $value = str_repeat('No. <strong>'.$i.'</strong> is <em style="color:red">great</em>! ',rand(1,10)); // must be a *STRING*

    $cache->store($key,$value);
}

echo '<h2>Insert x 1000 = ' .( microtime_float() - $begin_time) .' ms</h2>';
echo '<hr /><h2>test read</h2>';

for($i=0;$i<1000;$i+=200){

    $key = md5($i); //You must *HASH* it by your self
    if($cache->fetch($key,$value)){
        echo '<li>'.$key.'=>'.$value.'</li>';
    }else{
        echo '<li>Data get failed! <b>'.$key.'</b></li>';
    }
}
?>
