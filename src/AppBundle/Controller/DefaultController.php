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
        $params = OAIUtils::cleanOAIkeys($request->query->all());
        // Check whether the right arguments are given
        $reason = "";
        if ($request->query->has("verb")) {
            if (OAIUtils::badArgumentsForVerb(
                $request->query->all(),
                $request->query->get("verb"),
                $reason
            )) {
                $response->setContent(
                    $this->renderView('errors/badArgument.xml.twig', array(
                        "params" => $params,
                        "reason" => $reason
                    ))
                );
            } else {
                switch ($request->query->get('verb')) {
                    case "Identify":
                        $response->setContent($this->oaiIdentify($params));
                        break;
                    case "ListSets":
                        $response->setContent($this->oaiListSets($params));
                        break;
                    case "ListMetadataFormats":
                        $response->setContent($this->oaiListMetadataFormats($params));
                        break;
                    case "GetRecord":
                        $response->setContent($this->oaiGetRecord($params));
                        break;
                    case "ListIdentifiers":
                        $response->setContent($this->oaiListIdentifiers($params));
                        break;
                    case "ListRecords":
                        $response->setContent($this->oaiListRecords($params));
                        break;
                    default:
                        $response->setContent(
                            $this->renderView('errors/badVerb.xml.twig', array(
                                "params" => $params,
                                "message" => "Verb ".$request->query->get("verb")." unknown"
                            ))
                        );
                }
            }
        } else {
            $response->setContent(
                $this->renderView('errors/badVerb.xml.twig', array(
                    "params" => $params,
                    "message" => "No verb given",
                ))
            );
        }
        $response->headers->set("Content-Type", "text/xml");
        return $response;
    }

    protected function oaiIdentify(array $params)
    {
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Repository')
            ->findOneById(1);
        return $this->renderView('verbs/Identify.xml.twig', array(
            "params" => $params,
            "repository" => $repository,
        ));
    }

    protected function oaiListSets(array $params)
    {
        return $this->renderView('errors/noSetHierarchy.xml.twig', array(
                "params" => $params
            ));
    }

    protected function oaiListMetadataFormats(array $params)
    {
        /* Two modes are possible:
         * Either identifier is set, so we retrieve all MetadataFormats for
         * identifier */
        if (isset($params['identifier'])) {
            $baseUrl = $this->getRepositoryBaseUrl();
            if (preg_match(
                "/^oai:$baseUrl:(\d+)$/",
                $params['identifier'],
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
        } else {
            $metadataFormats = $this->getDoctrine()
                ->getRepository('AppBundle:MetadataFormat')
                ->findAll();
            return $this->renderView('verbs/ListMetadataFormats.xml.twig', array(
                    "params" => $params,
                    "metadataFormats" => $metadataFormats
            ));
        }
    }

    public function oaiGetRecord(array $params)
    {
        //Check if id exists
        $baseUrl = $this->getRepositoryBaseUrl();
        if (preg_match(
            "/^oai:$baseUrl:(\d+)$/",
            $params['identifier'],
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
        
        return $this->renderView($template, array(
            "params" => $params,
        ));
    }
    
    public function oaiListIdentifiers(array $params)
    {
        $error = false;
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
                    == $params["metadataPrefix"]) {
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

    public function oaiListRecords(array $params)
    {
        $error = false;

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
                    == $params["metadataPrefix"]) {
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
