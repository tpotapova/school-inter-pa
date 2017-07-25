<?php


namespace PersonalAccountBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;


class StudentInvoiceCollector
{
    protected $student_invoice_collection;

    public function __construct()
    {
        $this->student_invoice_collection = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getStudentInvoiceCollection()
    {
        return $this->student_invoice_collection;
    }

    /**
     * @param ArrayCollection $student_invoice_collection
     */
    public function setStudentInvoiceCollection($student_invoice_collection)
    {
        $this->student_invoice_collection = $student_invoice_collection;
    }
}