<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * 
 * @Route("/api/drivers", name="api_drivers_")
 */
class ManagingDriverController extends AbstractController
{

    /**
     * get all drivers
     * @Route("", name="list")
     * @return Response
     */
    public function list(UserRepository $userRepository): Response
    {
        // préparer les données
        $driversList = $userRepository->findAllDrivers();
        
        //La méthode json va "serializer" les données, c'est à dire les transformer en JSON.
        return $this->json($driversList, Response::HTTP_OK, [], ['groups' => "api_drivers_list"]);
    }
}