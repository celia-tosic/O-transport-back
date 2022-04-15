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
        foreach ($customers as $currentCustomer) {

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
            'roles' => ['ROLE_ADMIN'],
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

        $nbDeliveries = 20;

        $merchandises = [
            'sable', 'béton', 'gravier', 'bois', 'acier', 'aluminium'
        ];



        for ($i = 0; $i < $nbDeliveries; $i++) {
            // On créé une nouvelle livraison
            $delivery = new Delivery();
            // On randomise le choix de la marchandise
            $merchandisesCount = count($merchandises) - 1;
            $merchandisesIndex = rand(0, $merchandisesCount);
            // On affecte la marchandise
            $delivery->setMerchandise($merchandises[$merchandisesIndex]);
            // On génène un volume aléatoire avec Faker
            $delivery->setVolume($faker->numberBetween(0, 20));
            // Idem avec le commentaire
            $delivery->setComment($faker->sentence());
            // On y affecte un client aléatoire
            $customerIndex = rand(0, 3);
            $randomCustomer = $customersObjects[$customerIndex];
            $delivery->setCustomer($randomCustomer);

            // On attribue un administrateur
            $delivery->setAdmin($userObjects[rand(5, 6)]);
            $delivery->setStatus($faker->numberBetween(0, 2));
            if ($delivery->getStatus() == 2) {
                // Si la livraison est terminé, elle a forcément un chauffeur
                $driverIndex = rand(1, 4);
                $delivery->setUpdatedAt(new DateTime());
                $delivery->setDriver($userObjects[$driverIndex]);
            } else {
                $driverIndex = rand(0, 4);
                $delivery->setDriver($userObjects[$driverIndex]);
                //Si un driver a été affecté,
                if ($delivery->getDriver() != null) {
                    // Si le status du driver est déjà à 1, le status de la livraison est toujours à 0
                    if ($delivery->getDriver()->getstatus() === 1) {
                        $delivery->setStatus(0); // En cours de livraison
                    } else {
                        // Sinon la livraison est en cours donc on met la livraison et le status du driver a 1
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
