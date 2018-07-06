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
use AppBundle\Exception\OAIPMHBadResumptionTokenException;
use Doctrine\Common\Persistence\ObjectManager;
use \DateTime;

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
        $moreitems        = false;
        $tokenData = OAIPMHUtils::parseResumptionToken($this->reqParams);
        $this->reqParams = $tokenData["reqParams"];

        $items = $this->em
           ->getRepository('AppBundle:Item')
           ->getItemsOffset($tokenData["offset"]);

        $retRecords[] = array();
        for($i=0;$i<count($items);$i++){
            // check whether item is in time range
            if (!OAIPMHUtils::isItemTimestampInsideDateSelection($items[$i], $this->reqParams)) {
                continue;
            }
            //check whether metadataPrefix is available for item
            foreach ($items[$i]->getRecords() as $record) {
                if ($record->getMetadataFormat()->getMetadataPrefix()
                    == $this->reqParams["metadataPrefix"]) {
                    $retRecords[] = $record;
                }
            }
            //There are more items to be delivered with resumptionToken
            if (count($retRecords) > OAIPMHVerb::THRESHOLD){
                array_pop($retRecords);
                $moreitems=true;
                $offset+=$i;
                break;
            }
        }

        $timestamp = new DateTime();
        $timestamp->modify('+1 hour');

        if($moreitems){
            $this->setResponseParam(
                "resumptionToken",
                OAIPMHUtils::constructResumptionToken($this->reqParams, $tokenData["offset"], $tokenData["cursor"])
            );
        }

        if (count($retRecords) == 0) {
            throw new OAIPMHCannotDisseminateFormatException();
        }

        // Attributes for resumptionToken
        if (array_key_exists("resumptionToken", $this->responseParams)){
            $this->setResponseParam("cursor", $tokenData["cursor"]);
            $this->setResponseParam("expirationDate", $timestamp->format(DateTime::ATOM));
        }
        
        $this->setResponseParam("records", $retRecords);
    }
}
