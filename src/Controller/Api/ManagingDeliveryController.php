<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\User;
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
        // Data preparation : we get the data from the repository
        // custom request in DQL (cf. DeliveryRepository.php)
        $pendingList = $deliveryRepository->findPendingDeliveries();
        
        //json method json "serializes" the data --> transform to JSON
        return $this->json($pendingList, Response::HTTP_OK, [], ['groups' => "api_deliveries_list"]);
    }

    /**
     * get list of shipping deliveries (status = 1)
     * @Route("/shipping", name="shipping_list", methods="GET")
     */
    public function shippingList(DeliveryRepository $deliveryRepository): Response
    {
        $shippingList = $deliveryRepository->findShippingDeliveries();
        
        return $this->json($shippingList, Response::HTTP_OK, [], ['groups' => "api_deliveries_list"]);
    }


    /**
     * Get list of completed deliveries (status = 2)
     * @Route("/completed", name="completed_list", methods="GET")
     */
    public function completedList(DeliveryRepository $deliveryRepository): Response
    {
        $completedList = $deliveryRepository->findCompletedDeliveries();

        return $this->json($completedList, Response::HTTP_OK, [], ['groups' => "api_deliveries_list"]);
    }

    /**
     * Affect a driver to a specific delivery
     *
     * @Route("/{id}/affect", name="affect_driver", requirements={"id"="\d+"}, methods="PUT")
     */
    public function affectDriver(int $id, UserRepository $userRepository, DeliveryRepository $deliveryRepository, Request $request, ManagerRegistry $doctrine): Response
    {
        $currentDelivery = $deliveryRepository->find($id);
        $jsonContent = $request->getContent();

        // If the delivery doesn't exist
        if (is_null($currentDelivery)) {
            return JsonErrorResponse::sendError("Cette livraison est inconnue", 404);
        }

        // we decode the Json response  
        $decodedDriverId = json_decode($jsonContent, true);

        // we get the user object
        $userToAffect = $userRepository->find($decodedDriverId);

        // we affect the driver to the delivery
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
        // we get the request's content in JSON and we decode it in array
        $data = $request->toArray();
        
        //we isolate the two parts (objects) of the table : delivery and customer
        $deliveryObject = $data["delivery"];
        $customerObject = $data["customer"];

        $admin = $userRepository->find($deliveryObject["adminId"]);

        //we transform the 2 objects in JSON to be able to deserialize them with the deserializer of Symfony. 
        $deliveryString = $serializer->serialize($deliveryObject, 'json');
        $customerString = $serializer->serialize($customerObject, 'json');

        $entityManager = $doctrine->getManager();

        //we deserialize and we create a new object Delivery
        $delivery = $serializer->deserialize($deliveryString, Delivery::class, 'json');

        // we set some infos by default
        $delivery->setCreatedAt(new DateTime());
        $delivery->setUpdatedAt(null);
        $delivery->setAdmin($admin);
        $delivery->setStatus(0);
        //TODO the admin must correspond to the user creating the delivery 

        // We verify if the client is already in the DB or not 
        $customerFoundByName = $customerRepository->findByName($customerObject['name']);        

        //If the client name doesn't exist... 
        if (!$customerFoundByName) {
            //... then we create a new customer
            $customer = $serializer->deserialize($customerString, Customer::class, 'json');

        } else {
            // if the name exists, we verify if there is a client with the same address
            foreach ($customerFoundByName as $customer) {

                if ($customer->getAddress() === $customerObject['address']) {

                    $updatePhoneNumber = $customerObject['phoneNumber'];
                    $customer->setPhoneNumber($updatePhoneNumber);
                    $delivery->setCustomer($customer);

                    // data validation with validator (@Assert in entities)
                    $errorsDelivery = $validator->validate($delivery);
                    $errorsCustomer = $validator->validate($customer);
                    
                    if ( (count($errorsDelivery) > 0  && count($errorsCustomer) > 0) || (count($errorsDelivery) > 0  || count($errorsCustomer) > 0) )
                    {   
                        
                        return JsonErrorResponse::sendValidatorErrorsOnManyEntities($errorsDelivery, $errorsCustomer);
                    }

                    $entityManager->persist($delivery);
                    $entityManager->flush();

                    $entityManager->persist($customer);

                    return $this->json($delivery, Response::HTTP_CREATED, [], ['groups' => "api_deliveries_details"]);

                } else {
                    // If there is no client with the same address, then we create a new customer
                    $customer = $serializer->deserialize($customerString, Customer::class, 'json');
                }
            }
        }

        // data validation with validator (@Assert in entities)
        $errorsDelivery = $validator->validate($delivery);
        $errorsCustomer = $validator->validate($customer);
        
        if ( (count($errorsDelivery) > 0  && count($errorsCustomer) > 0) || (count($errorsDelivery) > 0  || count($errorsCustomer) > 0) )
        {   
            
            return JsonErrorResponse::sendValidatorErrorsOnManyEntities($errorsDelivery, $errorsCustomer);
        }

        //we affect the customer to the delivery and we create the customer and the delivery
        $delivery->setCustomer($customer);
        $entityManager->persist($customer);
        $entityManager->persist($delivery);
        $entityManager->flush();

        return $this->json($delivery, Response::HTTP_CREATED, [], ['groups' => "api_deliveries_details"]);
    }

    /**
     * Get a delivery details
     * @Route("/{id}", name="read", requirements={"id"="\d+"}, methods="GET")
     */
    public function read(int $id, DeliveryRepository $deliveryRepository): Response
    {
        $currentDelivery = $deliveryRepository->find($id);

        return $this->json($currentDelivery, Response::HTTP_OK, [], ['groups' => "api_deliveries_details"]);
    }

    /**
     * Update an existing delivery
     * @Route("/{id}", name="update", requirements={"id"="\d+"}, methods="PUT")
     */
    public function update(int $id, CustomerRepository $customerRepository, ServiceDeliveryTestsContent $deliveryCheck,DeliveryRepository $deliveryRepository, Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        $currentDelivery = $deliveryRepository->find($id);

        // we get the content of the request in JSON
        $jsonContent = $request->getContent();

        // we decode the content  
        $decode = json_decode($jsonContent, true);
    
        $customerToUpdate = $decode['customer'];
        
        $entityManager = $doctrine->getManager();

        // On vérifie si chaque champs à évolué, si oui on l'update
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
        // On vérifie si il y a des erreurs dans les deux entités
        $updateErrorsOnDelivery = $validator->validate($currentDelivery);
        $updateErrorsOnCustomer = $validator->validate($currentDelivery->getCustomer());
       
        if ( (count($updateErrorsOnDelivery) > 0  && count($updateErrorsOnCustomer) > 0) || (count($updateErrorsOnDelivery) > 0  || count($updateErrorsOnCustomer) > 0) )
        {   
            return JsonErrorResponse::sendValidatorErrorsOnManyEntities($updateErrorsOnDelivery, $updateErrorsOnCustomer);
        
        } else {
        
            // Dans le cas où il n'y a pas d'erreur, on modifie la date de mise à jour
            $currentDelivery->setUpdatedAt(new DateTime());
            $entityManager->flush();
            return $this->json($currentDelivery, Response::HTTP_ACCEPTED, [], ['groups' => "api_deliveries_details"]);
        }
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

        //If the delivery doesn't exist
        if (is_null($deliveryToDelete)) {
            return JsonErrorResponse::sendError("Cette livraison est inconnue", 404);
        }

        $entityManager->remove($deliveryToDelete);
        $entityManager->flush();

        return $this->json($deliveryToDelete, Response::HTTP_OK, [], ['groups' => "api_delivery_deleted"]);
    }
}
