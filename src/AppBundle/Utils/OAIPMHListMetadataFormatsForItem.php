<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Utils;

use AppBundle\Entity\Item;
use AppBundle\Exception\OAIPMHNoMetadataFormatsException;
use AppBundle\Utils\OAIPMHVerb;
use Doctrine\Common\Persistence\ObjectManager;

class OAIPMHListMetadataFormatsForItem extends OAIPMHItemVerb
{
    public function __construct(ObjectManager $em, array $reqParams, Item $item)
    {
        parent::__construct($em, $reqParams, $item);
        $this->setName("ListMetadataFormats");
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveResponseParams()
    {
        $metadataFormats = array();
        foreach ($this->item->getRecords() as $record) {
            $metadataFormats[] = $record->getMetadataFormat();
        }
        if (count($metadataFormats) == 0) {
            throw new OAIPMHNoMetadataFormatsException($this->item->getId());
        }
        $this->setResponseParam("metadataFormats", $metadataFormats);
        return $this;
    }
}
