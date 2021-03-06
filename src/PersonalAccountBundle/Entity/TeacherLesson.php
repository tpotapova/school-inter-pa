<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="teacher_lesson", uniqueConstraints={@UniqueConstraint(name="teacher_lesson_unique", columns={"teacher","lesson","rate","title","active"})})
 */
class TeacherLesson
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Teacher")
     * @ORM\JoinColumn(name="teacher", referencedColumnName="id")
     */
    protected $teacher;

    /**
     * @ORM\ManyToOne(targetEntity="Lesson")
     * @ORM\JoinColumn(name="lesson", referencedColumnName="id")
     * @Assert\Type(type="PersonalAccountBundle\Entity\Lesson")
     * @Assert\Valid()
     */
    protected $lesson;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $title;

    /**
     * @ORM\Column(type="integer", length=100)
     */
    protected $rate;

    /**
     * @ORM\Column(type="integer", length=100)
     */
    protected $comission;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $active;


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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param mixed $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * @return mixed
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * @param mixed $teacher
     */
    public function setTeacher($teacher)
    {
        $this->teacher = $teacher;
    }

    /**
     * @return mixed
     */
    public function getLesson()
    {
        return $this->lesson;
    }

    /**
     * @param mixed $lesson
     */
    public function setLesson($lesson)
    {
        $this->lesson = $lesson;
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
    /**
     * @return mixed
     */
    public function getComission()
    {
        return $this->comission;
    }

    /**
     * @param mixed $comission
     */
    public function setComission($comission)
    {
        $this->comission = $comission;
    }
}