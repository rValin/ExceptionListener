<?php

namespace RValin\ExceptionListenerBundle\Controller;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityRepository;
use RValin\ExceptionListenerBundle\Model\ExceptionItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;

class ExceptionApiController extends Controller
{
    /**
     * @Route("/occurence/new",
     *     name="rvalin_exception_occurence_new"
     * )
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleErrorAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $exceptionOccurence = $serializer->deserialize($request->getContent(), $this->container->getParameter('rvalin.exception_listener.occurence_class'), 'json');

        $exceptionManager = $this->get('rvalin.exception_listener.exception_manager');
        $exceptionManager->handleNewOccurence($exceptionOccurence);

        return new JsonResponse(['result' => 200]);
    }

    /**
     * @Route("/list",
     *     name="rvalin_exception_list"
     * )
     * @param Request $request
     * @return Response
     */
    public function listErrorsAction(Request $request)
    {
        $request->request->add(json_decode($request->getContent(), true));
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'order-by' => 'lastSawOn',
            'dir' => 'desc',
            'results' => 10,
            'page' => 1,
        ]);

        $config = $optionsResolver->resolve($request->query->all());

        $itemClass = $this->container->getParameter('rvalin.exception_listener.item_class');
        /** @var EntityRepository $repo */
        $repo = $this->getDoctrine()->getManager()->getRepository($itemClass);
        $itemsQb = $repo->createQueryBuilder('i')
