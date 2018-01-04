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
use AppBundle\Excepton\OAIPMHCannotDisseminateFormatException;
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

        if (count($retRecords) == 0) {
            throw new OAIPMHCannotDisseminateFormatException();
        }

        $this->setResponseParam("records", $retRecords);
    }
}
