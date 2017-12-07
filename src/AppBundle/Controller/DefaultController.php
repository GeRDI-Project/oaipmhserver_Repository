<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="oaipmh-server")
     */
    public function indexAction(Request $request)
    {
        // get verb from request
        switch (strtolower($request->query->get('verb'))) {
            case "identify":
                return $this->render('verbs/identify.html.twig');
            default:
                return $this->render('errors/illegalOAIverb.html.twig', array(
                    'base_url'       => $request->getBaseUrl(),
                ));
        }
    }
}
