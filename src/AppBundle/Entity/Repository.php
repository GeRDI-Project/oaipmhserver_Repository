<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Repository
 *
 * @ORM\Table(name="repository")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RepositoryRepository")
 */
class Repository
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="base_url", type="string", length=255)
     */
    private $baseUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="compression", type="string", length=255)
     */
    private $compression;

    /**
     * @var string
     *
     * @ORM\Column(name="highest_id", type="string", length=255)
     */
    private $highestId;

    /**
     * @var string
     *
     * @ORM\Column(name="deleted_record", type="string", length=255)
     */
    private $deletedRecord;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_email", type="string", length=255)
     */
    private $adminEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="protocol_version", type="string", length=100)
     */
    private $protocolVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="granularity", type="string", length=100)
     */
    private $granularity;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="earliest_timestamp", type="datetime")
     */
    private $earliestTimestamp;


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
}
