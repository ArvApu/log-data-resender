<?php

declare(strict_types=1);

namespace App\Repository;

use App\Constant\Enum\ResendJobStatus;
use App\Entity\ResendJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResendJob>
 */
class ResendJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResendJob::class);
    }

    /**
     * @return ResendJob[]
     */
    public function findLatest(int $limit = 25): array
    {
        return $this->createQueryBuilder('job')
            ->orderBy('job.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findNextQueued(): ?ResendJob
    {
        return $this->createQueryBuilder('job')
            ->andWhere('job.status = :status')
            ->setParameter('status', ResendJobStatus::QUEUED->value)
            ->orderBy('job.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
