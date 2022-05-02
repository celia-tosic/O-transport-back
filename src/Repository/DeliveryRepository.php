<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Delivery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Delivery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Delivery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Delivery[]    findAll()
 * @method Delivery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Delivery::class);
    }

    /**
     * return all deliveries pending
     *
     */
    public function findPendingDeliveries() {

        $entityManager = $this->getEntityManager(); 

        $query = $entityManager->createQuery(
            'SELECT d, c, u
            FROM App\Entity\Delivery d
            INNER JOIN d.customer c
            LEFT JOIN d.driver u 
            WHERE d.status = 0
            ORDER BY d.driver'
        );

        return $query->getResult(); 
    }

        /**
     * return all deliveries shipping
     *
     */
    public function findShippingDeliveries() {

        $entityManager = $this->getEntityManager(); 

        $query = $entityManager->createQuery(
            'SELECT d, c
            FROM App\Entity\Delivery d
            INNER JOIN d.customer c
            WHERE d.status = 1'
        );

        return $query->getResult(); 
    }

    /**
     * return all deliveries pending
     *
     */
    public function findCompletedDeliveries() {

        $entityManager = $this->getEntityManager(); 

        $query = $entityManager->createQuery(
            'SELECT d, c
            FROM App\Entity\Delivery d
            INNER JOIN d.customer c
            WHERE d.status = 2
            ORDER BY d.updated_at DESC'
        );

        return $query->getResult(); 
    }
    /**

     * return all deliveries for one driver
     */
    public function findAllDeliveriesByDriver($id) {

        $entityManager = $this->getEntityManager(); 

        $query = $entityManager->createQuery(
            'SELECT d 
            FROM App\Entity\Delivery d 
            WHERE d.driver = :id
            AND (d.status = 1 OR d.status = 0)
            ORDER BY d.status DESC' 
        )->setParameter('id', $id);
            

        return $query->getResult(); 
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Delivery $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Delivery $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Delivery[] Returns an array of Delivery objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Delivery
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
