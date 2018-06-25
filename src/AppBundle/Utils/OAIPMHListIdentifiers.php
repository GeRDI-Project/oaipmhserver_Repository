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
use \DateTime;
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
        $retItems = array();
        $offset = 0;
        $completeListSize=0;
        $cursor=0;
        $moreitems =false;

        // check whether resumptionToken is avaiable, apply arguments encoded in resumptionToken
        if (array_key_exists("resumptionToken", $this->reqParams)) {
            $tokendata=OAIPMHUtils::parse_resumptionToken($this->reqParams['resumptionToken']);
            $this->reqParams = array_merge($this->reqParams,$tokendata[0]);
            $offset=$tokendata[1];
            $cursor=$tokendata[2]+$this->getThreshold();
            $this->setResponseParam("resumptionToken", "");
        }

        $items = $this->em
           ->getRepository('AppBundle:Item')
           ->getNitems($offset,"5");
        $completeListSize=count($items)+$offset;


        for($i=0;$i<count($items);$i++){
            //print("Neuer Lauf ".$i);
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
            if (count($retItems)>$this->getThreshold()){
                array_pop($retItems);
                $moreitems=true;
                $offset+=$i;
                break;
            }
        }

        $timestamp = new DateTime();
        $timestamp->modify('+1 hour');

        if($moreitems){
            $resumptionToken = OAIPMHUtils::construct_resumptionToken($this->reqParams, $offset, $cursor);
            $this->setResponseParam("resumptionToken", $resumptionToken);
        }
        else{
            // correct completeListSize to actual Size
            $completeListSize=$cursor+count($retItems);
        }

        // Attributes for resumptionToken
        if (array_key_exists("resumptionToken", $this->responseParams)){
            $this->setResponseParam("completeListSize", $completeListSize);
            $this->setResponseParam("cursor", $cursor);
            $this->setResponseParam("expirationDate", $timestamp->format('Y-m-d H:i:sP'));
        }

        $this->setResponseParam("items", $retItems);
    }
}
