<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity
 * @ORM\Table(name="`group`", uniqueConstraints={@UniqueConstraint(name="unique_group_name", columns={"name","active"})})
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
     * @ORM\Column(type="string", length = 100)
     */
    protected $name;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $active;

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

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }
}