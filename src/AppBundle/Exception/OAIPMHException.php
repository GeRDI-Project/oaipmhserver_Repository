<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Exception;

use Exception;

class OAIPMHException extends Exception
{
    /**
     * @var String Message to be displayed in an OAI-PMH response
     */
    protected $reason;

    /**
     * @var String Code to be set in a error tag in an OAI-PMH response.
     */

    protected $errorCode;

    /**
     * @param String $reason
     *
     * return OAIPMHException
     */
    public function setReason(String $reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return String
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param String $errorCode
     *
     * return OAIPMHException
     */

    public function setErrorCode(String $errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @return String
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param String $additionalText
     *
     * @return OAIPMHException
     */
    public function appendReason($additionalText)
    {
        $this->reason .= $additionalText;
    }
}
