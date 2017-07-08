<?php

namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Entity\Student;
use PersonalAccountBundle\Entity\Teacher;
use PersonalAccountBundle\Form\StudentGroupsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/teachers", name="teachers")
     */
    public function showTeachersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('PersonalAccountBundle:Teacher')->findAll();
        $form = $this->createFormBuilder()
            ->add('teacher', EntityType::class,  [
                'class' => Teacher::class,
                'required'=>true,
                'expanded' =>true,
                'multiple' => true,
            ])
            ->add('delete', SubmitType::class,  ['attr' => ['class' => 'btn-danger']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid() and $form->get('delete')->isClicked()){
            $data = $form->getData();
            foreach ($data['teacher'] as $t) {
                $em->remove($t);
            }
            $em->flush();
            return $this->redirect($this->generateUrl($request->get('_route'), $request->query->all()));
        }
        return $this->render('PersonalAccountBundle:Admin:teachers.html.twig', [
            'result' => $result,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/students", name="students")
     */
    public function showStudentsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('PersonalAccountBundle:Student')->findAll();
        $form = $this->createFormBuilder()
            ->add('student', EntityType::class,  [
                'class' => Student::class,
                'required'=>true,
                'expanded' =>true,
                'multiple' => true,
            ])
            ->add('delete', SubmitType::class,  ['attr' => ['class' => 'btn-danger']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid() and $form->get('delete')->isClicked()){
            $data = $form->getData();
            foreach ($data['student'] as $s) {
                $em->remove($s);
            }
            $em->flush();
            return $this->redirect($this->generateUrl($request->get('_route'), $request->query->all()));
        }
        return $this->render('PersonalAccountBundle:Admin:students.html.twig', [
            'result' => $result,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/students/{student_id}", name="edit_student_groups")
     */
    public function editStudentGroupsAction($student_id, Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('PersonalAccountBundle:Student')->find($student_id);
        if (!$entity) {
            throw $this->createNotFoundException('Студент не найден');
        };
        $form = $this->createForm(StudentGroupsType::class, $entity);
        $form->handleRequest($request);
        $redirect_url = $this->get('router')->generate('students');
        if ($form->get('save')->isClicked() and $form->isValid()) {
            // Save
            $em->persist($entity);
            $em->flush();
            return $this->redirect($redirect_url);
        }
        return $this->render('PersonalAccountBundle:Admin:studentGroups.html.twig',[
            'entity'      => $entity,
            'form'   => $form->createView(),
        ]);
    }
}