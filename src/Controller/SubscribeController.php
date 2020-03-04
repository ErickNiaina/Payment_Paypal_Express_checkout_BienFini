<?php

namespace App\Controller;


//require_once(__DIR__ . '/../../inc.php');

use DateTime;
use DateTimeZone;
use App\Service\Offers;
use App\Service\PaymentService;
use App\Service\PayService;
use App\Service\UserSubscribeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubscribeController extends AbstractController
{

	/**
     * @Route("/subscription", name="subscribe_choice")
     */
	public function choiceSubscribe(UserSubscribeService $userSubscribeService){
		$idUserActif = $userSubscribeService->getIdUserCourant();
        $user = $userSubscribeService->getOneUserActif($idUserActif);
        $dateNow = new DateTime();
		$dateEnd = $user->getEndSubscription();
		
		if($dateEnd > $dateNow){
            return $this->render('subscription\subscription_choix.html.twig',[
                'endSubscription' => $dateEnd,
                'userSubscribe' => $user,
            ]);
        }
		else{
			$offer = Offers::getOffers();
					return $this->render('subscription\subscription_choix.html.twig',[
					'offer' => $offer,
				]);
		}
	}

	/**
     * @Route("/create/payment", name="payment")
     */
	public function createPayment(Request $request,PaymentService $paymentService){

		$idSubscribe = $request->request->get('offer');
		$paymentService->createPayment($idSubscribe);
		$offer = Offers::getOffers();
		//dd($offer);
		return new Response('');
	}


	/**
     * @Route("/pay", name="approved_payment")
     */
	public function approvedPayment(Request $request,PayService $payservice,UserSubscribeService $subscribeservice)
	{
		$paymentId = $request->query->get('paymentId');
		$payerId = $request->query->get('PayerID');
		$idUserCourant = $subscribeservice->getIdUserCourant();
		
		$payment = $payservice->payApproved($paymentId,$payerId);
		echo $payment->getId().'<br>';
		echo $payment->getState().'<br>'; //state = APPROVED
		echo $payment->getPayer()->status.'<br>';//status = VERIFIED
		$objetItemList = $payment->getTransactions()[0]->item_list;
		$subscription = $objetItemList->items[0]->name;
		echo $subscription;//nom d'abonnement
		echo $payment->getTransactions()[0]->related_resources[0]->sale->state.'<br>';//verification du status = Completed
dd($payment);
		/*if($payment->getState() && ($payment->getTransactions()[0]->related_resources[0]->sale->state == 'Completed')
		&&($payment->getPayer() == 'Verified')){

		}*/

		$period = ($subscription === 'Abonnement mensuel' ? new \DateInterval('P1M') : new \DateInterval('P1Y'));
		$dateEnd = (new DateTime())->add($period)->format('Y-m-d H:i');
		
		$subscribeservice->modifierUtilisateurAvoirAbonnement($payerId,$paymentId,$dateEnd,$idUserCourant);

		return $this->redirectToRoute('subscribe_choice',[
			'payment' => $payment
		]);
	}
	

}
