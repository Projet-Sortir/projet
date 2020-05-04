<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
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

        switch ($request->query->get('action')) {
            case 'inscrire':
                inscrire();
                break;
            case 'desister':
                desister();
                break;
            case 'publier':
                publier();
                break;
            case 'annuler':
                annuler();
                break;
            default:break;
        }

        if ($request->query->get('action') == "inscrire") {
            inscrire();
        }

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

        return $this->render('sortie/sorties.html.twig', [
            'sites'=>$sites,
            'sorties'=>$sorties
        ]);

        function inscrire() {

        }

        function desister() {

        }

        function publier() {

        }

        function annuler() {

        }
    }

    /**
     * @Route("/ajouter_sortie", name="ajouter_sortie")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function ajouterSortie(Request $request, EntityManagerInterface $em)
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        $error = '';

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if ($sortie->getDateLimiteInscription() > $sortie->getDateHeureDebut()) {
                $error = 'La date de cloture doit etre antérieur a la date de la sortie';
            } else {
                //TODO : user
                //$sortie->setOrganisateur($this->getUser());
                //$sortie->setSite($this->getUser()->getSite());

                $etatRepo = $this->getDoctrine()->getRepository(Etat::class);

                if ($request->get('action') == 'publier') {
                    $sortie->setEtat($etatRepo->find(2));
                    $this->addFlash('success', 'Sortie Publiée !');
                } else {
                    $sortie->setEtat($etatRepo->find(1));
                    $this->addFlash('success', 'Sortie Enregistrée !');
                }

                $em->persist($sortie);
                $em->flush();

                return $this->redirectToRoute('accueil');
            }
        }

        return $this->render('sortie/ajouter_sortie.html.twig', ['sortieForm' => $sortieForm->createView(), 'error' => $error]);
    }
}
