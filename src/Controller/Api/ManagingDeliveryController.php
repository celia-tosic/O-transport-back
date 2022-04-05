<?php

namespace App\Controller\Api;

use App\Repository\DeliveryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/admin/deliveries", name="api_deliveries_")
 */
class ManagingDeliveryController extends AbstractController
{
    
    /**
     * @Route("/pending", name="pending_list")
     */
    public function pendingList(DeliveryRepository $deliveryRepository): Response
    {
        // préparer les données
        $pendingList = $deliveryRepository->findPendingDeliveries();
        //La méthode json va "serializer" les données, c'est à dire les transformer en JSON.
        return $this->json($pendingList, Response::HTTP_OK, [], ['groups' => "api_deliveries_list"]);
    }

        /**
     * @Route("/completed", name="completed_list")
     */
    public function completedList(DeliveryRepository $deliveryRepository): Response
    {
        // préparer les données
        $completedList = $deliveryRepository->findCompletedDeliveries();
        //La méthode json va "serializer" les données, c'est à dire les transformer en JSON.
        return $this->json($completedList, Response::HTTP_OK, [], ['groups' => "api_deliveries_list"]);
    }
}
