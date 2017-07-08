<?php


namespace PersonalAccountBundle\Controller;

use PersonalAccountBundle\Form\LessonType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PersonalAccountBundle\Entity\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;


class GroupsController extends Controller
{
    /**
     * @Route("/groups", name="groups")
     */
    public function showGroupsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('PersonalAccountBundle:Group')->findAll();
        $form = $this->createFormBuilder()
            ->add('group', EntityType::class,  [
                'class' => Group::class,
                'choice_label' => 'name',
                'required'=>true,
                'expanded' =>true,
                'multiple' => true,
            ])
            ->add('delete', SubmitType::class,  ['attr' => ['class' => 'btn-danger']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid() and $form->get('delete')->isClicked()){
            $data = $form->getData();
            foreach ($data['group'] as $g) {
                $em->remove($g);
            }
            $em->flush();
            return $this->redirect($this->generateUrl($request->get('_route'), $request->query->all()));
        }
        return $this->render('PersonalAccountBundle:Admin:groups.html.twig', [
            'result' => $result,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/add_group", name="add_group")
     */
     public function addGroupAction(Request $request)
     {
         $em = $this->getDoctrine()->getManager();
         $group = new Group();
         $form = $this->createForm(LessonType::class, $group,['data_class' => 'PersonalAccountBundle\Entity\Group']);
         $form->handleRequest($request);
         if ($form->get('save')->isClicked() and $form->isValid()) {
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