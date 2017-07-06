<?php

namespace PersonalAccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('PersonalAccountBundle:Default:index.html.twig');
    }
}
