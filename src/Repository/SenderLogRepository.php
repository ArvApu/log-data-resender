<?php

namespace App\Repository;

use App\Entity\SenderLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SenderLog>
 *
 * @method SenderLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SenderLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SenderLog[]    findAll()
 * @method SenderLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SenderLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SenderLog::class);
    }

    public function save(SenderLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SenderLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
