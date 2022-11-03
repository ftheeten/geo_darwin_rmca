<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ddocfilm
 *
 * @ORM\Table(name="ddocfilm", uniqueConstraints={@ORM\UniqueConstraint(name="ddocfilm_unique", columns={"idcollection", "iddoc", "film"})}, indexes={@ORM\Index(name="IDX_CA4AC8F231E478089F44A603", columns={"idcollection", "iddoc"})})
 * @ORM\Entity
 */
class Ddocfilm extends GeodarwinDocForeignKey
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ddocfilm_pk_seq", allocationSize=1, initialValue=1)
     */
    protected $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="film", type="string", nullable=false)
     */
    private $film;


    /**
     * Set film
     *
     * @param string $film
     *
     * @return Ddocfilm
     */
    public function setFilm($film)
    {
        $this->film = $film;

        return $this;
    }

    /**
     * Get film
     *
     * @return string
     */
    public function getFilm()
    {
        return $this->film;
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
	
	public function setPk($pk)
    {
        $this->pk = $pk;

        return $this;
    }	
}
