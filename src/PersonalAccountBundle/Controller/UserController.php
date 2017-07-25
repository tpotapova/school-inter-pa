<?php

namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Entity\Student;
use PersonalAccountBundle\Entity\Teacher;
use PersonalAccountBundle\Form\StudentGroupsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/teachers", name="teachers")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showTeachersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('PersonalAccountBundle:Teacher')->findBy(['active' => true]);
        $form = $this->createFormBuilder()
            ->add('teacher', EntityType::class,  [
                'class' => Teacher::class,
                'required'=>true,
                'expanded' =>true,
                'multiple' => true,
                'query_builder' => function ($repository) {
                    return $repository
                        ->createQueryBuilder('t')
                        ->where('t.active = true');
                },
            ])
            ->add('delete', SubmitType::class,  ['attr' => ['class' => 'btn-danger']])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid() and $form->get('delete')->isClicked()){
            $data = $form->getData();
            foreach ($data['teacher'] as $t) {
                $t->setActive(null);
                $em->persist($t);
                $lessons = $em->getRepository('PersonalAccountBundle:TeacherLesson')->findBy(['teacher' => $t->getId()]);
                foreach ($lessons as $l) {
                    $l->setActive(null);
                    $events = $em->getRepository('PersonalAccountBundle:ScheduleEvent')->findBy(['teacher_lesson' => $l->getId()]);
                    if ($events) {
                        foreach ($events as $e) {
                            $em->remove($e);
                        };
                    }
                    $em->persist($l);
                }
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
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showStudentsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('PersonalAccountBundle:Student')->findBy(['active' => true]);
        $form = $this->createFormBuilder()
            ->add('student', EntityType::class,  [
                'class' => Student::class,
                'required'=>true,
                'expanded' =>true,
                'multiple' => true,
                'query_builder' => function ($repository) {
                    return $repository
                        ->createQueryBuilder('s')
                        ->where('s.active = true');
                },
            ])
            ->add('delete', SubmitType::class,  ['attr' => ['class' => 'btn-danger']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid() and $form->get('delete')->isClicked()){
            $data = $form->getData();
            foreach ($data['student'] as $s) {
                $s->setActive(null);
                $groups = $s->getGroups();
                foreach($groups as $g) {
                    $s->removeGroup($g);
                }
                $em->persist($s);
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
     * @Security("has_role('ROLE_SUPER_ADMIN')")
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