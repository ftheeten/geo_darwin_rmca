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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

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
	
	//foreign keys
	public function __construct()
	{
		$this->dlinkcontribute=Array();
	}
	
	public function getDlinkcontribute()
	{
		return $this->dlinkcontribute;
	}
	
	public function initDlinkcontribute($em)
	{
		$this->attachForeignkeys(
			$em,
			Dlinkcontribute::class,
			"dlinkcontribute", 
			array("idcontribution"=>$this->idcontribution), 
			"getPk", "getContributororder"
			);
		
		$this->description=$this->datetype;	
		return $this->dlinkcontribute;
		
	}
	
	public function initNewDlinkcontribute($em, $new_dlinkcontribute)
	{
		if(count($new_dlinkcontribute)>0)
		{
		$this->reattachForeignKeysSignature(
			$em,
			Dlinkcontribute::class,
			"dlinkcontribute", 
			"pk",  
			"getLinkSignature", 
			$new_dlinkcontribute, 			
			"getPk",
			array("idcontribution"=>$this->idcontribution));
		}
	}
	
	protected $description;
	
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
}
