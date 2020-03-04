<?php

namespace App\Service;

use DateTime;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Luracast\Restler\Data\Object;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class UserSubscribeService
{
    public function __construct(EntityManagerInterface $em,TokenStorageInterface $tokenStorage) 
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    //modifier payer_id et profile_id si l'utilisateur a un abonnement
    public function modifierUtilisateurAvoirAbonnement($payer_id,$profile_id,$end_subscription,$user_id){
        $editSubscribe = $this->em->getRepository(User::class)->updateUserHaveSubscribe($payer_id,$profile_id,$end_subscription,$user_id);
        return $editSubscribe;
    }

    //recuperer user courant dans un service
    public function getIdUserCourant(){

        $user = $this->tokenStorage->getToken()->getUser()->getId();
        return $user;
    }


    //preciser la date d'expiration d'abonnement d'utilisateur
    public function modifierDateExpiration($end_subscription,$user_id){

        $endSubscribe = $this->em->getRepository(User::class)->updateEndSubscriptionUser($end_subscription,$user_id);
        return $endSubscribe;
    }

    //retourner l'utilisateur courant
    public function getOneUserActif($userId){

        $userCourant = $this->em->getRepository(User::class)->findOneBy(array('id' => $userId));
        return $userCourant;
    }
    
}