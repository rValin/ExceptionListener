<?php

namespace RValin\ExceptionListenerBundle\Manager;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use RValin\ExceptionListenerBundle\Model\ExceptionItem;
use RValin\ExceptionListenerBundle\Model\ExceptionOccurrence;
use RValin\ExceptionListenerBundle\Provider\ExceptionItemProvider;

class ExceptionManager
{
    /**
     * @var ExceptionItemProvider
     */
    private $exceptionItemProvider;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $exceptionItemClass;

    /**
     * ExceptionManager constructor.
     * @param ExceptionItemProvider $exceptionItemProvider
     * @param EntityManager $entityManager
     * @param $exceptionItemClass
     */
    public function __construct(ExceptionItemProvider $exceptionItemProvider, EntityManager $entityManager, $exceptionItemClass)
    {
        $this->entityManager = $entityManager;
        $this->exceptionItemProvider = $exceptionItemProvider;
        $this->exceptionItemClass = $exceptionItemClass;
    }

    /**
     * Save a new occurence
     * @param ExceptionOccurrence $exceptionOccurrence
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleNewOccurence(ExceptionOccurrence $exceptionOccurrence)
    {
        $exceptionOccurrence->setExceptionDate(new \DateTime());
        $exceptionItem = $this->getExceptionItemForOccurence($exceptionOccurrence);

        if ($exceptionItem->getStatus() === ExceptionItem::STATUS_DELETED || $exceptionItem->getStatus() === ExceptionItem::STATUS_RESOLVED) {
            $exceptionItem->setStatus(ExceptionItem::STATUS_REGRESSION);
        }

        $exceptionItem->addOccurence($exceptionOccurrence);
        $exceptionItem->setLastSawOn($exceptionOccurrence->getExceptionDate());
        $this->entityManager->persist($exceptionOccurrence);
        $this->entityManager->flush();
    }

    /**
     * @param ExceptionOccurrence $exceptionOccurrence
     * @return mixed|ExceptionOccurrence
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getExceptionItemForOccurence(ExceptionOccurrence $exceptionOccurrence)
    {
        $exceptionItem = $this->exceptionItemProvider->findItem($exceptionOccurrence);

        if (!empty($exceptionItem)) {
            return $exceptionItem;
        }

        $exceptionItem = new $this->exceptionItemClass();
        if (!$exceptionItem instanceof ExceptionItem) {
            throw new \InvalidArgumentException(sprintf(
                'The class %s should be an instance on %s',
                $this->exceptionItemClass,
                'RValin\ExceptionListenerBundle\Model\ExceptionItem'
            ));
        }

        $exceptionItem->setFirstSawOn($exceptionOccurrence->getExceptionDate());
        $exceptionItem->setName($exceptionOccurrence->getMessage());
        $exceptionItem->setExceptionClass($exceptionOccurrence->getExceptionClass());
        $exceptionItem->setLanguage($exceptionOccurrence->getLanguage());
        $this->entityManager->persist($exceptionItem);

        return $exceptionItem;
    }
}