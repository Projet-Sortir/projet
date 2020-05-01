<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function updateProfil(Participant $participant, bool $checkPseudoUnique, bool $changePwd, $id)
    {
        $errors = [];

        if ($checkPseudoUnique) {
            $qbPseudo = $this->createQueryBuilder('p')
                ->select('p.pseudo')
                ->where('p.pseudo = :pseudo')
                ->setParameter('pseudo', $participant->getPseudo());
            $pseudos = $qbPseudo->getQuery()->getResult();
            if ($pseudos!=null) {
                return $errors[] = 'pseudo existant';
            }
        }

        $qb = $this->createQueryBuilder('p')
            ->update('App:Participant', 'p')
            ->set('p.pseudo', ':pseudo')
            ->set('p.prenom', ':prenom')
            ->set('p.nom', ':nom')
            ->set('p.telephone', ':telephone')
            ->set('p.mail', ':mail')
            ->set('p.site', ':site');

        if ($changePwd) {
            $qb->set('p.password', ':password')
                ->setParameter('password', $participant->getPassword());
        }

        $qb->where('p.id = :id')
            ->setParameter('pseudo', $participant->getPseudo())
            ->setParameter('prenom', $participant->getPrenom())
            ->setParameter('nom', $participant->getNom())
            ->setParameter('telephone', $participant->getTelephone())
            ->setParameter('mail', $participant->getMail())
            ->setParameter('site', $participant->getSite())
            ->setParameter('id', $id);

        $query = $qb->getQuery();
        $query->execute();
        return $errors;
    }

    // /**
    //  * @return Participant[] Returns an array of Participant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
