<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dkeyword
 *
 * @ORM\Table(name="dkeyword", uniqueConstraints={@ORM\UniqueConstraint(name="dkeyword_unique", columns={"id", "idcollection", "keyword"})}, indexes={@ORM\Index(name="IDX_F67CDF6D31E47808BF396750", columns={"idcollection", "id"})})
 * @ORM\Entity
 */
class Dkeyword
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dkeyword_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="keyword", type="string", nullable=false)
     */
    private $keyword;

    /**
     * @var integer
     *
     * @ORM\Column(name="keywordlevel", type="smallint", nullable=false)
     */
    private $keywordlevel;

   

    /**
     * @var integer
     *
     * @ORM\Column(name="idcollection", type="integer", nullable=false)
     */
    private $idcollection;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;


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
     * Set keyword
     *
     * @param string $keyword
     *
     * @return Dkeyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set keywordlevel
     *
     * @param integer $keywordlevel
     *
     * @return Dkeyword
     */
    public function setKeywordlevel($keywordlevel)
    {
        $this->keywordlevel = $keywordlevel;

        return $this;
    }

    /**
     * Get keywordlevel
     *
     * @return integer
     */
    public function getKeywordlevel()
    {
        return $this->keywordlevel;
    }

    /**
     * Set relationidcollection
     *
     * @param \AppBundle\Entity\Ddocument $idcollection
     *
     * @return Dkeyword
     */
    public function setrelationidcollection(\AppBundle\Entity\Ddocument $doc = null)
    {
        $this->id=$doc->getIddoc();
		$this->idcollection=$doc->getIdcollection();

        return $this;
    }
	
	/**
     * Set idcollection
     *
     * @param integer $keyword
     *
     * @return Dkeyword
     */
    public function setIdcollection($idcollection)
    {
        $this->idcollection = $idcollection;

        return $this;
    }
	
	/**
     * Set idcollectionobj
     *
     * @param \AppBundle\Entity\Ddocument $doc
     *
     * @return Dkeyword
     */
    public function setIdcollectionobj(\AppBundle\Entity\Ddocument $doc = null)
    {
        if( $doc !==null)
		{
			$this->id=$doc->getIddoc();
			$this->idcollection=$doc->getIdcollection();
		}
        return $this;
    }
    
    /**
     * Set idcollectionval
     *
     * @param integer idcollectionval
     *
     * @return Dkeyword
     */
    public function setIdcollectionVal($idcollectionval)
    {
        $this->idcollection = $idcollectionval;

        return $this;
    }
    
    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Dkeyword
     */
    public function setId($id)
    {
        $this->id = $id;
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
	//FTHEETEN HAD to remove explicit foreign key mapping to persist
	
	public function setPk($pk)
    {
        $this->pk = $pk;

        return $this;
    }	
}