//            ->leftJoin('i.occurences', 'o')
//            ->addSelect('o')
            ->addOrderBy('i.'.$config['order-by'], $config['dir'])
            ->setFirstResult($config['results'] * ($config['page'] - 1))
            ->setMaxResults($config['results'])
        ;

        $search = $request->request->get('search');
        $statuts = $request->request->get('statuts');
        $criticalLevels = $request->request->get('criticalLevels');
        if (!empty($search)) {
            $words = explode(' ', $search);

            foreach ($words as $key => $word) {
                $itemsQb->andWhere('i.name like :word'.$key)
                    ->setParameter('word'.$key, '%'.$word.'%');
            }
        }

        if (!empty($statuts)) {
            $itemsQb->andWhere('i.status in (:status)')
                ->setParameter('status', $statuts);
        }

        if (!empty($criticalLevels)) {
            $itemsQb->andWhere('i.criticalLevel in (:criticalLevels)')
                ->setParameter('criticalLevels', $criticalLevels);
        }


        $items = $itemsQb->getQuery()->getResult();
        $nbItems = $itemsQb->select('count(i.id)')->setMaxResults(null)->setFirstResult(null)->getQuery()->getSingleScalarResult();

        $serializer = $this->get('jms_serializer');

        return new Response(
            $serializer->serialize([
                'items' => $items,
                'config' => $config,
                'nb_items' => (int) $nbItems,
            ], 'json'),
            200,
            ['content-type' => 'application/json']
        );
    }

    /**
     * @Route("/item",
     *     name="rvalin_exception_exception"
     * )
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function errorDetailsAction(Request $request)
    {
        $id = $request->query->get('id');

        if (empty($id)) {
            throw $this->createNotFoundException('Missing id parameter');
        }

        $itemClass = $this->container->getParameter('rvalin.exception_listener.item_class');
        /** @var EntityRepository $repo */
        $entityManager = $this->getDoctrine()->getManager();
        $repo = $entityManager->getRepository($itemClass);
        /** @var EntityRepository $repoOccurence */
        $repoOccurence = $entityManager->getRepository($this->container->getParameter('rvalin.exception_listener.occurence_class'));
        $item = $repo->createQueryBuilder('i')
            ->join('i.occurences', 'o')
            ->addSelect('o')
            ->orderBy('o.exceptionDate', 'desc')
            ->andWhere('i.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if (empty($item)) {
            throw $this->createNotFoundException('Item not found for id '.$id);
        }

        $nbOccurences = $repoOccurence->createQueryBuilder('o')
            ->andWhere('o.exceptionItem = :item')
            ->setParameter('item', $item)
            ->select('count(o.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $nbOccurencesByDays = $this->getCountOccurenceMonth($item);
        $nbOccurencesByHours = $this->getCountOccurenceHours($item);
//        $browserDetails = $this->getBrowserDetail();

        $nbUsers = $repoOccurence->createQueryBuilder('o')
            ->andWhere('o.exceptionItem = :item')
            ->setParameter('item', $item)
            ->select('count(DISTINCT o.user)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
        $nbUsers += $repoOccurence->createQueryBuilder('o')
            ->andWhere('o.exceptionItem = :item')
            ->andWhere('o.user is null')
            ->setParameter('item', $item)
            ->select('count(o.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;



        $serializer = $this->get('jms_serializer');
        return new Response(
            $serializer->serialize([
                'item' => $item,
                'nb_users' => $nbUsers,
                'nb_occurences' => $nbOccurences,
                'nb_occurences_days' => $nbOccurencesByDays,
                'nb_occurences_hours' => $nbOccurencesByHours,
                'browser_stats' => $this->getBrowsersData($item),
            ], 'json'),
            200,
            ['content-type' => 'application/json']
        );
    }

    private function getCountOccurenceMonth(ExceptionItem $exceptionItem)
    {
        $repoOccurence = $this->getDoctrine()
            ->getManager()
            ->getRepository($this->container->getParameter('rvalin.exception_listener.occurence_class'))
        ;

        $previousMonthDate = (new \DateTime())->sub(\DateInterval::createFromDateString('1 month'));
        $nbOccurencesByDays = $repoOccurence->createQueryBuilder('o')
            ->andWhere('o.exceptionItem = :item')
            ->andWhere('SUBSTRING(o.exceptionDate, 1, 10) >= :date')
            ->setParameter('date', $previousMonthDate->format('Y-m-d'))
            ->setParameter('item', $exceptionItem)
            ->select('count(o.id) as nb_occurences')
            ->addSelect('SUBSTRING(o.exceptionDate, 1, 10) as day')
            ->groupBy('day')
            ->getQuery()
            ->getResult()
        ;

        $days = [];
        foreach ($nbOccurencesByDays as $day) {
            $days[$day['day']] = (int) $day['nb_occurences'];
        }

        $today = new \DateTime();
        while ($previousMonthDate->format('Y-m-d') <= $today->format('Y-m-d')) {
            if (empty($days[$previousMonthDate->format('Y-m-d')])) {
                $days[$previousMonthDate->format('Y-m-d')] = 0;
            }
            $previousMonthDate->add(\DateInterval::createFromDateString('1 day'));
        }

        ksort($days);
        return $days;
    }

    private function getCountOccurenceHours(ExceptionItem $exceptionItem)
    {
        $repoOccurence = $this->getDoctrine()
            ->getManager()
            ->getRepository($this->container->getParameter('rvalin.exception_listener.occurence_class'))
        ;

        $previousDayDate = (new \DateTime())->sub(\DateInterval::createFromDateString('1 day'));
        $nbOccurencesByHours = $repoOccurence->createQueryBuilder('o')
            ->andWhere('o.exceptionItem = :item')
            ->andWhere('SUBSTRING(o.exceptionDate, 1, 13) >= :date')
            ->setParameter('date', $previousDayDate->format('Y-m-d H'))
            ->setParameter('item', $exceptionItem)
            ->select('count(o.id) as nb_occurences')
            ->addSelect('SUBSTRING(o.exceptionDate, 1, 13) as hour')
            ->groupBy('hour')
            ->getQuery()
            ->getResult()
        ;

        $hours = [];
        foreach ($nbOccurencesByHours as $hour) {
            $hours[$hour['hour']] = (int) $hour['nb_occurences'];
        }

        $today = new \DateTime();
        while ($previousDayDate->format('Y-m-d H') <= $today->format('Y-m-d H')) {
            if (empty($hours[$previousDayDate->format('Y-m-d H')])) {
                $hours[$previousDayDate->format('Y-m-d H')] = 0;
            }
            $previousDayDate->add(\DateInterval::createFromDateString('1 hour'));
        }

        ksort($hours);
        return $hours;
    }

    private function getBrowsersData(ExceptionItem $exceptionItem)
    {
        $stats = [];

        foreach($exceptionItem->getOccurences() as $occurence) {
            $browser = $occurence->getBrowser();

            if (empty($browser)) {
                $browserName = 'unknow';
                $browserVersion = 'unknow';
                $osName = 'unknow';
                $osVersion = 'unknow';
            } else {
                $browserName = $browser->browser->getName();
                $browserVersion = $browser->browser->toString();
                $osName = $browser->os->getName();
                $osVersion = $browser->os->toString();
            }
            $stats['browser'][$browserName] = empty($stats['browser'][$browserName]) ? 1 : $stats['browser'][$browserName] + 1;
            $stats['browser_version'][$browserVersion] = empty($stats['browser_version'][$browserVersion]) ? 1 : $stats['browser_version'][$browserVersion] + 1;
            $stats['os'][$osName] = empty($stats['os'][$osName]) ? 1 : $stats['os'][$osName] + 1;
            $stats['os_version'][$osVersion] = empty($stats['os_version'][$osVersion]) ? 1 : $stats['os_version'][$osVersion] + 1;
        }

        return $stats;
    }

    /**
     * @Route("/update",
     *     name="rvalin_exception_update"
     * )
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateErrorStatus(Request $request)
    {
        $id = $request->query->get('id');
        $request->request->add(json_decode($request->getContent(), true));
        if (empty($id)) {
            throw $this->createNotFoundException('Missing id');
        }

        $status = $request->request->get('status');
        $criticalLevel = $request->request->get('criticalLevel');


        $itemClass = $this->container->getParameter('rvalin.exception_listener.item_class');
        /** @var EntityRepository $repo */
        $repo = $this->getDoctrine()->getManager()->getRepository($itemClass);
        $item = $repo->find($id);
        if (!$item instanceof ExceptionItem) {
            throw $this->createNotFoundException(sprintf('ExceptionItem %s not found', $id));
        }

        if (!empty($status) && in_array($status, [ExceptionItem::STATUS_RESOLVED,ExceptionItem::STATUS_REGRESSION,ExceptionItem::STATUS_DELETED,ExceptionItem::STATUS_OPEN,ExceptionItem::STATUS_IGNORE])) {
            $item->setStatus($status);
        }

        if (!empty($criticalLevel) && in_array($criticalLevel, [ExceptionItem::CRITICAL_LEVEL_HIGH,ExceptionItem::CRITICAL_LEVEL_NORMAL,ExceptionItem::CRITICAL_LEVEL_LOW])) {
            $item->setCriticalLevel($criticalLevel);
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['result' => 200]);
    }
}