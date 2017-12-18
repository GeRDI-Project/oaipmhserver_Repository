<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Item;
use AppBundle\Entity\Record;
use AppBundle\Utils\OAIUtils;

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
        $params = OAIUtils::cleanOAIkeys($request->query->all());
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
            case "ListIdentifiers":
                $response->setContent($this->oaiListIdentifiers($request, $params));
                break;
            case "ListRecords":
                $response->setContent($this->oaiListRecords($request, $params));
                break;
            default:
                if (! $request->query->has("verb")) {
                    $message = "No verb given";
                } else {
                    $message = "Verb " . $request->query->get("verb") . " unknown";
                }
                $response->setContent(
                    $this->renderView('errors/badVerb.xml.twig', array(
                        "params" => $params,
                        "message" => $message
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
            $template = 'verbs/Identify.xml.twig';
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
                    return $this->renderView('verbs/ListMetadataFormats.xml.twig', array(
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
            return $this->renderView('verbs/ListMetadataFormats.xml.twig', array(
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
    
    public function oaiListIdentifiers(Request $request, array $params)
    {
        $error = false;
        //Check for badArguments
        if ((! $request->query->has("metadataPrefix")
                and ! $request->query->has("resumptionToken"))
            or count($request->query) != count($params)
            or count($params) > 2 and $request->query->has("resumptionToken")) {
            $template = 'errors/badArgument.xml.twig';
            $error = true;
        }

        //Check whether there is a set-selection (not supported yet)
        if (! $error and isset($params["set"])) {
            $template = 'errors/noSetHierarchy.xml.twig';
            $error = true;
        }

        //Check for a resumptionToken (not supported yet)
        if (! $error and isset($params["resumptionToken"])) {
            $template = 'errors/badResumptionToken.xml.twig';
            $error = true;
        }

        if ($error) {
            return $this->renderView($template, array("params" => $params));
        }
        

        $items = $this->getDoctrine()
           ->getRepository('AppBundle:Item')
           ->findAll();

        $retItems = array();
        foreach ($items as $item) {
            if (!OAIUtils::isItemTimestampInsideDateSelection($item, $params)) {
                continue;
            }
            //check whether metadataPrefix is available for item
            foreach ($item->getRecords() as $record) {
                if ($record->getMetadataFormat()->getMetadataPrefix()
                    == $request->query->get("metadataPrefix")) {
                    $retItems[] = $item;
                }
            }
        }
        if (count($retItems) == 0) {
            return $this->renderView(
                'errors/cannotDisseminateFormat.xml.twig',
                array(
                    "params" => $params,
                )
            );
        } else {
            return $this->renderView(
                'verbs/ListIdentifiers.xml.twig',
                array(
                    "params" => $params,
                    "items"  => $retItems,
                    "baseUrl" => $this->getRepositoryBaseUrl()
                )
            );
        }
    }

    public function oaiListRecords(Request $request, array $params)
    {
        $error = false;
        //Check for badArguments
        if ((! $request->query->has("metadataPrefix")
                and ! $request->query->has("resumptionToken"))
            or count($request->query) != count($params)
            or count($params) > 2 and $request->query->has("resumptionToken")) {
            $template = 'errors/badArgument.xml.twig';
            $error = true;
        }

        //Check whether there is a set-selection (not supported yet)
        if (! $error and isset($params["set"])) {
            $template = 'errors/noSetHierarchy.xml.twig';
            $error = true;
        }

        //Check for a resumptionToken (not supported yet)
        if (! $error and isset($params["resumptionToken"])) {
            $template = 'errors/badResumptionToken.xml.twig';
            $error = true;
        }

        if ($error) {
            return $this->renderView($template, array("params" => $params));
        }
        

        $items = $this->getDoctrine()
           ->getRepository('AppBundle:Item')
           ->findAll();

        $retVal = array();
        foreach ($items as $item) {
            if (!OAIUtils::isItemTimestampInsideDateSelection($item, $params)) {
                continue;
            }
            //check whether metadataPrefix is available for item
            foreach ($item->getRecords() as $record) {
                if ($record->getMetadataFormat()->getMetadataPrefix()
                    == $request->query->get("metadataPrefix")) {
                    $val["item"] = $item;
                    $val["record"] = $record;
                    $retVal[] = $val;
                }
            }
        }
        if (count($retVal) == 0) {
            return $this->renderView(
                'errors/cannotDisseminateFormat.xml.twig',
                array(
                    "params" => $params,
                )
            );
        } else {
            return $this->renderView(
                'verbs/ListRecords.xml.twig',
                array(
                    "params" => $params,
                    "retVal"  => $retVal,
                    "baseUrl" => $this->getRepositoryBaseUrl()
                )
            );
        }
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
}
