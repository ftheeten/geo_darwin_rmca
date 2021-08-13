<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dlocpolygon
 *
 * @ORM\Table(name="dlocpolygon", uniqueConstraints={@ORM\UniqueConstraint(name="dlocpolygon_unique", columns={"idcollection", "idpt", "polyarea", "polyname"})}, indexes={@ORM\Index(name="IDX_C727A7E931E4780850E3C8BA", columns={"idcollection", "idpt"}), @ORM\Index(name="IDX_C727A7E9C9A8DD1E", columns={"polyarea"})})
 * @ORM\Entity
 */
class Dlocpolygon
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dlocpolygon_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="polyname", type="string", precision=0, scale=0, nullable=false, unique=false)
     */
    private $polyname;

    /**
     * @var string
     *
     * @ORM\Column(name="polydescription", type="text", precision=0, scale=0, nullable=true, unique=false)
     */
    private $polydescription;

    /**
     * @var integer
     *
     * @ORM\Column(name="idpoly", type="integer", precision=0, scale=0, nullable=true, unique=false)
     */
    private $idpoly;

    /**
     * @var \AppBundle\Entity\Dloccenter
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dloccenter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idcollection", referencedColumnName="idcollection", nullable=true),
     *   @ORM\JoinColumn(name="idpt", referencedColumnName="idpt", nullable=true)
     * })
     */
    private $idcollection;

    /**
     * @var \AppBundle\Entity\Larea
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Larea")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="polyarea", referencedColumnName="polyarea", nullable=true)
     * })
     */
    private $polyarea;


}

