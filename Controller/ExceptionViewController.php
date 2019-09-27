<?php

namespace RValin\ExceptionListenerBundle\Controller;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;

class ExceptionViewController extends Controller
{
    /**
     * @Route("/",
     *     name="rvalin_exception_view"
     * )
     * @param Request $request
     * @return Response
     */
    public function view(Request $request)
    {
        return $this->render('RValinExceptionListenerBundle:Default:app.html.twig');
    }
}