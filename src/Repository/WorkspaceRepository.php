<?php

namespace App\Repository;

use App\Entity\Workspace;
use App\Entity\WorkspaceUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Workspace|null find($id, $lockMode = null, $lockVersion = null)
 * @method Workspace|null findOneBy(array $criteria, array $orderBy = null)
 * @method Workspace[]    findAll()
 * @method Workspace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkspaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workspace::class);
    }

    /*
    #[ArrayShape(['total' => "int", 'itemsWithRole' => "array"])]
    public function findByParams(
        #[ArrayShape(['userId' => 'int', 'page' => 'int', 'limit' => 'int|null', 'search' => 'string'])]
        array $params
    ): array
    {
        ['userId' => $userId, 'page' => $page, 'limit' => $limit, 'search' => $search] = $params;
        $query = $this->createQueryBuilder('p')
            ->join(WorkspaceUser::class, 'pu', Join::WITH, 'pu.project = p.id')
            ->andWhere('pu.user = :userId')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('userId', $userId)
            ->select('p, pu.role')
        ;
        if ($search !== '') {
            $query
                ->andWhere('p.name LIKE :search')
                ->setParameter('search', "%$search%")
            ;
        }
        $query->orderBy('p.id', 'DESC');
        $offset = ($page - 1) * $limit;
        if (null !== $limit) {
            $query
                ->setFirstResult($offset)
                ->setMaxResults($limit)
            ;
        }
        $paginator = new Paginator($query);
        $total = $paginator->count();
        /**
         * @var {0: Workspace, role: string,  $items
         * /
        $items = $query->getQuery()->getResult();
        return ['total' => $total, 'itemsWithRole' => $items];
    }
    */

    /**
     * @return array{0: Workspace, role: string, listOrder: int}[]
     */
    public function findByUserId(int $userId): array
    {
        $query = $this->createQueryBuilder('p')
            ->join(WorkspaceUser::class, 'pu', Join::WITH, 'pu.workspace = p.id')
            ->andWhere('pu.user = :userId')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('userId', $userId)
            ->orderBy('pu.listOrder')
            ->addOrderBy('pu.id')
            ->select('p, pu.role, pu.listOrder')
        ;

        return $query->getQuery()->getResult();
    }

    public function isSlugUnique(string $slug): bool
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->select('COUNT(p.slug)')
            ->getQuery()
        ;
        $count = (int)$query->getSingleScalarResult();

        return 0 === $count;
    }
}
