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
     * @var string
     *
     * @ORM\Column(name="idcollection", type="string", nullable=false)
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
     * @param string $idcollection
     *
     * @return Ddocument
     */
    public function setIdcollection($idcollection)
    {
        $this->idcollection = $idcollection;

        return $this;
    }

    /**
     * Get idcollection
     *
     * @return string
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
     * @return integer
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
	
	
    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Dlinkcontdoc
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
	
	
	//document
	public $document;
	public $contribution;
	
	public function getDocument()
	{
		return $this->document;
	}
	
	public function getContribution()
	{
		return $this->contribution;
	}
	
	public function setDocument_db($em)
	{
		$tmp_doc=$em->getRepository(Ddocument::class)
						 ->findOneBy(array('iddoc' => $this->getId(),
									"idcollection"=> $this->getIdcollection()));
		if($tmp_doc !==null)
		{
			$tmp_doc->setTitle_db($em);
		}
		$this->document=$tmp_doc;
	}
	
	public function setContribution_db($em)
	{
		$tmp_cont=$em->getRepository(Dcontribution::class)
						 ->findOneBy(array('idcontribution' => $this->getIdcontribution()
									));
		if($tmp_cont !==null)
		{
			$tmp_cont->setDescriptionDB($em);
		}
		$this->contribution=$tmp_cont;
	}
	
	public function getLinkSignature()
	{		
		return $this->idcontribution."_".$this->idcollection."_".$this->id;
		
	}
	
	public function setPk($param)
    {
        $this->pk=$param;
    }
}
