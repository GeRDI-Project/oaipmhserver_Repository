<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Utils;

use AppBundle\Utils\OAIPMHVerb;
use AppBundle\Utils\OAIPMHUtils;
use AppBundle\Exception\OAIPMHCannotDisseminateFormatException;
use Doctrine\Common\Persistence\ObjectManager;

class OAIPMHListRecords extends OAIPMHParamVerb
{

    public function __construct(ObjectManager $em, array $reqParams)
    {
        parent::__construct($em, $reqParams);
        $this->setName("ListRecords");
    }
    /**
     * {@inheritDoc}
     */
    public function retrieveResponseParams()
    {
        $items = $this->em
           ->getRepository('AppBundle:Item')
           ->findAll();

        $retRecords = array();
        $offset = 0;
        $completeListSize = 0;

        // check whether resumptionToken is avaiable, apply arguments encoded in resumptionToken
        if (array_key_exists("resumptionToken", $this->reqParams)) {
            $this->reqParams = array_merge($this->reqParams, (OAIPMHUtils::parse_resumptionToken($this->reqParams['resumptionToken'], $this->em)));
            $this->setResponseParam("resumptionToken", "");
        }

        foreach ($items as $item) {
            if (!OAIPMHUtils::isItemTimestampInsideDateSelection($item, $this->reqParams)) {
                continue;
            }
            //check whether metadataPrefix is available for item
            foreach ($item->getRecords() as $record) {
                if ($record->getMetadataFormat()->getMetadataPrefix()
                    == $this->reqParams["metadataPrefix"]) {
                    $retRecords[] = $record;
                }
            }
        }

        $completeListSize=count($retRecords);

        if (array_key_exists("resumptionToken", $this->reqParams)) {
            $offset = OAIPMHUtils::getoffset_resumptionToken($this->reqParams['resumptionToken'], $this->em);
            $retRecords = array_slice($retRecords, intval($offset)*$this->getThreshold());
        }

        if (count($retRecords) == 0) {
            throw new OAIPMHCannotDisseminateFormatException();
        }

        if (count($retRecords) > $this->getThreshold()){
            $retRecords = array_slice($retRecords, 0, $this->getThreshold(), $preserve_keys = TRUE);
            $resumptionToken = OAIPMHUtils::construct_resumptionToken($this->reqParams, $offset, $this->em);
            $this->setResponseParam("resumptionToken", $resumptionToken);
        }

        // Attributes for resumptionToken
        if (array_key_exists("resumptionToken", $this->responseParams)){
            $this->setResponseParam("completeListSize", $completeListSize);
            $this->setResponseParam("cursor", intval($offset)*$this->getThreshold());
        }
        
        $this->setResponseParam("records", $retRecords);
    }
}
