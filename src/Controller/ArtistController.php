<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class ArtistController extends AbstractController
{
    #[Route('/artist', name: 'app_artist')]
    public function index(): Response
    {
        return $this->render('artist/index.html.twig', [
            'controller_name' => 'ArtistController',
        ]);
    }
    #[Route('/profile/artist/new', name: 'app_artist_new')]
    public function newArtist(Request $request, EntityManagerInterface $em): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Logique d'attribution automatique de l'image
            $genre = $artist->getGenre();
            $imageName = match ($genre) {
                'Rock' => 'rock.jpeg',
                'Pop' => 'pop.webp',
                'Techno' => 'techno.jpeg',
                'Rap' => 'rap.jpeg',
                'Classique' => 'classic.jpeg',
                default => 'default.jpeg',
            };

            $artist->setImage($imageName);
            $artist->setOwner($this->getUser());

            $em->persist($artist);
            $em->flush();

            $this->addFlash('success', 'Page artiste créée avec l\'image associée au style ' . $genre);
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/new_artist.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/artist/{id}/manage', name: 'app_artist_manage')]
    #[IsGranted('ROLE_USER')]
    public function manage(Artist $artist): Response
    {
        if ($artist->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de gérer cet artiste.");
        }

        return $this->render('artist/manage.html.twig', [
            'artist' => $artist,
            'concerts' => $artist->getConcerts(),
        ]);
    }

}
