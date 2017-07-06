<?php
namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Entity\Student;
use PersonalAccountBundle\Entity\Teacher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PersonalAccountBundle\Form\StudentType;
use PersonalAccountBundle\Form\TeacherType;
use PersonalAccountBundle\Services;

class IndexController extends Controller
{

    /**
     * @Route("/index/{slug}", name="student_show")
     */
    public function showAction($slug)
    {
    return $this->render('PersonalAccountBundle:Student:timetable.html.twig');
    }

    /**
     * @Route("/student", name="indexStudent")
     */
    public function indexStudentAction(Request $request)
    {
        $student = new Student();
        $session = $request->getSession();
        $user_id = $session->get('user_id');
        $student->setUserID($user_id);
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($student);
            $em->flush();

            return $this->redirectToRoute('student_show');
        }

        return $this->render('PersonalAccountBundle:Student:index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/teacher", name="indexTeacher")
     */
    public function indexTeacherAction(Request $request)
    {
        $teacher = new Teacher();
        $session = $request->getSession();
        $user_id = $session->get('user_id');
        $teacher->setUserID($user_id);
        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($teacher);
            $em->flush();
        }

        return $this->render('PersonalAccountBundle:Teacher:index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/schedule", name="google_schedule")
     */
    public function scheduleAction(Request $request)
    {
        $google = $this->get("app.google");
        $calendar = $google->getCalendar();
        $events = $calendar->events->listEvents('9u1lthok84u28c5n5i0uqrqb08@group.calendar.google.com', [
            'timeMin' => '2016-11-10T00:00:00Z',
            'timeMax' =>'2016-12-10T00:00:00Z',
        ]);
        var_dump($events);
        return new Response(
            '<html><body>Bla-bla-bla</body></html>'
        );
    }

}