<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Concert;
use App\Entity\Artist;
use App\Form\ConcertType;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ConcertRepository;
use Knp\Component\Pager\PaginatorInterface;

final class ConcertController extends AbstractController
{
    #[Route('/concert', name: 'app_concert_index', methods: ['GET'])]
    public function index(
        ConcertRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $genre = $request->query->get('genre');
        $artistSearch = $request->query->get('artist');

        $query = $repository->findByFilters($genre, $artistSearch);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('concert/index.html.twig', [
            'pagination' => $pagination,
            // 4. On renvoie les valeurs actuelles pour pré-remplir le formulaire de filtre
            'currentGenre' => $genre,
            'currentArtist' => $artistSearch
        ]);
    }
    #[Route('/concert/new/{id}', name: 'app_concert_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Artist $artist, Request $request, EntityManagerInterface $em): Response
    {
        // SECURITÉ : On vérifie que Pierre ne crée pas un concert pour l'artiste de Paul
        if ($artist->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas programmer de concert pour cet artiste.");
        }

        $concert = new Concert();
        // On lie déjà l'artiste au concert
        $concert->setArtist($artist);

        $form = $this->createForm(ConcertType::class, $concert, [
            'user' => $this->getUser(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $genre = $artist->getGenre();
            $imageName = match ($genre) {
                'Rock' => 'rock.jpg',
                'Pop' => 'pop.jpeg',
                'Techno' => 'techno.jpeg',
                'Rap' => 'rap.jpeg',
                'Classique' => 'classic.jpeg',
                default => 'rock.jpg',
            };
            $concert->setImage($imageName);
            $em->persist($concert);
            $em->flush();

            $this->addFlash('success', 'Le concert de ' . $artist->getName() . ' a été programmé !');
            return $this->redirectToRoute('app_artist_manage', ['id' => $artist->getId()]);
        }

        return $this->render('concert/new.html.twig', [
            'form' => $form,
            'artist' => $artist
        ]);
    }
    #[Route('/concert/{id}', name: 'app_concert_show')]
    public function show(Concert $concert): Response
    {
        return $this->render('concert/show.html.twig', [
            'concert' => $concert,
        ]);
    }
    #[Route('/concert/{id}/edit', name: 'app_concert_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Concert $concert, Request $request, EntityManagerInterface $em): Response
    {
        // SECURITÉ : Vérifier si le user possède l'artiste de ce concert
        if ($concert->getArtist()->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Ce n'est pas votre concert !");
        }

        $form = $this->createForm(ConcertType::class, $concert, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Concert mis à jour !');

            return $this->redirectToRoute('app_artist_manage', ['id' => $concert->getArtist()->getId()]);
        }

        return $this->render('concert/edit.html.twig', [
            'form' => $form,
            'concert' => $concert
        ]);
    }

    #[Route('/concert/{id}/delete', name: 'app_concert_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Concert $concert, Request $request, EntityManagerInterface $em): Response
    {
        if ($concert->getArtist()->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Action interdite.");
        }

        if ($this->isCsrfTokenValid('delete' . $concert->getId(), $request->request->get('_token'))) {
            $artistId = $concert->getArtist()->getId();
            $em->remove($concert);
            $em->flush();
            $this->addFlash('danger', 'Concert supprimé.');

            return $this->redirectToRoute('app_artist_manage', ['id' => $artistId]);
        }

        return $this->redirectToRoute('app_home');
    }
}
