<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Utils;

use AppBundle\Exception\OAIPMHCannotDisseminateFormatException;
//mar_debug
use AppBundle\Exception\OAIPMHGoodResTokException;
use AppBundle\Utils\OAIPMHUtils;
use AppBundle\Utils\OAIPMHVerb;
use Doctrine\Common\Persistence\ObjectManager;

class OAIPMHListIdentifiers extends OAIPMHParamVerb
{
    // public function __construct(ObjectManager $em, array $reqParams)
    // {
    //     parent::__construct($em, $reqParams);
    //     $this->setName("ListIdentifiers");
    // }

    /**
     * @var array exclusive Parameters of OAI-PMH request
     */
    protected $exclParams;

    // mar_debug 
    // zusätzlicher paramter löschen, zum testen da, reqParams unbennnen
    public function __construct(ObjectManager $em, array $reqParams, array $exclParams)
    {
        parent::__construct($em, $reqParams);
        $this->exclParams = $exclParams;
        $this->setName("ListIdentifiers");
        print("Hello ListIdentifiers");
        print(var_dump($reqParams));
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

        //check whether resumptionToken is avaiable
        // if (array_key_exists("resumptionToken", $this->reqParams)) {
        //     print("Habe resumptionToken bekommen, stelle Anfrage ein");
        //     $metadataPrefix = explode('-', $this->reqParams['resumptionToken'])[2];
        //     print(var_dump($offset));
        //     //$retItems = array_slice($retItems, intval($offset)*$this->getThreshold());
        //     //$retItems = array_slice($retItems, 1, $preserve_keys = TRUE);
        // }

        foreach ($items as $item) {
            if (!OAIPMHUtils::isItemTimestampInsideDateSelection($item, $this->reqParams)) {
                continue;
            }

            //check whether metadataPrefix is available for item
            foreach ($item->getRecords() as $record) {
                //print("Record".var_dump($record)."RecordEnde\n");
                if ($record->getMetadataFormat()->getMetadataPrefix()
                    == $this->reqParams["metadataPrefix"]) {
                    $retItems[] = $item;
                    //print("item: ".var_dump($item));
                }
            }
        }

        // mar_debubg 
        // comment in
        // if (count($retItems) == 0) {
        //     throw new OAIPMHCannotDisseminateFormatException();
        // }

        $offset = 0;

        if (array_key_exists("resumptionToken", $this->reqParams)) {
            print("Habe resumptionToken bekommen");
            $offset = explode('-', $this->reqParams['resumptionToken'])[1];
            print(var_dump($offset));
            //$retItems = array_slice($retItems, intval($offset)*$this->getThreshold());
            //$retItems = array_slice($retItems, 1, $preserve_keys = TRUE);
        }

        if (count ($retItems) >= $this->getThreshold()){
            // add resumption Token
            print("Zu viele responsedd items\n");
            $retItems = array_slice($retItems, 0, $this->getThreshold(), $preserve_keys = TRUE);
            $this->setResponseParam("resumptionToken", "manualToken-1");
        }

        $this->setResponseParam("items", $retItems);
    }
}
