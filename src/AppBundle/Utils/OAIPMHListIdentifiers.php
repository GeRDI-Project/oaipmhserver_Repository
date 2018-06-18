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

        $qb->add('select', '*')
            ->setFirstResult( $offset )
            ->setMaxResults( $limit );

        // check whether resumptionToken is avaiable, apply arguments encoded in resumptionToken
        if (array_key_exists("resumptionToken", $this->reqParams)) {
            $this->reqParams = array_merge($this->reqParams, (OAIPMHUtils::parse_resumptionToken($this->reqParams['resumptionToken'])));
            $this->setResponseParam("resumptionToken", "");
        }


        $items = $this->em
           ->getRepository('AppBundle:Item')
           ->findAll();


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

        $timestamp = new DateTime();
        print("Timestamp test");
        print($timestamp->format('Y-m-d H:i:sP'));
        $timestamp->modify('+1 hour');
        print("Timestamp test");
        print($timestamp->format('Y-m-d H:i:sP'));

        $completeListSize=count($retItems);

        if (array_key_exists("resumptionToken", $this->reqParams)) {
            $offset = OAIPMHUtils::getoffset_resumptionToken($this->reqParams['resumptionToken']);
            $retItems = array_slice($retItems, intval($offset)*$this->getThreshold());
        }

        if (count ($retItems) > $this->getThreshold()){
            // add resumptionToken
            $retItems = array_slice($retItems, 0, $this->getThreshold(), $preserve_keys = TRUE);
            $resumptionToken = OAIPMHUtils::construct_resumptionToken($this->reqParams, $offset);
            $this->setResponseParam("resumptionToken", $resumptionToken);
        }

        // Attributes for resumptionToken
        if (array_key_exists("resumptionToken", $this->responseParams)){
            $this->setResponseParam("completeListSize", $completeListSize);
            $this->setResponseParam("cursor", intval($offset)*$this->getThreshold());
            $this->setResponseParam("expirationDate", $timestamp->format('Y-m-d H:i:sP'));
        }

        $this->setResponseParam("items", $retItems);
    }
}
