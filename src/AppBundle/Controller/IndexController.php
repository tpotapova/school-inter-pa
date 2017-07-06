<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    /**
     * @Route("/welcome")
     */
    public function welcomeAction()
    {

        return new Response(
            '<html><body>Welcome!</body></html>'
        );
    }
}