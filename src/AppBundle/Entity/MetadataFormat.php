<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
* @ORM\Table(name="metadataformats")
*/
class MetadataFormat
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
    protected $metadataPrefix;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $metadataSchema;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $metadataNamespace;

    /**
     * Get id
     *
     * @return integer
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
     * Set schema
     *
     * @param string $schema
     *
     * @return MetadataFormat
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Get schema
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
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
}
