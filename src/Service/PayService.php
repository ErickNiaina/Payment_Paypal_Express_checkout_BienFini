<?php

namespace App\Service;


use PayPal\Api\Payment;
use PayPal\Rest\ApiContext;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;


class PayService{


	public function payApproved($payId,$payerId)
	{
		$ids = require_once(__DIR__ . '/../../config.php');
		
		$apiContext = new ApiContext(
			new OAuthTokenCredential(
				$ids['id'],
				$ids['secret']
			)
        );

        $offer = Offers::getOffers();
        $payment = Payment::get($payId,$apiContext);
        $execution = (new PaymentExecution())
            ->setPayerId($payerId)
            ->setTransactions($payment->getTransactions());
            

            try {
                $payment->execute($execution,$apiContext);
                return $payment;
            } catch (PayPalConnectionException $e) {
                
                echo $e->getMessage();
            }
    }
}