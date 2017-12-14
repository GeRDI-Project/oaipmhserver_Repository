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
     * @todo implement fixtures for tests
     */
    public function indexAction(Request $request)
    {
        $response = new Response();
        //params is only for the view, since the validation fails if given params
        //are not valid
        $params = $this->cleanOAIPMHkeys($request->query->all());
        switch ($request->query->get('verb')) {
            case "Identify":
                if ($request->query->count() != 1) {
                    $template = 'errors/badArgument.xml.twig';
                } else {
                    $template = 'verbs/identify.xml.twig';
                }
                $repository = $this->getDoctrine()
                    ->getRepository('AppBundle:Repository')
                    ->find(1);
                $response->setContent(
                    $this->renderView($template, array(
                        "params" => $params,
                        "repository" => $repository,
                    ))
                );
                break;

            case "ListSets":
                if ($request->query->count() !=  1 or
                    ($request->query->count() == 2 and ! $request->query->has('resumptionToken'))) {

                    $response->setContent(
                        $this->renderView('errors/badArgument.xml.twig', array(
                            "params" => $params,
                        ))
                    );
                    continue;
                }
                $response->setContent(
                    $this->renderView('errors/noSetHierarchy.xml.twig', array(
                        "params" => $params
                    ))
                );
                break;

            case "ListMetadataFormats":
                //ListMetadataFormats allows for one optional argument "identifier"
                if ($request->query->count() != 1 or
                    ($request->query->count() == 2 and ! $request->query->has('identifier'))) {
                    $response->setContent(
                        $this->renderView('errors/badArgument.xml.twig', array(
                            "params" => $params,
                        ))
                    );
                    continue;
                }
                $metadataFormats = $this->getDoctrine()
                    ->getRepository('AppBundle:MetadataFormat')
                    ->findAll();
                $response->setContent(
                    $this->renderView('verbs/listMetadataFormats.xml.twig', array(
                        "params" => $params,
                        "metadataFormats" => $metadataFormats
                    ))
                );
                break;

            default:
                $response->setContent(
                    $this->renderView('errors/badVerb.xml.twig', array(
                        "params" => $params
                    ))
                );
        }
        $response->headers->set("Content-Type", "text/xml");
        return $response;
    }
    /**
     * @todo find suitable class for these functions
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
            unset($oaipmhkeys[$key]);
        }
        return $oaipmhkeys;
    }

    public function checkOAIPMHverbs($verb)
    {
        var_dump($verb);
    }
}
