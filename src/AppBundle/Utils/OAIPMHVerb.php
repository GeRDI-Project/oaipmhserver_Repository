<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Utils;

use Doctrine\Common\Persistence\ObjectManager;

abstract class OAIPMHVerb
{
    /**
     * @var String
     */
    protected $name;

    /**
     * @var array Parameters of OAI-PMH response
     */
    protected $responseParams;

    /**
     * @var Number of return items in a single response, further items with resumptionToken
     * Magic Number, @todo find a better place (config?)
     */
    protected $threshold = 1;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
        $this->params = array();
    }

    /**
     * Gets Name
     *
     * @return String Name of OAI-PMH verb
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets Name
     *
     * @param String $name Name of OAI-PMH verb.
     *
     * @return OAIPMHVerb
     */
    public function setName(String $name)
    {
        $this->name =  $name;

        return $this;
    }

    /**
     * Gets response parameters
     *
     * @return array Associative array of response paramaters
     */
    public function getResponseParams()
    {
        $this->retrieveResponseParams();
        return $this->responseParams;
    }


    /**
     * Sets key => value pair in response parameters
     *
     * @param String key
     * @param mixed value
     *
     * @return OAIPMHVerb
     */
    public function setResponseParam(String $key, $value)
    {
        $this->responseParams[$key] = $value;

        return $this;
    }

    /**
     * This function does the actual work in retrieving
     * the information in order to process the OAI-PMH verb
     *
     * @throws OAIPMHException
     *
     * @return OAIPMHVerb
     */
    abstract public function retrieveResponseParams();

    /**
     * Returns how many items max in one response, further items with resumptionTokens
     */
     public function getThreshold()
     {
        return $this->threshold;
     } 
}
