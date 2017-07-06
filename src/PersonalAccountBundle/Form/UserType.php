<?php
/**
 * Created by PhpStorm.
 * User: Potap
 * Date: 30.10.2016
 * Time: 1:22
 */

namespace PersonalAccountBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = ['Ученик'=> 'ROLE_USER','Учитель' => 'ROLE_ADMIN' ];
        $builder
            ->add('name', TextType::class,['label' => 'Логин'])
            ->add(
                'plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Пароль'],
                'second_options' => ['label' => 'Подтверждение пароля']
            ])
            ->add(
                'role',
                ChoiceType::class,
                [
                    'choices' => [
                        '' => $roles,
                    ],
                    'label' => 'Роль',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\User',
        ]);
    }
}