<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlinkcontloc
 *
 * @ORM\Table(name="dlinkcontloc", uniqueConstraints={@ORM\UniqueConstraint(name="dlinkcontloc_unique", columns={"idcontribution", "idcollection", "id"})}, indexes={@ORM\Index(name="IDX_C82D3C5831E47808BF396750", columns={"idcollection", "id"})})
 * @ORM\Entity
 */
class Dlinkcontloc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlinkcontloc_pk_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="idcontribution", type="integer", nullable=false)
     */
    private $idcontribution;

	/**
     * @var integer
     *
     * @ORM\Column(name="idpt", type="integer", nullable=false)
     */
    private $idpt;
	
    /**
     * Get pk
     *
     * @return integer
     */
    public function getPk()
    {
        return $this->pk;
    }
	
	 public function setPk($val)
    {
        $this->pk=$val;
		return $this;
    }
	

 /**
     * Set idcontribution
     *
     * @param integer $idcontribution
     *
     * @return Dlinkcontloc
     */
    public function setIdcontribution( $idcontribution = null)
    {
        $this->idcontribution = $idcontribution;

        return $this;
    }

    /**
     * Get idcontribution
     *
     * @return integer
     */
    public function getIdcontribution()
    {
        return $this->idcontribution;
    }
	
	/**
     * Set idptobj
     *
     * @param \AppBundle\Entity\Dloccenter $obj
     *
     * @return Dlinkcontloc
     */
    public function setIdPtObj(\AppBundle\Entity\Dloccenter $obj = null)
    {
		if($obj!==null)
		{
			$this->idpt = $obj->getIdpt();
			$this->idcollection = $obj->getIdcollection();
		}
        return $this;
    }
	
	/**
     * Set idpt
     *
     * @param integer $idpt
     *
     * @return Dlinkcontloc
     */
    public function setIdpt($id = null)
    {
        $this->idpt = $id;

        return $this;
    }

    /**
     * Get idpt
     *
     * @return \AppBundle\Entity\Dloccenter
     */
    public function getIdpt()
    {
        return $this->idpt;
    }

    /**
     * Set idcollection
     *
     * @param integer $idcollection
     *
     * @return Dlinkcontloc
     */
    public function setIdcollection( $idcollection = null)
    {
        $this->idcollection = $idcollection;

        return $this;
    }
	
	public $dcontribution;
	
	public function setDcontribution_db($em)
	{
		$tmp_cont=$em->getRepository(Dcontribution::class)
							->findOneBy(array(
								"idcontribution"=>$this->idcontribution,	
																
							));	
		if($tmp_cont!==null)
		{
			$tmp_cont->setDescriptionDB($em);
		}
		$this->dcontribution=$tmp_cont;
	}

}
