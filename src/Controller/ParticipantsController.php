<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantsController extends AbstractController
{
    /**
     * @Route("/participants", name="participants")
     */
    public function index()
    {
        return $this->render('participants/index.html.twig', [
            'controller_name' => 'ParticipantsController',
        ]);
    }
}
