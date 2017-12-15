<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Record
 *
 * @ORM\Table(name="record")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RecordRepository")
 */
class Record
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
     * @ORM\Column(name="xml", type="text")
     */
    private $xml;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=50)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="records")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity="MetadataFormat", inversedBy="records")
     * @ORM\JoinColumn(name="metadata_format_id", referencedColumnName="id")
     */
    private $metadataFormat;

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
     * Set xml
     *
     * @param string $xml
     *
     * @return Record
     */
    public function setXml($xml)
    {
        $this->xml = $xml;

        return $this;
    }

    /**
     * Get xml
     *
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Record
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set item
     *
     * @param \AppBundle\Entity\Item $item
     *
     * @return Record
     */
    public function setItem(\AppBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \AppBundle\Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set metadataFormat
     *
     * @param \AppBundle\Entity\MetadataFormat $metadataFormat
     *
     * @return Record
     */
    public function setMetadataFormat(\AppBundle\Entity\MetadataFormat $metadataFormat = null)
    {
        $this->metadataFormat = $metadataFormat;

        return $this;
    }

    /**
     * Get metadataFormat
     *
     * @return \AppBundle\Entity\MetadataFormat
     */
    public function getMetadataFormat()
    {
        return $this->metadataFormat;
    }
}
