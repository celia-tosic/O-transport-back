<?php

namespace App\Controller\Api;

use App\Repository\DeliveryRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * 
 * @Route("/api/drivers", name="app_following_delivery")
 */
class FollowingDeliveryController extends AbstractController
{
    /**
     * showDeliveries
     * @Route("/{id}/deliveries", name="showDelivery", methods="GET", requirements={"id"="\d+"})
     * @return Response
    */
    public function showDeliveries(int $id , DeliveryRepository $deliveryRepository): Response {
        $deliveryList = $deliveryRepository->findAllDeliveriesByDriver($id);
    
        return $this->json($deliveryList, Response::HTTP_OK, [], ['groups'=>"api_driver_deliveries"]);
    }

    /**
     * Function for a driver to start a delivery
     *
     * @Route("/{idDriver}/deliveries/{idDelivery}/start", name="start_delivery", methods="GET", requirements={"id"="\d+", "idDelivery"="\d+"})
     */
    public function startDelivery(int $idDriver, int $idDelivery, UserRepository $userRepository, DeliveryRepository $deliveryRepository, ManagerRegistry $doctrine): Response 
    {
        
        $userToSwitchStatus = $userRepository->find($idDriver);
        $deliveryToSwitchStatus = $deliveryRepository->find($idDelivery);
        dd($userToSwitchStatus, $deliveryToSwitchStatus);
        
        $entityManager = $doctrine->getManager(); 
        
        $userToSwitchStatus->setStatus(1);
        $deliveryToSwitchStatus->setStatus(1);
        $entityManager->flush(); 
        
        return $this->json($userToSwitchStatus, Response::HTTP_ACCEPTED, [], ['groups'=>"api_driver_deliveries"]);


    }



}