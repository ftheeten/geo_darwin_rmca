<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlinkcontribute
 *
 * @ORM\Table(name="dlinkcontribute", uniqueConstraints={@ORM\UniqueConstraint(name="dlinkcontribute_unique", columns={"idcontribution", "idcontributor"})}, indexes={@ORM\Index(name="IDX_8153F1E8AC9A611C", columns={"idcontribution"}), @ORM\Index(name="IDX_8153F1E81CAEE73C", columns={"idcontributor"})})
 * @ORM\Entity
 */
class Dlinkcontribute
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlinkcontribute_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="contributorrole", type="string", nullable=true)
     */
    private $contributorrole;

    /**
     * @var integer
     *
     * @ORM\Column(name="contributororder", type="smallint", nullable=true)
     */
    private $contributororder = '0';

    /**
     * @var \AppBundle\Entity\Dcontribution
     * 
	 * @ORM\Column(name="idcontribution", type="integer", nullable=false)          
     */
    private $idcontribution;

    /**
     * @var \AppBundle\Entity\Dcontributor
     *
     * @ORM\Column(name="idcontributor", type="integer", nullable=false)     
     */
    private $idcontributor;



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
     * Set contributorrole
     *
     * @param string $contributorrole
     *
     * @return Dlinkcontribute
     */
    public function setContributorrole($contributorrole)
    {
        $this->contributorrole = $contributorrole;

        return $this;
    }

    /**
     * Get contributorrole
     *
     * @return string
     */
    public function getContributorrole()
    {
        return $this->contributorrole;
    }

    /**
     * Set contributororder
     *
     * @param integer $contributororder
     *
     * @return Dlinkcontribute
     */
    public function setContributororder($contributororder)
    {
        $this->contributororder = $contributororder;

        return $this;
    }

    /**
     * Get contributororder
     *
     * @return integer
     */
    public function getContributororder()
    {
        return $this->contributororder;
    }

    /**
     * Set idcontribution
     *
     * @param integer $idcontribution
     *
     * @return Dlinkcontribute
     */
    public function setIdcontribution($idcontribution)
    {
		//if($idcontribution!==null)
		//{
			$this->idcontribution = $idcontribution;
		//}
        return $this;
    }
	
	

    /**
     * Get idcontribution
     *
     * @return \AppBundle\Entity\Dcontribution
     */
    public function getIdcontribution()
    {
        return $this->idcontribution;
    }

    /**
     * Set idcontributor
     *
     * @param integer $idcontributor
     *
     * @return Dlinkcontribute
     */
    public function setIdcontributor($idcontributor )
    {		
		$this->idcontributor = $idcontributor;
		
        return $this;
    }
	
	

    /**
     * Get idcontributor
     *
     * @return \AppBundle\Entity\Dcontributor
     */
    public function getIdcontributor()
    {
        return $this->idcontributor;
    }
	
	//ftheeten
	public function getLinkSignature()
	{
		//order is important when rattaching
		return str_pad($this->contributororder,3,0,STR_PAD_LEFT)."_".$this->idcontributor."_".$this->idcontribution."_".$this->contributorrole;
	}
}
