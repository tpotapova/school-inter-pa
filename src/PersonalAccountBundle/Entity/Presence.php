<?php

namespace PersonalAccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="presence", uniqueConstraints={@UniqueConstraint(name="presence_unique", columns={"student","journal_id", })})
 */

class Presence
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Journal")
     * @ORM\JoinColumn(name="journal_id", referencedColumnName="id")
     */
    protected $journal_id;

    /**
     * @ORM\ManyToOne(targetEntity="Student")
     * @ORM\JoinColumn(name="student", referencedColumnName="id")
     */
    protected  $student;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $presence;

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
    public function getJournalId()
    {
        return $this->journal_id;
    }

    /**
     * @param mixed $journal_id
     */
    public function setJournalId($journal_id)
    {
        $this->journal_id = $journal_id;
    }

    /**
     * @return mixed
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @param mixed $student
     */
    public function setStudent($student)
    {
        $this->student = $student;
    }

    /**
     * @return mixed
     */
    public function getPresence()
    {
        return $this->presence;
    }

    /**
     * @param mixed $presence
     */
    public function setPresence($presence)
    {
        $this->presence = $presence;
    }

}