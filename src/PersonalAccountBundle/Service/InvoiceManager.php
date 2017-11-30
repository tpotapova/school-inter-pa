<?php

namespace PersonalAccountBundle\Service;

use Doctrine\ORM\EntityManager;
use PersonalAccountBundle\Entity\Invoice;
use PersonalAccountBundle\Entity\StudentInvoice;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Validator\Constraints\DateTime;


class InvoiceManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    public function get_lesson_invoice($lesson_id,$startDate, $endDate)
    {
        $start  =  date('Y-m-d', strtotime($startDate));
        $current = date('Y-m-d', strtotime($endDate));
        $qb = $this->em->createQueryBuilder();
        $qb->select('j')
            ->from('PersonalAccountBundle:Journal', 'j')
            ->where('j.teacher_lesson = :lesson_id')
            ->andwhere('j.date BETWEEN :start AND :current')
            ->setParameter('lesson_id', $lesson_id)
            ->setParameter('start', $start)
            ->setParameter('current',$current);

        $result = $qb->getQuery()->getResult();
        return $result;
    }
    public function get_lesson_last_invoice($lesson_id){
        $qb = $this->em->createQueryBuilder('i');
        $qb->select('MAX(i.to_date) AS max_date')
            ->from('PersonalAccountBundle:Invoice', 'i')
            ->where('i.teacher_lesson = :lesson_id')
            ->setParameter('lesson_id', $lesson_id);
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result['max_date'];
    }
    public function get_lesson_by_id($lesson_id){
        $result = $this->em->getRepository('PersonalAccountBundle\Entity\TeacherLesson')->findOneBy(array('id' => $lesson_id));
        return $result;
    }

    public function get_lesson_invoice_by_student($presences){
        $qb = $this->em->createQueryBuilder();
        $qb ->select('s.id s_id, s.name s_name, s.surname s_surname, j.date as j_date')
            ->from('PersonalAccountBundle:Student', 's')
            ->leftJoin('PersonalAccountBundle:Presence', 'p','with','s.id = p.student')
            ->join('PersonalAccountBundle:Journal', 'j', 'with', 'p.journal_id = j.id')
            ->where('p.id in (:presences)')
            ->setParameter('presences', $presences);
        $result = $qb->getQuery()->getResult();
        return $result;
    }
    public function format_lesson_invoice_by_student($result){
        $studentInvoices = array();
        foreach($result as $val) {
            $studentInvoices[$val["s_surname"].' '.$val["s_name"]][] = $val["j_date"]->format('d-m-Y');
        }
        ksort($studentInvoices);
        return $studentInvoices;
    }

    public function lesson_presences($invoice_ids){
        $qb = $this->em->createQueryBuilder('p');
        $qb->select('p.id')
            ->from('PersonalAccountBundle:Presence', 'p')
            ->where('p.journal_id in (:invoice_ids)')
            ->andwhere('p.presence = 1')
            ->setParameter('invoice_ids', $invoice_ids);
        $result = $qb->getQuery()->getResult();
        $presences = array_column($result, 'id');
        return $presences;
    }

    public function fill_student_invoices($student_presences,$invoice_id, $lesson_rate){
        $invoice_id = $this->em->getRepository('PersonalAccountBundle:Invoice')->find($invoice_id);
        $student_presences = array_count_values(array_column($student_presences, 's_id'));
        foreach ($student_presences as $key => $value){
            $student_invoice = new StudentInvoice();
            $student = $this->em->getRepository('PersonalAccountBundle:Student')->find($key);
            $student_invoice->setInvoiceId($invoice_id);
            $student_invoice->setPayed(false);
            $student_invoice->setTotal($value * $lesson_rate);
            $student_invoice->setStudentId($student);
            $this->em->persist($student_invoice);
        }
        $this->em->flush();
        $this->em->clear();
    }

    public function show_all_invoices(){
        $qb = $this->em->createQueryBuilder('i');
        $qb->select('i,l.title as l_title,l.id as l_id')
            ->from('PersonalAccountBundle:Invoice','i')
            ->join('PersonalAccountBundle:TeacherLesson','l','with','i.teacher_lesson = l.id');
        $result = $qb->getQuery()->getScalarResult();
        return $result;
    }
    public function show_my_invoices(){
        $qb = $this->em->createQueryBuilder('mi');
        $qb->select('mi')
            ->addSelect('SUM(mi.total) as sum_total')
            ->addSelect('SUM(mi.lesson_comission) as comission_total')
            ->from('PersonalAccountBundle:Invoice','mi')
            ->groupBy('mi.from_date')
            ->addGroupBy('mi.to_date')
            ->orderBy('mi.to_date', 'DESC');
        $result = $qb->getQuery()->getScalarResult();
        return $result;
    }
    public function show_my_invoices_by_dates($startDate,$endDate){
        $qb = $this->em->createQueryBuilder('i');
        $qb->select('i,l.title as l_title,l.id as l_id')
            ->from('PersonalAccountBundle:Invoice','i')
            ->join('PersonalAccountBundle:TeacherLesson','l','with','i.teacher_lesson = l.id')
            ->where('i.to_date = :endDate')
            ->setParameter('endDate', $endDate);
        if ($startDate) {
            $qb->andwhere('i.from_date = :startDate')
                ->setParameter('startDate',$startDate);
        }
        $result = $qb->getQuery()->getScalarResult();
        return $result;
    }

    public function show_invoice_details($id){
        $qb = $this->em->createQueryBuilder('s_i');
        $qb->select('s_i, s.name s_name, s.surname s_surname')
            ->from('PersonalAccountBundle:StudentInvoice','s_i')
            ->join ('PersonalAccountBundle:Student','s', 'with', 's_i.student_id = s.id')
            ->where ('s_i.invoice_id = :id')
            ->setParameter('id', $id);
        $result = $qb->getQuery()->getScalarResult();
        return $result;
    }
    public function show_all_student_invoices($student_id) {
        $qb = $this->em->createQueryBuilder();
        $qb->select('i.from_date i_from, i.to_date i_to,l.title l_title, s.total s_total, s.payed s_payed')
            ->from('PersonalAccountBundle:Invoice','i')
            ->join ('PersonalAccountBundle:StudentInvoice','s', 'with','i.id = s.invoice_id')
            ->join('PersonalAccountBundle:TeacherLesson','l','with','i.teacher_lesson = l.id')
            ->where ('s.student_id = :student_id')
            ->setParameter('student_id',$student_id);
        $result = $qb->getQuery()->getScalarResult();
        return $result;
    }

    public function show_all_teacher_invoices($teacher_id) {
        $qb = $this->em->createQueryBuilder();
        $qb->select('i.from_date i_from, i.to_date i_to,i.total i_total, i.payed i_payed, i.lesson_comission i_lesson_comission, l.title l_title')
            ->from('PersonalAccountBundle:Invoice','i')
            ->join('PersonalAccountBundle:TeacherLesson','l','with','i.teacher_lesson = l.id')
            ->where ('l.teacher = :teacher_id')
            ->setParameter('teacher_id',$teacher_id);
        $result = $qb->getQuery()->getScalarResult();
        return $result;
    }

    public function show_grouped_student_invoices($startDate, $endDate){
        $sql ="select sum(teacher_lesson_total) as total, student_id, from_date, to_date, student_full_name,
            group_concat(distinct lesson_title separator ', ') as details
            from (
            select *, concat(title, ' (', cast(teacher_lesson_total / rate as INTEGER), ')') as lesson_title from (
            SELECT sum(si.total) as teacher_lesson_total, student_id, from_date, to_date, tl.title, tl.rate,
            concat(s.name, ' ', s.surname) as student_full_name
            from student_invoice si
            join invoice i on si.invoice_id = i.id
            join teacher_lesson tl on i.teacher_lesson = tl.id
            join student s on si.student_id = s.id
            group by si.student_id, i.from_date, i.to_date, i.teacher_lesson, tl.rate, s.name, s.surname
            ) t
            ) t2
            where to_date = :endDate %s
            group by student_id, from_date, to_date, student_full_name";
        $conn = $this->em->getConnection();
        if (!$startDate) {
            $andWhere = 'and from_date is null';
        } else {
            $andWhere = "and from_date = " . $conn->quote($startDate) . " ";
        }
        $sql = sprintf($sql, $andWhere);
        $result = $conn->prepare($sql);
        $result->bindParam(':endDate', $endDate);
        $result->execute();
        return $result->fetchAll();
    }

    /*
    public function get_group_unpaid_invoices($group_id){
        $qb = $this->em->createQueryBuilder();
        $qb->select('i.id i_id,j.id j_id, i.payed payed, i.to_date to_date, MAX(j.date) max_date')
            ->from('PersonalAccountBundle:Journal','j')
            ->innerJoin('PersonalAccountBundle:Invoice', 'i', 'with','j.teacher_lesson = i.teacher_lesson')
            ->having('to_date <= MAX(j.date)')
            ->where('j.group = :group_id')
            ->andWhere('i.payed = false')
            ->groupBy('j.teacher_lesson')
            ->setParameter('group_id', $group_id);
        $result = $qb->getQuery()->getResult();
        return $result;
    }*/

}