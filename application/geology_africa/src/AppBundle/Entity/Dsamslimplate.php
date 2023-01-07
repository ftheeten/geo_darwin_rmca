<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dsamslimplate
 *
 * @ORM\Table(name="dsamslimplate", uniqueConstraints={@ORM\UniqueConstraint(name="dsamslimplate_unique", columns={"idcollection", "idsample", "numplate"})}, indexes={@ORM\Index(name="IDX_743B1C6631E4780895B6DB6F", columns={"idcollection", "idsample"})})
 * @ORM\Entity
 */
class Dsamslimplate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dsamslimplate_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="numplate", type="string", nullable=false)
     */
    private $numplate;

    /**
     * @var string
     *
     * @ORM\Column(name="platedescription", type="text", nullable=true)
     */
    private $platedescription;

    /**
     * @var string
     *
     * @ORM\Column(name="platevariousinfo", type="string", nullable=true)
     */
    private $platevariousinfo;

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
     * Get pk
     *
     * @return integer
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * Set numplate
     *
     * @param string $numplate
     *
     * @return Dsamslimplate
     */
    public function setNumplate($numplate)
    {
        $this->numplate = $numplate;

        return $this;
    }

    /**
     * Get numplate
     *
     * @return string
     */
    public function getNumplate()
    {
        return $this->numplate;
    }

    /**
     * Set platedescription
     *
     * @param string $platedescription
     *
     * @return Dsamslimplate
     */
    public function setPlatedescription($platedescription)
    {
        $this->platedescription = $platedescription;

        return $this;
    }

    /**
     * Get platedescription
     *
     * @return string
     */
    public function getPlatedescription()
    {
        return $this->platedescription;
    }

    /**
     * Set platevariousinfo
     *
     * @param string $platevariousinfo
     *
     * @return Dsamslimplate
     */
    public function setPlatevariousinfo($platevariousinfo)
    {
        $this->platevariousinfo = $platevariousinfo;

        return $this;
    }

    /**
     * Get platevariousinfo
     *
     * @return string
     */
    public function getPlatevariousinfo()
    {
        return $this->platevariousinfo;
    }

   	/**
     * Set idcollection
     *
     * @param string $idcollection
     *
     * @return Dsamslimplate
     */
    public function setIdcollection($idcollection)
    {
        $this->idcollection = $idcollection;

        return $this;
    }

    /**
     * Set idcollectionobj
     *
     * @param \AppBundle\Entity\Dsample $idcollection
     *
     * @return Dsamslimplate
     */
    public function setIdcollectionobj(\AppBundle\Entity\Dsample $sample = null)
    {
        if($sample !==null)
		{
			 $this->idcollection = $sample->getIdCollection();
			 $this->idsample = $sample->getIdSample();
		}

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
     * @param integer
     *
     * @return Dsamslimplate
     */
    public function setIdsample( $idsample)
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
     * Get pk
     *
     * @return integer
     */
    
	
	public function setPk($pk)
	{
		$this->pk=$pk;
	}
}
