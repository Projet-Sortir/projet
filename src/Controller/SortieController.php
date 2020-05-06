<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\AnnulerType;
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
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function sorties(Request $request, EntityManagerInterface $em)
    {
        $id = $this->getUser()->getId();
        $idSortie=$request->query->get( 'id');

        switch ($request->get('action')) {
            case 'inscrire':
                //récuperer la sortie via son id
                $sortie = $em->getRepository('App:Sortie')->find($idSortie);
                //récuperer le participant via son id
                $participant = $em->getRepository('App:Participant')->find($id);
                //récuperer le tableau des inscrits à la sortie
                $inscrits = $sortie->getInscrits();
                //si le tableau d'inscrit contient le participant, ne rien faire et envoyer un message flash warning
                if ($sortie->getEtat()->getId()!=2) {
                    $this->addFlash('warning', 'La sortie n\'est pas ouverte, vous ne pouvez pas vous inscrire');
                    break;
                }
                else if ($inscrits->contains($participant)) {
                    $this->addFlash('warning', 'Vous etes déjà inscrit pour cette sortie');
                    break;

                //sinon ajouter le participant au tableau, mettre le nouveau tableau dans la sortie, flush, envoyer un message flash success
                } else {
                    $inscrits->add($participant);
                    $sortie->setInscrits($inscrits);
                    $em->flush();
                    $this->addFlash('success', 'Inscription réussi !');
                    break;
                }
            case 'desister':
                //TODO : Annuler la présence d'un participant à une sortie
                //$this->getDoctrine()->getRepository(Sortie::class)->deleteParticipant($idSortie, $id);

                // Récupérer la sortie par son id
                $sortie = $em->getRepository('App:Sortie')->find($idSortie);

                //Récupérer le participant/utilisateur par son id
                $participant = $em->getRepository('App:Participant')->find($id);

                //Récupérer le tableau des inscrits à la sortie
                $inscrits = $sortie->getInscrits();

                // Si le tableau d'inscrit contient le participant, lancer la fonction de suppression du participant, mettre le nouveau tableau dans la sortie,
                // flush,  envoyer un message flash success

                if ($inscrits->contains($participant))
                {
                 $inscrits->del($participant);
                 $sortie->setInscrits($inscrits);
                 $em->flush();
                 $this->addFlash('success', 'Vous êtes retiré de la sortie');
                 break;

                 //Sinon ne rien faire et envoyer un message flash warning

                } else
                    {
                    $this->addFlash('warning', 'Vous devez être inscrit sur cette sortie pour pour pouvoir en sortir!');
                    }
                break;
            case 'publier':
                //TODO : publier une sortie
                break;
            case 'annuler':
                //TODO : annuler une sortie
                break;
            default:break;
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
        $isInscrit = [];

        foreach ($sorties as $value) {
            if ($value->getInscrits()->contains($this->getUser())) {
                $isInscrit[$value->getId()] = true;
            } else {
                $isInscrit[$value->getId()] = false;
            }
        }

        return $this->render('sortie/sorties.html.twig', [
            'sites'=>$sites,
            'sorties'=>$sorties,
            'isInscrit'=>$isInscrit
        ]);
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
                $sortie->setOrganisateur($this->getUser());
                $sortie->setSite($this->getUser()->getSite());

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

    /**
     * @Route("/annuler_sortie/{id}", name="annuler_sortie")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function annulerSortie(Request $request, EntityManagerInterface $em, $id)
    {
        $sortie=$em -> getRepository(Sortie::class)->find($id);
        if ($sortie->getEtat()->getId()==1 || $sortie->getEtat()->getId()==2 || $sortie->getEtat()->getId()==3)
        {
            if($sortie->getOrganisateur()== $this->getUser())
            {
                $motif=new Sortie();
                $annulerForm = $this->CreateForm(AnnulerType::class, $motif);
                $annulerForm->handleRequest($request);

                if ($annulerForm->isSubmitted() && $annulerForm->isValid())
                    {
                        $etatRepo = $this->getDoctrine()->getRepository(Etat::class);
                        $sortie->setEtat($etatRepo->find(6));
                        $sortie->setInfosSortie($motif->getInfosSortie());
                        $em->flush();
                        $this->addFlash('success', 'Sortie annulée !');
                    }
            } else {
                $this->addFlash('warning', 'Impossible d\'annuler cette sortie');
                return $this->redirectToRoute("sorties");
            }
        } else {
            $this->addFlash('warning', 'Impossible d\'annuler cette sortie');
            return $this->redirectToRoute("sorties");
        }
        return $this->render('sortie/annuler_sortie.html.twig', ['sortie'=>$sortie, 'annulerForm'=>$annulerForm->createView()]);

    }
}
