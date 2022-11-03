<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** GeodarwinDocForeignKey*/
class GeodarwinDocForeignKey
{
	
	
	
	 /**
     * @var integer
     *
     * @ORM\Column(name="idcollection", type="integer", nullable=false)
     */
    protected $idcollection;
	
	//ftheeten fix
    /**
     * @var integer
     *
     * @ORM\Column(name="iddoc", type="integer", nullable=false)
     */
    protected $iddoc;	
    
	 /**
     * Set idcollection
     *
     * @param integer $id
     *
     * @return GeodarwinDocForeignKey
     */
    public function setIdcollection($id)
    {
        $this->idcollection = $id;
        return $this;
    }
	
		/**
     * Set iddoc
     *
     * @param integer $id
     *
     * @return GeodarwinDocForeignKey
     */
    public function setIddoc($id)
    {
        $this->iddoc = $id;
        return $this;
    }
	
	 /**
     * Get idcollection
     *
     * @return integer
     */
    public function getIdcollection()
    {
        return $this->idcollection;
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

	
	
}