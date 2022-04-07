<?php

namespace App\Controller\Api;

use App\Repository\DeliveryRepository;
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
}