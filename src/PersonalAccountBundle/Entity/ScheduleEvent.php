<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="schedule_event")
 * @UniqueEntity(fields={"teacher_lesson", "start_time","start_date"}, message="Событие уже добавлено")
 */
class ScheduleEvent
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
     * @ORM\Column(type="integer")
     */
    protected  $group_id;

    /**
     * @ORM\Column(type="date")
     */
    protected  $start_date;

    /**
     * @ORM\Column(type="time")
     */
    protected $start_time;

    /**
     * @ORM\Column(type="time")
     */
    protected $end_time;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected  $dow;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param mixed $start_date
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    /**
     * @return mixed
     */
    public function getDow()
    {
        return $this->dow;
    }

    /**
     * @param mixed $dow
     */
    public function setDow($dow)
    {
        $this->dow = $dow;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @param mixed $group_id
     */
    public function setGroupId($group_id)
    {
        $this->group_id = $group_id;
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



}