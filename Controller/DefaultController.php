<?php

namespace Grossum\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        //TODO: ONLY FOR TESTING

        $menuManager = $this->get('grossum_menu.menu.manager');
        $handler = $menuManager->getMenuHandler('GrossumContactBundle:Contact');
        $string = '<a href="' . $handler->getUrl() . '">List</a><br>';

        return (new Response($string));
    }
}
