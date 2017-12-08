<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
* @ORM\Table(name="repository")
*/
class Repository
{
    /**
    * @ORM\Column(type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $name;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $baseUrl;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $compression;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $highestId;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $deletedRecord;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $adminEmail;
    
    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $protocolVersion;
    
    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $granularity;
    
    /**
    * @ORM\Column(type="text")
    */
    protected $description;
    
    /**
    * @ORM\Column(type="datetime")
    */
    protected $earliestTimestamp;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Repository
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set baseUrl
     *
     * @param string $baseUrl
     *
     * @return Repository
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Get baseUrl
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set compression
     *
     * @param string $compression
     *
     * @return Repository
     */
    public function setCompression($compression)
    {
        $this->compression = $compression;

        return $this;
    }

    /**
     * Get compression
     *
     * @return string
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     * Set highestId
     *
     * @param string $highestId
     *
     * @return Repository
     */
    public function setHighestId($highestId)
    {
        $this->highestId = $highestId;

        return $this;
    }

    /**
     * Get highestId
     *
     * @return string
     */
    public function getHighestId()
    {
        return $this->highestId;
    }

    /**
     * Set deletedRecord
     *
     * @param string $deletedRecord
     *
     * @return Repository
     */
    public function setDeletedRecord($deletedRecord)
    {
        $this->deletedRecord = $deletedRecord;

        return $this;
    }

    /**
     * Get deletedRecord
     *
     * @return string
     */
    public function getDeletedRecord()
    {
        return $this->deletedRecord;
    }

    /**
     * Set adminEmail
     *
     * @param string $adminEmail
     *
     * @return Repository
     */
    public function setAdminEmail($adminEmail)
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    /**
     * Get adminEmail
     *
     * @return string
     */
    public function getAdminEmail()
    {
        return $this->adminEmail;
    }

    /**
     * Set protocolVersion
     *
     * @param string $protocolVersion
     *
     * @return Repository
     */
    public function setProtocolVersion($protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }

    /**
     * Get protocolVersion
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Set granularity
     *
     * @param string $granularity
     *
     * @return Repository
     */
    public function setGranularity($granularity)
    {
        $this->granularity = $granularity;

        return $this;
    }

    /**
     * Get granularity
     *
     * @return string
     */
    public function getGranularity()
    {
        return $this->granularity;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Repository
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set earliestTimestamp
     *
     * @param \DateTime $earliestTimestamp
     *
     * @return Repository
     */
    public function setEarliestTimestamp($earliestTimestamp)
    {
        $this->earliestTimestamp = $earliestTimestamp;

        return $this;
    }

    /**
     * Get earliestTimestamp
     *
     * @return \DateTime
     */
    public function getEarliestTimestamp()
    {
        return $this->earliestTimestamp;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
