<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Item;
use AppBundle\Entity\Record;

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
                $response->setContent($this->oaiIdentify($request, $params));
                break;
            case "ListSets":
                $response->setContent($this->oaiListSets($request, $params));
                break;
            case "ListMetadataFormats":
                $response->setContent($this->oaiListMetadataFormats($request, $params));
                break;
            case "GetRecord":
                $response->setContent($this->oaiGetRecord($request, $params));
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

    protected function oaiIdentify(Request $request, array $params)
    {
        if ($request->query->count() != 1) {
            $template = 'errors/badArgument.xml.twig';
        } else {
            $template = 'verbs/identify.xml.twig';
        }
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Repository')
            ->find(1);
        return $this->renderView($template, array(
            "params" => $params,
            "repository" => $repository,
        ));
    }

    protected function oaiListSets(Request $request, array $params)
    {
        if ($request->query->count() !=  1 or
            ($request->query->count() == 2 and ! $request->query->has('resumptionToken'))) {
                return $this->renderView('errors/badArgument.xml.twig', array(
                    "params" => $params,
                ));
        }
        return $this->renderView('errors/noSetHierarchy.xml.twig', array(
                "params" => $params
            ));
    }

    protected function oaiListMetadataFormats(Request $request, array $params)
    {
        /* Two modes are possible:
         * Either identifier is set, so we retrieve all MetadataFormats for
         * identifier */
        if ($request->query->count() == 2 and $request->query->has('identifier')) {
            $baseUrl = $this->getRepositoryBaseUrl();
            if (preg_match(
                "/^oai:$baseUrl:(\d+)$/",
                $request->query->get('identifier'),
                $matches
            )) {
                $item = $this->getDoctrine()
                    ->getRepository('AppBundle:Item')
                    ->findOneById($matches[1]);
                if (!is_null($item)) {
                    $metadataFormats = array();
                    foreach ($item->getRecords() as $record) {
                        $metadataFormats[] = $record->getMetadataFormat();
                    }
                    if (count($metadataFormats) == 0) {
                        return $this->renderView('errors/noMetadataFormats.xml.twig', array(
                            "params" => $params,
                        ));
                    }
                    return $this->renderView('verbs/listMetadataFormats.xml.twig', array(
                        "params" => $params,
                        "metadataFormats" => $metadataFormats,
                    ));
                }
            }
            return  $this->renderView('errors/idDoesNotExist.xml.twig', array(
                "params" => $params,
            ));
        /* Or identifier is not set, so we retrieve all MetadataFormats */
        } elseif ($request->query->count() == 1) {
            $metadataFormats = $this->getDoctrine()
                ->getRepository('AppBundle:MetadataFormat')
                ->findAll();
            return $this->renderView('verbs/listMetadataFormats.xml.twig', array(
                    "params" => $params,
                    "metadataFormats" => $metadataFormats
            ));
        } else {
            return $this->renderView('errors/badArgument.xml.twig', array(
                    "params" => $params,
            ));
        }
    }

    public function oaiGetRecord(Request $request, array $params)
    {
        //Check for badArguments
        if ($request->query->count() != 3) {
            $template = 'errors/badArgument.xml.twig';
        } else {
            //Check if id exists
            $baseUrl = $this->getRepositoryBaseUrl();
            if (preg_match(
                "/^oai:$baseUrl:(\d+)$/",
                $request->query->get('identifier'),
                $matches
            )) {
                $item = $this->getDoctrine()
                    ->getRepository('AppBundle:Item')
                    ->findOneById($matches[1]);
                if (is_null($item)) {
                    $template = 'errors/idDoesNotExist.xml.twig';
                } else {
                    //Check whether requested metadataPrefix can be disseminated
                    foreach ($item->getRecords() as $record) {
                        if ($record->getMetadataFormat()->getMetadataPrefix()
                            == $params["metadataPrefix"] ) {
                            return $this->renderView(
                                'verbs/GetRecord.xml.twig',
                                array(
                                    "params" => $params,
                                    "record" => $record,
                                    "item" => $item,
                                )
                            );
                        }
                    }
                }
                //Nothing found to disseminate!
                if (!isset($template)) {
                    $template = 'errors/cannotDisseminateFormat.xml.twig';
                }
            } else {
                $template = 'errors/idDoesNotExist.xml.twig';
            }
        }
        return $this->renderView($template, array(
            "params" => $params,
        ));
    }

    /**
     * @todo find suitable class for these functions
     */

    public function getRepositoryBaseUrl()
    {
        return $this->getDoctrine()
            ->getRepository('AppBundle:Repository')
            ->findOneById(1)
            ->getBaseUrl();
    }

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
            switch ($key) {
                case "identifier":
                case "metadataPrefix":
                    continue 2;
            }
            unset($oaipmhkeys[$key]);
        }
        return $oaipmhkeys;
    }
}
