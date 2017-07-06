<?php

namespace PersonalAccountBundle\Form;

use const Couchbase\HAVE_IGBINARY;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use PersonalAccountBundle\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('edited',HiddenType::class)
        ->add('id', HiddenType::class)
        ->add ('student', TextType::class, ['disabled' => true])
        ->add('presence', CheckboxType::class, ['data' => true, 'label' => false,'required' => false])
        ->add('excuse', CheckboxType::class, ['required' => false, 'label' => false]);
        //->add('date', HiddenType::class)
        //->add('teacher_lesson', HiddenType::class)
        //->add('start_time', HiddenType::class);
    }
    public function getName()
    {
        return 'attendance_collector_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\Attendance',
        ]);
    }
}