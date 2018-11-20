<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Yuansfer\Yuansfer;
use Yuansfer\Exception\YuansferException;

//init
$config = include __DIR__ . '/yuansfer_config.php';
$yuansfer = new Yuansfer($config);

// recommend: pass the test first
$yuansfer->setTestMode();

// create api
$api = $yuansfer->createSecurePay();

// set api parameters
$api
    ->setAmount(0.01) //The amount of the transaction.
    ->setCurrency('USD') // The currency, USD, CAD supported yet.
    ->setVendor('alipay') // The payment channel, alipay, wechatpay, unionpay, enterprisepay are supported yet.
    ->setTerminal('ONLINE') // ONLINE, WAP
    ->setReference(str_replace('.', '_', uniqid('test_', true))) //The unque ID of client’s system.
    ->setIpnUrl('https://domain/example/callback_secure_pay_ipn.php') // The asynchronous callback method. https only
    ->setCallbackUrl('https://domain/example/callback_secure_pay.php' . // The Synchronous callback method.
        '?yuansferId={yuansferId}&status={status}&amount={amount}&reference={reference}&note={note}'); // query name can change, like: id={yuansferId}&num={amount}

//optional parameters
$api->setDescription('description info') // it will be displayed on the card charge
    ->setNote('note info')
    ->setTimeout(120);  // units are minutes, default is 120

try {
    // send to api get response
    // SecurePay api return html
    echo $api->send();
} catch (YuansferException $e) {
    // required param is empty
    if ($e instanceof \Yuansfer\Exception\RequiredEmptyException) {
        $message = 'The param: ' . $e->getParam() . ' is empty, in API: ' . $e->getApi();
    }

    // http connect error
    if ($e instanceof \Yuansfer\Exception\HttpClientException) {
        $message = $e->getMessage();
    }

    // http response status code < 200 or >= 300, 301 and 302 will auto redirect
    if ($e instanceof \Yuansfer\Exception\HttpErrorException) {
        /** @var \Httpful\Response http response */
        $response = $e->getResponse();
    }
}
