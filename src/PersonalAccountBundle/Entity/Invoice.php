<?php
/**
 * Created by PhpStorm.
 * User: Potap
 * Date: 27.06.2017
 * Time: 12:10
 */

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="invoice", uniqueConstraints={@UniqueConstraint(name="ivoice_unique", columns={"teacher_lesson","to_date"})})
 */

class Invoice
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
     * @ORM\Column(type="date", nullable = true)
     */
    protected  $from_date;

    /**
     * @ORM\Column(type="date")
     */
    protected  $to_date;

    /**
     * @ORM\Column (type="integer", length=100)
     */
    protected  $total;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $payed;

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
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getFromDate()
    {
        return $this->from_date;
    }

    /**
     * @param mixed $from_date
     */
    public function setFromDate($from_date)
    {
        $this->from_date = $from_date;
    }

    /**
     * @return mixed
     */
    public function getToDate()
    {
        return $this->to_date;
    }

    /**
     * @param mixed $to_date
     */
    public function setToDate($to_date)
    {
        $this->to_date = $to_date;
    }
    /**
     * @return mixed
     */
    public function getPayed()
    {
        return $this->payed;
    }

    /**
     * @param mixed $payed
     */
    public function setPayed($payed)
    {
        $this->payed = $payed;
    }


}