<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlocstratumdesc
 *
 * @ORM\Table(name="dlocstratumdesc", uniqueConstraints={@ORM\UniqueConstraint(name="dlocstatumdesc_unique", columns={"idcollection", "idpt", "idstratum", "descript"})}, indexes={@ORM\Index(name="IDX_E798BD8931E4780850E3C8BA3D607B62", columns={"idcollection", "idpt", "idstratum"})})
 * @ORM\Entity
 */
class Dlocstratumdesc extends GeodarwinEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlocstratumdesc_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="descript", type="string", nullable=false)
     */
    private $descript;

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
     * @var integer
     *
     * @ORM\Column(name="idstratum", type="integer", nullable=false)
     */
    private $idstratum;
   

    /**
     * Set descript
     *
     * @param string $descript
     *
     * @return Dlocstratumdesc
     */
    public function setDescript($descript)
    {
        $this->descript = $descript;

        return $this;
    }

    /**
     * Get descript
     *
     * @return string
     */
    public function getDescript()
    {
        return $this->descript;
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
     * Set idcollectionobj
     *
     * @param \AppBundle\Entity\Dloclitho $litho
     *
     * @return Dlocstratumdesc
     */
    public function setIdcollectionobj(\AppBundle\Entity\Dloclitho $litho = null)
    {
        if( $litho !==null)
		{
			$this->idcollection=$litho->getIdcollection();
			$this->idpt=$litho->getIdpt();
			$this->idstratum=$litho->getIdpt();
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
     * Get pk
     *
     * @return integer
     */
    public function getPk()
    {
        return $this->pk;
    }
	
	public function getSignature()
	{
		return $this->getIdcollection()."_". $this->getIdpt()."_".$this->getIdstratum()."_".$this->getDescript();
	}
	
	public function setPk($pk)
	{
		$this->pk=$pk;
	}
}
