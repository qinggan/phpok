<?php
require(dir(__FILE__).'/secache.php');
class secache_no_flock extends secache{

    function secache_no_flock(){
        parent::secache();
        $this->__support_usleep = version_compare(PHP_VERSION,5,'>=')?20:1;
    }

    function lock($is_block,$whatever=false){

        ignore_user_abort(1);
        $lockfile = $this->_file . '.lck';

        if (file_exists($lockfile)) {
            if (time() - filemtime($lockfile) > 5){
               unlink($lockfile);
            }elseif(!$is_block){
                return false;
            }
        }

        $lock_ex = @fopen($lockfile, 'x');
        for ($i=0; ($lock_ex === false) && ($whatever || $i < 20); $i++) {
           clearstatcache();
           if($this->__support_usleep==1){
               usleep(rand(9, 999));
           }else{
               sleep(1);
           }
           $lock_ex = @fopen($lockfile, 'x');
        }

        return ($lock_ex !== false);
    }

    function unlock(){
        ignore_user_abort(0);
        return unlink($this->_file.'.lck');
    }
}
?>