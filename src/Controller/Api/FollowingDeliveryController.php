<?php

namespace App\Controller\Api;

use App\Repository\DeliveryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FollowingDeliveryController extends AbstractController
{
    /**
     * @Route("/api/drivers/deliveries", name="app_following_delivery")
     */
    public function index(DeliveryRepository $deliveryRepository): Response {
        $deliveryList = $deliveryRepository->findAllDeliveriesByDriver();
    
        return $this->json($deliveryList, Response::HTTP_OK, [], ['groups'=>"api_driver_deliveries"]);
    }
}