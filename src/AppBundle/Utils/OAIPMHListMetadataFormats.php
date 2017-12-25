<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Utils;

use AppBundle\Utils\OAIPMHVerb;
use AppBundle\Entity\MetadataFormat;
use Doctrine\Common\Persistence\ObjectManager;

class OAIPMHListMetadataFormats extends OAIPMHVerb
{
    public function __construct(ObjectManager $em)
    {
        parent::__construct($em);
        $this->setName("ListMetadataFormats");
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveResponseParams()
    {
        $this->setResponseParam(
            "metadataFormats",
            $this->em
                ->getRepository('AppBundle:MetadataFormat')
                ->findAll()
        );
    }
}
