<?php


namespace PersonalAccountBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class StudentInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add ('student_id', TextType::class, ['disabled' => true])
            ->add('total', IntegerType::class, ['disabled' => true])
            ->add('payed', CheckboxType::class, ['required' => false, 'label' => false]);
    }
    public function getName()
    {
        return 'student_invoice_collector_form';
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PersonalAccountBundle\Entity\StudentInvoice',
        ]);
    }
}
