<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
  
    public function homepageAction()
    {
        $entity = new User();
        $form = $this->createCreateForm($entity);

        return $this->render('AppBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
            ));
    }
}
