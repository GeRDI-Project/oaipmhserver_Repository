<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * MetadataFormat
 *
 * @ORM\Table(name="metadata_formats")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MetadataFormatRepository")
 */
class MetadataFormat
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
     * @ORM\Column(name="metadata_prefix", type="string", length=255)
     */
    private $metadataPrefix;

    /**
     * @var string
     *
     * @ORM\Column(name="metadata_schema", type="string", length=255)
     */
    private $metadataSchema;

    /**
     * @var string
     *
     * @ORM\Column(name="metadata_namespace", type="string", length=255)
     */
    private $metadataNamespace;

    /**
     * @ORM\OneToMany(targetEntity="Record", mappedBy="metadataFormat")
     */
    private $records;

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
     * Set metadataPrefix
     *
     * @param string $metadataPrefix
     *
     * @return MetadataFormat
     */
    public function setMetadataPrefix($metadataPrefix)
    {
        $this->metadataPrefix = $metadataPrefix;

        return $this;
    }

    /**
     * Get metadataPrefix
     *
     * @return string
     */
    public function getMetadataPrefix()
    {
        return $this->metadataPrefix;
    }

    /**
     * Set metadataSchema
     *
     * @param string $metadataSchema
     *
     * @return MetadataFormat
     */
    public function setMetadataSchema($metadataSchema)
    {
        $this->metadataSchema = $metadataSchema;

        return $this;
    }

    /**
     * Get metadataSchema
     *
     * @return string
     */
    public function getMetadataSchema()
    {
        return $this->metadataSchema;
    }

    /**
     * Set metadataNamespace
     *
     * @param string $metadataNamespace
     *
     * @return MetadataFormat
     */
    public function setMetadataNamespace($metadataNamespace)
    {
        $this->metadataNamespace = $metadataNamespace;

        return $this;
    }

    /**
     * Get metadataNamespace
     *
     * @return string
     */
    public function getMetadataNamespace()
    {
        return $this->metadataNamespace;
    }

    /**
     * Add record
     *
     * @param \AppBundle\Entity\Record $record
     *
     * @return MetadataFormat
     */
    public function addRecord(\AppBundle\Entity\Record $record)
    {
        $this->records[] = $record;

        return $this;
    }

    /**
     * Remove record
     *
     * @param \AppBundle\Entity\Record $record
     */
    public function removeRecord(\AppBundle\Entity\Record $record)
    {
        $this->records->removeElement($record);
    }

    /**
     * Get records
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecords()
    {
        return $this->records;
    }
}
