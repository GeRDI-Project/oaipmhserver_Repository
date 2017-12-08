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
        $params = $this->cleanOAIPMHkeys($request->query->all());
        switch ($request->query->get('verb')) {
            case "Identify":
                $response->setContent(
                    $this->renderView('verbs/identify.xml.twig', array(
                        "params" => $params
                    ))
                );
                break;
            case "ListSets":
                $response->setContent(
                    $this->renderView('errors/noSetHierarchy.xml.twig', array(
                        "params" => $params
                    ))
                );
                break;

            default:
                $response->setContent(
                    $this->renderView('errors/illegalOAIverb.xml.twig', array(
                        "params" => $params
                    ))
                );
        }
        $response->headers->set("Content-Type", "text/xml");
        return $response;
    }
    /**
     * @todo find suitable class for this function
     */
    public function cleanOAIPMHkeys(array $oaipmhkeys)
    {
        foreach ($oaipmhkeys as $key => $value) {
            if ($key == "verb") {
                switch ($value) {
                    case "Identify":
                    case "GetRecord":
                    case "ListIdentifiers":
                    case "ListMetadataFormats":
                    case "ListRecords":
                    case "ListSets":
                        continue 2;
                }
            }
            echo "unsetting $key";
            unset($oaipmhkeys[$key]);
        }
        return $oaipmhkeys;
    }
}
