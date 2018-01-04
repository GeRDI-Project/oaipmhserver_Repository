<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Exception;

class OAIPMHBadArgumentException extends OAIPMHException
{
    public function __construct()
    {
        parent::__construct();
        $this->setReason("Bad argument given");
        $this->setErrorCode("badArgument");
    }
}
