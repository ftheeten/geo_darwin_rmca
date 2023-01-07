<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\GeodarwinEntity;


/**
 * Dsample
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DsampleRepository")
 * @ORM\Table(name="dsample", uniqueConstraints={@ORM\UniqueConstraint(name="dsample_unique", columns={"idcollection", "idsample"})})
 */

class Dsample extends GeodarwinEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="seq_template_data_shared_pk", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="idcollection", type="string", nullable=false)
     */
    private $idcollection;

    /**
     * @var integer
     *
     * @ORM\Column(name="idsample", type="integer", nullable=false)
     */
    private $idsample;

    /**
     * @var string
     *
     * @ORM\Column(name="fieldnum", type="string", nullable=true)
     */
    private $fieldnum;

    /**
     * @var string
     *
     * @ORM\Column(name="museumnum", type="string", nullable=true)
     */
    private $museumnum;

    /**
     * @var string
     *
     * @ORM\Column(name="museumlocation", type="string", nullable=true)
     */
    private $museumlocation;

    /**
     * @var string
     *
     * @ORM\Column(name="boxnumber", type="string", nullable=true)
     */
    private $boxnumber;

    /**
     * @var string
     *
     * @ORM\Column(name="sampledescription", type="text", nullable=true)
     */
    private $sampledescription;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer", nullable=true)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="quantity", type="string", nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", nullable=true)
     */
    private $size;

    /**
     * @var integer
     *
     * @ORM\Column(name="dimentioncode", type="smallint", nullable=true)
     */
    private $dimentioncode;

    /**
     * @var string
     *
     * @ORM\Column(name="quality", type="string", nullable=true)
     */
    private $quality;

    /**
     * @var boolean
     *
     * @ORM\Column(name="slimplate", type="boolean", nullable=true)
     */
    private $slimplate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="chemicalanalysis", type="boolean", nullable=true)
     */
    private $chemicalanalysis;

    /**
     * @var boolean
     *
     * @ORM\Column(name="holotype", type="boolean", nullable=true)
     */
    private $holotype;

    /**
     * @var boolean
     *
     * @ORM\Column(name="paratype", type="boolean", nullable=true)
     */
    private $paratype;

    /**
     * @var integer
     *
     * @ORM\Column(name="radioactivity", type="smallint", nullable=true)
     */
    private $radioactivity;

    /**
     * @var string
     *
     * @ORM\Column(name="loaninformation", type="string", nullable=true)
     */
    private $loaninformation;

    /**
     * @var integer
     *
     * @ORM\Column(name="securitylevel", type="integer", nullable=true)
     */
    private $securitylevel;

    /**
     * @var string
     *
     * @ORM\Column(name="varioussampleinfo", type="string", nullable=true)
     */
    private $varioussampleinfo;
	
	
	

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
		$this->pk=$pk;
		return $this;
	}

    /**
     * Set idcollection
     *
     * @param string $idcollection
     *
     * @return Dsample
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
     * Set idsample
     *
     * @param integer $idsample
     *
     * @return Dsample
     */
    public function setIdsample($idsample)
    {
        $this->idsample = $idsample;

        return $this;
    }

    /**
     * Get idsample
     *
     * @return integer
     */
    public function getIdsample()
    {
        return $this->idsample;
    }

    /**
     * Set fieldnum
     *
     * @param string $fieldnum
     *
     * @return Dsample
     */
    public function setFieldnum($fieldnum)
    {
        $this->fieldnum = $fieldnum;

        return $this;
    }

    /**
     * Get fieldnum
     *
     * @return string
     */
    public function getFieldnum()
    {
        return $this->fieldnum;
    }

    /**
     * Set museumnum
     *
     * @param string $museumnum
     *
     * @return Dsample
     */
    public function setMuseumnum($museumnum)
    {
        $this->museumnum = $museumnum;

        return $this;
    }

    /**
     * Get museumnum
     *
     * @return string
     */
    public function getMuseumnum()
    {
        return $this->museumnum;
    }

    /**
     * Set museumlocation
     *
     * @param string $museumlocation
     *
     * @return Dsample
     */
    public function setMuseumlocation($museumlocation)
    {
        $this->museumlocation = $museumlocation;

        return $this;
    }

    /**
     * Get museumlocation
     *
     * @return string
     */
    public function getMuseumlocation()
    {
        return $this->museumlocation;
    }

    /**
     * Set boxnumber
     *
     * @param string $boxnumber
     *
     * @return Dsample
     */
    public function setBoxnumber($boxnumber)
    {
        $this->boxnumber = $boxnumber;

        return $this;
    }

    /**
     * Get boxnumber
     *
     * @return string
     */
    public function getBoxnumber()
    {
        return $this->boxnumber;
    }

    /**
     * Set sampledescription
     *
     * @param string $sampledescription
     *
     * @return Dsample
     */
    public function setSampledescription($sampledescription)
    {
        $this->sampledescription = $sampledescription;

        return $this;
    }

    /**
     * Get sampledescription
     *
     * @return string
     */
    public function getSampledescription()
    {
        return $this->sampledescription;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     *
     * @return Dsample
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set quantity
     *
     * @param string $quantity
     *
     * @return Dsample
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set size
     *
     * @param string $size
     *
     * @return Dsample
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set dimentioncode
     *
     * @param integer $dimentioncode
     *
     * @return Dsample
     */
    public function setDimentioncode($dimentioncode)
    {
        $this->dimentioncode = $dimentioncode;

        return $this;
    }

    /**
     * Get dimentioncode
     *
     * @return integer
     */
    public function getDimentioncode()
    {
        return $this->dimentioncode;
    }

    /**
     * Set quality
     *
     * @param string $quality
     *
     * @return Dsample
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get quality
     *
     * @return string
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set slimplate
     *
     * @param boolean $slimplate
     *
     * @return Dsample
     */
    public function setSlimplate($slimplate)
    {
        $this->slimplate = $slimplate;

        return $this;
    }

    /**
     * Get slimplate
     *
     * @return boolean
     */
    public function getSlimplate()
    {
        return $this->slimplate;
    }

    /**
     * Set chemicalanalysis
     *
     * @param boolean $chemicalanalysis
     *
     * @return Dsample
     */
    public function setChemicalanalysis($chemicalanalysis)
    {
        $this->chemicalanalysis = $chemicalanalysis;

        return $this;
    }

    /**
     * Get chemicalanalysis
     *
     * @return boolean
     */
    public function getChemicalanalysis()
    {
        return $this->chemicalanalysis;
    }

    /**
     * Set holotype
     *
     * @param boolean $holotype
     *
     * @return Dsample
     */
    public function setHolotype($holotype)
    {
        $this->holotype = $holotype;

        return $this;
    }

    /**
     * Get holotype
     *
     * @return boolean
     */
    public function getHolotype()
    {
        return $this->holotype;
    }

    /**
     * Set paratype
     *
     * @param boolean $paratype
     *
     * @return Dsample
     */
    public function setParatype($paratype)
    {
        $this->paratype = $paratype;

        return $this;
    }

    /**
     * Get paratype
     *
     * @return boolean
     */
    public function getParatype()
    {
        return $this->paratype;
    }

    /**
     * Set radioactivity
     *
     * @param integer $radioactivity
     *
     * @return Dsample
     */
    public function setRadioactivity($radioactivity)
    {
        $this->radioactivity = $radioactivity;

        return $this;
    }

    /**
     * Get radioactivity
     *
     * @return integer
     */
    public function getRadioactivity()
    {
        return $this->radioactivity;
    }

    /**
     * Set loaninformation
     *
     * @param string $loaninformation
     *
     * @return Dsample
     */
    public function setLoaninformation($loaninformation)
    {
        $this->loaninformation = $loaninformation;

        return $this;
    }

    /**
     * Get loaninformation
     *
     * @return string
     */
    public function getLoaninformation()
    {
        return $this->loaninformation;
    }

    /**
     * Set securitylevel
     *
     * @param integer $securitylevel
     *
     * @return Dsample
     */
    public function setSecuritylevel($securitylevel)
    {
        $this->securitylevel = $securitylevel;

        return $this;
    }

    /**
     * Get securitylevel
     *
     * @return integer
     */
    public function getSecuritylevel()
    {
        return $this->securitylevel;
    }

    /**
     * Set varioussampleinfo
     *
     * @param string $varioussampleinfo
     *
     * @return Dsample
     */
    public function setVarioussampleinfo($varioussampleinfo)
    {
        $this->varioussampleinfo = $varioussampleinfo;

        return $this;
    }

    /**
     * Get varioussampleinfo
     *
     * @return string
     */
    public function getVarioussampleinfo()
    {
        return $this->varioussampleinfo;
    }
	
	//foreign keys
	//many to many
	protected $dsamminerals;
	protected $dsamslimplate;
	//many to many
	protected $dlinklocsam;
	protected $dsamgranulo;
	//many to many
	protected $dlinkcontsam;
	//many to many
	protected $dlinkdocsam;
	protected $dsamarays;
	protected $dsamheavymin;
	
	public function __construct()
	{
		$this->dsamminerals=Array();
		$this->dsamslimplate=Array();
		$this->dlinklocsam=Array();
		$this->dsamgranulo=Array();
		$this->dlinkcontsam=Array();
		$this->dlinkdocsam=Array();
		$this->dsamarays=Array();
		$this->dsamheavymin=Array();
	}
	
	
	//get
	public function getDsamminerals()
	{
		return $this->dsamminerals;
	}
	
	public function getDsamslimplate()
	{
		return $this->dsamslimplate;
	}
	
	public function getDlinklocsam()
	{
		return $this->dlinklocsam;
	}
	
	public function getDsamgranulo()
	{
		return $this->dsamgranulo;
	}
	
	public function getDlinkcontsam()
	{
		return $this->dlinkcontsam;
	}
	
	public function getDlinkdocsam()
	{
		return $this->dlinkdocsam;
	}
	
	public function getDsamarays()
	{
		return $this->dsamarays;
	}
	
	public function getDsamheavymin()
	{
		return $this->dsamheavymin;
	}
	
	//set
	public function setDsamminerals($obj)
	{
		$this->dsamminerals=$obj;
		return $this;
	}
	
	public function setDsamslimplate($obj)
	{
		$this->dsamslimplate=$obj;
		return $this;
	}
	
	public function setDlinklocsam($obj)
	{
		$this->dlinklocsam=$obj;
		return $this;
	}
	
	public function setDsamgranulo($obj)
	{
		$this->dsamgranulo=$obj;
		return $this;
	}
	
	public function setDlinkcontsam($obj)
	{
		$this->dlinkcontsam=$obj;
	}
	
	public function setDlinkdocsam($obj)
	{
		$this->dlinkdocsam=$obj;
		return $this;
	}
	
	public function setDsamarays($obj)
	{
		 $this->dsamarays=$obj;
		 return $this;
	}
	
	public function setDsamheavymin($obj)
	{
		$this->dsamheavymin=$obj;
		return $this;
	}
	
	//init
	
	public function initDsamminerals($em)
	{
		$this->attachForeignkeysAsObject(
			$em, 
			Dsamminerals::class, 
			"dsamminerals",
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				),
				"getPk");
		$objs=[];
		foreach($this->dsamminerals as $obj)
		{
			
			$obj->setLminerals_db($em);
			$objs[$obj->lminerals->getMname()]=$obj;
		}
		$this->dsamminerals=$objs;
		return $this->dsamminerals;
	}
	
	public function initDsamslimplate($em)
	{
		$this->attachForeignkeysAsObject(
			$em, 
			Dsamslimplate::class, 
			"dsamslimplate",
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				),
				"getPk");
		return $this->dsamslimplate;
	}
	
	public function initDlinklocsam($em)
	{
		$objs=[];
		$this->attachForeignkeysAsObject(
			$em, 
			Dlinklocsam::class, 
			"dlinklocsam",
			array(
				"idcollecsample"=>$this->idcollection,
				"idsample"=>$this->idsample
				),
				"getPk");
		
		foreach($this->dlinklocsam as $obj)
		{
			
			$obj->setDloclitho_db($em);
			$objs[$obj->dloclithos->getPk()]=$obj;
		}
		$this->dlinklocsam=$objs;
		return $this->dlinklocsam;
	}
	
	public function initDsamgranulo($em)
	{
		$this->attachForeignkeysAsObject(
			$em, 
			Dsamgranulo::class, 
			"dsamgranulo",
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				),
				"getPk");
		return $this->dsamgranulo;
	}
	
	public function initDlinkcontsam($em)
	{
		$this->attachForeignkeysAsObject(
			$em, 
			Dlinkcontsam::class, 
			"dlinkcontsam",
			array(
				"idcollection"=>$this->idcollection,
				"id"=>$this->idsample
				),
				"getPk");
		foreach($this->dlinkcontsam as $obj)
		{
			$obj->setDcontribution_db($em);
		}
		return $this->dlinkcontsam;
	}
	
	public function initDlinkdocsam($em)
	{
		$this->attachForeignkeysAsObject(
			$em, 
			Dlinkdocsam::class, 
			"dlinkdocsam",
			array(
				"idcollectionsample"=>$this->idcollection,
				"idsample"=>$this->idsample
				),
				"getPk");
		foreach($this->dlinkdocsam as $obj)
		{
			$obj->setDocument_db($em);
		}
		return $this->dlinkdocsam;
	}
	
	public function initDsamarays($em)
	{
		$this->attachForeignkeysAsObject(
			$em, 
			Dsamarays::class, 
			"dsamarays",
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				),
				"getPk");
		return $this->dsamarays;
	}
	
	public function initDsamheavymin($em)
	{
		$this->attachForeignkeysAsObject(
			$em, 
			Dsamheavymin::class, 
			"dsamheavymin",
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				),
				"getPk");
		return $this->dsamheavymin;
	}
	
	//reattach
	
	public function initNewDsamminerals($em, $new_dsamminerals)
	{
		$this->initDsamminerals($em);
		$this->reattachForeignkeysAsObject(
			$em, 
			Dsamminerals::class, 
			"dsamminerals",
			"getPk",
			 $new_dsamminerals,
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				));
		return $this->dsamminerals;
	}
	
	public function initNewDsamslimplate($em, $new_dsamslimplate)
	{
		$this->initDsamslimplate($em);
		$this->reattachForeignKeysAsObject(
			$em, 
			Dsamslimplate::class, 
			"dsamslimplate",
			"getPk",
			 $new_dsamslimplate,
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				));
		return $this->dsamslimplate;
	}
	
	public function initNewDlinklocsam($em, $new_dlinklocsam)
	{
		$this->initDlinklocsam($em);
		$this->reattachForeignKeysAsObject(
			$em, 
			Dlinklocsam::class, 
			"dlinklocsam",
			"getPk",
			 $new_dlinklocsam,
			array(
				"idcollecsample"=>$this->idcollection,
				"idsample"=>$this->idsample
				));
		return $this->dlinklocsam;
	}
	
	public function initNewDsamgranulo($em, $new_dsamgranulo)
	{
		$this->initDsamgranulo($em);
		if(count($new_dsamgranulo)>0)
		{
			$this->reattachForeignKeysAsObject(
				$em, 
				Dsamgranulo::class, 
				"dsamgranulo",
				"getPk",
				 $new_dsamgranulo,
				array(
					"idcollection"=>$this->idcollection,
					"idsample"=>$this->idsample
					));
		}
		return $this->dsamgranulo;
	}
	
	public function initNewDlinkcontsam($em, $new_dlinkcontsam)
	{
		$this->initDlinkcontsam($em);
		$this->reattachForeignKeysAsObject(
			$em, 
			Dlinkcontsam::class, 
			"dlinkcontsam",
			"getPk",
			 $new_dlinkcontsam,
			array(
				"idcollection"=>$this->idcollection,
				"id"=>$this->idsample
				));
		return $this->dlinkcontsam;
	}
	
	public function initNewDlinkdocsam($em, $new_dlinkdocsam)
	{
		$this->initDlinkdocsam($em);
		$this->reattachForeignKeysAsObject(
			$em, 
			Dlinkdocsam::class, 
			"dlinkdocsam",
			"getPk",
			 $new_dlinkdocsam,
			array(
				"idcollectionsample"=>$this->idcollection,
				"idsample"=>$this->idsample
				));
		return $this->dlinkdocsam;
	}
	
	public function initNewDsamarays($em, $new_dsamarays)
	{
		$this->initDsamarays($em);
		$this->reattachForeignKeysAsObject(
			$em, 
			Dsamarays::class, 
			"dsamarays",
			"getPk",
			 $new_dsamarays,
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				));
		return $this->dsamarays;
	}
	
	public function initNewDsamheavymin($em, $new_dsamheavymin)
	{
		$this->initDsamheavymin($em);
		$this->reattachForeignKeysAsObject(
			$em, 
			Dsamheavymin::class, 
			"dsamheavymin",
			"getPk",
			 $new_dsamheavymin,
			array(
				"idcollection"=>$this->idcollection,
				"idsample"=>$this->idsample
				));
		return $this->dsamheavymin;
	}
	
	
	
	
}
