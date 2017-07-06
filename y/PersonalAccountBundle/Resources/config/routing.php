<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('personal_account_homepage', new Route('/', array(
    '_controller' => 'PersonalAccountBundle:Default:index',
)));

return $collection;
