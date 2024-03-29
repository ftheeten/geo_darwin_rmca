<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ddocmap
 *
 * @ORM\Table(name="ddocmap", uniqueConstraints={@ORM\UniqueConstraint(name="ddocmap_unique", columns={"iddoc", "idcollection"})}, indexes={@ORM\Index(name="IDX_2FED311C31E478089F44A603", columns={"idcollection", "iddoc"})})
 * @ORM\Entity
 */
class Ddocmap extends GeodarwinDocForeignKey
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ddocmap_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="projection", type="string", nullable=true)
     */
    private $projection;

    /**
     * @var string
     *
     * @ORM\Column(name="sheetnumber", type="string", nullable=true)
     */
    private $sheetnumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="oncartesius", type="boolean", nullable=true)
     */
    private $oncartesius;

    


    /**
     * Get pk
     *
     * @return integer
     */
    public function getPk()
    {
        return $this->pk;
    }
	
	public function setPk($pk)
    {
        $this->pk = $pk;

        return $this;
    }	

    /**
     * Set projection
     *
     * @param string $projection
     *
     * @return Ddocmap
     */
    public function setProjection($projection)
    {
        $this->projection = $projection;

        return $this;
    }

    /**
     * Get projection
     *
     * @return string
     */
    public function getProjection()
    {
        return $this->projection;
    }

    /**
     * Set sheetnumber
     *
     * @param string $sheetnumber
     *
     * @return Ddocmap
     */
    public function setSheetnumber($sheetnumber)
    {
        $this->sheetnumber = $sheetnumber;

        return $this;
    }

    /**
     * Get sheetnumber
     *
     * @return string
     */
    public function getSheetnumber()
    {
        return $this->sheetnumber;
    }

    /**
     * Set oncartesius
     *
     * @param boolean $oncartesius
     *
     * @return Ddocmap
     */
    public function setOncartesius($oncartesius)
    {
        $this->oncartesius = $oncartesius;

        return $this;
    }

    /**
     * Get oncartesius
     *
     * @return boolean
     */
    public function getOncartesius()
    {
        return $this->oncartesius;
    }

   
}
