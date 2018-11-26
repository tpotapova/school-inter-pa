<?php

namespace PersonalAccountBundle\Form;

use PersonalAccountBundle\Entity\AttendanceCollector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PresenceCollectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attendance_collection', CollectionType::class, ['entry_type' => PresenceType::class,
            'label' => false])
            ->add('homework', TextareaType::class, [
                'label' => 'Домашняя работа',
                'mapped' => false,
                'required' => false,
                'attr' => ['cols' => '10', 'rows' => '10'],
                ])
            ->add('save', SubmitType::class);
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PresenceCollector::class,
            'cascade_validation' => true,
        ]);
    }
}