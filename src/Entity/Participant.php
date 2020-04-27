<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipantRepository")
 */
class Participant implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mail;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sortie", mappedBy="organisateur")
     */
    private $organisateurSorties;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Sortie", mappedBy="inscrits")
     */
    private $participantSorties;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="participants")
     */
    private $site;

    public function __construct()
    {
        $this->participantSorties = new ArrayCollection();
        $this->organisateurSorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom)
    {
        $this->nom = $nom;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom)
    {
        $this->prenom = $prenom;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo)
    {
        $this->pseudo = $pseudo;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone)
    {
        $this->telephone = $telephone;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail)
    {
        $this->mail = $mail;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif)
    {
        $this->actif = $actif;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getRoles()
    {
        // TODO: Implement getRoles() method.
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->getPseudo();
    }

    public function eraseCredentials(){}

    /**
     * @return ArrayCollection
     */
    public function getOrganisateurSorties(): ArrayCollection
    {
        return $this->organisateurSorties;
    }

    /**
     * @param ArrayCollection $organisateurSorties
     */
    public function setOrganisateurSorties(ArrayCollection $organisateurSorties): void
    {
        $this->organisateurSorties = $organisateurSorties;
    }

    /**
     * @return ArrayCollection
     */
    public function getParticipantSorties(): ArrayCollection
    {
        return $this->participantSorties;
    }

    /**
     * @param ArrayCollection $participantSorties
     */
    public function setParticipantSorties(ArrayCollection $participantSorties): void
    {
        $this->participantSorties = $participantSorties;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site): void
    {
        $this->site = $site;
    }
}
