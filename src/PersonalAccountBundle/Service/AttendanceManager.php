<?php
namespace PersonalAccountBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use PersonalAccountBundle\Entity\Attendance;
use PersonalAccountBundle\Entity\Journal;
use PersonalAccountBundle\Entity\Presence;
use Doctrine\DBAL\DBALException;

class AttendanceManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    protected function get_schedule_per_day($somedate)
    {
        $day = date('w', strtotime($somedate));
        $qb = $this->em->createQueryBuilder();
        $qb->select('s')
            ->from('PersonalAccountBundle:ScheduleEvent', 's')
            ->where($qb->expr()->like('s.dow', $qb->expr()->literal('%' . $day . '%'))
                );

        $result = $qb->getQuery()->getResult();
        return $result;
    }
    protected function fill_journal($date){
        $schedule = $this->get_schedule_per_day($date);
        $values = new ArrayCollection();
        if ($schedule) {
            foreach ($schedule as $value) {
                $group = $this->em->getRepository('PersonalAccountBundle:Group')->find($value->getGroupId());
                $journal = new Journal;
                $journal->setGroup($group);
                $journal->setEdited(false);
                $journal->setDate(new \DateTime($date));
                $journal->setTeacherLesson($value->getTeacherLesson());
                $journal->setStartTime($value->getStartTime());
                $journal->setEndTime($value->getEndTime());
                $journal->setHomework('');
                $values->add($journal);
            }
        }
        return $values;
    }

    protected function fill_presence($arr)
    {
        $presences = new ArrayCollection();
        foreach($arr as $value){
            $group = $this->em->getRepository('PersonalAccountBundle:Group')->find($value->getGroup());
            $students = $group->getStudents();
            foreach ($students as $student) {
                $presence = new Presence;
                $j = $this->em->getRepository('PersonalAccountBundle:Journal')->find($value->getId());
                $presence->setStudent($student);
                $presence->setJournalId($j);
                $presence->setPresence(false);
                $presences->add($presence);
            }
        }
        return $presences;
    }
    protected function add($data){
        foreach ($data as $d) {
            $this->em->persist($d);
        }
        $this->em->flush();
        $this->em->clear();
    }

    public function create($date)
    {
        $journal = $this->fill_journal($date);
        if ($journal->count() > 0) {
            $this->em->getConnection()->beginTransaction();
            $this->add($journal);
            $presence=$this->fill_presence($journal);
            $this->add($presence);
            $this->em->getConnection()->commit();
        }
    }
}