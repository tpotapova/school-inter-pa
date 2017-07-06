<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="group_schedule")
 */
class GroupSchedule
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected  $group_id;

    /**
     * @ORM\Column(type="array")
     */
    protected  $lessons;

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
    public function getLessons()
    {
        return $this->lessons;
    }

    /**
     * @param mixed $lessons
     */
    public function setLessons($lessons)
    {
        $this->lessons = $lessons;
    }


}