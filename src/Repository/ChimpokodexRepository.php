<?php

namespace App\Repository;

use App\Entity\Chimpokodex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chimpokodex>
 *
 * @method Chimpokodex|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chimpokodex|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chimpokodex[]    findAll()
 * @method Chimpokodex[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChimpokodexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chimpokodex::class);
    }

    /**
     * Fonction permettant de récupérer toutes les données activées
     *
     * @return array
     */
    public function allActivated(): array
    {
        return $this->createQueryBuilder("c")->andWhere("c.status = 'on'")->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @return array
     */
    public function byIdActivated(int $id): array
    {
        return $this->createQueryBuilder("c")->andWhere("c.status = 'on'")->andWhere("c.id = '" . $id . "'")->getQuery()->getResult();
    }

//    /**
//     * @return Chimpokodex[] Returns an array of Chimpokodex objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Chimpokodex
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
