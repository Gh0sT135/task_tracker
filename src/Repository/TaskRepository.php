<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Task;
use App\Enum\TaskStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return list<Task>
     */
    public function search(?string $query = null, ?TaskStatus $status = null, ?int $projectId = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')->addSelect('p')
            ->leftJoin('t.category', 'c')->addSelect('c')
            ->orderBy('t.id', \SortDirection::Ascending);

        if (null !== $query && '' !== trim($query)) {
            $qb->andWhere('LOWER(t.title) LIKE :query')
                ->setParameter('query', '%'.mb_strtolower(trim($query)).'%');
        }

        if (null !== $status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if (null !== $projectId) {
            $qb->andWhere('t.project = :projectId')
                ->setParameter('projectId', $projectId);
        }

        return $qb->getQuery()->getResult();
    }
}
