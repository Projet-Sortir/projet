<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use DateTime;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

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
                //$this->getDoctrine()->getRepository(Sortie::class)->deleteParticipant($idSortie, $id);

                // Récupérer la sortie par son id
                $sortie = $em->getRepository('App:Sortie')->find($idSortie);

                //Récupérer le participant/utilisateur par son id
                $participant = $em->getRepository('App:Participant')->find($id);

                //Récupérer le tableau des inscrits à la sortie
                $inscrits = $sortie->getInscrits();

                // Si le tableau d'inscrit contient le participant, lancer la fonction de suppression du participant, mettre le nouveau tableau dans la sortie,
                // flush,  envoyer un message flash success

                if ($sortie->getEtat()->getId()!=2 && $sortie->getEtat()->getId()!=3) {
                    $this->addFlash('warning', 'La sortie n\'est pas ouverte, vous ne pouvez pas vous désinscrire');
                    break;
                }

                if ($inscrits->contains($participant))
                {
                 $inscrits->removeElement($participant);
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
                $publie = $em->getRepository('App:Etat')->find(2);
                $sortie = $em->getRepository('App:Sortie')->find($idSortie);
                if ($sortie->getEtat()->getId()!=1 || $sortie->getOrganisateur()!=$this->getUser()) {
                    $this->addFlash('warning', 'Vous ne pouvez pas publier cette sortie');
                } else if ($sortie->getDateLimiteInscription() > $sortie->getDateHeureDebut() || new DateTime()> $sortie->getDateLimiteInscription()) {
                    $this->addFlash('warning', 'Les dates sont invalides');
                    //TODO : redirect to modifier sortie
                } else {
                    $sortie->setEtat($publie);
                    $em->flush();
                    $this->addFlash('success', 'Sortie publiée !');
                }
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

        $sortieForm->get('dateHeureDebut')->setData(new DateTime());
        $sortieForm->get('dateLimiteInscription')->setData(new DateTime());

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if ($sortie->getDateLimiteInscription() > $sortie->getDateHeureDebut()) {
                $this->addFlash('error','La date de cloture doit etre antérieur a la date de la sortie');
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

        return $this->render('sortie/ajouter_sortie.html.twig', ['sortieForm' => $sortieForm->createView()]);
    }

    /**
     * @Route("/details/{id}", name="details")
     * @param $id
     * @return Response
     */
    public function detail($id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $sortie = $this->getDoctrine()->getRepository(Sortie::class)->find($id);

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="modifierSortie")
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function modiferSortie($id, Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $user = $this->getUser();
        $sortie = $em->getRepository(Sortie::class)->find($id);

        if ($sortie->getOrganisateur() != $user || $sortie->getEtat()->getId()!=1) {
            $this->addFlash('warning', 'Vous ne pouvez pas modifier cette sortie');
            return $this->redirectToRoute('details', ['id'=>$id]);
        } else {
            $sortieModifie = new Sortie();
            $sortieForm = $this->createForm(SortieType::class, $sortieModifie);

            $sortieForm->get('nom')->setData($sortie->getNom());
            $sortieForm->get('dateHeureDebut')->setData($sortie->getDateHeureDebut());
            $sortieForm->get('dateLimiteInscription')->setData($sortie->getDateLimiteInscription());
            $sortieForm->get('nbInscriptionsMax')->setData($sortie->getNbInscriptionsMax());
            $sortieForm->get('duree')->setData($sortie->getDuree());
            $sortieForm->get('infosSortie')->setData($sortie->getInfosSortie());
            $sortieForm->get('lieu')->setData($sortie->getLieu());

            $sortieForm->handleRequest($request);

            if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
                if ($request->get('action') == 'supprimer') {
                    $em->remove($sortie);
                    $this->addFlash('success', 'Sortie supprimée !');
                    return $this->redirectToRoute('sorties');
                }
                if ($sortie->getDateLimiteInscription() > $sortie->getDateHeureDebut()) {
                    $this->addFlash('error', 'La date de cloture doit etre antérieur a la date de la sortie');
                } else {
                    $sortie->setNom($sortieModifie->getNom());
                    $sortie->setDateHeureDebut($sortieModifie->getDateHeureDebut());
                    $sortie->setDateLimiteInscription($sortieModifie->getDateLimiteInscription());
                    $sortie->setNbInscriptionsMax($sortieModifie->getNbInscriptionsMax());
                    $sortie->setDuree($sortieModifie->getDuree());
                    $sortie->setInfosSortie($sortieModifie->getInfosSortie());
                    $sortie->setLieu($sortieModifie->getLieu());
                    if ($request->get('action') == 'publier') {
                        $sortie->setEtat($this->getDoctrine()->getRepository(Etat::class)->find(2));
                        $this->addFlash('success', 'Sortie Publiée !');
                    }
                    $em->flush();
                    $this->addFlash('success', 'Sortie modifiée !');
                }
            }
        }

        return $this->render('sortie/modifier_sortie.html.twig', [
            'sortieForm'=>$sortieForm->createView()
        ]);
    }
}
