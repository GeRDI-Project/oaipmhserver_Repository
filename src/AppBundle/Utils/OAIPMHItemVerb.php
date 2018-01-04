<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Utils;

use AppBundle\Utils\OAIPMHVerb;
use AppBundle\Entity\Item;
use Doctrine\Common\Persistence\ObjectManager;

abstract class OAIPMHItemVerb extends OAIPMHParamVerb
{
    /**
     * @var AppBundle\Entity\Item Parameters of OAI-PMH request
     */
    protected $item;

    public function __construct(ObjectManager $em, array $reqParams, Item $item)
    {
        parent::__construct($em, $reqParams);
        $this->item = $item;
    }
}
