<?php
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    // print custom success html
    echo 'Pay Success';
} else {
    // do some thing about order failed
    echo 'Pay failed';
}

echo '<br><br>Debug:', nl2br(print_r($_GET, true));