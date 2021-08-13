<?php
 // src/AppBundle/Entity/Duser.php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
/**
 * @ORM\Entity
 * @ORM\Table(name="`duser`")
 */
class Duser extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;
	
    /**
     * @ORM\Column(type="string")
     */
    private $firstName;
	
	 /**
     * @ORM\Column(type="string")
     */
    private $lastName;
	
	
    public function getId()
    {
        return $this->id;
    }
	public function setId($id)
    {
        $this->id = $id;
    }
	

	
    public function getFirstName()
    {
        return $this->firstName;
    }
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }
	
	public function getLastName()
    {
        return $this->lastName;
    }
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
}
        


        