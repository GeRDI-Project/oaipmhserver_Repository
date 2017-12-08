<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="oaipmh-server")
     */
    public function indexAction(Request $request)
    {
        $response = new Response();
        switch (strtolower($request->query->get('verb'))) {
            case "identify":
                $response->setContent(
                    $this->renderView('verbs/identify.html.twig')
                );
                break;
            default:
                $response->setContent(
                    $this->renderView('errors/illegalOAIverb.html.twig', array(
                        'base_url' => $request->getBaseUrl(),
                    ))
                );
        }
        $response->headers->set("Content-Type", "text/xml");
        return $response;
    }
}
