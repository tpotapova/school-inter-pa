<?php

namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Entity\ScheduleEvent;
use PersonalAccountBundle\Entity\TeacherLesson;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
            $event_data[] = [
                "title"=>$value->getTeacherLesson()->getTitle(),
                "start" => $value->getStartTime()->format('H:i:s'),
                "end" => $value->getEndTime()->format('H:i:s'),
                "description"=>$value->getDescription(),
                "allDay"=>false,
                "id"=>$value->getId(),
                "title_id"=>$value->getTeacherLesson(),
                "dow" => $value->getDow(),
                "url" => $router->generate('edit_event', ['group_id' => $group_id, 'id' => $value->getid()]),
                "group_id" => $group_id,
            ] ;
        }
        return $event_data;

    }

    /**
 * @Route("{group_id}/json_events", name="json_events")
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
     * @Route ("{group_id}/group_schedule", name="group_schedule")
     */
    public function showScheduleAction($group_id, Request $request)
    {
        $router = $this->get('router');
        $events_load_url = $router->generate('json_events', ['group_id' => $group_id]);
        return $this->render('PersonalAccountBundle:Student:schedule.html.twig', ['events_load_url' => $events_load_url]);
    }
}