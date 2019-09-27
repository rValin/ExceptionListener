<?php

namespace RValin\ExceptionListenerBundle\Provider;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use RValin\ExceptionListenerBundle\Model\ExceptionItem;
use RValin\ExceptionListenerBundle\Model\ExceptionOccurrence;

class ExceptionItemProvider
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    public function __construct(EntityManager $entityManager, $itemClass)
    {
        $this->repository = $entityManager->getRepository($itemClass);
    }

    /**
     * @param ExceptionOccurrence $occurence
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findItem(ExceptionOccurrence $occurence)
    {
        $qb = $this->repository->createQueryBuilder('i')
            ->join('i.occurences', 'o')
            ->andWhere('o.message = :message')
            ->andWhere('o.file = :file')
            ->setParameter('message', $occurence->getMessage())
            ->setParameter('file', $occurence->getFile())
            ->setMaxResults(1)
            ->addOrderBy('o.exceptionDate', 'desc')
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}