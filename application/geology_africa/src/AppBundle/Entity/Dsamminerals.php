<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dsamminerals
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\DSammineralsRepository")
 * @ORM\Table(name="dsamminerals", uniqueConstraints={@ORM\UniqueConstraint(name="dsamminerals_unique", columns={"idcollection", "idsample", "idmineral"})}, indexes={@ORM\Index(name="IDX_1AC34B3131E4780895B6DB6F", columns={"idcollection", "idsample"}), @ORM\Index(name="IDX_1AC34B31C29FFB11", columns={"idmineral"})})
 * @ORM\Entity
 */
class Dsamminerals
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dsamminerals_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var integer
     *
     * @ORM\Column(name="grade", type="smallint", nullable=true)
     */
    private $grade;

	
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
     * @var integer
     *
     * @ORM\Column(name="idmineral", type="integer", nullable=false)
     */
    private $idmineral;

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
     * Set grade
     *
     * @param integer $grade
     *
     * @return Dsamminerals
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return integer
     */
    public function getGrade()
    {
        return $this->grade;
    }
	
	/**
     * Set idcollection
     *
     * @param string $idcollection
     *
     * @return Dsamminerals
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
     * @return Dsamminerals
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
     * @return integer
     */
    public function setIdsample( $idsample)
    {
        $this->idsample = $idsample;

        return $this;
    }

    /**
     * Get idsample
     *
     * @return \AppBundle\Entity\Dsample
     */
    public function getIdsample()
    {
        return $this->idsample;
    }
	

    /**
     * Set idmineral
     *
     * @param  integer
     *
     * @return Dsamminerals
     */
    public function setIdmineral($idmineral = null)
    {
        $this->idmineral = $idmineral;

        return $this;
    }
	
	
	
	

    /**
     * Get idmineral
     *
     * @return integer
     */
    public function getIdmineral()
    {
        return $this->idmineral;
    }
	

	
	public function setPk($pk)
	{
		$this->pk=$pk;
	}
	
	//fks
	public $lminerals;
	public function setLminerals_db($em)
	{
		$tmp_mineral=$em->getRepository(Lminerals::class)
							->findOneBy(array(
								"idmineral"=>$this->idmineral							
							));
		$this->lminerals=$tmp_mineral;
	}
	
/*
	public $dsamples;
	public $lminerals;
	
	public function getDsamples()
	{
		return $this->dsamples;
	}
	
	public function getLminerals()
	{
		return $this->lminerals
	}
	
	public function setDsamples_db($em)
	{
		$tmp_samples=$em->getRepository(Dsample::class)
							->findOneBy(array(
								"idcollection"=>$this->idcollection,
								"idsample"=>$this->idsample
							));
		$this->dsamples=$tmp_samples;
	}
	
	public function setLminerals_db($em)
	{
		$tmp_minerals=$em->getRepository(Dsample::class)
							->findOneBy(array(
								"idmineral"=>$this->idmineral							
							));
		$this->lminerals=$tmp_minerals;
	}
*/
	
	
}
