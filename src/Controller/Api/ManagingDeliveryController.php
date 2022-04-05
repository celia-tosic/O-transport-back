<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManagingDeliveryController extends AbstractController
{
    /**
     * @Route("/api/managing/delivery", name="app_api_managing_delivery")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/ManagingDeliveryController.php',
        ]);
    }
}
