<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Entity\Delivery;
use App\Repository\CustomerRepository;
use App\Repository\DeliveryRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Response\JsonErrorResponse;
use App\Service\DeliveryTestsContent as ServiceDeliveryTestsContent;

/**
 * @Route("/api/admin/deliveries", name="api_deliveries_")
 */
class ManagingDeliveryController extends AbstractController
{


    /**
     * get list of pending deliveries (status = 0)
     * @Route("/pending", name="pending_list", methods="GET")
     */
    public function pendingList(DeliveryRepository $deliveryRepository): Response
    {
        // préparer les données
        $pendingList = $deliveryRepository->findPendingDeliveries();
        //La méthode json va "serializer" les données, c'est à dire les transformer en JSON.
        return $this->json($pendingList, Response::HTTP_OK, [], ['groups' => "api_deliveries_list"]);
    }

    /**
     * get list of shipping deliveries (status = 1)
     * @Route("/shipping", name="shipping_list", methods="GET")
     */
    public function shippingList(DeliveryRepository $deliveryRepository): Response
    {
        // préparer les données
        $shippingList = $deliveryRepository->findShippingDeliveries();
        //La méthode json va "serializer" les données, c'est à dire les transformer en JSON.
        return $this->json($shippingList, Response::HTTP_OK, [], ['groups' => "api_deliveries_list"]);
    }


    /**
     * Get list of completed deliveries (status = 2)
     * @Route("/completed", name="completed_list", methods="GET")
     */
    public function completedList(DeliveryRepository $deliveryRepository): Response
    {
        // préparer les données
        $completedList = $deliveryRepository->findCompletedDeliveries();
        //La méthode json va "serializer" les données, c'est à dire les transformer en JSON.
        return $this->json($completedList, Response::HTTP_OK, [], ['groups' => "api_deliveries_list"]);
    }

    /**
     * Update the driver for a specific delivery
     *
     * @Route("/{id}/affect", name="affect_driver", requirements={"id"="\d+"}, methods="PUT")
     */
    public function affectDriver(int $id, UserRepository $userRepository, DeliveryRepository $deliveryRepository, Request $request, ManagerRegistry $doctrine): Response
    {
        $currentDelivery = $deliveryRepository->find($id);
        $jsonContent = $request->getContent();
        // $decodedDriverId = $serializer->deserialize($jsonContent, User::class, 'json');

        // On vérifie que l'identifiant envoyé existe en tant que livraison, si non, on renvoit un message d'erreur

        if (is_null($currentDelivery)) {
            return JsonErrorResponse::sendError("Cette livraison est inconnue", 404);
        }

        // On décode le json reçu pour ne prendre que l'ID envoyé 
        $decodedDriverId = json_decode($jsonContent, true);
        // dd($decodedDriverId);
        // On récupère l'objet User correspondant
        $userToAffect = $userRepository->find($decodedDriverId);
        // On l'affect à la livraison
        $currentDelivery->setDriver($userToAffect);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return $this->json($currentDelivery, Response::HTTP_OK, [], ['groups' => "api_deliveries_details"]);
    }

