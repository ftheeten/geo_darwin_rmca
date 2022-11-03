<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use AppBundle\Entity\GeodarwinDocForeignKey;

/**
 * Ddoctitle
 *
 * @ORM\Table(name="ddoctitle", uniqueConstraints={@ORM\UniqueConstraint(name="ddoctitle_unique", columns={"idcollection", "iddoc", "titlelevel"})}, indexes={@ORM\Index(name="IDX_ADADA4C931E478089F44A603", columns={"idcollection", "iddoc"})})
 * @ORM\Entity
 */
class Ddoctitle extends GeodarwinDocForeignKey
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ddoctitle_pk_seq", allocationSize=1, initialValue=1)
     */
    protected $pk;

    /**
     * @var integer
     *
     * @ORM\Column(name="titlelevel", type="smallint", nullable=false)
     */
    private $titlelevel = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    private $title;

	



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
     * Set titlelevel
     *
     * @param integer $titlelevel
     *
     * @return Ddoctitle
     */
    public function setTitlelevel($titlelevel)
    {
        $this->titlelevel = $titlelevel;

        return $this;
    }

    /**
     * Get titlelevel
     *
     * @return integer
     */
    public function getTitlelevel()
    {
        return $this->titlelevel;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Ddoctitle
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

	public function setPk($pk)
    {
        $this->pk = $pk;

        return $this;
    }	
}
