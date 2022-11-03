<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\GeodarwinDocForeignKey;

/**
 * Ddocaerphoto
 *
 * @ORM\Table(name="ddocaerphoto", uniqueConstraints={@ORM\UniqueConstraint(name="DDocAerPhoto_PID_key", columns={"pid"}), @ORM\UniqueConstraint(name="ddocaerphoto_unique", columns={"idcollection", "iddoc"}), @ORM\UniqueConstraint(name="ddocaerphoto_pid_unique", columns={"pid"})}, indexes={@ORM\Index(name="IDX_A43E8A964DFB1B2F", columns={"fid"})})
 * @ORM\Entity
 */
class Ddocaerphoto extends GeodarwinDocForeignKey
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ddocaerphoto_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="pid", type="string", nullable=true)
     */
    private $pid;

    /**
     * @var \AppBundle\Entity\Docplanvol
     *
     * @ORM\Column(name="fid", type="integer", nullable=false)
     */
    private $fid;

   



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
     * Set pid
     *
     * @param string $pid
     *
     * @return Ddocaerphoto
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Get pid
     *
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set fid
     *
     * @param string $idcontribution
     *
     * @return Ddocaerphoto
     */
    public function setFid($fid = null)
    {
        $this->fid = $fid;

        return $this;
    }

    /**
     * Get fid
     *
     * @return \AppBundle\Entity\Docplanvol
     */
    public function getFid()
    {
        return $this->fid;
    }

   
	
	/**
     * Set pk
     *
     * @param integer $pk
     *
     * @return Ddocarchive
     */
    public function setpk($pk)
    {
        $this->pk = $pk;

        return $this;
    }
	
	public function setplanvol_db($em)
	{
		$tmp_cont=$em->getRepository(Docplanvol::class)
						 ->findOneBy(array('fid' => $this->getFid()
									));
		
		$this->docplanvol=$tmp_cont;
	}
}
