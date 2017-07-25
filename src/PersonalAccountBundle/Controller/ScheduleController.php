<?php

namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Entity\ScheduleEvent;
use PersonalAccountBundle\Entity\TeacherLesson;
use PersonalAccountBundle\PersonalAccountBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PersonalAccountBundle\Form\ScheduleType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;


class ScheduleController extends Controller
{
    /**
     * @Route("{group_id}/add_event", name="add_event")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function addEventAction($group_id, Request $request)
    {
        $event = new ScheduleEvent();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ScheduleType::class, $event);
        $form->handleRequest($request);
        $show_modal = 'false';
        if ($form->get('save')->isClicked()) {
            $show_modal = 'true';
            if ($form->isValid()) {
                // Save
                $event->setGroupId($group_id);
                $em->persist($event);
                $em->flush();
            }
        }
        $router = $this->get('router');
        $events_load_url = $router->generate('json_events', ['group_id' => $group_id]);
        $modal_title = 'Добавить событие';

        return $this->render('PersonalAccountBundle:Admin:event.html.twig',[
            'form' => $form->createView(),
            'show_modal' => $show_modal,
            'group_id' => $group_id,
            'events_load_url' => $events_load_url,
            'modal_title' => $modal_title,
        ]);
    }

    protected function getEventData($group_id)
    {
        $em = $this->getDoctrine()->getManager();
        $options = $em->getRepository('PersonalAccountBundle\Entity\ScheduleEvent')->findBy(array('group_id' => $group_id));
        $event_data = [];
        /** @var RouterInterface $router */
        $router = $this->get('router');
        foreach($options as $value){
            $item = [
                "title"=>$value->getTeacherLesson()->getTitle(),
                "description"=>$value->getDescription(),
                "allDay"=>false,
                "id"=>$value->getId(),
                "title_id"=>$value->getTeacherLesson(),
                "url" => $router->generate('edit_event', ['group_id' => $group_id, 'id' => $value->getid()]),
                "group_id" => $group_id,
            ] ;
            if ($value->getDow()) {
                $item['dow'] = $value->getDow();
                $item ["start"] = $value->getStartTime()->format('H:i:s');
                $item["end"] = $value->getEndTime()->format('H:i:s');
            }
            else {
                $item["start"]= $value->getStartDate()->format('Y-m-d').'T'.$value->getStartTime()->format('H:i:s');
                $item["end"] = $value->getStartDate()->format('Y-m-d').'T'.$value->getEndTime()->format('H:i:s');
            }
            $event_data[] = $item;
        }
        return $event_data;

    }

    /**
     * @Route("{group_id}/json_events", name="json_events")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showJsonAction($group_id, Request $request)
    {
        $group_id = intval($group_id,10);
        $events = $this->getEventData($group_id);
        $response = new JsonResponse($events);
        return $response;
    }

    /**
     * @Route("{group_id}/edit_event/{id}", name="edit_event")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function editEventAction($group_id,$id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('PersonalAccountBundle\Entity\ScheduleEvent')->find($id);
        if (!$event){
            throw $this->createNotFoundException('Событие не найдено');
        }
        $form = $this->createForm(ScheduleType::class, $event);
        $form->handleRequest($request);
        $show_modal = 'true';
        $modal_title = "Редактировать событие";
        $router = $this->get('router');
        $redirect_url = $router->generate('add_event', ['group_id' => $group_id]);
        $events_load_url = $router->generate('json_events', ['group_id' => $group_id]);
        if ($form->get('save')->isClicked() and $form->isValid()) {

            $em->persist($event);
            $em->flush();

            return $this->redirect($redirect_url);

        }
        if ($form->get('delete')->isClicked()){
            $em->remove($event);
            $em->flush();

            return $this->redirect($redirect_url);
        }

        return $this->render('PersonalAccountBundle:Admin:modal.html.twig', ['form' => $form->createView(),
            'show_modal' => $show_modal,'events_load_url' => $events_load_url,'modal_title' => $modal_title]);
    }

    /**
     * @Route ("/student_schedule", name="student_schedule")
     * @Security("has_role('ROLE_USER')")
     */
    public function studentScheduleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $student = $em->getRepository('PersonalAccountBundle:Student')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$student or !($student->getActive())) {
            throw $this->createNotFoundException('Студент не найден');
        }
        $router = $this->get('router');
        $events_load_url = $router->generate('student_json_events');
        return $this->render('PersonalAccountBundle:Student:schedule.html.twig', ['events_load_url' => $events_load_url]);
    }

    /**
     * @Route("/student_json_events", name="student_json_events")
     * @Security("has_role('ROLE_USER')")
     */
    public function studentJsonAction(Request $request)
    {
        $events = $this->getStudentEventData();
        $response = new JsonResponse($events);
        return $response;
    }

    protected function getStudentEventData()
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $student = $em->getRepository('PersonalAccountBundle:Student')->findOneBy(['user_id' => $authUser->getId()]);
        $groups = $student->getGroups();
        $group_id = [];
        foreach ($groups as $g) {
            $group_id[] = $g->getId();
        };
        $options = $em->getRepository('PersonalAccountBundle\Entity\ScheduleEvent')->findBy(array('group_id' => $group_id));
        $event_data = [];
        foreach($options as $value){
            $event_data[] = [
                "title"=>$value->getTeacherLesson()->getTitle(),
                "start" => $value->getStartTime()->format('H:i:s'),
                "end" => $value->getEndTime()->format('H:i:s'),
                "description"=>$value->getDescription(),
                "allDay"=>false,
                "id"=>$value->getId(),
                "title_id"=>$value->getTeacherLesson(),
                "dow" => $value->getDow(),
            ] ;
        }
        return $event_data;

    }


    /**
     * @Route ("/teacher_schedule", name="teacher_schedule")
     * @Security("has_role('ROLE_ADMIN')")
     */

    public function teacherScheduleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $teacher = $em->getRepository('PersonalAccountBundle:Teacher')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$teacher or !($teacher->getActive())) {
            throw $this->createNotFoundException('Преподаватель не найден');
        }
        $events_load_url = $this->get('router')->generate('teacher_json_events');
        return $this->render('PersonalAccountBundle:Teacher:schedule.html.twig', ['events_load_url' => $events_load_url]);
    }

    /**
     * @Route("/teacher_json_events", name="teacher_json_events")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function teacherJsonAction(Request $request)
    {
        $events = $this->getTeacherEventData();
        $response = new JsonResponse($events);
        return $response;
    }

    protected function getTeacherEventData()
    {
        $em = $this->getDoctrine()->getManager();
        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        $teacher = $em->getRepository('PersonalAccountBundle:Teacher')->findOneBy(['user_id' => $authUser->getId()]);
        if (!$teacher or !($teacher->getActive())) {
            throw $this->createNotFoundException('Преподаватель не найден');
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
        $qb = $em->createQueryBuilder();
        $qb->select('e.start_time e_start, e.end_time e_end, e.description e_description, e.dow e_dow, e.start_date e_startDate, g.name g_name, t.title t_title')
            ->from('PersonalAccountBundle:ScheduleEvent','e')
            ->join('PersonalAccountBundle:Group','g','with','e.group_id = g.id')
            ->join('PersonalAccountBundle:TeacherLesson','t','with','t.id = e.teacher_lesson')
            ->where('e.teacher_lesson in (:lessonIds)')
            ->setParameter('lessonIds',$lessonIds);
        $result = $qb->getQuery()->getResult();
        $event_data = [];
        foreach($result as $value){
            $event_data[] = [
                "title"=>$value['t_title'].' Группа: '.$value['g_name'],
                "start" => $value['e_start']->format('H:i:s'),
                "end" => $value['e_end']->format('H:i:s'),
                "description"=>$value['e_description'],
                "allDay"=>false,
                "dow" => $value['e_dow'],
                "ranges" => ['start'=> $value['e_startDate']->format('Y-m-d')],
            ] ;
        }
        return $event_data;

    }


    /**
     * @Route("/schedule_choose_group", name="schedule_choose_group")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function chooseGroupAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('PersonalAccountBundle:Group')->findBy(['active' => true]);
        return $this->render('PersonalAccountBundle:Admin:groupChoose.html.twig',[
            'result' => $result,
            'schedule' => true
        ]);
    }
}