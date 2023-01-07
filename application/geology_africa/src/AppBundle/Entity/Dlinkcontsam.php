<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlinkcontsam
 *
 * @ORM\Table(name="dlinkcontsam", uniqueConstraints={@ORM\UniqueConstraint(name="dlinkcontsam_unique", columns={"idcontribution", "idcollection", "id"})}, indexes={@ORM\Index(name="IDX_A66CD89C31E47808BF396750", columns={"idcollection", "id"}), @ORM\Index(name="IDX_A66CD89CAC9A611C", columns={"idcontribution"})})
 * @ORM\Entity
 */
class Dlinkcontsam
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlinkcontsam_pk_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="idcontribution", type="integer", nullable=false)
     */
    private $idcontribution;


	

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
     * @return Dlinkcontsam
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
     * @return Dlinkcontsam
     */
    public function setIdcollectionobj(\AppBundle\Entity\Dsample $sample = null)
    {
        if($sample !==null)
		{
			 $this->idcollection = $sample->getIdCollection();
			 $this->id = $sample->getIdSample();
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
     * Set id
     *
     * @param integer
     *
     * @return Dsamgranulo
     */
    public function setId( $idsample)
    {
        $this->id = $idsample;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
	
	
	public function setIdsample($id)
	{
		$this->setId($id);
	}
	
	public function getIdsample()
	{
		return $this->getId();
	}
		
	
    /**
     * Set idcontribution
     *
     * @param integer $idcontribution
     *
     * @return Dlinkcontloc
     */
    public function setIdcontribution( $idcontribution )
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
	
	public function setPk($pk)
	{
		$this->pk=$pk;
	}
	
	public $dcontribution=[];
	
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
