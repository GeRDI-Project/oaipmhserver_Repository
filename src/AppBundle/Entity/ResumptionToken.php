<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Martin Pletl <pletl@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ResumptionToken
 *
 * @ORM\Table(name="resumptionTokens")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ResumptionTokenRepository")
 */
class ResumptionToken
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="params", type="string", length=512)
     */
    private $params;

    public function __construct()
    {
        $this->records = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return ResumptionToken
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get params
     *
     * @return string
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set params
     *
     * @param string $params
     *
     * @return ResumptionToken
     */
    public function setParams($params)
    {
        $this->params= $params;

        return $this;
    }

}
