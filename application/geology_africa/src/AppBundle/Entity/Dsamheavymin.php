<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dsamheavymin
 *
 * @ORM\Table(name="dsamheavymin", uniqueConstraints={@ORM\UniqueConstraint(name="dsamheavymin_unique", columns={"idcollection", "idsample"})})
 * @ORM\Entity
 */
class Dsamheavymin
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dsamheavymin_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var float
     *
     * @ORM\Column(name="weighthm", type="float", precision=10, scale=0, nullable=true)
     */
    private $weighthm;

    /**
     * @var float
     *
     * @ORM\Column(name="weightsample", type="float", precision=10, scale=0, nullable=true)
     */
    private $weightsample;

    /**
     * @var string
     *
     * @ORM\Column(name="observationhm", type="string", nullable=true)
     */
    private $observationhm;

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
     * Set weighthm
     *
     * @param float $weighthm
     *
     * @return Dsamheavymin
     */
    public function setWeighthm($weighthm)
    {
        $this->weighthm = $weighthm;

        return $this;
    }

    /**
     * Get weighthm
     *
     * @return float
     */
    public function getWeighthm()
    {
        return $this->weighthm;
    }

    /**
     * Set weightsample
     *
     * @param float $weightsample
     *
     * @return Dsamheavymin
     */
    public function setWeightsample($weightsample)
    {
        $this->weightsample = $weightsample;

        return $this;
    }

    /**
     * Get weightsample
     *
     * @return float
     */
    public function getWeightsample()
    {
        return $this->weightsample;
    }

    /**
     * Set observationhm
     *
     * @param string $observationhm
     *
     * @return Dsamheavymin
     */
    public function setObservationhm($observationhm)
    {
        $this->observationhm = $observationhm;

        return $this;
    }

    /**
     * Get observationhm
     *
     * @return string
     */
    public function getObservationhm()
    {
        return $this->observationhm;
    }

    /**
     * Set idcollection
     *
     * @param string $idcollection
     *
     * @return Dsamheavymin
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
     * @return Dsamheavymin
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
     * @return Dsamheavymin
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
	
	
	public function setPk($pk)
	{
		$this->pk=$pk;
	}
}
