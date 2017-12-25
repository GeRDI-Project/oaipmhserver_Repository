<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Exception;

class OAIPMHNoMetadataFormatsException extends OAIPMHException
{
    public function __construct(String $identifier)
    {
        parent::__construct();
        $this->setReason("No metadata found for item with id " . $identifier);
        $this->setErrorCode("noMetadataFormats");
    }
}
