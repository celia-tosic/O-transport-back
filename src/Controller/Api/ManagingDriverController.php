<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

/**
 * 
 * @Route("/api/drivers", name="api_drivers_")
 */
class ManagingDriverController extends AbstractController
{

    /**
     * get all drivers
     * @Route("", name="list", methods="GET")
     * @return Response
     */
    public function list(UserRepository $userRepository): Response
    {
        // préparer les données
        $driversList = $userRepository->findAllDrivers();
        
        //La méthode json va "serializer" les données, c'est à dire les transformer en JSON.
        return $this->json($driversList, Response::HTTP_OK, [], ['groups' => "api_drivers_list"]);
    }


     /**
     * Get a driver details
     * 
     * @Route("/{id}", name="read", methods="GET", requirements={"id"="\d+"})
     * @return Response
     */
    public function read(int $id, UserRepository $userRepository): Response
    {
        // préparer les données
        $user = $userRepository->find($id);

        if (is_null($user))
        {
            $data = 
            [
                'error' => true,
                'message' => 'Driver not found',
            ];
            return $this->json($data, Response::HTTP_NOT_FOUND, [], ['groups' => "api_drivers_details"]);
        }

        return $this->json($user, Response::HTTP_OK, [], ['groups' => "api_drivers_details"]);
    }


     /**
     * Deletes a driver
     * 
     * @Route("/{id}", name="delete", methods="DELETE", requirements={"id"="\d+"})
     * @return Response
     */
    public function delete(int $id, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        // préparer les données
        $user = $userRepository->find($id);

        $entityManager =$doctrine->getManager();

        if (is_null($user))
        {
            $data = 
            [
                'error' => true,
                'message' => 'Driver not found',
            ];
            return $this->json($data, Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($user);

        $entityManager->flush();

        return $this->json($user, Response::HTTP_OK, [], ['groups' => "api_drivers_delete"]);
    }
}