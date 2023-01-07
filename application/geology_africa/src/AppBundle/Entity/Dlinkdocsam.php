<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlinkdocsam
 *
 * @ORM\Table(name="dlinkdocsam", uniqueConstraints={@ORM\UniqueConstraint(name="dlinkdocsam_unique", columns={"iddoc", "idcollectiondoc", "idsample", "idcollectionsample"})}, indexes={@ORM\Index(name="IDX_448749F6C8D379729F44A603", columns={"idcollectiondoc", "iddoc"}), @ORM\Index(name="IDX_448749F6A84E066895B6DB6F", columns={"idcollectionsample", "idsample"})})
 * @ORM\Entity
 */
class Dlinkdocsam
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlinkdocsam_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="idcollectiondoc", type="string", nullable=false)
     */
    private $idcollectiondoc;

    /**
     * @var string
     *
     * @ORM\Column(name="idcollectionsample", type="string", nullable=false)
     */
    private $idcollectionsample;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="iddoc", type="integer", nullable=false)
     */
	private $iddoc;
	
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
     * Set idcollectiondoc
     *
     * @param string
     *
     * @return Dlinkdocsam
     */
    public function setIdcollectiondoc($idcollectiondoc)
    {
        $this->idcollectiondoc = $idcollectiondoc;

        return $this;
    }

    /**
     * Get idcollectiondoc
     *
     * @return string
     */
    public function getIdcollectiondoc()
    {
        return $this->idcollectiondoc;
    }

   
   /**
     * Set setIdcollectionsample
     *
     * @param string $idcollection
     *
     * @return Dlinkdocsam
     */
    public function setIdcollectionsample($idcollection)
    {
        $this->setIdcollectionsample = $idcollection;

        return $this;
    }

    /**
     * Set idcollectionsampleobj
     *
     * @param \AppBundle\Entity\Dsample $idcollection
     *
     * @return Dlinkdocsam
     */
    public function setIdcollectionsampleobj(\AppBundle\Entity\Dsample $sample = null)
    {
        if($sample !==null)
		{
			 $this->idcollectionsample = $sample->getIdCollection();
			 $this->idsample = $sample->getIdSample();
		}

        return $this;
    }

    /**
     * Get idcollection
     *
     * @return string
     */
    public function getIdcollectionsample()
    {
        return $this->idcollectionsample;
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
     * Set iddoc
     *
     * @param integer $iddoc
     *
     * @return Dlinkdocsam
     */
    public function setIddoc( $iddoc = null)
    {
        $this->iddoc = $iddoc;

        return $this;
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
	
	public function setPk($pk)
	{
		$this->pk=$pk;
	}
	
	//document
	public $document;
	
	public function getDocument()
	{
		return $this->document;
	}
	
	public function setDocument_db($em)
	{
		$tmp_doc=$em->getRepository(Ddocument::class)
						 ->findOneBy(array('iddoc' => $this->getIddoc(),
									"idcollection"=> $this->getIdcollectiondoc()));
		if($tmp_doc !==null)
		{
			$tmp_doc->setTitle_db($em);
		}
		$this->document=$tmp_doc;
	}
}
