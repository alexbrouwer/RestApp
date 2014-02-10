<?php

namespace Gearbox\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GearboxClientBundle:Default:index.html.twig');
    }
}
