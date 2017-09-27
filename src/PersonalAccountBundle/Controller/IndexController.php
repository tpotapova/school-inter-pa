<?php
namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Entity\Student;
use PersonalAccountBundle\Entity\Teacher;
use PersonalAccountBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PersonalAccountBundle\Form\StudentType;
use PersonalAccountBundle\Form\TeacherType;
use PersonalAccountBundle\Service;

class IndexController extends Controller
{

    /**
     * @Route("/student_profile", name="student")
     * @Security("has_role('ROLE_USER')")
     */
    public function showStudentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $student = $em->getRepository('PersonalAccountBundle:Student')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$student or !($student->getActive())) {
            throw $this->createNotFoundException('Студент не найден');
        };
        if ($student->getUserId()->getId() !== $authUser->getId()) {
            throw $this->createAccessDeniedException('Студент не авторизован');
        }
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);
        if ($form->get('save')->isClicked() and $form->isValid()) {
            // Save
            $em->persist($student);
            $em->flush();
        }
        return $this->render('PersonalAccountBundle:Student:profile.html.twig',[
            'student'=>$student,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/teacher_profile", name="teacher")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showTeacherAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $teacher = $em->getRepository('PersonalAccountBundle:Teacher')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$teacher or !($teacher->getActive())) {
            throw $this->createNotFoundException('Преподаватель не найден');
        };
        if ($teacher->getUserId()->getId() !== $authUser->getId()) {
            throw $this->createAccessDeniedException('Преподаватель не авторизован');
        }
        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($request);
        if ($form->get('save')->isClicked() and $form->isValid()) {
            // Save
            $em->persist($teacher);
            $em->flush();
        }
        return $this->render('PersonalAccountBundle:Teacher:profile.html.twig',[
            'teacher'=>$teacher,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/student", name="indexStudent")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexStudentAction(Request $request)
    {
        $student = new Student();
        $session = $request->getSession();
        $user_id = $session->get('user_id');
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save
            $em = $this->getDoctrine()->getManager();
            $student->setUserID($em->getRepository('PersonalAccountBundle:User')->find($user_id));
            $student->setActive(true);
            $em->persist($student);
            $em->flush();
            return $this->redirectToRoute('student');
        }


        return $this->render('PersonalAccountBundle:Student:index.html.twig', [
            'form' => $form->createView(),
            'errors' =>$form->getErrors(true),
        ]);
    }
    /**
     * @Route("/teacher", name="indexTeacher")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexTeacherAction(Request $request)
    {
        $teacher = new Teacher();
        $session = $request->getSession();
        $user_id = $session->get('user_id');
        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Save
            $em = $this->getDoctrine()->getManager();
            $teacher->setUserID($em->getRepository('PersonalAccountBundle:User')->find($user_id));
			$teacher->setActive(true);
            $em->persist($teacher);
            $em->flush();
            return $this->redirectToRoute('teacher');
        }

        return $this->render('PersonalAccountBundle:Teacher:index.html.twig', [
            'form' => $form->createView(),
            'errors' =>$form->getErrors(true),
        ]);
    }

}