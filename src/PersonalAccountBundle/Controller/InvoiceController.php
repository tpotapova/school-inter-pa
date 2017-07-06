<?php

namespace PersonalAccountBundle\Controller;

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
        $defaultData = array('date' => new \DateTime(date('Y-m-d')));
        $form = $this->createFormBuilder($defaultData)
            ->add('teacher_lesson', EntityType::class,  [
                'label_format' => '%name%',
                'required' => true,
                'class' => TeacherLesson::class,
                'choice_label' => 'title',
            ])
            ->add('date', DateType::class,  ['widget' => 'single_text','label' => 'Выберите дату','required'=>true])
            ->add('make', SubmitType::class,  ['attr' => ['class' => 'btn-primary']])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $redirect_url = $this->get('router')->generate('lesson_invoice', ['lesson_id'=>$data['teacher_lesson']->getId(),
                'date' => $data['date']->format('d-m-Y')]);
            return $this->redirect($redirect_url);
        }

        return $this->render('PersonalAccountBundle:Admin:invoiceParams.html.twig', [
            'form'=>$form->createView(),
           ]);
    }

    /**
     * @Route("{lesson_id}/{date}/lesson_invoice", name="lesson_invoice")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showInvoiceAction($lesson_id, $date, Request $request)
    {
        $manager= $this->container->get('app.invoice_manager');
        $result = $manager->get_lesson_invoice($lesson_id,$date);
        $unedited = [];
        $edited = [];
        foreach($result as $r){

            if ($r->getEdited()){
                array_push($edited, $r->getId());
            }
            else {
                array_push($unedited, [$r->getDate()->format('d-m-Y'),$r->getStartTime()->format('H.i')]);
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
        $invoice = new Invoice();
        $em = $this->getDoctrine()->getManager();
        $redirect_url = $this->get('router')->generate('invoices');
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);
        if ($form->get('save')->isClicked()) {
            $invoice->setFromDate($last_invoice ? new \DateTime(date("Y-m-d", strtotime($last_invoice))) : $last_invoice );
            $invoice->setToDate(new \DateTime(date("Y-m-d", strtotime($date))));
            $invoice->setTeacherLesson($em->getRepository('PersonalAccountBundle\Entity\TeacherLesson')
                                            ->findOneById($lesson_id));
            $invoice->setTotal($lesson_total);
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
        'student_presences' => $formatted_student_presences,
        'lesson_rate' => $lesson_rate,
        'unedited' => $unedited,
        'last_invoice_date' => $last_invoice_date,
        'current_invoice_date' => $date,
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
        $from_date = $invoice->getFromDate() ? $invoice->getFromDate()->format('d-m-Y') : '';
        $to_date = $invoice->getToDate()->format('d-m-Y');
        $form = $this->createFormBuilder()
                    ->add('delete', SubmitType::class, array('attr' => ['class' => 'btn-danger']))
                    //->add('edit', SubmitType::class, array('attr' => ['class' => 'btn-primary']))
                    //->add('save', SubmitType::class, array('attr' => ['class' => 'btn-info']))
                    ->getForm();
        $form->handleRequest($request);
        $redirect_url = $this->get('router')->generate('invoices');
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
            'invoice_title' => $invoice_title,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'form' => $form->createView(),
        ]);
    }
}
