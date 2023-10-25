<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $phone = new Phone;
            $phone->setName('Téléphone n°' . $i);
            $phone->setDescription('Description du téléphone' . $i);
            $phone->setPrice($i);
            $manager->persist($phone);
        }

        $listclient = [];
        for ($i = 1; $i <= 3; $i++) {
            // Création de l'auteur lui-même.
            $client = new Client();
            $client->setName('Client n°' . $i);
            $manager->persist($client);
            // On sauvegarde l'auteur créé dans un tableau.
            $listclient[] = $client;
        }

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setFirstName("Firstname " . $i);
            $user->setLastName("Lastname" . $i);
            $user->setEmail('user' . $i . '@email.fr');
            $user->setPassword('passworduser' . $i);
            $user->setClient($listclient[array_rand($listclient)]);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
