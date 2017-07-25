<?php


namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Form\LessonType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use PersonalAccountBundle\Entity\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;


class GroupsController extends Controller
{
    /**
     * @Route("/groups", name="groups")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function showGroupsAction(Request $request)
    {
        $errorsArray = [];
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('PersonalAccountBundle:Group')->findBy(['active'=> true]);
        $form = $this->createFormBuilder()
            ->add('group', EntityType::class,  [
                'class' => Group::class,
                'choice_label' => 'name',
                'required'=>true,
                'expanded' =>true,
                'multiple' => true,
                'query_builder' => function ($repository) {
                    return $repository
                        ->createQueryBuilder('e')
                        ->where('e.active = true');
                },
            ])
            ->add('delete', SubmitType::class,  ['attr' => ['class' => 'btn-danger']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid() and $form->get('delete')->isClicked()){
            $data = $form->getData();
            $currentDate = new \DateTime(date('Y-m-d'));
            $errorsArray = $this->checkGroupAttendance($data['group'],$currentDate);
            if (!$errorsArray) {
                foreach ($data['group'] as $g) {
                    $g->setActive(null);
                    $em->persist($g);
                    $events = $em->getRepository('PersonalAccountBundle:ScheduleEvent')->findBy(['group_id'=>$g->getId()]);
                    foreach ($events as $e) {
                        $em->remove($e);
                    }
                }
                $em->flush();
                return $this->redirect($this->generateUrl($request->get('_route'), $request->query->all()));

            }
        }
        return $this->render('PersonalAccountBundle:Admin:groups.html.twig', [
            'result' => $result,
            'form' => $form->createView(),
            'errors' =>$errorsArray,
        ]);
    }
    public function checkGroupAttendance($groups,$date){
        $groupIds = [];
        foreach($groups as $g){
            $groupIds[] = $g->getId();
        }
        $em=$this->getDoctrine()->getManager();
        $uneditedAttendances = $em->getRepository('PersonalAccountBundle:Journal')->findBy([
            'group' => $groupIds, 'edited' => false,
        ]);
        return $uneditedAttendances;
    }

    /**
     * @Route("/add_group", name="add_group")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
     public function addGroupAction(Request $request)
     {
         $em = $this->getDoctrine()->getManager();
         $group = new Group();
         $form = $this->createForm(LessonType::class, $group,['data_class' => 'PersonalAccountBundle\Entity\Group']);
         $form->handleRequest($request);
         if ($form->get('save')->isClicked() and $form->isValid()) {
             $group->setActive(true);
             // Save
             $em->persist($group);
             $em->flush();
             return $this->redirect($this->get('router')->generate('groups'));
         }

         return $this->render('PersonalAccountBundle:Admin:addLessonType.html.twig', [
             'form' => $form->createView(),
             'errors' => $form->getErrors(true),
         ]);
     }
}