<?php

namespace PersonalAccountBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;


class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('teacher_lesson', HiddenType::class)
            ->add('payed', HiddenType::class)
            ->add('from_date', HiddenType::class)
            ->add('to_date', HiddenType::class)
            ->add('total', HiddenType::class)
            ->add('save', SubmitType::class, array(
                'attr' => array('class' => 'btn-success'),
            ));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\Invoice',
        ]);
    }
}