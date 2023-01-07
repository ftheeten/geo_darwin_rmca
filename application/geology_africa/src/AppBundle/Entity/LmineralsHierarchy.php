<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LmineralsHierarchy
 *
 * @ORM\Table(name="lminerals_hierarchy", uniqueConstraints={@ORM\UniqueConstraint(name="lminerals_hierarchy", columns={"pk"})})
 * @ORM\Entity
 */
class LmineralsHierarchy
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="lminerals_hierarchy_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     */
    private $creationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", nullable=true)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", nullable=true)
     */
    private $comment;



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
     * Set name
     *
     * @param string $name
     *
     * @return LmineralsHierarchy
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return LmineralsHierarchy
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return LmineralsHierarchy
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return LmineralsHierarchy
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
	
	public function __toString() 
	{
		return $this->name;
	}
}

