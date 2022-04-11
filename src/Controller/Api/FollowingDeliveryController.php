<?php

namespace App\Controller\Api;

use App\Repository\DeliveryRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Response\JsonErrorResponse;

/**
 * 
 * @Route("/api/drivers", name="api_deliveries_")
 */
class FollowingDeliveryController extends AbstractController
{
    /**
     * show deliveries affected to a driver
     * @Route("/{id}/deliveries", name="affected", methods="GET", requirements={"id"="\d+"})
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

        // On vérifie que la livraison soit bien attribuée au chauffeur
        if ($userToSwitchStatus->getStatus() == 1) {

            return JsonErrorResponse::sendError("Vous ne pouvez commencer deux livraisons simultanément", 404);
        }

        $entityManager = $doctrine->getManager(); 
        
        $userToSwitchStatus->setStatus(1);
        $deliveryToSwitchStatus->setStatus(1);

        $entityManager->flush(); 
        
        return $this->json('Livraison commencée !', Response::HTTP_ACCEPTED, [], ['groups'=>"api_driver_deliveries"]);

    }

        /**
     * Function for a driver to end a delivery
     *
     * @Route("/{idDriver}/deliveries/{idDelivery}/end", name="end_delivery", methods="GET", requirements={"id"="\d+", "idDelivery"="\d+"})
     */
    public function endDelivery(int $idDriver, int $idDelivery, UserRepository $userRepository, DeliveryRepository $deliveryRepository, ManagerRegistry $doctrine): Response 
    {
        
        $userToSwitchStatus = $userRepository->find($idDriver);
        $deliveryToSwitchStatus = $deliveryRepository->find($idDelivery);

        $entityManager = $doctrine->getManager(); 
        
        $userToSwitchStatus->setStatus(0);
        $deliveryToSwitchStatus->setStatus(2);
        $entityManager->flush(); 
        
        return $this->json('Livraison terminée', Response::HTTP_ACCEPTED, [], ['groups'=>"api_driver_deliveries"]);

    }



}

