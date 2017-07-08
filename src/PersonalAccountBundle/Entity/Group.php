<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="`group`")
 * @UniqueEntity(fields="name", message="Это название уже используется, пожалуйста, выберите другое")
 */
class Group
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length = 100,unique=true)
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Student", mappedBy="groups", cascade={"persist"})
     */

    protected $students;

    public function __construct() {
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function addStudent(Student $student)
    {
        $this->students[] = $student;
    }
    public function removeStudent(Student $student)
    {
        $this->students->removeElement($student);
    }
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
    public function getStudents()
    {
        return $this->students;
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

}