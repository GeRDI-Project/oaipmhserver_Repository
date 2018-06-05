<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */

namespace AppBundle\Utils;

use AppBundle\Exception\OAIPMHCannotDisseminateFormatException;
use AppBundle\Utils\OAIPMHUtils;
use AppBundle\Utils\OAIPMHVerb;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use AppBundle\Entity\ResumptionToken;
// use Ramsey\Uuid\Uuid;
// use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;


class OAIPMHListIdentifiers extends OAIPMHParamVerb
{
    public function __construct(ObjectManager $em, array $reqParams)
    {
        parent::__construct($em, $reqParams);
        $this->setName("ListIdentifiers");
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveResponseParams()
    {
        $items = $this->em
           ->getRepository('AppBundle:Item')
           ->findAll();

        $retItems = array();
        $offset = 0;

        // check whether resumptionToken is avaiable, apply arguments encoded in resumptionToken
        if (array_key_exists("resumptionToken", $this->reqParams)) {
            $this->reqParams = array_merge($this->reqParams, (OAIPMHUtils::parse_resumptionToken($this->reqParams['resumptionToken'], $this->em)));
        }

        foreach ($items as $item) {
            if (!OAIPMHUtils::isItemTimestampInsideDateSelection($item, $this->reqParams)) {
                continue;
            }

            //check whether metadataPrefix is available for item
            foreach ($item->getRecords() as $record) {
                if ($record->getMetadataFormat()->getMetadataPrefix()
                    == $this->reqParams["metadataPrefix"]) {
                    $retItems[] = $item;
                }
            }
        }

        if (array_key_exists("resumptionToken", $this->reqParams)) {
            $offset = OAIPMHUtils::getoffset_resumptionToken($this->reqParams['resumptionToken'], $this->em);
            $retItems = array_slice($retItems, intval($offset)*$this->getThreshold());
        }

        if (count ($retItems) > $this->getThreshold()){
            // add resumptionToken
            $retItems = array_slice($retItems, 0, $this->getThreshold(), $preserve_keys = TRUE);
            $resumptionToken = OAIPMHUtils::construct_resumptionToken($this->reqParams, $offset, $this->em);
            $this->setResponseParam("resumptionToken", $resumptionToken);
        }

        $this->setResponseParam("items", $retItems);
    }
}