    /**
     * Post route to create a new delivery + customer
     * @Route("/create", name="create", methods={"POST"})
     */
    public function create(UserRepository $userRepository, CustomerRepository $customerRepository, Request $request, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        // On récupère le contenu de la requête en JSON et le décode en tableau
        $data = $request->toArray();
        
        //On isole nos deux "parties" (objets) du tableau : delivery et customer
        $deliveryObject = $data["delivery"];
        $customerObject = $data["customer"];

        //On transforme nos 2 objets en json pour qu'ils puissent être ensuite déserializé. 
        $deliveryString = $serializer->serialize($deliveryObject, 'json');
        $customerString = $serializer->serialize($customerObject, 'json');

        // On prépare la manipulation des données
        $entityManager = $doctrine->getManager();

        //On deserialise et on créé un nouvel objet livraison
        $delivery = $serializer->deserialize($deliveryString, Delivery::class, 'json');
        // On complète les infos qui ne sont pas dans le formulaire
        $delivery->setCreatedAt(new DateTime());
        $delivery->setUpdatedAt(null);
        //TODO il faut que l'admin corresponde à l'utilisateur créant la livraison (En session)
        // $delivery->setAdmin($userRepository->find(3));
        $delivery->setStatus(0);

        // On fabrique les tests en testant de récupérer les données dans la table Customer
        $test = $customerRepository->findByName($customerObject['name']);
        $test2 = $customerRepository->findByAddress($customerObject['address']);
        // On vérifie d'abord si le nom existe en BDD

        if (!$test) {
            // si il n'existe pas, on créé un nouveau customer
            $customer = $serializer->deserialize($customerString, Customer::class, 'json');
            $entityManager->persist($customer);
        } else {
            // si le nom existe, on verifie si l'adresse correspond
            if ($test === $test2) {
                // Si il existe on récupère le customer existant et on met à jour le numéro de téléphone
                // $updatePhoneNumber = $customerArray['phoneNumber'];
                $customer = $customerRepository->find($test[0]->getId());

                // $customer->setPhoneNumber($updatePhoneNumber);
            } else {
                // Si elle ne correspond pas, on créé un nouveau Customer
                $customer = $serializer->deserialize($customerString, Customer::class, 'json');
                $entityManager->persist($customer);
            }
        }


        // On vérifie la validité des données gràce au Validator Interface 
        // On fabrique un tableau d'erreur vide
        $messages = [];
        // On vérifie si il y a des erreurs dans l'entité Delivery
        $errorsD = $validator->validate($delivery);
        // On boucle sur chaque input pour vérifier la présense d'erreur et on les intègre dans le tableaux d'erreur
        foreach ($errorsD as $violation) {
            $messages[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        // On fait pareil pour l'entité Customer
        $errorsC = $validator->validate($customer);
        foreach ($errorsC as $violation) {
            $messages[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        // On vérifie que le tableau soit vide sinon on renvoi une réponse HTTP_UNPROCESSABLE_ENTITY (422)
        if ($messages != []) {
            return $this->json($messages, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        //on affecte le customer récupéré/créé à la livraison
        $delivery->setCustomer($customer);
        $entityManager->persist($delivery);

        $entityManager->flush();

        // On retourne la réponse adaptée (201 + Location: URL de la ressource)
        return $this->json($delivery, Response::HTTP_CREATED, [], ['groups' => 'api_deliveries_details']);
    }

    /**
     * Get content and route to read an existing delivery
     * @Route("/{id}", name="read", requirements={"id"="\d+"}, methods="GET")
     */
    public function read(int $id, DeliveryRepository $deliveryRepository): Response
    {
        $currentDelivery = $deliveryRepository->find($id);

        return $this->json($currentDelivery, Response::HTTP_OK, [], ['groups' => "api_deliveries_details"]);
    }

    /**
     * Get content and route to POST update an existing delivery
     * @Route("/{id}", name="update", requirements={"id"="\d+"}, methods="PUT")
     */
    public function update(int $id, CustomerRepository $customerRepository, ServiceDeliveryTestsContent $deliveryCheck,DeliveryRepository $deliveryRepository, Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        // Permet de sortir les informations en GET correspondant à l'id de la livraison. 
        $currentDelivery = $deliveryRepository->find($id);

        // On récupère le contenu en JSON
        $jsonContent = $request->getContent();

        // Ici nous traitons la méthode PUT de la requête
        // On décode le contenu pour pouvoir créer nos entités à partir du tableau 
        $decode = json_decode($jsonContent, true);
        // $decode = $decode['delivery'];
        $customerToUpdate = $decode['customer'];
        $entityManager = $doctrine->getManager();

        // On vérifie si chaque champs à évoluer, si oui on l'update
        $deliveryCheck->decodeDeliveryAndUpdate($currentDelivery, $decode);

        // On vérifie si le client de la livraison est différent de l'input renvoyé par le front
        if ($currentDelivery->getCustomer()->getName() !== $customerToUpdate['name']) {
          

            // pour vérifier si le nouveau client existe, on teste de requêter son nom dans le repo Customer 
            $existingCustomer = $customerRepository->findOneByName($customerToUpdate['name']);
            // Si il n'existe pas
            if (empty($existingCustomer)) {

                $testIfMoreThanOnce = $deliveryCheck->deliveriesRequestedMoreThanOnce($currentDelivery->getCustomer());

                if ($testIfMoreThanOnce == false) {
                    
                    $deliveryCheck->setCustomerFromArray($currentDelivery->getCustomer(), $customerToUpdate);

                } else {
                    // Si le nom du client renseigné n'existe pas, vérifie si il a déjà fait des livraisons. 
                    $customerToCreate = $deliveryCheck->createCustomerFromArray($customerToUpdate);

                    $entityManager->persist($customerToCreate);
                    $currentDelivery->setCustomer($customerToCreate);
                }
            } else {
                
                // Sinon on remplace le customer actuel par celui que nous avons trouvé du même nom. 
                $currentDelivery->setCustomer($existingCustomer);
            }
        }
        if ($currentDelivery->getCustomer()->getAddress() !== $customerToUpdate['address']) {

            $testIfMoreThanOnce = $deliveryCheck->deliveriesRequestedMoreThanOnce($currentDelivery->getCustomer());

            if ($testIfMoreThanOnce == false) {

                $deliveryCheck->setCustomerFromArray($currentDelivery->getCustomer(), $customerToUpdate);
                
            } else {
                // $customerToCreate = new Customer();
                $customerToCreate = $deliveryCheck->createCustomerFromArray($customerToUpdate);

                $entityManager->persist($customerToCreate);
                $currentDelivery->setCustomer($customerToCreate);
            }
        }
        if ($currentDelivery->getCustomer()->getPhoneNumber() !== $customerToUpdate['phoneNumber']) {
            $currentDelivery->getCustomer()->setPhoneNumber($customerToUpdate['phoneNumber']);
        }
      
        // Ici on test la validité des inputs modifiés
        // On fabrique un tableau d'erreur vide
        $messages = [];
        // On vérifie si il y a des erreurs dans l'entité Delivery
        $updateErrorsOnDelivery = $validator->validate($currentDelivery);
        $updateErrorsOnCustomer = $validator->validate($currentDelivery->getCustomer());
        // On boucle sur chaque input pour vérifier la présense d'erreur et on les intègre dans le tableaux d'erreur
        foreach ($updateErrorsOnDelivery as $violation) {
            $messages[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        foreach ($updateErrorsOnCustomer as $violation) {
            $messages[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        // On vérifie que le tableau soit vide sinon on renvoi une réponse HTTP_UNPROCESSABLE_ENTITY (422)
        if ($messages != []) {
            return $this->json($messages, Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            // Dans le cas où il n'y a pas d'erreur, on modifie la date de mise à jour
            $currentDelivery->setUpdatedAt(new DateTime());
        }

        $entityManager->flush();

        return $this->json($currentDelivery, Response::HTTP_ACCEPTED, [], ['groups' => "api_deliveries_details"]);
    }

    /**
     * function called to delete a delivery
     *
     * @Route("/{id}", name="delete", requirements={"id"="\d+"}, methods={"DELETE"})
     */
    public function delete(int $id, DeliveryRepository $deliveryRepository, ManagerRegistry $doctrine)
    {

        $deliveryToDelete = $deliveryRepository->find($id);
        $entityManager = $doctrine->getManager();

        //On gère le cas où la livraison n'existe pas 
        if (is_null($deliveryToDelete)) {
            return JsonErrorResponse::sendError("Cette livraison est inconnue", 404);
        }

        $entityManager->remove($deliveryToDelete);
        $entityManager->flush();

        return $this->json($deliveryToDelete, Response::HTTP_OK, [], ['groups' => "api_delivery_deleted"]);
        //return $this->json("Work", Response::HTTP_OK, [])
    }
}
