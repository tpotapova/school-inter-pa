<?php

namespace PersonalAccountBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class TeacherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,['label_format' => '%name%',])
            ->add('surname',TextType::class,['label_format' => '%name%',])
            ->add('email',EmailType::class)
            ->add('skype',TextType::class)
            ->add('city',TextType::class,['label_format' => '%name%',])
            ->add('comments',TextType::class, array(
                'required' => false, 'label_format' => '%name%'));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\Teacher',
        ]);
    }
}