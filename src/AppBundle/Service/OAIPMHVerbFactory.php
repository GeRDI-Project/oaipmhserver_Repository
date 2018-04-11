<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Service;

use AppBundle\Exception\OAIPMHBadVerbException;
use AppBundle\Exception\OAIPMHIdDoesNotExistException;
use AppBundle\Exception\OAIPMHNoSetHierarchyException;
use AppBundle\Utils\OAIPMHGetRecord;
use AppBundle\Utils\OAIPMHIdentify;
use AppBundle\Utils\OAIPMHListIdentifiers;
use AppBundle\Utils\OAIPMHListMetadataFormats;
use AppBundle\Utils\OAIPMHListMetadataFormatsForItem;
use AppBundle\Utils\OAIPMHListRecords;
use AppBundle\Utils\OAIPMHUtils;
use AppBundle\Utils\OAIPMHVerb;
use Doctrine\Common\Persistence\ObjectManager;

class OAIPMHVerbFactory
{

    /**
     * @var $em Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * Creates the OAIPMHVerb that needs to be executed
     *
     * @var array $params Array from HTTP request
     *
     * @throws OAIPMHException
     *
     * @return AppBundle\Service\OAIPMHVerb
     */
    public function createVerb(array $params)
    {
        OAIPMHUtils::validateOAIPMHArguments($params);

        $baseUrl = $this->retrieveRepositoryBaseUrl();

        if (isset($params["identifier"])) {
            $item = $this->retrieveItem($params["identifier"], $baseUrl);
        }

        switch ($params["verb"]) {
            case "Identify":
                return new OAIPMHIdentify($this->em);
                break;
            case "ListSets":
                throw new OAIPMHNoSetHierarchyException();
                break;
            case "ListMetadataFormats":
                if (isset($item)) {
                    return new OAIPMHListMetadataFormatsForItem($this->em, $params, $item);
                }
                return new OAIPMHListMetadataFormats($this->em);
                break;
            case "GetRecord":
                return new OAIPMHGetRecord($this->em, $params, $item);
                break;
            case "ListIdentifiers":
                $listIdentifiers = new OAIPMHListIdentifiers($this->em, $params, $params);
                print("<!--Start".var_dump($params)."Ende-->");
                $listIdentifiers->setResponseParam("baseUrl", $baseUrl);
                return $listIdentifiers;
                break;
            case "ListRecords":
                $listRecords = new OAIPMHListRecords($this->em, $params);
                $listRecords->setResponseParam("baseUrl", $baseUrl);
                return $listRecords;
                break;
        }

        $badVerb = new OAIPMHBadVerbException();
        $badVerb->setReason("Verb ".$params["verb"]." unknown");
        throw $badVerb;
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
    protected function retrieveRepositoryBaseUrl()
    {
        return $this->em
            ->getRepository('AppBundle:Repository')
            ->findOneById(1)
            ->getBaseUrl();
    }

    /**
     * Retrieves the item for the given identifier
     *
     * @param String $identifier
     *
     * @throws OAIPMHIdDoesNotExistException
     *
     * @return AppBundle\Entity\Item
     */
    protected function retrieveItem($identifier, $baseUrl)
    {
        //identifiers must comply to a pattern like
        //oai:www.alpendac.eu:123
        if (preg_match("/^oai:$baseUrl:(\d+)$/", $identifier, $matches)) {
            $item = $this->em
                ->getRepository('AppBundle:Item')
                ->findOneById($matches[1]);
            if (!is_null($item)) {
                return $item;
            }
        }
        throw new OAIPMHIdDoesNotExistException($identifier);
    }
}
