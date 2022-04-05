<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    * creates a user
    *
    * @Route("", name="create", methods="POST")
    * 
    */
    public function create(ValidatorInterface $validator, Request $request, SerializerInterface $serializer, UserPasswordHasherInterface $hasher, ManagerRegistry $doctrine)
    {
        //Si l'utilisateur n'a pas de rôle admin, on envoie ce message d'erreur
        // if (! $this->isGranted("ROLE_ADMIN"))
        // {
        //     $data = 
        //     [
        //         'error' => true,
        //         'msg' => 'Il faut être admin pour accéder à ce endpoint ( You SHALL not PASS )'
        //     ];
        //     return $this->json($data, Response::HTTP_FORBIDDEN);
        // }

        // On récupère la réponse en JSON
        $requestContentInJson = $request->getContent();
        // dd($requestContentInJson);

        // On transforme le JSON en objet, on va donc le deserializer
        $user = $serializer->deserialize($requestContentInJson, User::class, 'json');
        
        $user->setRoles(["ROLE_DRIVER"]);   
        $user->setStatus(0);  
        

        // On hash le mot de passe 
        $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        // $errors = $validator->validate($user);

        // if (count($errors) > 0)
        // {
        //     $data = [
        //         'error' => true,
        //         'message' => (string) $errors,
        //     ];

        //     return $this->json($data, Response::HTTP_NOT_FOUND);
        // }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        
        $entityManager->flush();

        // On peut mettre aussi HTTP::HTTP_CREATED au lieu de 201 
        return $this->json($user, 201, [], ['groups' => 'api_drivers_details']);
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