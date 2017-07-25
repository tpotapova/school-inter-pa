<?php

namespace PersonalAccountBundle\Form;

use PersonalAccountBundle\Entity\Teacher;
use PersonalAccountBundle\Entity\Lesson;
use PersonalAccountBundle\Form\LessonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;

class TeacherLessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, ['label_format' => '%name%'])
            ->add('rate', IntegerType::class,['label_format' => '%name%'])
            ->add('save', SubmitType::class,['attr' => ['class' => 'btn-primary']])
            ->add(
                'teacher',
                EntityType::class,
                [
                    'label_format' => '%name%',
                    'class' => Teacher::class,
                    'choice_label' => 'surname',
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('t')
                            ->where('t.active = true');

                    },
                ]
            )
            ->add(
                'lesson',
                EntityType::class,
                [
                    'label_format' => '%name%',
                    'class' => Lesson::class,
                    'choice_label' => 'name',
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('l')
                            ->where('l.active = true');

                    },
                ]
            );
        if ($builder->getData()->getId()) { // or !getId()
            $builder->add('delete', SubmitType::class, [
                'label'=>'Удалить','attr' => ['class' => 'btn-danger']
            ]);
        };

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\TeacherLesson',
        ]);
    }
}