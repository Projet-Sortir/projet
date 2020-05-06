<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParticipantsController extends AbstractController
{
    /**
     * @Route("/gerer_profil", name="gerer_profil")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function gererProfil(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $participant = new Participant();

        $profilForm = $this->createForm(ProfilType::class, $participant);

        $profilForm->get('pseudo')->setData($user->getUsername());
        $profilForm->get('prenom')->setData($user->getPrenom());
        $profilForm->get('nom')->setData($user->getNom());
        $profilForm->get('telephone')->setData($user->getTelephone());
        $profilForm->get('mail')->setData($user->getMail());
        $profilForm->get('site')->setData($user->getSite());

        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {
            $participantRepo = $em->getRepository('App:Participant');
            $newUser = $participantRepo->find($user->getId());

            if ($participant->getPseudo()!=$user->getUsername()) {
                if ($participantRepo->findBy(['pseudo'=>$participant->getPseudo()]) == []) {
                    $newUser->setPseudo($participant->getPseudo());
                } else {
                    $this->addFlash('warning', 'Pseudo deja utilisé');
                    return $this->redirectToRoute('gerer_profil');
                }
            }

            if ($participant->getPassword()!=null) {
                $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
                $hashed = $encoder->encodePassword($participant, $participant->getPassword());
                $newUser->setPassword($hashed);
            }

            $newUser->setPrenom($participant->getPrenom());
            $newUser->setNom($participant->getNom());
            $newUser->setTelephone($participant->getTelephone());
            $newUser->setMail($participant->getMail());
            $newUser->setSite($participant->getSite());
            $em->flush();
            $this->addFlash('success', 'Profil modifié');
        }

        return $this->render('participants/gerer_profil.html.twig', [
            'profilForm'=>$profilForm->createView()
        ]);
    }
}
