<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlinkcontloc
 *
 * @ORM\Table(name="dlinkcontloc", uniqueConstraints={@ORM\UniqueConstraint(name="dlinkcontloc_unique", columns={"idcontribution", "idcollection", "id"})}, indexes={@ORM\Index(name="IDX_C82D3C5831E47808BF396750", columns={"idcollection", "id"})})
 * @ORM\Entity
 */
class Dlinkcontloc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlinkcontloc_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var \AppBundle\Entity\Dloccenter
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dloccenter",  fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idcollection", referencedColumnName="idcollection"),
     * })
     */
    private $idcollection;

    /**
     * @var \AppBundle\Entity\Dcontribution
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dcontribution",  fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idcontribution", referencedColumnName="idcontribution")
     * })
     */
    private $idcontribution;

	/**
     * @var \AppBundle\Entity\Dloccenter
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dloccenter",  fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="idpt")
     * })
     */
    private $id;
	
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
     * Set idcontribution
     *
     * @param \AppBundle\Entity\Dcontribution $idcontribution
     *
     * @return Dlinkcontloc
     */
    public function setIdcontribution(\AppBundle\Entity\Dcontribution $idcontribution = null)
    {
        $this->idcontribution = $idcontribution;

        return $this;
    }

    /**
     * Get idcontribution
     *
     * @return \AppBundle\Entity\Dcontribution
     */
    public function getIdcontribution()
    {
        return $this->idcontribution;
    }
	
	    /**
     * Set id
     *
     * @param \AppBundle\Entity\Dloccenter $id
     *
     * @return Dlinkcontloc
     */
    public function setId(\AppBundle\Entity\Dloccenter $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \AppBundle\Entity\Dloccenter
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set idcollection
     *
     * @param \AppBundle\Entity\Dloccenter $idcollection
     *
     * @return Dlinkcontloc
     */
    public function setIdcollection(\AppBundle\Entity\Dloccenter $idcollection = null)
    {
        $this->idcollection = $idcollection;

        return $this;
    }

    /**
     * Get idcollection
     *
     * @return \AppBundle\Entity\Dloccenter
     */
    public function getIdcollection()
    {
        return $this->idcollection;
    }
}
