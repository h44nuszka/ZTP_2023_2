<?php
/**
 * Tag repository
 */
namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class tag repository
 * @extends ServiceEntityRepository<Tag>
 *
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    /**
     * Construct
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Save entity
     * @param Tag  $entity
     * @param bool $flush
     *
     * @return void
     */
    public function save(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
//
//    /**
//     * Remove entity
//     * @param Tag  $entity
//     * @param bool $flush
//     *
//     * @return void
//     */
//    public function remove(Tag $entity, bool $flush = false): void
//    {
//        $this->getEntityManager()->remove($entity);
//
//        if ($flush) {
//            $this->getEntityManager()->flush();
//        }
//    }
}
