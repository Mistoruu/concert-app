<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Artist;
use App\Entity\Concert;
use App\Repository\UserRepository;
use App\Repository\ArtistRepository;
use App\Repository\ConcertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'app_admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(
        UserRepository $userRepo,
        ArtistRepository $artistRepo,
        ConcertRepository $concertRepo
    ): Response {
        return $this->render('admin/index.html.twig', [
            'users_count' => $userRepo->count([]),
            'artists_count' => $artistRepo->count([]),
            'concerts_count' => $concertRepo->count([]),
            'latest_users' => $userRepo->findBy([], ['id' => 'DESC'], 5)
        ]);
    }

    #[Route('/users', name: 'users')]
    public function manageUsers(UserRepository $userRepo): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepo->findBy([], ['id' => 'DESC'])
        ]);
    }

    #[Route('/users/{id}/role', name: 'user_toggle_role', methods: ['POST'])]
    public function toggleRole(User $user, EntityManagerInterface $em): Response
    {
        // Empêcher de s'enlever son propre rôle admin
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier vos propres droits.');
            return $this->redirectToRoute('app_admin_users');
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $user->setRoles(['ROLE_USER']);
            $this->addFlash('success', 'Rôle Admin retiré à ' . $user->getUsername());
        } else {
            $user->setRoles(['ROLE_ADMIN']);
            $this->addFlash('success', $user->getUsername() . ' est désormais Admin !');
        }

        $em->flush();
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/artists', name: 'artists')]
    public function manageArtists(ArtistRepository $artistRepo): Response
    {
        return $this->render('admin/artists.html.twig', [
            'artists' => $artistRepo->findBy([], ['id' => 'DESC'])
        ]);
    }

    #[Route('/concerts', name: 'concerts')]
    public function manageConcerts(ConcertRepository $concertRepo): Response
    {
        return $this->render('admin/concerts.html.twig', [
            'concerts' => $concertRepo->findBy([], ['id' => 'DESC'])
        ]);
    }

    #[Route('/entity/{type}/{id}/delete', name: 'entity_delete', methods: ['POST'])]
    public function deleteEntity(string $type, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $entityClass = match($type) {
            'artist' => Artist::class,
            'concert' => Concert::class,
            default => null
        };

        if (!$entityClass) {
            throw $this->createNotFoundException('Type entité invalide');
        }

        $entity = $em->getRepository($entityClass)->find($id);

        if ($entity && $this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $em->remove($entity);
            $em->flush();
            $this->addFlash('success', ucfirst($type) . ' supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_' . $type . 's');
    }
}
