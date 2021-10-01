<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlinkcontdoc
 *
 * @ORM\Table(name="dlinkcontdoc", uniqueConstraints={@ORM\UniqueConstraint(name="dlinkcontdoc_unique", columns={"idcontribution", "idcollection", "id"})}, indexes={@ORM\Index(name="IDX_C63E6DE031E47808BF396750", columns={"idcollection", "id"}), @ORM\Index(name="IDX_C63E6DE0AC9A611C", columns={"idcontribution"})})
 * @ORM\Entity
 */
class Dlinkcontdoc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlinkcontdoc_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    // rmca custom mapping for foreign keys
    /**
     * @var integer
     *
     * @ORM\Column(name="idcollection", type="integer", nullable=false)
     */
    private $idcollection;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

     /**
     * @var \AppBundle\Entity\Dcontribution
     * 
	 * @ORM\Column(name="idcontribution", type="integer", nullable=false)          
     */
    private $idcontribution;


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
     * Set idcollection
     *
     * @param \AppBundle\Entity\Ddocument $idcollection
     *
     * @return Dlinkcontdoc
     */
    public function setIdcollection(\AppBundle\Entity\Ddocument $idcollection = null)
    {
        $this->idcollection = $idcollection;

        return $this;
    }

    /**
     * Get idcollection
     *
     * @return \AppBundle\Entity\Ddocument
     */
    public function getIdcollection()
    {
        return $this->idcollection;
    }

    /**
     * Set idcontribution
     *
     * @param integer $idcontribution
     *
     * @return Dlinkcontdoc
     */
    public function setIdcontribution($idcontribution = null)
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
	
	//custom_mapping ftheeten
	/**
     * Set relationidcollection
     *
     * @param \AppBundle\Entity\Ddocument $idcollection
     *
     * @return Dkeyword
     */
	 
    public function setrelationidcollection(\AppBundle\Entity\Ddocument $doc = null)
    {
        $this->id=$doc->getIddoc();
		$this->idcollection=$doc->getIdcollection();

        return $this;
    }
}
