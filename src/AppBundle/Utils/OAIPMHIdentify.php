<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Utils;

use AppBundle\Utils\OAIPMHVerb;
use Doctrine\Common\Persistence\ObjectManager;

class OAIPMHIdentify extends OAIPMHverb
{
    public function __construct(ObjectManager $em)
    {
        parent::__construct($em);
        $this->setName("Identify");
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveResponseParams()
    {
        $this->setResponseParam(
            "repository",
            $this->em
                ->getRepository('AppBundle:Repository')
                ->findOneById(1)
        );
    }
}
