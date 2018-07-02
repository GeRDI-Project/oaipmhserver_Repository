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
        $retRecords = array();
        $offset = 0;
        $completeListSize=0;
        $cursor=0;
        $moreitems =false;

         // check whether resumptionToken is avaiable, apply arguments encoded in resumptionToken
        if (array_key_exists("resumptionToken", $this->reqParams)) {
            $tokendata=OAIPMHUtils::parse_resumptionToken($this->reqParams['resumptionToken']);
            $this->reqParams = array_merge($this->reqParams,$tokendata["params"]);
            $offset=$tokendata["offset"];
            $cursor=$tokendata["cursor"]+$this->getThreshold();
            if (in_array(null, $reqParams, true) || in_array('', $reqParams, true)) {
                throw new OAIPMHBadResumptionTokenException();
            }
            $this->setResponseParam("resumptionToken", "");
        }

        $items = $this->em
           ->getRepository('AppBundle:Item')
           ->getNitems($offset,"5");

        

        for($i=0;$i<count($items);$i++){
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
            if (count($retRecords)>$this->getThreshold()){
                array_pop($retRecords);
                $moreitems=true;
                $offset+=$i;
                break;
            }
        }

        $timestamp = new DateTime();
        $timestamp->modify('+1 hour');


        if($moreitems){
            //print(gettype($offset));
            //print(gettype($cursor));
            $resumptionToken = OAIPMHUtils::construct_resumptionToken($this->reqParams, $offset, $cursor);
            $this->setResponseParam("resumptionToken", $resumptionToken);
        }
        else{
            // correct completeListSize to actual Size
            $completeListSize=$cursor+count($retRecords);
        }

        if (count($retRecords) == 0) {
            throw new OAIPMHCannotDisseminateFormatException();
        }

        // Attributes for resumptionToken
        if (array_key_exists("resumptionToken", $this->responseParams)){
            $this->setResponseParam("completeListSize", $completeListSize);
            $this->setResponseParam("cursor", $cursor);
            $this->setResponseParam("expirationDate", $timestamp->format('Y-m-d H:i:sP'));
        }
        
        $this->setResponseParam("records", $retRecords);
    }
}
