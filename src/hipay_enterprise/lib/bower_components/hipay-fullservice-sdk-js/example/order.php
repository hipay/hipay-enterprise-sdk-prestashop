<?php

namespace HiPay\Fullservice\TokenizationExample;

require 'credentials.php';
require __DIR__ . '/vendor/autoload.php';

$config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($credentials['private']['username'], $credentials['private']['password']);
$clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
$gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

//Instantiate order request
$orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();
$orderRequest->orderid = uniqid('test_', true);
$orderRequest->operation = 'Sale';
$orderRequest->description = 'Test transaction executed from the Direct Post/JavaScript example.';
$orderRequest->payment_product = 'cb';
$orderRequest->amount = 125.5;
$orderRequest->currency = 'EUR';
$orderRequest->paymentMethod = new \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod();
$orderRequest->paymentMethod->cardtoken = $_GET['token'];
$orderRequest->paymentMethod->eci = 7;
$orderRequest->paymentMethod->authentication_indicator = 0;

try {

	http_response_code(200);

	$transaction = $gatewayClient->requestNewOrder($orderRequest);	

	echo "<div>Transaction successfully created with the following info:</div>";
	echo "<ul><li><b>Transaction unique ID</b>: ". $transaction->getTransactionReference() . "</li>";
	echo "<li><b>Order ID</b>: ". $transaction->getOrder()->getId() . "</li></ul>";
	echo "<div>You can find all the transaction details in your HiPay Fullservice merchant back office.</div>";
	echo "<div>&nbsp;</div>";
	echo "<div><a href=".">Click here to retry the whole process.</a></div>";

}

catch (\Exception $e) {

	http_response_code(400);

	echo "<div>An error occured when making the transaction:</div>";
	echo "<div><b>".$e->getMessage()."</b></div>";
}

