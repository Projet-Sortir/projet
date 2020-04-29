<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sorties", name="sorties")
     * @param Request $request
     * @return Response
     */
    public function sorties(Request $request)
    {
        //TODO :
        //$id = $this->getUser()->getId();
        $id = 1;

        $siteRepo = $this->getDoctrine()->getRepository(Site::class);
        $sites = $siteRepo->findAll();

        $filtres = array(
            "site" => $request->query->get('site'),
            "recherche" => $request->query->get('recherche'),
            "debut" => $request->query->get('debut'),
            "fin" => $request->query->get('fin'),
            "organisateur" => $request->query->get('organisateur'),
            "inscrit" => $request->query->get('inscrit'),
            "non_inscrit" => $request->query->get('non_inscrit'),
            "passes" => $request->query->get('passes')
        );

        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sorties = $sortieRepo->lister($filtres, $id);

        //TODO : retirer les dump
        dump($filtres);
        dump($id);
        dump($sorties);

        return $this->render('sortie/sorties.html.twig', [
            'sites'=>$sites,
            'sorties'=>$sorties
        ]);
    }
}
