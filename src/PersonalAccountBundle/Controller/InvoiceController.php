<?php

namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Entity\StudentInvoiceCollector;
use PersonalAccountBundle\Form\StudentInvoiceCollectorType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PersonalAccountBundle\Entity\TeacherLesson;
use PersonalAccountBundle\Entity\Invoice;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use PersonalAccountBundle\Form\InvoiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PersonalAccountBundle\Service\InvoiceManager;
use Symfony\Component\Validator\Constraints\DateTime;

class InvoiceController extends Controller
{
    /**
     * @Route("/invoice_params", name="invoice_params")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function chooseInvoiceParamsAction(Request $request)
    {
        date_default_timezone_set( 'Europe/Moscow' );
        $form = $this->createFormBuilder()
            ->add('teacher_lesson', EntityType::class,  [
                'label_format' => '%name%',
                'required' => true,
                'class' => TeacherLesson::class,
                'choice_label' => 'title',
                'query_builder' => function ($repository) {
                    return $repository
                        ->createQueryBuilder('e')
                        ->where('e.active = true');
                },
            ])
            ->add('startDate', DateType::class,  ['widget' => 'single_text','label' => 'Начало рассчетного периода','required'=>true])
            ->add('endDate', DateType::class,  ['widget' => 'single_text','label' => 'Конец рассчетного периода','required'=>true])
            ->add('make', SubmitType::class,  ['attr' => ['class' => 'btn-primary']])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $redirect_url = $this->get('router')->generate('lesson_invoice', ['lesson_id'=>$data['teacher_lesson']->getId(),
                'startDate' => $data['startDate']->format('d-m-Y'), 'endDate' => $data['endDate']->format('d-m-Y')]);
            return $this->redirect($redirect_url);
        }

        return $this->render('PersonalAccountBundle:Admin:invoiceParams.html.twig', [
            'form'=>$form->createView(),
           ]);
    }
    /**
     * @Route("/grouped_student_invoices/{startDate}/{endDate}", name="grouped_student_invoices")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showGroupedStudentInvoices($startDate, $endDate, Request $request)
    {
        $manager = $this->container->get('app.invoice_manager');
        $result = $manager->show_grouped_student_invoices($startDate,$endDate);
        return $this->render('PersonalAccountBundle:Admin:studentInvoices.html.twig',['result' => $result]);
    }
    /**
     * @Route("/invoice_params_2", name="invoice_params_2")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    // TODO: Make multiple choice for lesson invoices
    public function chooseInvoiceParams2Action(Request $request)
    {
        date_default_timezone_set( 'Europe/Moscow' );
        $form = $this->createFormBuilder()
            ->add('teacher_lesson', EntityType::class,  [
                'label_format' => '%name%',
                'required' => true,
                'class' => TeacherLesson::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded'=>true,
                'choice_attr' => function() {
                    return ['checked' => 'checked'];
                },
                'query_builder' => function ($repository) {
                    return $repository
                        ->createQueryBuilder('e')
                        ->where('e.active = true');
                },
            ])
            ->add('startDate', DateType::class,  ['widget' => 'single_text','label' => 'Начало рассчетного периода','required'=>true])
            ->add('endDate', DateType::class,  ['widget' => 'single_text','label' => 'Конец рассчетного периода','required'=>true])
            ->add('make', SubmitType::class,  ['attr' => ['class' => 'btn-primary']])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $redirect_url = $this->get('router')->generate('lesson_invoice', ['lesson_id'=>$data['teacher_lesson']->getId(),
                'startDate' => $data['startDate']->format('d-m-Y'), 'endDate' => $data['endDate']->format('d-m-Y')]);
            return $this->redirect($redirect_url);
        }

        return $this->render('PersonalAccountBundle:Admin:invoiceParams.html.twig', [
            'form'=>$form->createView(),
        ]);
    }

    /**
     * @Route("{lesson_id}/{startDate}/{endDate}/lesson_invoice", name="lesson_invoice")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showInvoiceAction($lesson_id, $startDate, $endDate, Request $request)
    {
        $manager= $this->container->get('app.invoice_manager');
        $result = $manager->get_lesson_invoice($lesson_id,$startDate, $endDate);
        $unedited = [];
        $edited = [];
        foreach($result as $r){

            if ($r->getEdited()){
                array_push($edited, $r->getId());
            }
            else {
                array_push($unedited, [$r->getDate()->format('d-m-Y'), '(группа', $r->getGroup()->getName().')']);
            }
        };
        $last_invoice = $manager->get_lesson_last_invoice($lesson_id);
        $last_invoice_date = $last_invoice ? date("d-m-Y", strtotime($last_invoice)): $last_invoice;
        $lesson_rate = $manager->get_lesson_by_id($lesson_id)->getRate();
        $lesson_title = $manager->get_lesson_by_id($lesson_id)->getTitle();
        $lesson_presences = $manager->lesson_presences($edited);
        $student_presences = $manager->get_lesson_invoice_by_student($lesson_presences);
        $formatted_student_presences = $manager->format_lesson_invoice_by_student($student_presences);
        $lesson_total = $lesson_rate * (count($lesson_presences));
        //Add lesson comission
        $comission = $manager->get_lesson_by_id($lesson_id)->getComission();
        $lesson_comission = $comission * (count($lesson_presences));
        $teacher_payment = $lesson_total - $lesson_comission;
        $invoice = new Invoice();
        $em = $this->getDoctrine()->getManager();
        $redirect_url = $this->get('router')->generate('myinvoices');
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);
        if ($form->get('save')->isClicked()) {
            $invoice->setFromDate(new \DateTime(date("Y-m-d", strtotime($startDate))));
            $invoice->setToDate(new \DateTime(date("Y-m-d", strtotime($endDate))));
            $invoice->setTeacherLesson($em->getRepository('PersonalAccountBundle\Entity\TeacherLesson')
                                            ->findOneById($lesson_id));
            $invoice->setTotal($lesson_total);
            $invoice->setLessonComission($lesson_comission);
            $invoice->setPayed(false);
            if ($form->isValid()) {
                $em->persist($invoice);
                $em->flush();
            }
            $manager->fill_student_invoices($student_presences,$invoice->getId(),$lesson_rate);
            return $this->redirect($redirect_url);
        }
        return $this->render('PersonalAccountBundle:Admin:invoice.html.twig', [
        'lesson_total' => $lesson_total,
        'teacher_payment' => $teacher_payment,
        'lesson_comission' => $lesson_comission,
        'student_presences' => $formatted_student_presences,
        'lesson_rate' => $lesson_rate,
        'unedited' => $unedited,
        'last_invoice_date' => $last_invoice_date,
        'start_invoice_date' => $startDate,
        'current_invoice_date' => $endDate,
        'lesson_title' => $lesson_title,
        'form' => $form->createView(),
    ]);
    }
    /**
     * @Route("/invoices", name="invoices")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showAllInvoicesAction(Request $request)
    {
        $manager = $this->container->get('app.invoice_manager');
        $invoices = $manager->show_all_invoices();

        return $this->render('PersonalAccountBundle:Admin:allInvoices.html.twig',['invoices' => $invoices,]);
    }
    /**
     * @Route("/my_invoices", name="myinvoices")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    //TODO: show invoices dates list, make detailed invoices view by date
    public function showMyInvoicesAction(Request $request)
    {

        $manager = $this->container->get('app.invoice_manager');
        $invoices = $manager->show_my_invoices();

        return $this->render('PersonalAccountBundle:Admin:myInvoices.html.twig',['invoices' => $invoices,]);
    }
    /**
     * @Route("/my_invoices/{startDate}/{endDate}", name="myinvoices_by_dates")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showMyInvoicesByDatesAction($startDate, $endDate,Request $request){

        $manager = $this->container->get('app.invoice_manager');
        $invoices = $manager->show_my_invoices_by_dates($startDate, $endDate);

        return $this->render('PersonalAccountBundle:Admin:allInvoices.html.twig',['invoices' => $invoices,
            'startDate'=>$startDate,'endDate'=>$endDate]);

    }

    /**
     * @Route("/invoices/{id}", name="invoice_details")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showInvoiceDetailsAction($id, Request $request)
    {
        $manager = $this->container->get('app.invoice_manager');
        $student_invoices = $manager->show_invoice_details($id);
        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('PersonalAccountBundle\Entity\Invoice')->find($id);
        $invoices = $em->getRepository('PersonalAccountBundle\Entity\StudentInvoice')->findBy(['invoice_id' => $id]);
        $invoice_title = $invoice->getTeacherLesson()->getTitle();
        $l_comission = $invoice->getTeacherLesson()->getComission();
        $l_rate = $invoice->getTeacherLesson()->getRate();
        $from_date = $invoice->getFromDate() ? $invoice->getFromDate()->format('d-m-Y') : '';
        $to_date = $invoice->getToDate()->format('d-m-Y');
        $collector = new StudentInvoiceCollector();
        $collector->setStudentInvoiceCollection($invoices);
        $form1 = $this->createForm(StudentInvoiceCollectorType::class, $collector);
        $form1->handleRequest($request);
        if ($form1->get('save')->isClicked()) {
            if ($form1->isValid()) {
                $payed = 0;
                foreach ($invoices as $student_invoice) {
                    $student_invoice->setInvoiceId($invoice);
                    if ($student_invoice->getPayed()){
                        $payed+=1;
                    }
                    $em->persist($student_invoice);
                }
                $lesson_invoice = $em->getRepository('PersonalAccountBundle\Entity\Invoice')->find($id);
                if (count($invoices) == $payed) {
                    $lesson_invoice->setPayed(true);
                    $em->persist($lesson_invoice);
                }
                else {
                    $lesson_invoice->setPayed(false);
                    $em->persist($lesson_invoice);
                }
                $em->flush();
                return $this->redirect($this->generateUrl($request->get('_route'), ['id'=>$id]));
            }
        }
        $form = $this->createFormBuilder()
                    ->add('delete', SubmitType::class, array('attr' => ['class' => 'btn-danger']))
                    ->getForm();
        $form->handleRequest($request);
        $redirect_url = $this->get('router')->generate('myinvoices');
        if ($form->get('delete')->isClicked()){
            foreach ($invoices as $i) {
                $em->remove($i);
            }
            $em->remove($invoice);
            $em->flush();
            return $this->redirect($redirect_url);
        }
        return $this->render('PersonalAccountBundle:Admin:invoiceDetails.html.twig', [
            'student_invoices'=>$student_invoices,
            'l_comission' => $l_comission,
            'l_rate' => $l_rate,
            'invoice_title' => $invoice_title,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'form' => $form->createView(),
            'form1' => $form1->createView(),
        ]);
    }
    /**
     * @Route("/student_invoices/", name="student_invoices")
     * @Security("has_role('ROLE_USER')")
     */
    public function showStudentInvoicesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $student = $em->getRepository('PersonalAccountBundle:Student')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$student or !($student->getActive())) {
            throw $this->createNotFoundException('Студент не найден');
        }
        $manager = $this->container->get('app.invoice_manager');
        $invoices = $manager->show_all_student_invoices($student->getId());
        return $this->render('PersonalAccountBundle:Student:allInvoices.html.twig', ['invoices' => $invoices,]);
    }
    /**
     * @Route("/teacher_invoices/", name="teacher_invoices")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showTeacherInvoicesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $teacher = $em->getRepository('PersonalAccountBundle:Teacher')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$teacher or !($teacher->getActive())) {
            throw $this->createNotFoundException('Преподаватель не найден');
        }
        $manager = $this->container->get('app.invoice_manager');
        $invoices = $manager->show_all_teacher_invoices($teacher->getId());
        return $this->render('PersonalAccountBundle:Teacher:allInvoices.html.twig', ['invoices' => $invoices,]);
    }
}
