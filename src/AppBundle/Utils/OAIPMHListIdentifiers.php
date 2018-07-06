<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */

namespace AppBundle\Utils;

use AppBundle\Exception\OAIPMHCannotDisseminateFormatException;
use AppBundle\Exception\OAIPMHBadResumptionTokenException;
use AppBundle\Utils\OAIPMHUtils;
use AppBundle\Utils\OAIPMHVerb;
use Doctrine\Common\Persistence\ObjectManager;
use \DateTime;

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
        $moreitems        = false;
        $tokenData = OAIPMHUtils::parseResumptionToken($this->reqParams);
        $this->reqParams = $tokenData["reqParams"];

        $items = $this->em
           ->getRepository('AppBundle:Item')
           ->getItemsOffset($tokenData["offset"]);

        $retItems = array();
        for ($i=0; $i<count($items); $i++) {
            if (!OAIPMHUtils::isItemTimestampInsideDateSelection($items[$i], $this->reqParams)) {
                continue;
            }
            //check whether metadataPrefix is available for item
            foreach ($items[$i]->getRecords() as $record) {
                if ($record->getMetadataFormat()->getMetadataPrefix()
                    == $this->reqParams["metadataPrefix"]) {
                    $retItems[] = $items[$i];
                }
            }
            if (count($retItems) > OAIPMHVerb::THRESHOLD) {
                array_pop($retItems);
                $moreitems=true;
                $tokenData["offset"]+=$i;

                break;
            }
        }

        $timestamp = new DateTime();
        $timestamp->modify('+1 hour');

        if ($moreitems) {
            $this->setResponseParam(
                "resumptionToken",
                OAIPMHUtils::constructResumptionToken($this->reqParams, $tokenData["offset"], $tokenData["cursor"])
            );
        } else {

        }

        if (count($retItems) == 0) {
            throw new OAIPMHCannotDisseminateFormatException();
        }

        // Attributes for resumptionToken
        if (array_key_exists("resumptionToken", $this->responseParams)) {
            $this->setResponseParam("cursor", $tokenData["cursor"]);
            $this->setResponseParam("expirationDate", $timestamp->format(DateTime::ATOM));
        }

        $this->setResponseParam("items", $retItems);
    }
}
