<?php

namespace PersonalAccountBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Query\QueryBuilder;
use PersonalAccountBundle\Entity\Attendance;
use PersonalAccountBundle\Entity\AttendanceCollector;
use PersonalAccountBundle\Entity\Student;
use PersonalAccountBundle\Entity\TeacherLesson;
use PersonalAccountBundle\Form\PresenceCollectorType;
use PersonalAccountBundle\Form\AttendanceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class AttendanceController extends Controller
{
    /**
     * @Route ("{teacher_id}/{group_id}/attendance/", name="attendance")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showScheduleAction($group_id, $teacher_id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $teacher = $em->getRepository('PersonalAccountBundle:Teacher')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$teacher or !($teacher->getActive())) {
            throw $this->createNotFoundException('Преподаватель не найден');
        }
        if ($teacher_id != $teacher->getId()) {
            throw $this->createAccessDeniedException('Доступ запрещен');
        }
        $show_modal = 'false';
        $router = $this->get('router');
        $events_load_url = $router->generate('json', ['teacher_id' => $teacher_id,'group_id' => $group_id]);

        return $this->render('PersonalAccountBundle:Teacher:a1.html.twig', ['show_modal' => $show_modal,
            //'form' => $form->createView(),
            'group_id' => $group_id,
            'teacher_id' => $teacher_id,
            'events_load_url' => $events_load_url,
            ]);
    }
    //Add different route for teacher to mark attendances of specific lesson

    /**
     * @Route("/choose_lesson", name="choose_lesson")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function chooseTeacherLessonAction(Request $request)
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
        $lessons = $em->getRepository('PersonalAccountBundle:TeacherLesson')->findBy(['teacher' => $teacher, 'active' => true]);
        if (!$lessons){
            throw $this->createNotFoundException('Занятия не найдены');
            return;
        }
        return $this->render('PersonalAccountBundle:Teacher:lessonChoose.html.twig',[
            'result' => $lessons,
            'teacher_id' => $teacher->getId(),
        ]);
    }
    /**
     * @Route ("{teacher_id}/{lesson_id}/attendances/", name="attendances")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showSchedule2Action($lesson_id, $teacher_id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $teacher = $em->getRepository('PersonalAccountBundle:Teacher')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$teacher or !($teacher->getActive())) {
            throw $this->createNotFoundException('Преподаватель не найден');
        }
        if ($teacher_id != $teacher->getId()) {
            throw $this->createAccessDeniedException('Доступ запрещен');
        }
        $show_modal = 'false';
        $router = $this->get('router');
        $events_load_url = $router->generate('json_l', ['teacher_id' => $teacher_id,'lesson_id' => $lesson_id]);

        return $this->render('PersonalAccountBundle:Teacher:a1.html.twig', ['show_modal' => $show_modal,
            'lesson_id' => $lesson_id,
            'teacher_id' => $teacher_id,
            'events_load_url' => $events_load_url,
        ]);
    }
    /**
     * @Route("{teacher_id}/{lesson_id}/json_l_events/", name="json_l")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showJsonLAction($lesson_id,$teacher_id, Request $request)
    {
        $lesson_id = intval($lesson_id,10);
        $teacher_id = intval($teacher_id,10);
        $events = $this->getEventsData($lesson_id,$teacher_id);
        $response = new JsonResponse($events);
        return $response;
    }
    protected function getEventsData($lesson_id, $teacher_id)
    {
        $em = $this->getDoctrine()->getManager();
        $scheduleQueryBuilder = $em
            ->getRepository('PersonalAccountBundle:Journal')
            ->createQueryBuilder('e')
            ->join('e.teacher_lesson', 't')
            ->where('t.teacher = :teacher_id')
            ->andwhere('t.id = :lesson_id');
        $scheduleQueryBuilder
            ->setParameter('teacher_id', $teacher_id)
            ->setParameter('lesson_id', $lesson_id);
        $result = $scheduleQueryBuilder
            ->getQuery()
            ->getResult();
        $event_data = [];
        foreach($result as $value){
            $d = $value->getDate();
            $start = $value->getStartTime();
            $end = $value->getEndTime();
            $edited = $value->getEdited();
            $counterAttendances = 0;
            if ($edited){
                $attendances = $em->getRepository('PersonalAccountBundle\Entity\Presence')->findBy(array('journal_id' => $value->getId(), 'presence' => true));
                $counterAttendances = count($attendances);
            }
            $event_data[] = [
                "title"=>"Группа ".$value->getGroup()->getName()."<br/>Кол-во учеников: ".$counterAttendances,
                "start" => $d->format('Y-m-d') .' ' .$start->format('H:i:s'),
                "end" => $d->format('Y-m-d') .' ' .$end->format('H:i:s'),
                "allDay"=>false,
                "id"=>$value->getId(),
                "title_id"=>$value->getTeacherLesson()->getId(),
                "teacher_lesson"=>$value->getTeacherLesson(),
                "color" => ($value->getEdited()) ? 'green' : 'blue',
                "url" => $this->get('router')->generate('edit_attendances', ['lesson_id' => $lesson_id,
                    'teacher_id' => $teacher_id, 'journal_id' => $value->getId()]),
            ];
        }
        return $event_data;
    }

    /**
     * @Route("{teacher_id}/{lesson_id}/{journal_id}/attendances", name="edit_attendances")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAttendancesAction($lesson_id, $teacher_id, $journal_id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $teacher = $em->getRepository('PersonalAccountBundle:Teacher')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$teacher or !($teacher->getActive())) {
            throw $this->createNotFoundException('Преподаватель не найден');
        }
        if ($teacher_id != $teacher->getId()) {
            throw $this->createAccessDeniedException('Доступ запрещен');
        }
        $show_modal = 'true';
        $attendances = $em->getRepository('PersonalAccountBundle\Entity\Presence')->findBy(array('journal_id' => $journal_id));
        $journal = $em->getRepository('PersonalAccountBundle\Entity\Journal')->find($journal_id);
        $modal_title = $journal->getTeacherLesson()->getTitle();
        $date_caption = $journal->getDate();
        $start_time = $journal->getStartTime();
        $collector = new AttendanceCollector();
        $collector->setAttendanceCollection($attendances);
        $form = $this->createForm(PresenceCollectorType::class, $collector);
        $form->handleRequest($request);
        $redirect_url = $this->get('router')->generate('attendances', ['lesson_id' => $lesson_id,'teacher_id'=>$teacher_id]);
        $events_load_url = $this->get('router')->generate('json_l', ['lesson_id' => $lesson_id, 'teacher_id'=>$teacher_id]);
        if ($form->get('save')->isClicked()) {
            if ($form->isValid()) {
                $journal = $em->getRepository('PersonalAccountBundle\Entity\Journal')->find($journal_id);
                $journal->setEdited(true);
                foreach ($attendances as $attendance) {
                    $attendance->setJournalId($journal);

                    $em->persist($attendance);
                }
                $em->flush();
            }

            return $this->redirect($redirect_url);

        }
        return $this->render('PersonalAccountBundle:Teacher:attendance.html.twig', [
            'form' => $form->createView(),
            'show_modal' => $show_modal,
            'events_load_url' => $events_load_url,
            'modal_title' => $modal_title,
            'date_caption' => $date_caption,
            'start_time' => $start_time,
        ]);
    }


    /**
     * @Route ("{group_id}/attendance/", name="all_attendances")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showAttendanceAction($group_id, Request $request)
    {

        $show_modal = 'false';
        $router = $this->get('router');
        $events_load_url = $router->generate('json2', ['group_id' => $group_id]);

        return $this->render('PersonalAccountBundle:Admin:a1.html.twig', ['show_modal' => $show_modal,
            'group_id' => $group_id,
            'events_load_url' => $events_load_url,
        ]);
    }

    /**
     * @Route("{group_id}/json2_events/", name="json2")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showAllJsonAction($group_id, Request $request)
    {
        $group_id = intval($group_id,10);
        $events = $this->getAllEventData($group_id);
        $response = new JsonResponse($events);
        return $response;
    }
    protected function getAllEventData($group_id)
    {
        $em = $this->getDoctrine()->getManager();
        $scheduleQueryBuilder = $em
            ->getRepository('PersonalAccountBundle:Journal')
            ->createQueryBuilder('e')
            ->join('e.teacher_lesson', 't')
            ->where('e.group = :group_id')
            ->setParameter('group_id', $group_id);
        $result = $scheduleQueryBuilder
            ->getQuery()
            ->getResult();
        $event_data = [];
        foreach($result as $value){
            $d = $value->getDate();
            $start = $value->getStartTime();
            $end = $value->getEndTime();
            $event_data[] = [
                "title"=>$value->getTeacherLesson()->getTitle(),
                "start" => $d->format('Y-m-d') .' ' .$start->format('H:i:s'),
                "end" => $d->format('Y-m-d') .' ' .$end->format('H:i:s'),
                "allDay"=>false,
                "id"=>$value->getId(),
                "title_id"=>$value->getTeacherLesson()->getId(),
                "teacher_lesson"=>$value->getTeacherLesson(),
                "group_id" => $group_id,
                "color" => ($value->getEdited()) ? 'green' : 'blue',
                "url" => $this->get('router')->generate('show_presense_list', ['group_id' => $group_id, 'journal_id' => $value->getId()]),
            ];
        }
        return $event_data;
    }

    /**
     * @Route("{group_id}/{journal_id}/show_presense", name="show_presense_list")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showPresenceListAction($group_id, $journal_id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $show_modal = 'true';
        $attendances = $em->getRepository('PersonalAccountBundle\Entity\Presence')->findBy([
            'journal_id' => $journal_id,
        ]);
        $journal = $em->getRepository('PersonalAccountBundle\Entity\Journal')->find($journal_id);
        $modal_title = $journal->getTeacherLesson()->getTitle();
        $date_caption = $journal->getDate();
        $start_time = $journal->getStartTime();
        $events_load_url = $this->get('router')->generate('json2', ['group_id' => $group_id]);

        return $this->render('PersonalAccountBundle:Admin:attendance.html.twig', [
            'show_modal' => $show_modal,
            'events_load_url' => $events_load_url,
            'modal_title' => $modal_title,
            'attendances' => $attendances,
            'date_caption' => $date_caption,
            'start_time' => $start_time,
        ]);
    }

    protected function getEventData($group_id, $teacher_id)
    {
        $em = $this->getDoctrine()->getManager();
        $scheduleQueryBuilder = $em
            ->getRepository('PersonalAccountBundle:Journal')
            ->createQueryBuilder('e')
            ->join('e.teacher_lesson', 't')
            ->where('t.teacher = :teacher_id')
            ->andwhere('e.group = :group_id');
            //->groupby('e.group','t.teacher','e.start_time');
        $scheduleQueryBuilder
            ->setParameter('teacher_id', $teacher_id)
            ->setParameter('group_id', $group_id);
        $result = $scheduleQueryBuilder
            ->getQuery()
            ->getResult();
        $event_data = [];
        foreach($result as $value){
            $d = $value->getDate();
            $start = $value->getStartTime();
            $end = $value->getEndTime();
            $event_data[] = [
                "title"=>$value->getTeacherLesson()->getTitle(),
                "start" => $d->format('Y-m-d') .' ' .$start->format('H:i:s'),
                "end" => $d->format('Y-m-d') .' ' .$end->format('H:i:s'),
                "allDay"=>false,
                "id"=>$value->getId(),
                "title_id"=>$value->getTeacherLesson()->getId(),
                "teacher_lesson"=>$value->getTeacherLesson(),
                "group_id" => $group_id,
                "color" => ($value->getEdited()) ? 'green' : 'blue',
                "url" => $this->get('router')->generate('edit_attendance', ['group_id' => $group_id,
                    'teacher_id' => $teacher_id, 'journal_id' => $value->getId()]),
            ];
        }
        return $event_data;
    }


    /**
     * @Route("{teacher_id}/{group_id}/json_events/", name="json")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showJsonAction($group_id,$teacher_id, Request $request)
    {
        $group_id = intval($group_id,10);
        $teacher_id = intval($teacher_id,10);
        $events = $this->getEventData($group_id,$teacher_id);
        $response = new JsonResponse($events);
        return $response;
    }
    /**
     * @Route("{teacher_id}/{group_id}/{journal_id}/attendance", name="edit_attendance")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAttendanceAction($group_id, $teacher_id, $journal_id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $teacher = $em->getRepository('PersonalAccountBundle:Teacher')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$teacher or !($teacher->getActive())) {
            throw $this->createNotFoundException('Преподаватель не найден');
        }
        if ($teacher_id != $teacher->getId()) {
            throw $this->createAccessDeniedException('Доступ запрещен');
        }
        $show_modal = 'true';
        $attendances = $em->getRepository('PersonalAccountBundle\Entity\Presence')->findBy(array('journal_id' => $journal_id));
        $journal = $em->getRepository('PersonalAccountBundle\Entity\Journal')->find($journal_id);
        $modal_title = $journal->getTeacherLesson()->getTitle();
        $date_caption = $journal->getDate();
        $start_time = $journal->getStartTime();
        $collector = new AttendanceCollector();
        $collector->setAttendanceCollection($attendances);
        $form = $this->createForm(PresenceCollectorType::class, $collector);
        $form->handleRequest($request);
        $redirect_url = $this->get('router')->generate('attendance', ['group_id' => $group_id,'teacher_id'=>$teacher_id]);
        $events_load_url = $this->get('router')->generate('json', ['group_id' => $group_id, 'teacher_id'=>$teacher_id]);
        if ($form->get('save')->isClicked()) {
                if ($form->isValid()) {
                    $journal = $em->getRepository('PersonalAccountBundle\Entity\Journal')->find($journal_id);
                    $journal->setEdited(true);
                    foreach ($attendances as $attendance) {
                        $attendance->setJournalId($journal);

                        $em->persist($attendance);
                    }
                    $em->flush();
                }

            return $this->redirect($redirect_url);

        }
        return $this->render('PersonalAccountBundle:Teacher:attendance.html.twig', [
            'form' => $form->createView(),
            'show_modal' => $show_modal,
            'events_load_url' => $events_load_url,
            'modal_title' => $modal_title,
            'date_caption' => $date_caption,
            'start_time' => $start_time,
            ]);
    }

    /**
     * @Route("/attendance_choose_group", name="attendance_choose_group")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function chooseGroupAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('PersonalAccountBundle:Group')->findBy(['active' =>true]);
        return $this->render('PersonalAccountBundle:Admin:groupChoose.html.twig',[
            'result' => $result,
            'schedule' => false
        ]);
    }

    /**
     * @Route("/choose_group", name="choose_group")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function chooseTeacherGroupAction(Request $request)
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
        $lessons = $em->getRepository('PersonalAccountBundle:TeacherLesson')->findBy(['teacher' => $teacher]);
        if (!$lessons){
            throw $this->createNotFoundException('Занятия не найдены');
            return;
        }
        $lessonIds = [];
        foreach ($lessons as $l) {
            $lessonIds[] = $l->getId();
        };
        $events = $em->getRepository('PersonalAccountBundle:ScheduleEvent')->findBy(['teacher_lesson' => $lessonIds]);
        $groupIds =[];
        foreach ($events as $e){
            $groupIds[]=$e->getGroupId();
        }
        $result = $em->getRepository('PersonalAccountBundle:Group')->findBy(['id' => $groupIds, 'active' =>true]);
        return $this->render('PersonalAccountBundle:Teacher:groupChoose.html.twig',[
            'result' => $result,
            'teacher_id' => $teacher->getId(),
        ]);
    }

    /**
     * @Route("/student_attendance", name="student_attendance")
     * @Security("has_role('ROLE_USER')")
     */
    public function showStudentAttendanceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $student = $em->getRepository('PersonalAccountBundle:Student')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$student or !($student->getActive())) {
            throw $this->createNotFoundException('Студент не найден');
        }
        $events_load_url = $this->get('router')->generate('json3');
        return $this->render('PersonalAccountBundle:Student:attendance.html.twig', ['show_modal' => false,
            'events_load_url' => $events_load_url,
        ]);
    }

    /**
     * @Route("student_attendance/json_events/", name="json3")
     * @Security("has_role('ROLE_USER')")
     */
    public function showStudentJsonAction(Request $request)
    {
        $events = $this->getStudentAttendanceData();
        $response = new JsonResponse($events);
        return $response;
    }
    protected function getStudentAttendanceData()
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $student = $em->getRepository('PersonalAccountBundle:Student')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$student or !($student->getActive())) {
            throw $this->createNotFoundException('Студент не найден');
        }
        $presences = $em->getRepository('PersonalAccountBundle:Presence')->findBy(['student'=>$student->getId()]);
        if (!$presences) {
            throw $this->createNotFoundException('Посещения не найдены');
            return;
        }
        $qb = $em->createQueryBuilder();
        $qb ->select('j.date j_date, j.start_time j_start, j.end_time j_end,t.title t_title, p.presence p_presence')
            ->from('PersonalAccountBundle:Journal', 'j')
            ->join('PersonalAccountBundle:Presence', 'p','with','j.id = p.journal_id')
            ->join('PersonalAccountBundle:TeacherLesson','t','with','t.id = j.teacher_lesson')
            ->where('p.student = :student_id')
            ->setParameter('student_id', $student->getId());
        $result = $qb
            ->getQuery()
            ->getResult();
        $event_data = [];
        foreach($result as $value){
            $d = $value['j_date'];
            $start = $value['j_start'];
            $end = $value['j_end'];
            $event_data[] = [
                "title"=>$value['t_title'],
                "start" => $d->format('Y-m-d') .' ' .$start->format('H:i:s'),
                "end" => $d->format('Y-m-d') .' ' .$end->format('H:i:s'),
                "allDay"=>false,
                "color" => ($value['p_presence']) ? 'green' : 'blue',
            ];
        }
        return $event_data;
    }
}