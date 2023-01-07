<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Dlinklocsam
 *
 * @ORM\Table(name="dlinklocsam", uniqueConstraints={@ORM\UniqueConstraint(name="dlinklocsam_unique", columns={"idcollectionloc", "idpt", "idstratum", "idcollecsample", "idsample"})}, indexes={@ORM\Index(name="IDX_A8D4CB9BB28ADCE995B6DB6F", columns={"idcollecsample", "idsample"}), @ORM\Index(name="IDX_A8D4CB9BC6C028CA50E3C8BA3D607B62", columns={"idcollectionloc", "idpt", "idstratum"})})
 * @ORM\Entity
 */
class Dlinklocsam
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlinklocsam_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

   /**
     * @var string
     *
     * @ORM\Column(name="idcollecsample", type="string", nullable=false)
     */
    private $idcollecsample;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="idsample", type="integer", nullable=false)
     */
    private $idsample;

    /**
     * @var string
     *
     * @ORM\Column(name="idcollectionloc", type="string", nullable=false)
     */
    private $idcollectionloc;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="idpt", type="string", nullable=false)
     */
    private $idpt;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="idstratum", type="string", nullable=false)
     */
    private $idstratum;



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
     * @return Dlinklocsam
     */
    public function setIdcollecsample($idcollection)
    {
        $this->idcollecsample = $idcollection;

        return $this;
    }

    /**
     * Set idcollectionobj
     *
     * @param \AppBundle\Entity\Dsample $idcollection
     *
     * @return Dlinklocsam
     */
    public function setIdcollecsampleobj(\AppBundle\Entity\Dsample $sample = null)
    {
        if($sample !==null)
		{
			 $this->idcollecsample = $sample->getIdCollection();
			 $this->idsample = $sample->getIdSample();
		}

        return $this;
    }

   

    /**
     * Set idcollectionloc
     *
     * @param \AppBundle\Entity\Dloclitho $idcollectionloc
     *
     * @return Dlinklocsam
     */
    public function setIdcollectionloc(\AppBundle\Entity\Dloclitho $idcollectionloc = null)
    {
        $this->idcollectionloc = $idcollectionloc;

        return $this;
    }

    /**
     * Get idcollectionloc
     *
     * @return \AppBundle\Entity\Dloclitho
     */
    public function getIdcollectionloc()
    {
        return $this->idcollectionloc;
    }
	
	//dloclitho
	
	public $dloclitho;
	
	
	public function getLocLitho()
	{
		return $this->dloclitho;
	}
	
	public function getSample()
	{
		return $this->dsample;
	}
	

	
	public function setPk($pk)
	{
		$this->pk=$pk;
	}
	
	public function getIdpt()
	{
		return $this->idpt;
	}
	
	public function setIdpt($val)
	{
		$this->idpt=$val;
		return $this;
	}
	
	public function getIdstratum()
	{
		return $this->idstratum;
		
	}
	
	public function setIdstratum($val)
	{
		$this->idstratum=$val;
		return $this;
	}
	
	//fks
	public $dloclithos;
	public $dsample;
	
	public function setDloclitho_db($em)
	{
		$tmp_loc=$em->getRepository(DLoclitho::class)
							->findOneBy(array(
								"idcollection"=>$this->idcollectionloc,	
								"idpt"=>$this->idpt,
								"idstratum"=>$this->idstratum,								
							));
		if($tmp_loc!==null)
		{
			$tmp_loc->attachLoccenter($em);
		}
		$this->dloclithos=$tmp_loc;
	}
	
	public function setDsample_db($em)
	{
		$tmp_loc=$em->getRepository(Dsample::class)
							->findOneBy(array(
								"idcollection"=>$this->idcollecsample,	
								"idsample"=>$this->idsample,
								));
		$this->dsample=$tmp_loc;
	}
	
	/*public $dsamples;
	public $dloclithos;
	
	public function getDsamples()
	{
		return $this->dsamples;
	}
	
	public function getDloclithos()
	{
		return $this->dloclithos;
	}
	
	public function setDsamples_db($em)
	{
		$tmp_samples=$em->getRepository(Dsample::class)
							->findOneBy(array(
								"idcollection"=>$this->idcollection,
								"idsample"=>$this->idsample
							));
		$this->dsamples=$tmp_samples;
	}*/
	
}
