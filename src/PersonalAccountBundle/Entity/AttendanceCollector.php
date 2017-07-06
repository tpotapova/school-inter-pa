<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;


class AttendanceCollector
{
    protected $attendance_collection;

    public function __construct()
    {
        $this->attendance_collection = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getAttendanceCollection()
    {
        return $this->attendance_collection;
    }

    /**
     * @param ArrayCollection $attendance_collection
     */
    public function setAttendanceCollection($attendance_collection)
    {
        $this->attendance_collection = $attendance_collection;
    }


}