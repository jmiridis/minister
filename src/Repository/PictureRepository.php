<?php

namespace App\Repository;

use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Picture>
 *
 * @method Picture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picture[]    findAll()
 * @method Picture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Picture::class);
    }

    public function findAllActive()
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where($qb->expr()->eq('p.isActive', $qb->expr()->literal(true)))
            ->orderBy('p.position');

        return $qb->getQuery()->getResult();
    }

    public function findAllSorted()
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->orderBy('p.position');

        return $qb->getQuery()->getResult();
    }

    public function save(Picture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Picture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws Exception
     */
    public function moveToPosition(Picture $picture, int $newPosition): void
    {
        $id              = $picture->getId();
        $currentPosition = $picture->getPosition();
        if ($currentPosition === $newPosition) {
            return;
        }
        if ($currentPosition < $newPosition) {
            $query = "UPDATE picture SET position = position - 1 WHERE position > $currentPosition and position <= $newPosition;";
        } else {
            $query = "UPDATE picture SET position = position + 1 WHERE position >= $newPosition and position <= $currentPosition;";
        }
        $query .= "UPDATE picture SET position = $newPosition WHERE id = $id";

        $this->getEntityManager()->getConnection()->executeQuery($query);
    }

    /**
     * @throws Exception
     */
    public function insert(Picture $picture): void
    {
        $id    = $picture->getId();
        $query = "UPDATE picture SET position = position + 1;";
        $query .= "UPDATE picture SET position = 1 WHERE id = $id";

        $this->getEntityManager()->getConnection()->executeQuery($query);
    }
}
