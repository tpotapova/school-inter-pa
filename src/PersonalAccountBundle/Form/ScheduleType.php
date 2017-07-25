<?php

namespace PersonalAccountBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use PersonalAccountBundle\Entity\TeacherLesson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class ScheduleType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $days = ['Понедельник' => 1, 'Вторник' => 2, 'Среда' => 3, 'Четверг' => 4, 'Пятница' => 5, 'Суббота' => 6, 'Воскресенье' => 0,];
        $builder
            ->add('teacher_lesson',
                EntityType::class,
                [
                    'label_format' => '%name%',
                    'class' => TeacherLesson::class,
                    'choice_label' => 'title',
                    'query_builder' => function ($repository) {
                        return $repository
                            ->createQueryBuilder('e')
                            ->where('e.active = true');
                },
                ]
            )
            ->add('start_date', DateType::class, ['label_format' => '%name%',])
            //Disable date   ['attr' => ['style'=>'display:none;'],'label' => false,])
            ->add('start_time' , TimeType::class, ['label_format' => '%name%',])
            ->add('end_time' , TimeType::class, ['label_format' => '%name%',])
            ->add('dow', ChoiceType::class,['choices' => $days, 'multiple' => true,  'required' => false, 'label_format' => '%name%'])
            ->add('description', TextType::class, ['required' => false, 'label_format' => '%name%',] )
            ->add('save', SubmitType::class)
            ->add('delete', SubmitType::class);

    }
     public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\ScheduleEvent',
        ]);
    }
}