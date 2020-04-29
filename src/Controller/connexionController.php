<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class connexionController extends AbstractController
{

    /**
     * @Route("/connection", name="connection")
     */
    public function connection()
    {
        return $this->render("connection/connection.html.twig");
    }
}