<?php

namespace PersonalAccountBundle\Form;

use PersonalAccountBundle\Entity\AttendanceCollector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class AttendanceCollectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attendance_collection', CollectionType::class, ['entry_type' => AttendanceType::class,
            'label' => 'Список учеников'])
        ->add('save', SubmitType::class);
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AttendanceCollector::class,
            'cascade_validation' => true,
        ]);
    }
}