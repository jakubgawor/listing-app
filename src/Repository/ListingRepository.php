<?php

namespace App\Repository;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Listing>
 *
 * @method Listing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Listing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Listing[]    findAll()
 * @method Listing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Listing::class);
    }

    public function findOneBySlugAndStatus(string $slug, string $status)
    {
        return $this->createQueryBuilder('l')
            ->where('l.slug = :slug')
            ->andWhere('l.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', $status)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByStatus(string $status)
    {
        return $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->setParameter('status', $status)
            ->orderBy('l.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug)
    {
        return $this->createQueryBuilder('l')
            ->where('l.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return Listing[] Returns an array of Listing objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Listing
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
