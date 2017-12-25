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

abstract class OAIPMHParamVerb extends OAIPMHVerb
{
    /**
     * @var array Parameters of OAI-PMH request
     */
    protected $reqParams;

    public function __construct(ObjectManager $em, array $reqParams)
    {
        parent::__construct($em);
        $this->reqParams = $reqParams;
    }
}
