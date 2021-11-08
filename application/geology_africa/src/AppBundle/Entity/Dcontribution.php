<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\GeodarwinEntity;

/**
 * Dcontribution
 *
 * @ORM\Table(name="dcontribution", uniqueConstraints={@ORM\UniqueConstraint(name="dcontribution_unique", columns={"idcontribution"})})
 * @ORM\Entity
 */
class Dcontribution extends GeodarwinEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dcontribution_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var integer
     *
     * @ORM\Column(name="idcontribution", type="integer", nullable=false)
     */
    private $idcontribution;

    /**
     * @var string
     *
     * @ORM\Column(name="datetype", type="string", nullable=true)
     */
    private $datetype;
	
	
	/**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;
	
    /**
     * @var integer
     *
     * @ORM\Column(name="date_format", type="integer", nullable=true)
     */
    private $date_format;


    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer", nullable=true)
     */
    private $year = '0';



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
     * @param integer $idcontribution
     *
     * @return Dcontribution
     */
    public function setIdcontribution($idcontribution)
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

    /**
     * Set datetype
     *
     * @param string $datetype
     *
     * @return Dcontribution
     */
    public function setDatetype($datetype)
    {
        $this->datetype = $datetype;

        return $this;
    }

    /**
     * Get datetype
     *
     * @return string
     */
    public function getDatetype()
    {
        return $this->datetype;
    }
	
	/**
     * Set name
     *
     * @param string $name
     *
     * @return Dcontribution
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Dcontribution
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
	
	 /**
     * Set date_format
     *
     * @param integer $dateformat
     *
     * @return Dcontribution
     */
    public function setDateformat($dateformat)
    {
        $this->date_format = $dateformat;

        return $this;
    }

    /**
     * Get date_format
     *
     * @return integer
     */
    public function getDateformat()
    {
        return $this->date_format;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Dcontribution
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }
	
	//foreign keys and additional fields
	protected $dlinkcontribute;
	protected $dlinkcontdoc;
	protected $description;
	public function __construct()
	{
		$this->dlinkcontribute=Array();
		$this->dlinkcontdoc=Array();
	}
	
	public function getDlinkcontdoc()
	{
		return $this->dlinkcontdoc;
	}
	
	public function getDlinkcontribute()
	{
		return $this->dlinkcontribute;
	}	
	
	public function setDescription($desc)
	{
		$this->description=$desc;
	}
	
	public function setDescriptionDB($em)
	{
		$tmp=$this->getDatetype(). ": ";
		$desc_links=$em->getRepository(Dlinkcontribute::class)->findBy(array("idcontribution" => $this->getIdcontribution()), array("contributororder"=>"ASC"));
		$peoples=Array();
		foreach($desc_links as $obj)
		{
			$contributor=$em->getRepository(Dcontributor::class)->findOneBy(array("idcontributor"=> $obj->getIdcontributor()));
			$peoples[]=$contributor->getPeople();
		}
		$tmp.=implode(", ", $peoples)." (".$this->getYear().")";
		print($tmp);
		$this->description=$tmp;
	}
	
	public function getDescription()
	{	
		return $this->description;
	}
	
	public function setDateByElements($year, $month=null, $day=null)
	{
		$this->setDate($this->handle_date_general($year, $month, $day));
		
		$this->setDateformat($this->handle_date_format_general($year, $month, $day));
	}
	
	public function setDateByElementsForm($form, $year, $month=null, $day=null)
	{
		$tmp_date=$this->handle_date_generalForm($form, $year, $month, $day);	
		$this->setDate($tmp_date);		
		$this->setDateformat($this->handle_date_format_general($year, $month, $day));
	}
	
	//link to documents
	
	public function initDlinkcontdoc($em)
	{		
		
		$this->attachForeignkeysAsObject($em,Dlinkcontdoc::class,"dlinkcontdocs", array("idcontribution"=>$this->getIdcontribution()), "getPk");
        foreach($this->dlinkcontdocs as $obj)
		{
			$obj->setDocument_db($em);
		}
		
		return $this->dlinkcontdocs;
	}
	
		public function initDlinkcontribute($em)
	{
		//print("BEGIN_1");		
		$this->attachForeignkeysAsObject($em,Dlinkcontribute::class,"dlinkcontribute", array("idcontribution"=>$this->getIdcontribution()), "getContributororder");
		
		$this->description=$this->datetype;
		
		foreach($this->dlinkcontribute as $contrib)
		{
			$contrib->setContributor_db($em);
		}
		//print_r($this->dlinkcontribute);
		//print("END_1");
		return $this->dlinkcontribute;
		
	}
	
	public function initNewDlinkcontribute($em, $new_dlinkcontribute)
	{
		//print("BEGIN_2");
		$this->initDlinkcontribute($em);
		if(count($new_dlinkcontribute)>0)
		{
		
		
		$this->reattachForeignKeysAsObject(
			$em,
			Dlinkcontribute::class,
			"dlinkcontribute", 			
			"getLinkSignature", 
			$new_dlinkcontribute,		
			array("idcontribution"=>$this->idcontribution));
		}
		//print("END_2");
	}
	
	//2021 10 20
	public function initNewDlinkdocument($em, $new_dlinkdocument)
	{
		$this->initDlinkcontdoc($em);
		if(count($new_dlinkdocument)>0)
		{
			$this->reattachForeignKeysAsObject(
			$em,
			Dlinkcontdoc::class,
			"dlinkcontdoc", 			
			"getPk", 
			$new_dlinkdocument,		
			array("idcontribution"=>$this->idcontribution));
		}
	}
	
}
