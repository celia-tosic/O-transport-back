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
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/admin/deliveries", name="api_deliveries_")
 */
class ManagingDeliveryController extends AbstractController
{

    /**
     * get list of pending deliveries
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
     * Get list of completed deliveries
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
     * @Route("/create", name="create", methods={"POST"})
     */
    public function create(UserRepository $userRepository, CustomerRepository $customerRepository, Request $request, SerializerInterface $serializer, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        // On récupère le contenu en JSON
        $jsonContent = $request->getContent();

        // On décode le contenu pour pouvoir créer nos entités à partir du tableau 
        $decode = json_decode($jsonContent, true);
        $deliveryArray = $decode['delivery'];
        $customerArray = $decode['customer'];

        // méthode logique de déserialisation du contenu JSON
        //$delivery = $serializer->deserialize($jsonContent, Delivery::class, 'json');

        // a partir du decode, on créé une nouvel livraison
        $delivery = new Delivery(); 
        $delivery->setMerchandise($deliveryArray['merchandise']);
        $delivery->setVolume($deliveryArray['volume']);
        $delivery->setComment($deliveryArray['comment']);
        // On complète les infos qui ne sont pas dans le formulaire
        $delivery->setCreatedAt(new DateTime()); 
        $delivery->setUpdatedAt(null);
        //TODO il faut que l'admin corresponde à l'utilisateur créant la livraison (En session)
        $delivery->setAdmin($userRepository->find(3));
        $delivery->setStatus(0);
        //TODO le customer est ajouté à la mano, mais celui-ci devra être dynamisé en fonction de sa création (ou non)
        $delivery->setCustomer($customerRepository->find(1));


        $entityManager = $doctrine->getManager();
        $entityManager->persist($delivery);

        // On utilise l'autre "clé" du decode pour créer notre customer
        //TODO Il faut être capable de vérifier si le client existe déjà avant de le créer
        $customer = new Customer(); 
        $customer->setName($customerArray['name']);
        $customer->setAddress($customerArray['address']);
        $customer->setPhoneNumber($customerArray['phoneNumber']);
        
        $entityManager->persist($customer);
        $entityManager->flush();

        // On retourne la réponse adaptée (201 + Location: URL de la ressource)
     return $this->json($delivery, Response::HTTP_CREATED, [], ['groups' => 'api_deliveries_details']);
    }
}
