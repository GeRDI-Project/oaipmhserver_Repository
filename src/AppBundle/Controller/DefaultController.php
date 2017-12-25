<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Item;
use AppBundle\Entity\Record;
use AppBundle\Exception\OAIPMHException;
use AppBundle\Exception\OAIPMHBadResumptionTokenException;
use AppBundle\Exception\OAIPMHIdDoesNotExistException;
use AppBundle\Exception\OAIPMHNoMetadataFormatsException;
use AppBundle\Exception\OAIPMHNoSetHierarchyException;
use AppBundle\Exception\OAIPMHCannotDisseminateFormatException;
use AppBundle\Exception\OAIPMHBadVerbException;
use AppBundle\Utils\OAIPMHUtils;

/**
 *
 * DefaultController for routes that should react like a OAI-PMH server
 *
 */
class DefaultController extends Controller
{
    /**
     * { @inheritDoc }
     * This is the default symfony action for a route
     * @Route("/", name="oaipmh-server")
     */
    public function indexAction(Request $request)
    {
        $response = new Response();
        $params = OAIPMHUtils::cleanOAIPMHkeys($request->query->all());
        // Check whether the right arguments are given
        try {
            if (!$request->query->has("verb")) {
                throw new OAIPMHBadVerbException();
            }
            OAIPMHUtils::badArgumentsForVerb(
                $request->query->all(),
                $request->query->get("verb")
            );
            switch ($request->query->get('verb')) {
                case "Identify":
                    $response->setContent($this->oaipmhIdentify($params));
                    break;
                case "ListSets":
                    $response->setContent($this->oaipmhListSets());
                    break;
                case "ListMetadataFormats":
                    $response->setContent($this->oaipmhListMetadataFormats($params));
                    break;
                case "GetRecord":
                    $response->setContent($this->oaipmhGetRecord($params));
                    break;
                case "ListIdentifiers":
                    $response->setContent($this->oaipmhListIdentifiers($params));
                    break;
                case "ListRecords":
                    $response->setContent($this->oaipmhListRecords($params));
                    break;
                default:
                    $badVerb = new OAIPMHBadVerbException();
                    $badVerb->setReason("Verb ".$request->query->get("verb")." unknown");
                    throw $badVerb;
            }
        } catch (OAIPMHException $e) {
            $response->setContent(
                $this->renderView('errors/oaipmhError.xml.twig', array(
                    "params" => $params,
                    "reason"   => $e->getReason(),
                    "code"     => $e->getErrorCode()
                ))
            );
        }
        $response->headers->set("Content-Type", "text/xml");
        return $response;
    }

    /**
     *
     * Processes a oaipmh - Identify request
     *
     * @param array $params Requested params (they are assumed to be complete and validated)
     *
     * @return String The payload for the http answer in xml
     */
    protected function oaipmhIdentify(array $params)
    {
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Repository')
            ->findOneById(1);
        return $this->renderView('verbs/Identify.xml.twig', array(
            "params" => $params,
            "repository" => $repository,
        ));
    }

    /**
     *
     * Processes a oaipmh - ListSets request
     *
     * @param array $params Requested params (they are assumed to be complete and validated)
     *
     * @throws AppBundle\Exception\OAIPMHNoSetHierarchyException
     *
     * @return String The payload for the http answer in xml
     */
    protected function oaipmhListSets()
    {
        throw new OAIPMHNoSetHierarchyException();
    }

    /**
     *
     * Processes a oaipmh - ListMetadataFormat request
     *
     * @param array $params Requested params (they are assumed to be complete and validated)
     *
     * @throws AppBundle\Exception\OAIPMHException
     *
     * @return String The payload for the http answer in xml
     */
    protected function oaipmhListMetadataFormats(array $params)
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
                        throw new OAIPMHNoMetadataFormatsException($params['identifier']);
                    }
                    return $this->renderView('verbs/ListMetadataFormats.xml.twig', array(
                        "params" => $params,
                        "metadataFormats" => $metadataFormats,
                    ));
                }
            }
            throw new OAIPMHIdDoesNotExistException($params['identifier']);
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

    /**
     *
     * Processes a oaipmh - GetRecord request
     *
     * @param array $params Requested params (they are assumed to be complete and validated)
     *
     * @throws AppBundle\Exception\OAIPMHException
     *
     * @return String The payload for the http answer in xml
     */
    protected function oaipmhGetRecord(array $params)
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
            if (!is_null($item)) {
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
                //Nothing found to disseminate!
                $cannotDisseminateFormat = new OAIPMHCannotDisseminateFormatException();
                $cannotDisseminateFormat->appendReason($params["metadataPrefix"]
                    . " for item with id "
                    . $params['identifier']);
                throw $cannotDisseminateFormat;
            }
        }
        throw new OAIPMHIdDoesNotExistException($params['identifier']);
    }

    /**
     *
     * Processes a oaipmh - ListIdentifiers request
     *
     * @param array $params Requested params (they are assumed to be complete and validated)
     *
     * @return String The payload for the http answer in xml
     */
    public function oaipmhListIdentifiers(array $params)
    {
        //Check whether there is a set-selection (not supported yet)
        if (isset($params["set"])) {
            throw new OAIPMHNoSetHierarchyException();
        }

        //Check for a resumptionToken (not supported yet)
        if (isset($params["resumptionToken"])) {
            throw new OAIPMHBadResumptionTokenException();
        }

        $items = $this->getDoctrine()
           ->getRepository('AppBundle:Item')
           ->findAll();

        $retItems = array();

        foreach ($items as $item) {
            if (!OAIPMHUtils::isItemTimestampInsideDateSelection($item, $params)) {
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
            throw new OAIPMHCannotDisseminateFormatException();
        }

        return $this->renderView(
            'verbs/ListIdentifiers.xml.twig',
            array(
                "params" => $params,
                "items"  => $retItems,
                "baseUrl" => $this->getRepositoryBaseUrl()
            )
        );
    }

    /**
     *
     * Processes a oaipmh - ListRecords request
     *
     * @param array $params Requested params (they are assumed to be complete and validated)
     *
     * @throws AppBundle\Exception\OAIPMHException
     *
     * @return String The payload for the http answer in xml
     */
    public function oaipmhListRecords(array $params)
    {
        //Check whether there is a set-selection (not supported yet)
        if (isset($params["set"])) {
            throw new OAIPMHNoSetHierarchyException();
        }

        //Check for a resumptionToken (not supported yet)
        if (isset($params["resumptionToken"])) {
            throw new OAIPMHBadResumptionTokenException();
        }

        $items = $this->getDoctrine()
           ->getRepository('AppBundle:Item')
           ->findAll();

        $retVal = array();

        foreach ($items as $item) {
            if (!OAIPMHUtils::isItemTimestampInsideDateSelection($item, $params)) {
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
            throw new OAIPMHCannotDisseminateFormatException();
        }

        return $this->renderView(
            'verbs/ListRecords.xml.twig',
            array(
                "params" => $params,
                "retVal"  => $retVal,
                "baseUrl" => $this->getRepositoryBaseUrl()
            )
        );
    }

    /**
     * Retrieves the base url for the repository from the database
     *
     * @internal This function needs to reside in the controller since it is too small
     * for a service but needs the container to retrieve the base url
     * dynamically.
     *
     * @return $string The base url
     */
    protected function getRepositoryBaseUrl()
    {
        return $this->getDoctrine()
            ->getRepository('AppBundle:Repository')
            ->findOneById(1)
            ->getBaseUrl();
    }
}
