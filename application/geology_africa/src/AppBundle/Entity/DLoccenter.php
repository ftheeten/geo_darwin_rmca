<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dloccenter
 *
 * @ORM\Table(name="dloccenter", uniqueConstraints={@ORM\UniqueConstraint(name="dloccenter_unique", columns={"idcollection", "idpt"})}, indexes={@ORM\Index(name="IDX_C376102433EB2703", columns={"idprecision"})})
 * @ORM\Entity
 */
class DLoccenter
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
     * @ORM\Column(name="originalcoord", type="text", nullable=true)
     */
    private $originalcoord;
	


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
     * @return Dloccenter
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
     * @return Dloccenter
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
     * Set originalcoord
     *
     * @param string $originalcoord
     *
     * @return Dloccenter
     */
    public function setOriginalcoord($originalcoord)
    {
        $this->originalcoord = $originalcoord;

        return $this;
    }

    /**
     * Get originalcoord
     *
     * @return string
     */
    public function getOriginalcoord()
    {
        return $this->originalcoord;
    }
	
}
