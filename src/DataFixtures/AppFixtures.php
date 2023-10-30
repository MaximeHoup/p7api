<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $phone = new Phone();
            $phone->setName('Téléphone n°'.$i);
            $phone->setDescription('Description du téléphone'.$i);
            $phone->setPrice($i);
            $manager->persist($phone);
        }

        $listclient = [];
        $client = new Client();
        $client->setName('Client');
        $client->setEmail('client@mail.fr');
        $client->setPassword($this->userPasswordHasher->hashPassword($client, 'password'));
        $client->setRoles(['ROLE_USER']);
        $manager->persist($client);
        $listclient[] = $client;

        $clientAdmin = new Client();
        $clientAdmin->setName('Client admin');
        $clientAdmin->setEmail('clientadmin@mail.fr');
        $clientAdmin->setPassword($this->userPasswordHasher->hashPassword($clientAdmin, 'password'));
        $clientAdmin->setRoles(['ROLE_ADMIN']);
        $manager->persist($clientAdmin);
        $listclient[] = $clientAdmin;

        for ($i = 1; $i <= 10; ++$i) {
            $user = new User();
            $user->setFirstName('Firstname '.$i);
            $user->setLastName('Lastname'.$i);
            $user->setEmail('user'.$i.'@email.fr');
            $user->setClient($listclient[array_rand($listclient)]);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
