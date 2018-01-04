<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Utils;

use AppBundle\Entity\Item;
use AppBundle\Exception\OAIPMHCannotDisseminateFormatException;
use AppBundle\Utils\OAIPMHVerb;
use Doctrine\Common\Persistence\ObjectManager;

class OAIPMHGetRecord extends OAIPMHItemVerb
{
    public function __construct(ObjectManager $em, array $reqParams, Item $item)
    {
        parent::__construct($em, $reqParams, $item);
        $this->setName("GetRecord");
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveResponseParams()
    {
        foreach ($this->item->getRecords() as $record) {
            if ($record->getMetadataFormat()->getMetadataPrefix()
                == $this->reqParams["metadataPrefix"] ) {
                $this->setResponseParam("record", $record);
                return $this;
            }
        }
        $cannotDisseminateFormat = new OAIPMHCannotDisseminateFormatException();
        $cannotDisseminateFormat->appendReason($this->reqParams["metadataPrefix"]
            . " for item with id "
            . $this->reqParams['identifier']);
        throw $cannotDisseminateFormat;
    }
}
