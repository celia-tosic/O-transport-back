<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // On utilise Faker pour générer des données aléatoires
        $faker = Factory::create('fr_FR');


        // --- CUSTOMERS ---
        $leroy = [
            'name' => 'Leroy Merlin',
            'address' => '1 Rue de la Garenne, 44700 ORVAULT',
        ];

        $bricodepot = [
            'name' => 'Brico Depot',
            'address' => '33 Rue de la Brocante, 69000 LYON',
        ];

        $mrBricolage = [
            'name' => 'Mr Bricolage',
            'address' => '11 Rue de la Guarrigue, 30000 Nimes',
        ];

        $bricomarche = [
            'name' => 'Bricomarché',
            'address' => 'Rue François Durand, 23300 La Souterraine'
        ];

        $customers[] = $leroy;
        $customers[] = $bricodepot;
        $customers[] = $mrBricolage;
        $customers[] = $bricomarche;
        
        $customersObjects = [];

       
        // Boucle de création d'objet "Customer"
        foreach ($customers as $currentCustomer ) {

            // On fabrique les nouveaux objets customers
            $customer = new Customer();
            $customer->setName($currentCustomer['name']);
            $customer->setAddress($currentCustomer['address']);
            $customer->setPhoneNumber($faker->mobileNumber());
            $customersObjects[] = $customer;
            
            $manager->persist($customer);

        }
        
        // --- USERS ---

        //Drivers
        $sylvain = [
            'email' => 'sylvain@driver.com', 
            'roles' => ['ROLE_DRIVER'], 
            'password' => '$2y$13$5Nszt9g3Ujlyr9SbRlSuJes0aYdx/ceG/CkW7dwpvjqvFSocSjM/C',
            'firstname' => 'Sylvain', 
            'lastname' => 'Danlaitan', 
            'status' => 0,
        ];

        $yves = [
            'email' => 'yves@driver.com', 
            'roles' => ['ROLE_DRIVER'],
            'password' => '$2y$13$5Nszt9g3Ujlyr9SbRlSuJes0aYdx/ceG/CkW7dwpvjqvFSocSjM/C',
            'firstname' => 'Yves', 
            'lastname' => 'Atrovite', 
            'status' => 0,
        ];

        $francois = [
            'email' => 'francois@driver.com', 
            'roles' => ['ROLE_DRIVER'],
            'password' => '$2y$13$5Nszt9g3Ujlyr9SbRlSuJes0aYdx/ceG/CkW7dwpvjqvFSocSjM/C',
            'firstname' => 'François', 
            'lastname' => 'Papraissé', 
            'status' => 2,
        ];

        $alphonse = [
            'email' => 'alphonse@driver.com', 
            'roles' => ['ROLE_DRIVER'],
            'password' => '$2y$13$5Nszt9g3Ujlyr9SbRlSuJes0aYdx/ceG/CkW7dwpvjqvFSocSjM/C',
            'firstname' => 'Alphonse', 
            'lastname' => 'Danlmur', 
            'status' => 0,
        ];

        $romain = [
            'email' => 'romain@admin.com', 
            'roles' => ['ROLE_ADMIN'],
            'password' => '$2y$13$sUv.tOxU2KXZAxiBUFAvReWV/TG87EDsNYChWjp1GPKZOQ7dSjCt.',
            'firstname' => 'Romain', 
            'lastname' => 'Defer', 
            'status' => null,
        ];

        //Admin
        $loic = [
            'email' => 'loic@admin.com',
            'roles' =>['ROLE_ADMIN'],
            'password' => '$2y$13$sUv.tOxU2KXZAxiBUFAvReWV/TG87EDsNYChWjp1GPKZOQ7dSjCt.',
            'firstname' => 'Loic',
            'lastname' => 'Omprampa',
            'status' => null,
        ];

        $users[] = $francois;
        $users[] = $yves;
        $users[] = $alphonse;
        $users[] = $sylvain;
        $users[] = $loic;
        $users[] = $romain;
        $userObjects = [];

        foreach ($users as $currentUser) {
            $user = new User; 
            $user->setEmail($currentUser['email']); 
            $user->setRoles($currentUser['roles']); 
            $user->setPassword($currentUser['password']); 
            $user->setFirstname($currentUser['firstname']); 
            $user->setLastname($currentUser['lastname']); 
            $user->setPhoneNumber($faker->mobileNumber());
            if ($currentUser['status'] !== null ){
                $user->setStatus($currentUser['status']);  
            } 

            $userObjects[] = $user;
            
            $manager->persist($user);
        }
        
    
        // --- DELIVERIES ---
        
        $nbDeliveries = 10;

        $merchandises = [
            'sable', 'béton', 'gravier', 'bois', 'acier', 'aluminium'
        ]; 

        
        
        for ($i = 0; $i < $nbDeliveries; $i++) {

            $deliveryObject = [];
            $delivery = new Delivery();
            $delivery->setStatus($faker->numberBetween(0, 2));
            $merchandisesCount = count($merchandises)-1;
            $merchandisesIndex = rand(0, $merchandisesCount);
            $delivery->setMerchandise($merchandises[$merchandisesIndex]);
            $delivery->setVolume($faker->numberBetween(0,20));
            $delivery->setComment($faker->sentence());
            $delivery->setCreatedAt(new DateTime());
            $customerIndex = rand(0, 3);
            $randomCustomer = $customersObjects[$customerIndex];
            $delivery->setCustomer($randomCustomer);
            $delivery->setAdmin($userObjects[rand(4,5)]);
            $driverIndex = rand(0, 3);
            $delivery->setDriver($userObjects[$driverIndex]);

            $manager->persist($delivery); 
        }


        $manager->flush();
    }
}
