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

    public function findVerifiedBySlug($slug)
    {
        return $this->createQueryBuilder('l')
            ->where('l.slug = :slug')
            ->andWhere('l.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', ListingStatusEnum::VERIFIED)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findVerified()
    {
        return $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->setParameter('status', ListingStatusEnum::VERIFIED)
            ->orderBy('l.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findNotVerified()
    {
        return $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->setParameter('status', ListingStatusEnum::NOT_VERIFIED)
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

}
