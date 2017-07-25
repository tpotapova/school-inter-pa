<?php

namespace PersonalAccountBundle\Form;

use PersonalAccountBundle\Entity\StudentInvoiceCollector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class StudentInvoiceCollectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('student_invoice_collection', CollectionType::class, ['entry_type' => StudentInvoiceType::class,
            'label' => 'Список счетов студентов'])
            ->add('save', SubmitType::class);
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StudentInvoiceCollector::class,
            'cascade_validation' => true,
        ]);
    }
}