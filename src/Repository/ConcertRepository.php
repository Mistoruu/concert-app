<?php

namespace App\Repository;

use App\Entity\Concert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class ConcertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Concert::class);
    }

    /**
     * Récupère la Query pour la pagination avec filtres optionnels
     */
    public function findByFilters(?string $genre, ?string $artistName): Query
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.artist', 'a')
            ->addSelect('a')
            ->orderBy('c.date', 'ASC');

        // Filtre par Genre (si présent)
        if ($genre) {
            $qb->andWhere('a.genre = :genre')
                ->setParameter('genre', $genre);
        }

        // Filtre par Nom d'artiste (si présent, recherche partielle)
        if ($artistName) {
            $qb->andWhere('a.name LIKE :artist')
                ->setParameter('artist', '%' . $artistName . '%');
        }

        return $qb->getQuery();
    }
}
