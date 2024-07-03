<?php

namespace App\DataFixtures;

use App\Entity\Absence;
use App\Entity\Session;
use App\Entity\Etudiant;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
;

class AbsenceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $sessionRepository=$manager->getRepository(Session::class);
        $etudiantRepository=$manager->getRepository(Etudiant::class);
        $sessions = $sessionRepository->findAll();
        $etudiants = $etudiantRepository->findAll();
        for ($i = 1; $i <=6; $i++) {
            $absence = new Absence();
            $posE = rand(0, count($etudiants) -1);
            $posS = rand(0, count($sessions) -1);
            $absence->setEtudiant($etudiants[$posE]);
            $absence->setSession($sessions[$posS]);
        }
        $manager->flush();
    }

    public function getDependencies() {
        return array(
            SessionFixtures::class,
            EtudiantFixtures::class,
        );
    }
}
