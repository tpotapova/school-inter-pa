<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="weekdays")
 */
class Weekdays
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected  $name;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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