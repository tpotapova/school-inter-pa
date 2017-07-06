<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="journal", uniqueConstraints={@UniqueConstraint(name="journal_unique", columns={"group_id","teacher_lesson", "start_time","date"})})
 */

class Journal
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TeacherLesson")
     * @ORM\JoinColumn(name="teacher_lesson", referencedColumnName="id")
     */
    protected  $teacher_lesson;

    /**
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $group;
    /**
     * @ORM\Column(type="date")
     */
    protected  $date;

    /**
     * @ORM\Column(type="time")
     */
    protected $start_time;

    /**
     * @ORM\Column(type="time")
     */
    protected $end_time;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $edited;

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
    public function getTeacherLesson()
    {
        return $this->teacher_lesson;
    }

    /**
     * @param mixed $teacher_lesson
     */
    public function setTeacherLesson($teacher_lesson)
    {
        $this->teacher_lesson = $teacher_lesson;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param mixed $start_time
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @param mixed $end_time
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
    }

    /**
     * @return mixed
     */
    public function getEdited()
    {
        return $this->edited;
    }

    /**
     * @param mixed $edited
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;
    }


}