<?php

namespace App\DataFixtures;

use App\Entity\Etudiant;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
;

class EtudiantFixtures extends Fixture{

    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder){
        $this->encoder=$encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $plainPassword = 'passer';
        for ($i = 1; $i <=10; $i++) {
            $user = new Etudiant();
            $user->setNomComplet('Etudiant Kokou '.$i);
            $user->setEmail("etudiant".$i."@gmail.com");
            $encoded = $this->encoder->hashPassword($user,
            $plainPassword);
            $user->setPassword($encoded);
            $user->setTuteur("Tuteur".$i);
            $manager->persist($user);
            $this->addReference("Etudiant".$i, $user);
        }
        $manager->flush();
    }
}
