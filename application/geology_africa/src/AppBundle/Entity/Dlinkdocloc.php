<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlinkdocloc
 *
 * @ORM\Table(name="dlinkdocloc", uniqueConstraints={@ORM\UniqueConstraint(name="dlinkdocloc_unique", columns={"idcollecloc", "idpt", "idcollecdoc", "iddoc"})}, indexes={@ORM\Index(name="IDX_2AC6AD32C656DF3B9F44A603", columns={"idcollecdoc", "iddoc"}), @ORM\Index(name="IDX_2AC6AD32C8458E8350E3C8BA", columns={"idcollecloc", "idpt"})})
 * @ORM\Entity
 */
 
class Dlinkdocloc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlinkdocloc_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    // rmca custom mapping for foreign keys
     /**
     * @var string
     *
     * @ORM\Column(name="idcollecdoc", type="string", nullable=false)
     */
    private $idcollecdoc;

     // rmca custom mapping for foreign keys
     /**
     * @var string
     *
     * @ORM\Column(name="idcollecloc", type="string", nullable=false)
     */
    private $idcollecloc;

	/**
     * @var integer
     *
     * @ORM\Column(name="idpt", type="integer", nullable=false)
     */
	private $idpt;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="iddoc", type="integer", nullable=false)
     */
	private $iddoc;

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
    public function setIdcollecdoc( $idcollecdoc = null)
    {
        $this->idcollecdoc = $idcollecdoc;

        return $this;
    }

    /**
     * Get idcollecdoc
     *
     * @return string
     */
    public function getIdcollecdoc()
    {
        return $this->idcollecdoc;
    }

    /**
     * Set idcollecloc
     *
     * @param string $idcollecloc
     *
     * @return Dlinkdocloc
     */
    public function setIdcollecloc( $idcollecloc = null)
    {
        $this->idcollecloc = $idcollecloc;

        return $this;
    }

    /**
     * Get idcollecloc
     *
     * @return string
     */
    public function getIdcollecloc()
    {
        return $this->idcollecloc;
    }
	
	/**
     * Set idpt
     *
     * @param integer $idpt
     *
     * @return Dlinkdocloc
     */
    public function setIdpt( $idpt = null)
    {
        $this->idpt = $idpt;

        return $this;
    }

    /**
     * Get idpt
     *
     * @return integer
     */
    public function getIdpt()
    {
        return $this->idpt;
    }
	
	/**
     * Set iddoc
     *
     * @param integer $iddoc
     *
     * @return Dlinkdocloc
     */
    public function setIddoc( $iddoc = null)
    {
        $this->iddoc = $iddoc;

        return $this;
    }

    /**
     * Get iddoc
     *
     * @return integer
     */
    public function getIddoc()
    {
        return $this->iddoc;
    }
	
	//custom_mapping ftheeten
	/**
     * Set relationidcollection
     *
     * @param \AppBundle\Entity\Ddocument $idcollection
     *
     * @return Dlinkdocloc
     */
	 
    public function setrelationidcollection(\AppBundle\Entity\Ddocument $doc = null)
    {
        $this->iddoc=$doc->getIddoc();
		$this->idcollecdoc=$doc->getIdcollection();

        return $this;
    }
	
	//custom_mapping ftheeten
	/**
     * Set relationidloc
     *
     * @param \AppBundle\Entity\DLoccenter $idloc
     *
     * @return Dlinkdocloc
     */
	 
	  public function setrelationidloc(\AppBundle\Entity\DLoccenter $loc = null)
    {
        $this->idcollecloc=$loc->getIdcollection();
		$this->idpt=$loc->getIdpt();

        return $this;
    } 
	
	//document
	public $document;
	
	public function getDocument()
	{
		return $this->document;
	}
	
	public function setDocument_db($em)
	{
		$tmp_doc=$em->getRepository(Ddocument::class)
						 ->findOneBy(array('iddoc' => $this->getIddoc(),
									"idcollection"=> $this->getIdcollecdoc()));
		if($tmp_doc !==null)
		{
			$tmp_doc->setTitle_db($em);
		}
		$this->document=$tmp_doc;
	}
	
	//point
	public $dloccenter;
	public function getDlocenter()
	{
		return $this->dloccenter;
	}
	
	
	
	public function setDloccenter_db($em)
	{
		
		$tmp_loc=$em->getRepository(DLoccenter::class)
						 ->findOneBy(array('idpt' => $this->getIdpt(),
									"idcollection"=> $this->getIdcollecloc()));
		if($tmp_loc!=null)
		{
			
			$tmp_loc->setDescription_db($em);
			
		}
		$this->dloccenter=$tmp_loc;
		
	}
	
	public function setPk($param)
    {
        $this->pk=$param;
    }
	
	/*public function getLinkSignature()
	{		
		return $this->idcollecloc."_".$this->idpt."_".$this->idcollecdoc."_".$this->iddoc;
		
	}*/
}
