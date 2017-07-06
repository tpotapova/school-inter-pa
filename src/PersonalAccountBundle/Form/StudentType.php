<?php


namespace PersonalAccountBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class StudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,['label_format' => '%name%',])
            ->add('surname',TextType::class,['label_format' => '%name%',])
            ->add('birthday',BirthdayType::class, array(
                 'widget' => 'single_text','label_format' => '%name%'))
            ->add('email',EmailType::class)
            ->add('skype',TextType::class)
            ->add('fb_url',UrlType::class, array(
                'required' => false,'label_format' => '%name%'))
            ->add('vk_url',UrlType::class, array(
                'required' => false,'label_format' => '%name%'))
            ->add('grade',IntegerType::class, array(
                'required' => false,'label_format' => '%name%'))
            ->add('parent1_name',TextType::class, array(
                'required' => false,'label_format' => '%name%'))
            ->add('parent2_name',TextType::class, array(
                'required' => false,'label_format' => '%name%'))
            ->add('city',TextType::class,['label_format' => '%name%',])
            ->add('comments',TextType::class, array(
                'required' => false, 'label_format' => '%name%'));

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\Student',
        ]);
    }
}