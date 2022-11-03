<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ddocscale
 *
 * @ORM\Table(name="ddocscale", uniqueConstraints={@ORM\UniqueConstraint(name="ddocscale_unique", columns={"idcollection", "iddoc", "scale"})}, indexes={@ORM\Index(name="IDX_6ADDF92631E478089F44A603", columns={"idcollection", "iddoc"})})
 * @ORM\Entity
 */
class Ddocscale extends GeodarwinDocForeignKey
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ddocscale_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var integer
     *
     * @ORM\Column(name="scale", type="integer", nullable=false)
     */
    private $scale;

    /**
     * Get pk
     *
     * @return integer
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * Set scale
     *
     * @param integer $scale
     *
     * @return Ddocscale
     */
    public function setScale($scale)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Get scale
     *
     * @return integer
     */
    public function getScale()
    {
        return $this->scale;
    }
	
	public function setPk($pk)
    {
        $this->pk = $pk;

        return $this;
    }	


	
	
}
