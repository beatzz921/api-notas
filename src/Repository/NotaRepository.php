<?php

namespace App\Repository;

use App\Entity\Nota;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Nota>
 *
 * @method Nota|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nota|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nota[]    findAll()
 * @method Nota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nota::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Nota $entity, bool $flush = true): void
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
    public function remove(Nota $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Nota[] Returns an array of Nota objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Nota
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function filtrar(string $filtro, bool $eliminada, User $usuario): array
    {
        $qb = $this->createQueryBuilder('nota');
        return $qb
            ->innerJoin('nota.tags','tag')
            ->where($qb->expr()->Like('nota.titulo', ":filtro"))
            ->orWhere($qb->expr()->Like('nota.descripcion', ":filtro"))
            ->orWhere($qb->expr()->Like('tag.titulo', ":filtro"))
            ->andWhere('nota.eliminada =:eliminada')
            ->andWhere('nota.usuario =:usuario')
            ->setParameter('filtro', "%" . $filtro . "%")
            ->setParameter('eliminada', $eliminada)
            ->setParameter('usuario', $usuario)
            ->orderBy('nota.titulo', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
