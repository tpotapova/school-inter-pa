<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity
* @ORM\Table(name="student")
*/
class Student
{
    /**
    * @ORM\Column(type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\Type(type="PersonalAccountBundle\Entity\User")
     * @Assert\Valid()
     */
    protected  $user_id;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $name;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $surname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $skype;

    /**
    * @ORM\Column(type="date")
    */
    protected $birthday;

    /**
    * @ORM\Column(type="string", length=200, nullable=true)
    */
    protected $parent1_name;

    /**
    * @ORM\Column(type="string",length=200, nullable=true)
    */
    protected $parent2_name;

    /**
    * @ORM\Column(type="string", length=100)
    */
    protected $city;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comments;

    /**
    * @ORM\Column(type="integer", nullable=true)
    */
    protected $grade;

    /**
     * Many Users have Many Groups.
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="students", cascade={"persist"})
     * @ORM\JoinTable(name="students_groups")
     */
    protected $groups;

    public function __construct() {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param mixed $group
     */
    public function addGroup(Group $group)
    {
        $group->addStudent($this);// synchronously updating inverse side
        $this->groups[] = $group;
    }
    public function removeGroup(Group $group)
    {
        if ($this->groups->contains($group)) {
            $group->removeStudent($this);
            return $this->groups->removeElement($group);
        }
    }

    /**
     * @ORM\Column(type="string", length=400, nullable=true)
     */
    protected $vk_url;

    /**
     * @ORM\Column(type="string", length=400, nullable=true)
     */
    protected $fb_url;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param mixed $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param mixed $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return mixed
     */
    public function getParent1Name()
    {
        return $this->parent1_name;
    }

    /**
     * @param mixed $parent1_id
     */
    public function setParent1Name($parent1_name)
    {
        $this->parent1_name = $parent1_name;
    }

    /**
     * @return mixed
     */
    public function getParent2Name()
    {
        return $this->parent2_name;
    }

    /**
     * @param mixed $parent2_id
     */
    public function setParent2Name($parent2_name)
    {
        $this->parent2_name = $parent2_name;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param mixed $grade
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;
    }

    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return mixed
     */
    public function getVkUrl()
    {
        return $this->vk_url;
    }

    /**
     * @param mixed $vk_url
     */
    public function setVkUrl($vk_url)
    {
        $this->vk_url = $vk_url;
    }

    /**
     * @return mixed
     */
    public function getFbUrl()
    {
        return $this->fb_url;
    }

    /**
     * @param mixed $fb_url
     */
    public function setFbUrl($fb_url)
    {
        $this->fb_url = $fb_url;
    }

    /**
     * @return mixed
     */
    public function getSkype()
    {
        return $this->skype;
    }

    /**
     * @param mixed $skype
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    public function __toString()
    {
        return $this->name.' '.$this->surname;
    }

}
