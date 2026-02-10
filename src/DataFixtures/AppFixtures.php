<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Artist;
use App\Entity\Concert;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création de l'ADMIN
        $admin = new User();
        $admin->setEmail('admin@concert.com')
            ->setUsername('Administrateur')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // 2. Création de l'utilisateur Pierre pour l'example (le propriétaire des artistes)
        $pierre = new User();
        $pierre->setEmail('pierre@test.com')
            ->setUsername('Pierre_Music')
            ->setPassword($this->hasher->hashPassword($pierre, 'password123'));
        $manager->persist($pierre);

        $artistData = [
            ['name' => 'The Rolling Stones', 'genre' => 'Rock', 'img' => 'rock.jpeg'],
            ['name' => 'Dua Lipa', 'genre' => 'Pop', 'img' => 'pop.webp'],
            ['name' => 'Amelie Lens', 'genre' => 'Techno', 'img' => 'techno.jpeg'],
            ['name' => 'SCH', 'genre' => 'Rap', 'img' => 'rap.jpeg'],
            ['name' => 'Lang Lang', 'genre' => 'Classique', 'img' => 'classic.jpeg'],
        ];

        $concertImages = [
            'Rock' => 'rock.jpg',
            'Pop' => 'pop.jpeg',
            'Techno' => 'techno.jpeg',
            'Rap' => 'rap.jpeg',
            'Classique' => 'classic.jpeg',
        ];

        $artists = [];
        foreach ($artistData as $data) {
            $artist = new Artist();
            $artist->setName($data['name'])
                ->setGenre($data['genre'])
                ->setImage($data['img'])
                ->setBiography("Bio de style " . $data['genre'])
                ->setOwner($pierre);

            $manager->persist($artist);
            $artists[] = $artist;
        }

        for ($i = 1; $i <= 20; $i++) {
            $concert = new Concert();
            $randomArtist = $artists[array_rand($artists)];

            $concert->setTitle("Concert " . $randomArtist->getGenre() . " n°" . $i)
                ->setDescription("Description...")
                ->setDate(new \DateTimeImmutable("+" . ($i + 2) . " days"))
                ->setLieu("Salle " . $i)
                ->setArtist($randomArtist)
                ->setImage($concertImages[$randomArtist->getGenre()]);

            $manager->persist($concert);
        }

        $manager->flush();
    }
}
