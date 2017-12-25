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

        if (count($retItems) == 0) {
            throw new OAIPMHCannotDisseminateFormatException();
        }

        $this->setResponseParam("items", $retItems);
    }
}
