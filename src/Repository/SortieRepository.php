<?php

namespace App\Repository;

use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function lister($filtres, $id)
    {
        $qbSite = $this->createQueryBuilder('sortie')
            ->select('site.id')
            ->from('App:Site', 'site')
            ->where('site.nom = :nom')
            ->setParameter('nom', $filtres['site'])
            ->setMaxResults(1);
        $idSite = $qbSite->getQuery()->getResult();

        $qbSortie = $this->createQueryBuilder('s');
        $qbSortie->where($qbSortie->expr()->orX('s.etat != 1', 's.organisateur = :id'))
            ->setParameter('id', $id)
            ->setMaxResults(20)
            ->orderBy('s.dateHeureDebut', 'ASC')
            ->leftjoin('s.inscrits', 'i');

        if ($filtres['site'] != null) {
            $qbSortie->andWhere('s.site = :idSite')
                ->setParameter('idSite', $idSite);
        }
        if ($filtres['recherche'] != null) {
            $qbSortie->andWhere($qbSortie->expr()->like('s.nom', ':recherche'))
                ->setParameter('recherche', '%'.$filtres['recherche'].'%');
        }
        if ($filtres['debut'] != null) {
            $qbSortie->andWhere('s.dateHeureDebut > :debut')
                ->setParameter('debut', $filtres['debut']);
        }
        if ($filtres['fin'] != null) {
            $qbSortie->andWhere('s.dateHeureDebut < :fin')
                ->setParameter('fin', $filtres['fin']);
        }

        $where = [];
        $param = [];

        if ($filtres['organisateur'] == 'organisateur') {
            $where[] = 's.organisateur = :idOrg';
            $param['idOrg'] = $id;
        }

        if ($filtres['inscrit'] == 'inscrit') {
            $where[] = 'i.id = :idInscr';
            $param['idInscr'] = $id;
        }

        if ($filtres['non_inscrit'] == 'non_inscrit') {
            $qbId = $this->createQueryBuilder('s')
                ->select('s.id')
                ->leftjoin('s.inscrits', 'i')
                ->where('i.id = :id')
                ->setParameter('id', $id);

            $idSortieInscr = $qbId->getQuery()->getResult();
            foreach ($idSortieInscr as $key => $value) {
                $idSortieInscr[$key] = implode('', $value);
            }
            $idSortieInscr = implode(',', $idSortieInscr);

            $where[] = 's.id NOT IN ('.$idSortieInscr.')';
        }

        if ($filtres['passes'] == 'passes') {
            $where[] = 's.dateHeureDebut < :date';
            $param['date'] = new DateTime();
        }

        if (count($where) != 0) {
            $condition = $where[0];
            for ($i = 1; $i < count($where); $i++) {
                $condition .= ' or ' . $where[$i];
            }

            $qbSortie->andWhere($condition);
            foreach ($param as $key => $value) {
                $qbSortie->setParameter($key, $value);
            }
        }

        $query = $qbSortie->getQuery();
        return $query->execute();
    }
    
}
