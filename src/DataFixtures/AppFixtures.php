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
            'address' => '1 Rue de la Garenne, 44300 ORVAULT',
        ];

        $bricodepot = [
            'name' => 'Brico Depot',
            'address' => '33 Rue de la Brocante, 69000 LYON',
        ];

        $customers[] = $leroy;
        $customers[] = $bricodepot;
        
        $customersObjects = [];

       
        // Boucle de création d'objet "Customer"
        foreach ($customers as $currentCustomer ) {

            // On fabrique les nouveaux objets customers
            $customer = new Customer();
            $customer->setName($currentCustomer['name']);
            $customer->setAddress($currentCustomer['address']);
            $customersObjects[] = $customer;
            
            $manager->persist($customer);

        }
        
        // --- USERS ---

        //Drivers
        $francois = [
            'email' => 'francois@driver.com', 
            'roles' => ['ROLE_DRIVER'], 
            'password' => '$2y$13$5Nszt9g3Ujlyr9SbRlSuJes0aYdx/ceG/CkW7dwpvjqvFSocSjM/C',
            'firstname' => 'François', 
            'lastname' => 'Boudin', 
            'status' => 1,
        ];

        $sylvain = [
            'email' => 'sylvain@driver.com', 
            'roles' => ['ROLE_DRIVER'],
            'password' => '$2y$13$5Nszt9g3Ujlyr9SbRlSuJes0aYdx/ceG/CkW7dwpvjqvFSocSjM/C',
            'firstname' => 'Sylvain', 
            'lastname' => 'Lefort', 
            'status' => 0,
        ];

        //Admin
        $loic = [
            'email' => 'loic@admin.com',
            'roles' =>['ROLE_ADMIN'],
            'password' => '$2y$13$sUv.tOxU2KXZAxiBUFAvReWV/TG87EDsNYChWjp1GPKZOQ7dSjCt.',
            'firstname' => 'Loic',
            'lastname' => 'Oclock',
            'status' => null,
           
        ];

        $users[] = $francois;
        $users[] = $sylvain;
        $users[] = $loic;
        $userObjects = [];

        foreach ($users as $currentUser) {
            $user = new User; 
            $user->setEmail($currentUser['email']); 
            $user->setRoles($currentUser['roles']); 
            $user->setPassword($currentUser['password']); 
            $user->setFirstname($currentUser['firstname']); 
            $user->setLastname($currentUser['lastname']); 
            if ($currentUser['status'] !== null ){
                $user->setStatus($currentUser['status']);  
            } 

            $userObjects[] = $user;
            
            $manager->persist($user);
        }
        
    
        // --- DELIVERIES ---
        
        $nbDeliveries = 3;
        
        for ($i = 0; $i < $nbDeliveries; $i++) {

            $deliveryObject = [];
            $delivery = new Delivery();
            $delivery->setStatus($faker->numberBetween(0, 2));
            $delivery->setMerchandise($faker->word());
            $delivery->setVolume($faker->numberBetween(0,20));
            $delivery->setComment($faker->sentence());
            $delivery->setCreatedAt(new DateTime());
            $customerIndex = rand(0, 1);
            $randomCustomer = $customersObjects[$customerIndex];
            $delivery->setCustomer($randomCustomer);
            $delivery->setAdmin($userObjects[2]);
            $driverIndex = rand(0, 1);
            $delivery->setDriver($userObjects[$driverIndex]);

            $manager->persist($delivery); 
        }


        $manager->flush();
    }
}
