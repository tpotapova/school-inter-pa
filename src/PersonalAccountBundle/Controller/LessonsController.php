<?php

namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Entity\Lesson;
use PersonalAccountBundle\Entity\Teacher;
use PersonalAccountBundle\Entity\TeacherLesson;
use PersonalAccountBundle\Form\LessonType;
use PersonalAccountBundle\PersonalAccountBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PersonalAccountBundle\Form\TeacherLessonType;
use PersonalAccountBundle\Form\TeacherType;
use PersonalAccountBundle\Services;

class LessonsController extends Controller
{
    /**
     * @Route("/add_lesson", name="add_lesson")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function addLessonAction(Request $request)
    {
        $lesson = new TeacherLesson();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(TeacherLessonType::class, $lesson);
        $form->handleRequest($request);
        $redirect_url = $this->get('router')->generate('lessons');
        if ($form->get('save')->isClicked() and $form->isValid()) {
                // Save
                $lesson->setActive(true);
                $em->persist($lesson);
                $em->flush();
                return $this->redirect($redirect_url);
        }
        return $this->render('PersonalAccountBundle:Admin:lesson.html.twig', ['form' => $form->createView()]);
    }
    /**
     * @Route("/lesson_categories", name="lesson_categories")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function lessonCategoriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
            ->add('lesson', EntityType::class,  [
                'label_format' => '%name%',
                'class' => Lesson::class,
                'required'=>true,
                'choice_label' => 'name',
                'expanded' =>true,
                'multiple' => true,
                'query_builder' => function ($repository) {
                    return $repository
                        ->createQueryBuilder('c')
                        ->where('c.active = true');
                },
            ])
            ->add('add', SubmitType::class,  ['attr' => ['class' => 'btn-primary']])
            ->add('delete', SubmitType::class,  ['attr' => ['class' => 'btn-danger']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->get('add')->isClicked()) {
            $redirect_url = $this->get('router')->generate('add_lesson_category');
            return $this->redirect($redirect_url);
        }

        if ($form->isValid() and $form->get('delete')->isClicked()){
            $data = $form->getData();
            foreach ($data['lesson'] as $l) {
                $l->setActive(null);
            }
            $em->flush();
            return $this->redirect($this->generateUrl($request->get('_route'), $request->query->all()));
        }

        return $this->render('PersonalAccountBundle:Admin:lessonTypes.html.twig', [
            'form'=>$form->createView(),
        ]);
    }
    /**
     * @Route("/add_lesson_category", name="add_lesson_category")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function addLessonCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $category = new Lesson();
        $form= $this->createForm(LessonType::class,$category);
        $form->handleRequest($request);
        if ($form->get('save')->isClicked() and $form->isValid()) {
            // Save
            $category->setActive(true);
            $em->persist($category);
            $em->flush();
            return $this->redirect($this->get('router')->generate('lesson_categories'));
        }
        return $this->render('PersonalAccountBundle:Admin:addLessonType.html.twig', [
            'form'=>$form->createView(),
            'errors' => $form->getErrors(true),
        ]);
    }
    /**
     * @Route("/lessons", name="lessons")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showlessonsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder('l');
        $qb->select('tl.id as tl_id, tl.rate as tl_rate, tl.comission as tl_comission, tl.title as tl_title,l.name as l_name,t.surname as t_surname, t.name as t_name')
            ->from('PersonalAccountBundle:TeacherLesson','tl')
            ->join('PersonalAccountBundle:Lesson','l','with','tl.lesson = l.id')
            ->join('PersonalAccountBundle:Teacher','t','with','tl.teacher = t.id')
            ->where('tl.active = true');
        $result = $qb->getQuery()->getScalarResult();
        return $this->render('PersonalAccountBundle:Admin:lessons.html.twig', [
            'result' => $result,
        ]);
    }
    /**
     * @Route("/lessons/{lesson_id}", name="edit_lesson")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function editLessonAction($lesson_id,Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('PersonalAccountBundle:TeacherLesson')->find($lesson_id);
        if (!$entity) {
            throw $this->createNotFoundException('Занятие не найдено');
        };
        $form = $this->createForm(TeacherLessonType::class, $entity);
        $form->handleRequest($request);
        $redirect_url = $this->get('router')->generate('lessons');
        if ($form->get('save')->isClicked() and $form->isValid()) {
            // Save
            $em->persist($entity);
            $em->flush();
            return $this->redirect($redirect_url);
        }
        if ($form->get('delete')->isClicked()){
            $entity->setActive(null);
            $em->persist($entity);
            $events = $em->getRepository('PersonalAccountBundle:ScheduleEvent')->findBy(['teacher_lesson'=>$entity->getId()]);
            foreach ($events as $e) {
                $em->remove($e);
            }
            $em->flush();
            return $this->redirect($redirect_url);
        }
        return $this->render('PersonalAccountBundle:Admin:lesson.html.twig',[
            'entity'      => $entity,
            'form'   => $form->createView(),
        ]);
    }
}