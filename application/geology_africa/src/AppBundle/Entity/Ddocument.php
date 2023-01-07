<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\GeodarwinEntity;

/**
 * Ddocument
 *
 * @ORM\Table(name="ddocument", uniqueConstraints={@ORM\UniqueConstraint(name="ddocument_unique", columns={"idcollection", "iddoc"})}, indexes={@ORM\Index(name="IDX_5ACD9119C67345B7", columns={"medium"})})
 * @ORM\Entity
 */
class Ddocument extends GeodarwinEntity
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
     * @ORM\Column(name="iddoc", type="integer", nullable=false)
     */
    private $iddoc;

    /**
     * @var integer
     *
     * @ORM\Column(name="idpt", type="integer", nullable=true)
     */
    private $idpt = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="numarchive", type="string", nullable=true)
     */
    private $numarchive;

    /**
     * @var string
     *
     * @ORM\Column(name="caption", type="text", nullable=true)
     */
    private $caption;

    /**
     * @var string
     *
     * @ORM\Column(name="centralnum", type="string", nullable=true)
     */
    private $centralnum;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", nullable=true)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="numericallocation", type="string", nullable=true)
     */
    private $numericallocation;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", nullable=true)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="docinfo", type="string", nullable=true)
     */
    private $docinfo;

    /**
     * @var string
     *
     * @ORM\Column(name="edition", type="string", nullable=true)
     */
    private $edition;

    /**
     * @var string
     *
     * @ORM\Column(name="pubplace", type="string", nullable=true)
     */
    private $pubplace;

    /**
     * @var string
     *
     * @ORM\Column(name="doccartotype", type="string", nullable=true)
     */
    private $doccartotype;

    /**
     * @var \AppBundle\Entity\Lmedium
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Lmedium")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="medium", referencedColumnName="medium")
     * })
     */
    private $medium;

    

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
     * Set iddoc
     *
     * @param integer $iddoc
     *
     * @return Ddocument
     */
    public function setIddoc($iddoc)
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

    /**
     * Set idpt
     *
     * @param integer $idpt
     *
     * @return Ddocument
     */
    public function setIdpt($idpt)
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
     * Set numarchive
     *
     * @param string $numarchive
     *
     * @return Ddocument
     */
    public function setNumarchive($numarchive)
    {
        $this->numarchive = $numarchive;

        return $this;
    }

    /**
     * Get numarchive
     *
     * @return string
     */
    public function getNumarchive()
    {
        return $this->numarchive;
    }

    /**
     * Set caption
     *
     * @param string $caption
     *
     * @return Ddocument
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set centralnum
     *
     * @param string $centralnum
     *
     * @return Ddocument
     */
    public function setCentralnum($centralnum)
    {
        $this->centralnum = $centralnum;

        return $this;
    }

    /**
     * Get centralnum
     *
     * @return string
     */
    public function getCentralnum()
    {
        return $this->centralnum;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return Ddocument
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set numericallocation
     *
     * @param string $numericallocation
     *
     * @return Ddocument
     */
    public function setNumericallocation($numericallocation)
    {
        $this->numericallocation = $numericallocation;

        return $this;
    }

    /**
     * Get numericallocation
     *
     * @return string
     */
    public function getNumericallocation()
    {
        return $this->numericallocation;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Ddocument
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set docinfo
     *
     * @param string $docinfo
     *
     * @return Ddocument
     */
    public function setDocinfo($docinfo)
    {
        $this->docinfo = $docinfo;

        return $this;
    }

    /**
     * Get docinfo
     *
     * @return string
     */
    public function getDocinfo()
    {
        return $this->docinfo;
    }

    /**
     * Set edition
     *
     * @param string $edition
     *
     * @return Ddocument
     */
    public function setEdition($edition)
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get edition
     *
     * @return string
     */
    public function getEdition()
    {
        return $this->edition;
    }

    /**
     * Set pubplace
     *
     * @param string $pubplace
     *
     * @return Ddocument
     */
    public function setPubplace($pubplace)
    {
        $this->pubplace = $pubplace;

        return $this;
    }

    /**
     * Get pubplace
     *
     * @return string
     */
    public function getPubplace()
    {
        return $this->pubplace;
    }

    /**
     * Set doccartotype
     *
     * @param string $doccartotype
     *
     * @return Ddocument
     */
    public function setDoccartotype($doccartotype)
    {
        $this->doccartotype = $doccartotype;

        return $this;
    }

    /**
     * Get doccartotype
     *
     * @return string
     */
    public function getDoccartotype()
    {
        return $this->doccartotype;
    }

    /**
     * Set medium
     *
     * @param \AppBundle\Entity\Lmedium $medium
     *
     * @return Ddocument
     */
    public function setMedium(\AppBundle\Entity\Lmedium $medium = null)
    {
        $this->medium = $medium;

        return $this;
    }

    /**
     * Get medium
     *
     * @return \AppBundle\Entity\Lmedium
     */
    public function getMedium()
    {
        return $this->medium;
    }
	
	
	
	
	/**
     * get dkeyword
     *
     * @param \Doctrine\Common\Collections\Collection
     */
    public function getDkeywordList()
    {
       return $this->dkeywords;
    }
	
	
	
	
	/**
     * get ddoctitle
     *
     * @param \Doctrine\Common\Collections\Collection
     */
    public function getDdoctitleList()
    {
       return $this->ddoctitle;
    }
	
	

	

	/**
     * get dlinkcontdoc
     *
     * @param \Doctrine\Common\Collections\Collection
     */
    public function getDLinkcontdocList()
    {
       return $this->dlinkcontdocs;
    }
	
	 public function getDLinkdoclocList()
    {
       return $this->dlinkdocloc;
    }
	
	
	
	
	//foreign keys
	protected $dkeywords;
	protected $dlinkcontdocs;
	protected $dlinkdocloc;
	protected $ddoctitle;
	protected $ddocsatellite;
	protected $ddocscale;
	protected $ddocfilm;
	protected $ddocmap;
	protected $ddocarchive;
	protected $ddocaerphoto;
	protected $dlinkdocsam;
	
    public function __construct()
    {
     
		$this->dkeywords =  Array();
		$this->dlinkcontdocs =  Array();
		$this->dlinkdocloc =  Array();
		$this->ddoctitle =  Array();
		$this->ddocsatellite =  Array();	
        $this->ddocscale=Array();
		$this->ddocmap=Array();
		$this->ddocarchive=Array();
		$this->ddocaerphoto=Array();
		$this->dlinkdocsam=Array();
    }
	
	
	
	
	public function getDkeyword()
    {
        return $this->dkeywords;
    }
	
	public function getDlinkcontdoc()
    {
        return $this->dlinkcontdocs;
    }
	
	public function getDlinkdocloc()
    {
        return $this->dlinkdocloc;
    }
	
	public function getDdocaerphoto()
    {
        return $this->ddocaerphoto;
    }
	
	public function getDdocttitle()
    {
        return $this->ddoctitle;
    }
	
	public function getDdocscale()
    {
        return $this->ddocscale;
    }
	
	public function getDdocfilm()
    {
        return $this->ddocfilm;
    }
	
	public function getDdocmap()
    {
        return $this->ddocmap;
    }
	
	public function getDdocarchive()
    {
        return $this->ddocarchive;
    }
	
	public function getDlinkdocsam()
	{
		return $this->dlinkdocsam;
	}
	
	public function initDkeywords($em)
	{		
		$this->attachForeignkeysAsObject($em,Dkeyword::class,"dkeywords", array("idcollection"=>$this->idcollection, "id"=>$this->iddoc));		
		return $this->dkeywords;
	}
	
	public function initNewDkeywords($em, $new_keywords)
	{		
		if(count($new_keywords)>0)
		{			
			$this->initDkeywords($em);
			$this->reattachForeignKeysAsObject(
				$em,
				Dkeyword::class,
				"dkeywords",				
				"getKeyword", 
				$new_keywords, 
				array("idcollection"=>$this->idcollection, "id"=>$this->iddoc)
			);	
		}
		return $this->dkeywords;
	}
	
	public function initDLinkcontdoc($em)
	{		
		
		$this->attachForeignkeysAsObject($em,Dlinkcontdoc::class,"dlinkcontdocs", array("idcollection"=>$this->idcollection, "id"=>$this->iddoc), "getPk");
		foreach($this->dlinkcontdocs as $obj)
		{
			$obj->setContribution_db($em);
		}
		return $this->dlinkcontdocs;
	}
	
	
	public function initNewDLinkcontdocs($em, $new_contributions)
	{
		if(count($new_contributions)>0)
		{			
			$this->reattachForeignKeysSignature(
				$em,
				DLinkcontdoc::class,
				"dlinkcontdocs", 
				"pk",  
				"getIdContribution", 
				$new_contributions, 
				"getPk",
				 array("idcollection"=>$this->idcollection, "id"=>$this->iddoc)
			);	
		}
		return $this->dlinkcontdocs;
	}
	
	
	public function initDLinkdocloc($em)
	{		
		
		
		$this->attachForeignkeysAsObject($em,Dlinkdocloc::class,"dlinkdocloc", array("idcollecdoc"=>$this->idcollection, "iddoc"=>$this->iddoc), "getPk");
		foreach($this->dlinkdocloc as $obj)
		{
			
			$obj->setDloccenter_db($em);
		}
		return $this->dlinkdocloc;
	}
	
	
	public function initNewDLinkdocloc($em, $new_locs)
	{
		
		if(count($new_locs)>0)
		{			
			
			
			
			$this->reattachForeignKeysAsObject(
				$em,
				Dlinkdocloc::class,
				"dlinkdocloc", 			
				"getPk", 
				$new_locs,		
				 array("idcollecdoc"=>$this->idcollection, "iddoc"=>$this->iddoc)
			);
			
		}
		return $this->dlinkdocloc;
	}
	
	public function initDdocaerphoto($em)
	{		
		
		$this->attachForeignkeysAsObject($em,Ddocaerphoto::class,"ddocaerphoto", array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc), "getPk");
		foreach($this->ddocaerphoto as $obj)
		{
			$obj->setplanvol_db($em);
		}
		return $this->ddocaerphoto;
	}
	
	public function initNewDdocaerphoto($em, $new_ddocaerphoto)
	{
		if(count($new_ddocaerphoto)>0)
		{			
			$this->reattachForeignKeysAsObject(
			$em,
			Ddocaerphoto::class,
			"ddocaerphoto", 			
			"getPk", 
			$new_ddocaerphoto,		
			array("idcollection"=>$this->idcollection,"iddoc"=>$this->iddoc ));
		}
		return $this->ddocaerphoto;
	}
	
	public function initDdoctitles($em)
	{
		
		$this->attachForeignkeysAsObject($em,Ddoctitle::class,"ddoctitle", array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc));		
		return $this->ddoctitle;
	}
	
	public function initDlinkdocsam($em)
	{
		$this->attachForeignkeysAsObject(
			$em, 
			Dlinkdocsam::class, 
			"dlinkdocsam",
			array(
				"idcollectiondoc"=>$this->idcollection,
				"iddoc"=>$this->iddoc
				),
				"getIdsample");
		return $this->dlinkdocsam;
	}
	
	public function initNewDdoctitles($em, $new_titles)
	{		
		if(count($new_titles)>0)
		{	
			$this->initDdoctitles($em);
			$this->reattachForeignKeysAsObject(
				$em,
				Ddoctitle::class,
				"ddoctitle",				
				"getTitle", 
				$new_titles, 
				array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc)
			);	
		}
		return $this->ddoctitle;
	}
	
	public function initDdocscale($em)
	{
		
		$this->attachForeignkeysAsObject($em,Ddocscale::class,"ddocscale", array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc));		
		return $this->ddocscale;
	}
	
	public function initNewDdocscale($em, $new_scales)
	{		
		if(count($new_scales)>0)
		{	
			$this->initDdocscale($em);
			$this->reattachForeignKeysAsObject(
				$em,
				Ddocscale::class,
				"ddocscale",				
				"getScale", 
				$new_scales, 
				array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc)
			);	
		}
		return $this->ddocscale;
	}
	
	public function initDdocfilm($em)
	{
		
		$this->attachForeignkeysAsObject($em,Ddocfilm::class,"ddocfilm", array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc));	
		return $this->ddocfilm;
	}
	
	public function initNewDdocfilm($em, $new_films)
	{		
		if(count($new_films)>0)
		{	
			$this->initDdocfilm($em);
			$this->reattachForeignKeysAsObject(
				$em,
				Ddocfilm::class,
				"ddocfilm",				
				"getFilm", 
				$new_films, 
				array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc)
			);	
		}
		return $this->ddocfilm;
	}
	
	public function initDdocsatellite($em)
	{
		
		$this->attachForeignkeysAsObject($em,Ddocsatellite::class,"ddocsatellite", array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc));		
		return $this->ddocsatellite;
	}
	
	public function initNewDdocsatellite($em, $new_satellite)
	{		
		if(count($new_satellite)>0)
		{		
			$this->initDdocsatellite($em);
			$this->reattachForeignKeysAsObject(
				$em,
				Ddocsatellite::class,
				"ddocsatellite",				
				"getPk", 
				$new_satellite, 
				array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc)
			);	
		}
		return $this->ddocsatellite;
	}
	
	public function initDdocarchive($em)
	{
		
		$this->attachForeignkeysAsObject($em,Ddocarchive::class,"ddocarchive", array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc));		
		return $this->ddocarchive;
	}
	
	public function initNewDdocarchive($em, $new_archive)
	{		
		if(count($new_archive)>0)
		{		
			$this->initDdocarchive($em);
			$this->reattachForeignKeysAsObject(
				$em,
				Ddocarchive::class,
				"ddocarchive",				
				"getPk", 
				$new_archive, 
				array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc)
			);	
		}
		return $this->ddocarchive;
	}
	
	public function initDdocmap($em)
	{
		
		$this->attachForeignkeysAsObject($em,Ddocmap::class,"ddocmap", array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc));		
		return $this->ddocmap;
	}
	
	public function initNewDdocmap($em, $new_map)
	{		
		if(count($new_map)>0)
		{		
			$this->initDdocmap($em);
			$this->reattachForeignKeysAsObject(
				$em,
				Ddocmap::class,
				"ddocmap",				
				"getPk", 
				$new_map, 
				array("idcollection"=>$this->idcollection, "iddoc"=>$this->iddoc)
			);	
		}
		return $this->ddocmap;
	}
	
	public function initNewDlinkdocsam($em, $new_dlinkdocsam)
	{
		$this->initDlinkdocsam($em);
		$this->attachForeignkeysAsObject(
			$em, 
			Dlinkdocsam::class, 
			"dlinkdocsam",
			"getPk",
			 $new_dlinkdocsam,
			array(
				"idcollectiondoc"=>$this->idcollection,
				"iddoc"=>$this->iddoc
				));
		return $this->dlinkdocsam;
	}
	
	//attach title
	protected $title;
	
	public function setTitle($str)
	{
		$this->title=$str;
		return $this;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle_db($em)
	{
		$titles_obj=$em->getRepository(Ddoctitle::class)
						 ->findBy(array('iddoc' => $this->getIddoc(),
									"idcollection"=> $this->getIdcollection()),
									array('pk'=>'ASC'));
		$titles_elem=Array();
		if($titles_obj !==NULL)
		{
			foreach($titles_obj as $obj)
			{
				$titles_elem[]=$obj->getTitle();
			}
		}
		if($this->getNumarchive()!==NULL)
		{
			$titles_elem[]=$this->getNumarchive();
		}
		if($this->getCaption()!==NULL)
		{
			$titles_elem[]=$this->getCaption();
		}
		$this->title= implode("; ", $titles_elem);
	}
	
	/**
     * Set pk
     *
     * @param integer $pk
     *
     * @return Ddocument
     */
    public function setPk($pk)
    {
        $this->pk = $pk;

        return $this;
    }
	

}
