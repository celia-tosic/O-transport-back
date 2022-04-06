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
use LDAP\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Serializer;
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
     * Post route to create a new delivery + customer
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

        // On prépare la manipulation des données
        $entityManager = $doctrine->getManager();
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

        // On fabrique les tests en testant de récupérer les données dans la table Customer
        $test = $customerRepository->findByName($customerArray['name']);
        $test2 = $customerRepository->findByAddress($customerArray['address']);
        // On vérifie d'abord si le nom existe en BDD

        if (!$test) {
            // si il n'existe pas, on créé un nouveau customer
            $customer = new Customer();
            // On utilise l'autre "clé" du decode pour créer notre customer
            $customer->setName($customerArray['name']);
            $customer->setAddress($customerArray['address']);
            $customer->setPhoneNumber($customerArray['phoneNumber']);
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
                $customer = new Customer();
                // On utilise l'autre "clé" du decode pour créer notre customer
                $customer->setName($customerArray['name']);
                $customer->setAddress($customerArray['address']);
                $customer->setPhoneNumber($customerArray['phoneNumber']);
                $entityManager->persist($customer);
            }
        }

        //on affecte le customer récupéré/créé à la livraison
        $delivery->setCustomer($customer);
        $entityManager->persist($delivery);

        $entityManager->flush();

        // On retourne la réponse adaptée (201 + Location: URL de la ressource)
        return $this->json($delivery, Response::HTTP_CREATED, [], ['groups' => 'api_deliveries_details']);
    }

    /**
     * Get content and route to POST update an existing delivery
     * @Route("/{id}", name="update", requirements={"id"="\d+"}, methods={"GET", "POST"})
     */
    public function update(int $id, DeliveryRepository $deliveryRepository, Request $request, ManagerRegistry $doctrine): Response
    {
        // Permet de sortir les informations en GET correspondant à l'id de la livraison. 
        $currentDelivery = $deliveryRepository->find($id);

        // On récupère le contenu en JSON
        $jsonContent = $request->getContent();

        // On décode le contenu pour pouvoir créer nos entités à partir du tableau 


        if ($jsonContent != "") {
            // Ici nous traitons la méthode POST de la requête
            // On décode le contenu pour pouvoir créer nos entités à partir du tableau 
            $decode = json_decode($jsonContent, true);
            $deliveryToUpdate = $decode['delivery'];
            $customerToUpdate = $decode['customer'];

            $entityManager = $doctrine->getManager(); 

            // dd($currentDelivery->getMerchandise());
            // dd($deliveryToUpdate['merchandise']);

            // On vérifie si chaque champs à évoluer, si oui on l'update
            if ( $currentDelivery->getMerchandise() !== $deliveryToUpdate['merchandise']) {
                $currentDelivery->setMerchandise($deliveryToUpdate['merchandise']);
            }
            if ( $currentDelivery->getVolume() !== $deliveryToUpdate['volume']) {
                $currentDelivery->setVolume($deliveryToUpdate['volume']);
            }
            if ( $currentDelivery->getComment() !== $deliveryToUpdate['comment']) {
                $currentDelivery->setComment($deliveryToUpdate['comment']);
            }
            //TODO il faut réfléchir à un moyen pour que l'UpdatedAt n'évolue que si il y a une modification au dessus
            $currentDelivery->setUpdatedAt(new DateTime());

            if ( $currentDelivery->getCustomer()->getName() !== $customerToUpdate['name']) {
                $currentDelivery->getCustomer()->setName($customerToUpdate['name']);
            }
            if ( $currentDelivery->getCustomer()->getAddress() !== $customerToUpdate['address']) {
                $currentDelivery->getCustomer()->setAddress($customerToUpdate['address']);
            }
            if ( $currentDelivery->getCustomer()->getPhoneNumber() !== $customerToUpdate['phoneNumber']) {
                $currentDelivery->getCustomer()->setPhoneNumber($customerToUpdate['phoneNumber']);
            }

            $entityManager->flush();

            return $this->json($currentDelivery, Response::HTTP_ACCEPTED, [], ['groups' => "api_deliveries_details"]);


        } else {
            // Ici nous traitons la méthode GET de la requête
            return $this->json($currentDelivery, Response::HTTP_OK, [], ['groups' => "api_deliveries_details"]);
        }
    }
}