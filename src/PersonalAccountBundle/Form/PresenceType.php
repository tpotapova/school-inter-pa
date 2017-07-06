<?php

namespace PersonalAccountBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PresenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add ('student', TextType::class, ['disabled' => true])
            ->add('presence', CheckboxType::class, ['label' => false,'required' => false])
            ->add('excuse', CheckboxType::class, ['required' => false, 'label' => false]);
    }
    public function getName()
    {
        return 'presence_collector_form';
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\Presence',
        ]);
    }

}