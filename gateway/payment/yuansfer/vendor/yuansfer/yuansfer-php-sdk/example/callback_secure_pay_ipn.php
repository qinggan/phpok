<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Yuansfer\Yuansfer;

class SampleOrder {
    /**
     * find order with reference
     *
     * @param $ref
     *
     * @return SampleOrder
     */
    public static function find($ref)
    {
        return new SampleOrder($ref);
    }

    private $ref;

    public function __construct($ref)
    {
        $this->ref = $ref;
    }

    public function success()
    {
        $now = date('Y-m-d H:i:s');

        file_put_contents('ipn_callback.log', "{$now} order {$this->ref} is success\n", FILE_APPEND);
        file_put_contents('ipn_callback.log', 'POST: ' . print_r($_POST, true) . "\n", FILE_APPEND);
    }

    public function failed()
    {
        $now = date('Y-m-d H:i:s');

        file_put_contents('ipn_callback.log', "{$now} order {$this->ref} is failed\n", FILE_APPEND);
        file_put_contents('ipn_callback.log', 'POST: ' . print_r($_POST, true) . "\n", FILE_APPEND);
    }
}

//init
$config = include __DIR__ . '/yuansfer_config.php';
$yuansfer = new Yuansfer($config);
$yuansfer->setTestMode();

if (!$yuansfer->verifyIPN()) {
    // verifySign not verified
    header('HTTP/1.1 503 Service Unavailable', true, 503);

    exit;
}

//find order use $_POST['reference']
$order = SampleOrder::find($_POST['reference']);

if ($_POST['status'] === 'success') {
    // process of order success
    $order->success();

    // must output: "success", otherwise yuansfer will be considered a failure
    echo 'success';
} else {
    // process of order failed
    $order->failed();
}




