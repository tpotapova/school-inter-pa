<?php

namespace PersonalAccountBundle\Form;

use PersonalAccountBundle\Entity\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class StudentGroupsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('groups', EntityType::class,[
            'label' => 'Группы',
            'class' => Group::class,
            'choice_label' => 'name',
            'expanded' =>true,
            'multiple' => true,
            ])
            ->add('save', SubmitType::class,['attr' => ['class' => 'btn-primary']]);

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\Student',
        ]);
    }
}