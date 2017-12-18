<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Item;
use AppBundle\Entity\Record;
use \DateTime;

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
            case "ListIdentifiers":
                $response->setContent($this->oaiListIdentifiers($request, $params));
                break;
            case "ListRecords":
                $response->setContent($this->oaiListRecords($request, $params));
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
            if (!$this->insideDateSelection($item, $params)) {
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
            if (!$this->insideDateSelection($item, $params)) {
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

    public function insideDateSelection(Item $item, array $params)
    {
        if (!isset($params["from"]) and !isset($params["until"])) {
            return true;
        }
        $checkDates = array("from" => false, "until" => false);
        foreach ($checkDates as $key => $value) {
            if (isset($params[$key])) {
                $checkDates[$key] = DateTime::createFromFormat(
                    'Y-m-d\TH:i:sZ',
                    $params[$key]
                );
                if (! $checkDates[$key]) {
                    $checkDates[$key] = DateTime::createFromFormat(
                        'Y-m-d',
                        $params[$key]
                    );
                }
            }
        }
        if ($checkDates["from"]
            and $item->getTimestamp() < $checkDates["from"]) {
            return false;
        } elseif ($checkDates["until"]
            and $item->getTimestamp() > $checkDates["until"]) {
            return false;
        }
        return true;

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
                case "resumptionToken":
                case "set":
                    continue 2;
                case "from":
                case "until":
                    if ($this->validateOaiDate($oaipmhkeys[$key])) {
                        continue 2;
                    }
            }
            unset($oaipmhkeys[$key]);
        }
        return $oaipmhkeys;
    }

    protected function validateOaiDate(String $date)
    {
        $long = DateTime::createFromFormat(
            'Y-m-d\TH:i:sZ',
            $date
        );
        if ($long && $long->format('Y-m-d\TH:i:sZ')) {
            return true;
        } else {
            $short = DateTime::createFromFormat(
                'Y-m-d',
                $date
            );
            return $short && $short->format('Y-m-d') == $date;
        }
    }
}
