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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Response\JsonErrorResponse;

/**
 * 
 * @Route("/api/admin/drivers", name="api_drivers_")
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
        // Data preparation : we get the data from the repository
        // custom request in DQL (cf. UserRepository.php)
        $driversList = $userRepository->findAllDrivers();
        
        //json method json "serializes" the data --> transform to JSON
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
        $user = $userRepository->find($id);

        //if the user doesn't exist ...
        if (is_null($user))
        {
            //... we send an error message
            return JsonErrorResponse::sendError("Cet utilisateur est inconnu", 404);
        }

        // else we send driver's infos
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

        // we get the response in JSON
        $requestContentInJson = $request->getContent();

        // we transform the JSON in object (deserialize)
        $user = $serializer->deserialize($requestContentInJson, User::class, 'json');
        
        // we set a driver role and a status to 0 (=available) by default
        $user->setRoles(["ROLE_DRIVER"]);   
        $user->setStatus(0);  

        // data validation with validator (@Assert in entities)
        $errors = $validator->validate($user, null, ["creation"]);

        if (count($errors) > 0)
        {   
            // if some erros --> we send errors  
            return JsonErrorResponse::sendValidatorErrors($errors, 500);
        }
        
        // hash password 
        $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $entityManager = $doctrine->getManager();
       
        // stockage in DB
        $entityManager->persist($user);
       
        $entityManager->flush();
        
        // send status 201 created if it's ok 
        return $this->json($user, 201, [], ['groups' => "api_drivers_details"]);
    }

     /**
     * Updates a user
     * 
     * @Route("/{id}", name="update", methods="PUT", requirements={"id"="\d+"})
     * @return Response
     */
    public function update(ValidatorInterface $validator, int $id, ManagerRegistry $doctrine,  UserPasswordHasherInterface $hasher, Request $request, UserRepository $userRepository, SerializerInterface $serializer): Response
    {

        $user = $userRepository->find($id);

        if (is_null($user)) {
            return JsonErrorResponse::sendError("Cet utilisateur est inconnu", 404);
        }

        // get modified data from the request
        $requestContentInJson = $request->getContent();
       
        // we apply the modifications 
        $serializer->deserialize($requestContentInJson, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
        
        // To verify if there is a new password, we deserialize the json and we verify if the password exists 
        $userObject = json_decode($requestContentInJson);

        //if the password exists...
        if (isset($userObject->password)) {

            // ... we virify the data with the group "modificationIfPasswordExist"
            $errors = $validator->validate($user, null, ["modificationIfPasswordExist"]);

            if (count($errors) > 0) {
                return JsonErrorResponse::sendValidatorErrors($errors, 500);
            }

            //if it's ok, we hash the password
            $hashedPassword = $hasher->hashPassword($user, $userObject->password);
            $user->setPassword($hashedPassword);
    
        // if the password doesn't exist, we virify the data with the group "modification"

        } else {
            $errors = $validator->validate($user, null, ['modification']);

            if (count($errors) > 0) {
                return JsonErrorResponse::sendValidatorErrors($errors, 500);
            }
        }

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }


     /**
     * Deletes a driver
     * 
     * @Route("/{id}", name="delete", methods="DELETE", requirements={"id"="\d+"})
     * @return Response
     */
    public function delete(int $id, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        $user = $userRepository->find($id);

        $entityManager =$doctrine->getManager();

        if (is_null($user))
        {
            return JsonErrorResponse::sendError("Cet utilisateur est inconnu", 404);
        }

        //If the driver is delivering (status 1, not available), we cannot delete him
        if ($user->getStatus() === 1 )
        {
            return JsonErrorResponse::sendError("Suppression impossible", 405);
        }

        //We remove the user of the DB
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json($user, Response::HTTP_OK, [], ['groups' => "api_drivers_delete"]);
    }
}