<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DLoclitho
 *
 * @ORM\Table(name="dloclitho", uniqueConstraints={@ORM\UniqueConstraint(name="dloclitho_unique", columns={"idcollection", "idpt", "idstratum"})}, indexes={@ORM\Index(name="IDX_AA614F2531E4780850E3C8BA", columns={"idcollection", "idpt"})})
 * @ORM\Entity
 */
class DLoclitho extends GeodarwinEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dloclitho_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var integer
     *
     * @ORM\Column(name="idstratum", type="integer", nullable=false)
     */
    private $idstratum;

    /**
     * @var string
     *
     * @ORM\Column(name="lithostratum", type="string", nullable=true)
     */
    private $lithostratum;

    /**
     * @var float
     *
     * @ORM\Column(name="topstratum", type="float", precision=10, scale=0, nullable=true)
     */
    private $topstratum;

    /**
     * @var float
     *
     * @ORM\Column(name="bottomstratum", type="float", precision=10, scale=0, nullable=true)
     */
    private $bottomstratum;

    /**
     * @var boolean
     *
     * @ORM\Column(name="alternance", type="boolean", nullable=true)
     */
    private $alternance;

    /**
     * @var string
     *
     * @ORM\Column(name="descriptionstratum", type="string", nullable=true)
     */
    private $descriptionstratum;

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
     * Get pk
     *
     * @return integer
     */
    public function getPk()
    {
        return $this->pk;
    }
	
	/**
     * Set pk
     *
     * @param string $pk
     *
     * @return Dloclitho
     */
    public function setPk($pk)
    {
        $this->pk = $pk;

        return $this;
    }

    /**
     * Set idstratum
     *
     * @param integer $idstratum
     *
     * @return Dloclitho
     */
    public function setIdstratum($idstratum)
    {
        $this->idstratum = $idstratum;

        return $this;
    }

    /**
     * Get idstratum
     *
     * @return integer
     */
    public function getIdstratum()
    {
        return $this->idstratum;
    }

    /**
     * Set lithostratum
     *
     * @param string $lithostratum
     *
     * @return Dloclitho
     */
    public function setLithostratum($lithostratum)
    {
        $this->lithostratum = $lithostratum;

        return $this;
    }

    /**
     * Get lithostratum
     *
     * @return string
     */
    public function getLithostratum()
    {
        return $this->lithostratum;
    }

    /**
     * Set topstratum
     *
     * @param float $topstratum
     *
     * @return Dloclitho
     */
    public function setTopstratum($topstratum)
    {
        $this->topstratum = $topstratum;

        return $this;
    }

    /**
     * Get topstratum
     *
     * @return float
     */
    public function getTopstratum()
    {
        return $this->topstratum;
    }

    /**
     * Set bottomstratum
     *
     * @param float $bottomstratum
     *
     * @return Dloclitho
     */
    public function setBottomstratum($bottomstratum)
    {
        $this->bottomstratum = $bottomstratum;

        return $this;
    }

    /**
     * Get bottomstratum
     *
     * @return float
     */
    public function getBottomstratum()
    {
        return $this->bottomstratum;
    }

    /**
     * Set alternance
     *
     * @param boolean $alternance
     *
     * @return Dloclitho
     */
    public function setAlternance($alternance)
    {
        $this->alternance = $alternance;

        return $this;
    }

    /**
     * Get alternance
     *
     * @return boolean
     */
    public function getAlternance()
    {
        return $this->alternance;
    }

    /**
     * Set descriptionstratum
     *
     * @param string $descriptionstratum
     *
     * @return Dloclitho
     */
    public function setDescriptionstratum($descriptionstratum)
    {
        $this->descriptionstratum = $descriptionstratum;

        return $this;
    }

    /**
     * Get descriptionstratum
     *
     * @return string
     */
    public function getDescriptionstratum()
    {
        return $this->descriptionstratum;
    }

    /**
     * Set idcollection
     *
     * @param string $idcollection
     *
     * @return DLoclitho
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
     * @return DLoclitho
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
	
	//foreign keys
	public $dlocstratumdesc;
	
	public function initDlocstratumdesc($em)
	{
		
		//$this->attachForeignkeysAsObject($em,Dlocstratumdesc::class,"dlocstratumdesc", array("idcollection"=>$this->idcollection, "idpt"=>$this->idpt, "idstratum"=>$this->idstratum), "getPk");

		$this->attachForeignkeysAsObject($em,Dlocstratumdesc::class,"dlocstratumdesc", array("idcollection"=>$this->idcollection, "idpt"=>$this->idpt, "idstratum"=> $this->idstratum), "getPk");			
		return $this->dlocstratumdesc;
	}
	
	public function initNewDlocstratumdesc($em, $new_dlocstratumdesc)
	{		
		$this->initDlocstratumdesc($em);
		//take description in the signature
		if(count($new_dlocstratumdesc)>0)
		{			
			/*$this->reattachForeignKeysAsObject(
				$em,
				Dlocstratumdesc::class,
				"dlocstratumdesc",				
				"getSignature", 
				$new_dlocstratumdesc, 
				array("idcollection"=>$this->idcollection, "idpt"=>$this->idpt, "idstratum"=>$this->idstratum)
			);	*/
			$this->reattachForeignKeysAsObject(
				$em,
				Dlocstratumdesc::class,
				"dlocstratumdesc",				
				"getPk",//"getSignature", 
				$new_dlocstratumdesc, 
				array("idcollection"=>$this->idcollection, "idpt"=>$this->idpt, "idstratum"=> $this->idstratum)
			);
		}
		return $this->dlocstratumdesc;
	}
	
	//ftheeten
	public function getSignature()
	{
		//order is important when rattaching
		return $this->getIdcollection()."_". $this->getIdpt()."_".$this->getIdstratum()."_".$this->getTopstratum()."_".$this->getBottomstratum()."_".var_export($this->getAlternance(), true)."_".$this->getDescriptionstratum()."_".$this->getLithostratum();
		//return $this->getIdcollection()."_". $this->getIdpt()."_".$this->getIdstratum();
		//return $this->getPk();
	}
}
