<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ParticipantsController extends AbstractController
{
    /**
     * @Route("/gerer_profil", name="gerer_profil")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function gererProfil(Request $request, UserPasswordEncoderInterface $encoder)
    {
        //TODO : use user in session
        // $user = $this->getUser();
        $user = $this->getDoctrine()->getRepository(Participant::class)->find(1);

        $checkPseudoUnique = false;
        $changePwd = false;
        $participant = new Participant();
        $update = '';

        $profilForm = $this->createForm(ProfilType::class, $participant);

        $profilForm->get('pseudo')->setData($user->getUsername());
        $profilForm->get('prenom')->setData($user->getPrenom());
        $profilForm->get('nom')->setData($user->getNom());
        $profilForm->get('telephone')->setData($user->getTelephone());
        $profilForm->get('mail')->setData($user->getMail());
        $profilForm->get('site')->setData($user->getSite());

        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {
            $participantRepo = $this->getDoctrine()->getRepository(Participant::class);

            if ($participant->getPassword()!=null) {
                $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
                $hashed = $encoder->encodePassword($participant, $participant->getPassword());
                $participant->setPassword($hashed);
                $changePwd = true;
            }

            if ($participant->getPseudo()!=$user->getUsername()) {
                $checkPseudoUnique = true;
            }

            $update = $participantRepo->updateProfil($participant, $checkPseudoUnique, $changePwd, $user->getId());
        }

        return $this->render('participants/gerer_profil.html.twig', [
            'profilForm'=>$profilForm->createView(),
            'updateResult'=>$update
        ]);
    }
}
