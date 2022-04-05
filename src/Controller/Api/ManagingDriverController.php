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
        // On prépare les données : On récupère les données depuis le repository
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
        // On prépare les données : on récupère les données de l'utilisateur en question
        $user = $userRepository->find($id);

        //ON gère le cas si l'utilisateur n'existe pas
        if (is_null($user))
        {
            $data = 
            [
                'error' => true,
                'message' => 'Cet utilisateur est inconnu',
            ];
            return $this->json($data, Response::HTTP_NOT_FOUND, [], ['groups' => "api_drivers_details"]);
        }

        //On retourne le résultat en JSON
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

        // On récupère la réponse en JSON
        $requestContentInJson = $request->getContent();

        // On transforme le JSON en objet (on va donc le deserializer)
        $user = $serializer->deserialize($requestContentInJson, User::class, 'json');
        
        // On attribue un role Driver et un statut 0(=disponible) par défaut
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
        //doctrine prend en charge l'utilisateur créé...
        $entityManager->persist($user);
        //...et l'enregistre en base de données 
        $entityManager->flush();
        
        //On retourne au format JSON l'utilisateur créé. 
        return $this->json($user, 201, [], ['groups' => 'api_drivers_details']);
    }

     /**
     * Updates a user
     * 
     * @Route("/{id}", name="update", methods="PUT", requirements={"id"="\d+"})
     * @return Response
     */
    public function update(ValidatorInterface $validator, int $id, ManagerRegistry $doctrine,  UserPasswordHasherInterface $hasher, Request $request, UserRepository $userRepository, SerializerInterface $serializer): Response
    {

        // On récupère l'utilisateur dans la BDD
        $user = $userRepository->find($id);

        //On gèrer le cas où l'utilisateur n'existe pas en BDD
        if (is_null($user))
        {
            $data = [
                'error' => true,
                'message' => 'Cet utilisateur est inconnu',
            ];

            return $this->json($data, Response::HTTP_NOT_FOUND);
        }

        // On récupère les données modifiées depuis la requête
        $requestContentInJson = $request->getContent();
       
        // On modifie l'utilisateur avec les données modifiées
        $serializer->deserialize($requestContentInJson, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
        
        // Pour vérifier si on nous a envoyé un mot de passe, on désérialise le json et on vérifie si un champ mot de passe existe 
        $userObject = json_decode($requestContentInJson);
        
        if (isset($userObject->password))
        {
            $hashedPassword = $hasher->hashPassword($user, $userObject->password);
            $user->setPassword($hashedPassword);
        }
        
        // //On demande au validator de vérifier si les données son correctes (on a mis les vérifications dans les entités avec assert avant)
        // $errors = $validator->validate($user);

        // if (count($errors) > 0)
        // {
        //     // TODO comment faire pour mutualiser / simplifier l'envoi d'erreur
        //     $data = [
        //         'error' => true,
        //         'message' => (string) $errors,
        //     ];

        //     return $this->json($data, Response::HTTP_NOT_FOUND);
        // }

        // On enregistre l'utilisateur avec les modifications en BDD
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
        // ON prépare les données
        $user = $userRepository->find($id);

        $entityManager =$doctrine->getManager();

        //On gère le cas où l'utilisateur n'existe pas 
        if (is_null($user))
        {
            $data = 
            [
                'error' => true,
                'message' => 'Driver not found',
            ];
            return $this->json($data, Response::HTTP_NOT_FOUND);
        }

        //On supprime l'utilisateur de la BDD
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json($user, Response::HTTP_OK, [], ['groups' => "api_drivers_delete"]);
    }
}