<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dloccenter
 *
 * @ORM\Table(name="dloccenter", uniqueConstraints={@ORM\UniqueConstraint(name="dloccenter_unique", columns={"idcollection", "idpt"})}, indexes={@ORM\Index(name="IDX_C376102433EB2703", columns={"idprecision"})})
 * @ORM\Entity
 */
class DLoccenter extends GeodarwinEntity
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
     * @ORM\Column(name="idpt", type="integer", nullable=false)
     */
    private $idpt = '0';

    /**
     * @var decimal
     *
     * @ORM\Column(name="coord_lat", type="decimal", precision=10, scale=4, nullable=true)
     */
    private $coord_lat;

    /**
     * @var decimal
     *
     * @ORM\Column(name="coord_long", type="decimal", precision=10, scale=4, nullable=true)
     */
    private $coord_long;

    /**
     * @var integer
     *
     * @ORM\Column(name="altitude", type="integer", nullable=true)
     */
    private $altitude = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="fieldnum", type="string", nullable=true)
     */
    private $fieldnum;

    /**
     * @var string
     *
     * @ORM\Column(name="place", type="string", nullable=true)
     */
    private $place;

    /**
     * @var string
     *
     * @ORM\Column(name="geodescription", type="text", nullable=true)
     */
    private $geodescription;

    /**
     * @var string
     *
     * @ORM\Column(name="positiondescription", type="string", nullable=true)
     */
    private $positiondescription;

    /**
     * @var string
     *
     * @ORM\Column(name="variousinfo", type="string", nullable=true)
     */
    private $variousinfo;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="text", nullable=true)
     */
    private $country;
	
    /**
     * @var string
     *
     * @ORM\Column(name="docref", type="string", nullable=true)
     */
    private $docref;

    /**
     * @var \AppBundle\Entity\Lprecision
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Lprecision")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idprecision", referencedColumnName="idprecision")
     * })
     */
    private $idprecision;

    /**
     * @var string
     *
     * @ORM\Column(name="epsg", type="text", nullable=true)
     */
    private $epsg;
	
	/**
     * @var string
     *
     * @ORM\Column(name="wkt", type="text", nullable=true)
     */
    private $wkt;
	
	/**
     * @var string
     *
     * @ORM\Column(name="original_latitude", type="text", nullable=true)
     */
    private $original_latitude;
	
	/**
     * @var string
     *
     * @ORM\Column(name="original_longitude", type="text", nullable=true)
     */
    private $original_longitude;
	


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
     * @return DLoccenter
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
     * Set idpt
     *
     * @param integer $idpt
     *
     * @return DLoccenter
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
     * Set coord_lat
     *
     * @param decimal $coord_lat
     *
     * @return Dloccenter
     */
    public function setCoordLat($coord_lat)
    {
        $this->coord_lat = $coord_lat;

        return $this;
    }

    /**
     * Get coord_lat
     *
     * @return decimal
     */
    public function getCoordLat()
    {
        return $this->coord_lat;
    }

    /**
     * Set coord_long
     *
     * @param decimal $coord_long
     *
     * @return Dloccenter
     */
    public function setCoordLong($coord_long)
    {
        $this->coord_long = $coord_long;

        return $this;
    }

    /**
     * Get coord_long
     *
     * @return decimal
     */
    public function getCoordLong()
    {
        return $this->coord_long;
    }

    /**
     * Set altitude
     *
     * @param integer $altitude
     *
     * @return Dloccenter
     */
    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;

        return $this;
    }

    /**
     * Get altitude
     *
     * @return integer
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Dloccenter
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
     * Set fieldnum
     *
     * @param string $fieldnum
     *
     * @return Dloccenter
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
     * Set place
     *
     * @param string $place
     *
     * @return Dloccenter
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set geodescription
     *
     * @param string $geodescription
     *
     * @return Dloccenter
     */
    public function setGeodescription($geodescription)
    {
        $this->geodescription = $geodescription;

        return $this;
    }

    /**
     * Get geodescription
     *
     * @return string
     */
    public function getGeodescription()
    {
        return $this->geodescription;
    }

    /**
     * Set positiondescription
     *
     * @param string $positiondescription
     *
     * @return Dloccenter
     */
    public function setPositiondescription($positiondescription)
    {
        $this->positiondescription = $positiondescription;

        return $this;
    }

    /**
     * Get positiondescription
     *
     * @return string
     */
    public function getPositiondescription()
    {
        return $this->positiondescription;
    }

    /**
     * Set variousinfo
     *
     * @param string $variousinfo
     *
     * @return Dloccenter
     */
    public function setVariousinfo($variousinfo)
    {
        $this->variousinfo = $variousinfo;

        return $this;
    }

    /**
     * Get variousinfo
     *
     * @return string
     */
    public function getVariousinfo()
    {
        return $this->variousinfo;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Dloccenter
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
	
    /**
     * Set docref
     *
     * @param string $docref
     *
     * @return Dloccenter
     */
    public function setDocref($docref)
    {
        $this->docref = $docref;

        return $this;
    }

    /**
     * Get docref
     *
     * @return string
     */
    public function getDocref()
    {
        return $this->docref;
    }

    /**
     * Set idprecision
     *
     * @param \AppBundle\Entity\Lprecision $idprecision
     *
     * @return Dloccenter
     */
    public function setIdprecision(\AppBundle\Entity\Lprecision $idprecision = null)
    {
        $this->idprecision = $idprecision;

        return $this;
    }

    /**
     * Get idprecision
     *
     * @return \AppBundle\Entity\Lprecision
     */
    public function getIdprecision()
    {
        return $this->idprecision;
    }
	
	/**
     * Set epsg
     *
     * @param string $epsg
     *
     * @return Dloccenter
     */
    public function setEpsg($epsg)
    {
        $this->epsg = $epsg;

        return $this;
    }

    /**
     * Get epsg
     *
     * @return string
     */
    public function getEpsg()
    {
        return $this->epsg;
    }
	
	    /**
     * Set wkt
     *
     * @param string $wkt
     *
     * @return Dloccenter
     */
    public function setWkt($wkt)
    {
        $this->wkt = $wkt;

        return $this;
    }

    /**
     * Get wkt
     *
     * @return string
     */
    public function getWkt()
    {
        return $this->wkt;
    }
	
	/**
     * Set original_latitude
     *
     * @param string $original_latitude
     *
     * @return Dloccenter
     */
    public function setOriginalLatitude($original_latitude)
    {
        $this->original_latitude = $original_latitude;

        return $this;
    }

    /**
     * Get original_latitude
     *
     * @return string
     */
    public function getOriginalLatitude()
    {
        return $this->original_latitude;
    }
	
	/**
     * Set original_longitude
     *
     * @param string $original_longitude
     *
     * @return Dloccenter
     */
    public function setOriginaLongitude($original_longitude)
    {
        $this->original_longitude = $original_longitude;

        return $this;
    }

    /**
     * Get originalLongitude
     *
     * @return string
     */
    public function getOriginalLongitude()
    {
        return $this->original_longitude;
    }
	
	
	
	/**
     * @var string
     *
     * @ORM\Column(name="coordinate_format", type="text", nullable=true)
     */
    private $coordinate_format;
	
	/**
     * Set coordinate_format
     *
     * @param string $coordinate_format
     *
     * @return Dloccenter
     */
    public function setCoordinateFormat($coordinate_format)
    {
        $this->coordinate_format = $coordinate_format;

        return $this;
    }

    /**
     * Get coordinate_format
     *
     * @return string
     */
    public function getCoordinateFormat()
    {
        return $this->fieldnum;
    }
	
	/**
     * @var integer
     *
     * @ORM\Column(name="latitude_degrees", type="integer", nullable=true)
     */
    private $latitude_degrees ;
	
	/**
     * @var decimal
     *
     * @ORM\Column(name="latitude_minutes", type="decimal", precision=10, scale=8, nullable=true)
     */
    private $latitude_minutes;
	
    /**
     * @var decimal
     *
     * @ORM\Column(name="latitude_seconds", type="decimal", precision=10, scale=8, nullable=true)
     */
    private $latitude_seconds;
	
	/**
     * @var string
     *
     * @ORM\Column(name="latitude_direction", type="string",nullable=true)
     */
    private $latitude_direction;
	
	
	/**
     * Set latitude_degrees
     *
     * @param integer $latitude_degrees
     *
     * @return Dloccenter
     */
    public function setLatitudeDegrees($latitude_degrees)
    {
        $this->latitude_degrees = $latitude_degrees;

        return $this;
    }

    /**
     * Get latitude_degrees
     *
     * @return integer
     */
    public function getLatitudeDegrees()
    {
        return $this->latitude_degrees;
    }
	
	/**
     * Set latitude_minutes
     *
     * @param decimal $latitude_minutes
     *
     * @return Dloccenter
     */
    public function setLatitudeMinutes($latitude_minutes)
    {
        $this->latitude_minutes = $latitude_minutes;

        return $this;
    }

    /**
     * Get latitude_seconds
     *
     * @return decimal
     */
    public function getLatitudeMinutes()
    {
        return $this->latitude_minutes;
    }
	
	/**
     * Set latitude_minutes
     *
     * @param decimal $latitude_seconds
     *
     * @return Dloccenter
     */
    public function setLatitudeSeconds($latitude_seconds)
    {
        $this->latitude_seconds = $latitude_seconds;

        return $this;
    }

    /**
     * Get latitude_seconds
     *
     * @return decimal
     */
    public function getLatitudeSeconds()
    {
        return $this->latitude_seconds;
    }
	
	/**
     * Set latitude_direction
     *
     * @param string $latitude_direction
     *
     * @return Dloccenter
     */
    public function setLatitudeDirection($latitude_direction)
    {
        $this->latitude_direction = $latitude_direction;

        return $this;
    }

    /**
     * Get latitude_direction
     *
     * @return string
     */
    public function getLatitudeDirection()
    {
        return $this->latitude_direction;
    }
	
		/**
     * @var integer
     *
     * @ORM\Column(name="longitude_degrees", type="integer", nullable=true)
     */
    private $longitude_degrees ;
	
	/**
     * @var decimal
     *
     * @ORM\Column(name="longitude_minutes", type="decimal", precision=10, scale=8, nullable=true)
     */
    private $longitude_minutes;
	
    /**
     * @var decimal
     *
     * @ORM\Column(name="longitude_seconds", type="decimal", precision=10, scale=8, nullable=true)
     */
    private $longitude_seconds;
	
	/**
     * @var string
     *
     * @ORM\Column(name="longitude_direction", type="string",nullable=true)
     */
    private $longitude_direction;
	
	
	/**
     * Set longitude_degrees
     *
     * @param integer $longitude_degrees
     *
     * @return Dloccenter
     */
    public function setLongitudeDegrees($longitude_degrees)
    {
        $this->longitude_degrees = $longitude_degrees;

        return $this;
    }

    /**
     * Get longitude_degrees
     *
     * @return integer
     */
    public function getLongitudeDegrees()
    {
        return $this->longitude_degrees;
    }
	
	/**
     * Set longitude_minutes
     *
     * @param decimal $longitude_minutes
     *
     * @return Dloccenter
     */
    public function setLongitudeMinutes($longitude_minutes)
    {
        $this->longitude_minutes = $longitude_minutes;

        return $this;
    }

    /**
     * Get longitude_seconds
     *
     * @return decimal
     */
    public function getLongitudeMinutes()
    {
        return $this->longitude_minutes;
    }
	
	/**
     * Set longitude_minutes
     *
     * @param decimal $longitude_seconds
     *
     * @return Dloccenter
     */
    public function setLongitudeSeconds($longitude_seconds)
    {
        $this->longitude_seconds = $longitude_seconds;

        return $this;
    }

    /**
     * Get longitude_seconds
     *
     * @return decimal
     */
    public function getLongitudeSeconds()
    {
        return $this->longitude_seconds;
    }
	
	/**
     * Set longitude_direction
     *
     * @param string $longitude_direction
     *
     * @return Dloccenter
     */
    public function setLongitudeDirection($longitude_direction)
    {
        $this->longitude_direction = $longitude_direction;

        return $this;
    }

    /**
     * Get longitude_direction
     *
     * @return string
     */
    public function getLongitudeDirection()
    {
        return $this->longitude_direction;
    }
	
	//foreign keys
	protected $dloclitho;
	
	public function __construct()
    {     
		$this->dloclitho =  Array();		
    }
	
	public function initDloclitho($em)
	{
		
		$this->attachForeignkeysAsObject($em,DLoclitho::class,"dloclitho", array("idcollection"=>$this->idcollection, "idpt"=>$this->idpt));
		foreach($this->dloclitho as $obj)
		{
			$obj->initDlocstratumdesc($em);
		}		
		return $this->dloclitho;
	}
	
	public function initNewDloclitho($em, $new_dloclitho)
	{		
		$this->initDloclitho($em);
		//take description in the signature
		if(count($new_dloclitho)>0)
		{			
			$this->reattachForeignKeysAsObject(
				$em,
				DLoclitho::class,
				"dloclitho",				
				"getPk",//"getSignature", 
				$new_dloclitho, 
				array("idcollection"=>$this->idcollection, "idpt"=>$this->idpt)
			);	
		}
		return $this->dloclitho;
	}
	
	//attach description (display in FK)
	protected $description;
	
	public function setDescription($str)
	{
		$this->description=$str;
		return $this;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function setDescription_db($em)
	{
		
		$elem=Array();
		$elem[]=(string)$this->getIdcollection();
		$elem[]=(string)$this->getIdpt();
		if($this->getFieldnum()!==NULL)
		{
			$elem[]=$this->getFieldnum();
		}
		if($this->getPlace()!==NULL)
		{
			$elem[]=$this->getPlace();
		}
		$this->description= implode("; ", $elem);
	}
	
	 
}