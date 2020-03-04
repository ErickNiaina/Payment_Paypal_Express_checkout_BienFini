<?php

namespace App\Service;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\ItemList;
use PayPal\Rest\ApiContext;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;


class PaymentService{


	public function createPayment($idSubscribe)
	{
		$ids = require_once(__DIR__ . '/../../config.php');
		$vat = 0.2;
		$offer = Offers::getOffers();

		$apiContext = new ApiContext(
			new OAuthTokenCredential(
				$ids['id'],
				$ids['secret']
			)
		);

		$vat = VatService::getVatPrice($offer[$idSubscribe]['price'],$vat);
		$priceVat = $offer[$idSubscribe]['price'] + $vat;

		$list = new ItemList();
		
		$item = (new Item())
			->setName($offer[$idSubscribe]['name'])
			->setPrice($offer[$idSubscribe]['price'])
			->setCurrency('EUR')
			->setQuantity(1);
		$list->addItem($item);

		
        $detail = (new Details())
			->setSubtotal($offer[$idSubscribe]['price'])
			->setTax($vat);

		$amount = (new Amount())
			->setTotal($priceVat)
			->setCurrency('EUR')
			->setDetails($detail);

		$transaction = (new Transaction())
			->setItemList($list)
			->setDescription('Achat d\'abonnement')
			->setAmount($amount)
			->setCustom('demo-1');


		$payment = new Payment();
		$payment->setTransactions([$transaction]);
		$payment->setIntent('sale');
		$redirectUrls = (new RedirectUrls())
			->setReturnUrl('http://localhost:8000/pay')
			->setCancelUrl('http://localhost:8000/index');
		$payment->setRedirectUrls($redirectUrls);
		$payment->setPayer((new Payer())->setPaymentMethod('paypal'));
		
		
		try {
			$payment->create($apiContext);

			header('Location:'.$payment->getApprovalLink());
			die;
			 

		} catch (PayPalConnectionException $e) {
			
			echo $e->getMessage();
		}
		

	}
}




