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

        // We use Faker to generate data
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


        // Creation of Customer Object
        foreach ($customers as $currentCustomer) {

            // new objects customer
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
            'password' => '$2y$13$tkj73PN1HlvnRBgJmp6bIuKe9y8pQgEyCcof9PE.IVkHk1EmjJ4Om',
            'firstname' => 'Sylvain',
            'lastname' => 'Danlaitan',
            'status' => 0,
        ];

        $yves = [
            'email' => 'yves@driver.com',
            'roles' => ['ROLE_DRIVER'],
            'password' => '$2y$13$4tsgE5Xoq9bZwS6qFv2w0eJ07me3djlZ45t3ILA0ejda7HDs4pfPu',
            'firstname' => 'Yves',
            'lastname' => 'Atrovite',
            'status' => 0,
        ];

        $francois = [
            'email' => 'francois@driver.com',
            'roles' => ['ROLE_DRIVER'],
            'password' => '$2y$13$J2YasHbayh8q1NzdjIRQWOqno02jIP1mnRe4/ALCA5jjn/HhGThrW',
            'firstname' => 'François',
            'lastname' => 'Papraissé',
            'status' => 2,
        ];

        $alphonse = [
            'email' => 'alphonse@driver.com',
            'roles' => ['ROLE_DRIVER'],
            'password' => '$2y$13$diKKnUb0MZF6Y.mCvn9vq.BpKIIX6KI.CxnhKJeT1KUji1V113/lC',
            'firstname' => 'Alphonse',
            'lastname' => 'Danlmur',
            'status' => 0,
        ];

        $romain = [
            'email' => 'romain@admin.com',
            'roles' => ['ROLE_ADMIN'],
            'password' => '$2y$13$6FCRBUkk0J7NP6fq6apwue6AexFG6xpFOjrXpJtKVCtnEqVZH7y4m',
            'firstname' => 'Romain',
            'lastname' => 'Defer',
            'status' => null,
        ];

        //Admin
        $loic = [
            'email' => 'loic@admin.com',
            'roles' => ['ROLE_ADMIN'],
            'password' => '$2y$13$kFGvXwj2Gk4vMFaCkUqGieDiw0eOvCvsa5k4CNxvXnyOfA6PJ7JjK',
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
        $userObjects[0] = null;

        foreach ($users as $currentUser) {
            $user = new User;
            $user->setEmail($currentUser['email']);
            $user->setRoles($currentUser['roles']);
            $user->setPassword($currentUser['password']);
            $user->setFirstname($currentUser['firstname']);
            $user->setLastname($currentUser['lastname']);
            $user->setPhoneNumber($faker->mobileNumber());
            if ($currentUser['status'] !== null) {
                $user->setStatus($currentUser['status']);
            }

            $userObjects[] = $user;

            $manager->persist($user);
        }


        // --- DELIVERIES ---

        $nbDeliveries = 10;

        $merchandises = [
            'Sable', 'Béton', 'Gravier', 'Bois', 'Acier', 'Aluminium'
        ];



        for ($i = 0; $i < $nbDeliveries; $i++) {
            // we create a new delivery
            $delivery = new Delivery();
            
            $merchandisesCount = count($merchandises) - 1;
            $merchandisesIndex = rand(0, $merchandisesCount);
            
            $delivery->setMerchandise($merchandises[$merchandisesIndex]);
            // random volume
            $delivery->setVolume($faker->numberBetween(0, 20));
            // random comment
            $delivery->setComment($faker->sentence());
            // random client 
            $customerIndex = rand(0, 3);
            $randomCustomer = $customersObjects[$customerIndex];
            $delivery->setCustomer($randomCustomer);

            // we affect an admin
            $delivery->setAdmin($userObjects[rand(5, 6)]);
            $delivery->setStatus($faker->numberBetween(0, 2));
            if ($delivery->getStatus() == 2) {
                // if the delivery is completed, a driver must be assigned
                $driverIndex = rand(1, 4);
                $delivery->setUpdatedAt(new DateTime());
                $delivery->setDriver($userObjects[$driverIndex]);
            } else {
                $driverIndex = rand(0, 4);
                $delivery->setDriver($userObjects[$driverIndex]);
                //if a driver is affected
                if ($delivery->getDriver() != null) {
                    // if the driver's status is = 1, status delivery = 0
                    if ($delivery->getDriver()->getstatus() === 1) {
                        $delivery->setStatus(0); 
                    } else {
                       
                        $delivery->setStatus(1);
                        $delivery->getDriver()->setStatus(1);
                    }
                } else {
                    $delivery->setStatus(0);
                }
            }
            $delivery->setCreatedAt(new DateTime());
            $manager->persist($delivery);
        }


        $manager->flush();
    }
}
