<?php

namespace App\DataFixtures;

use App\Entity\Cours;
use App\Entity\Salle;
use App\Entity\Session;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
;

class SessionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $coursRepository=$manager->getRepository(Cours::class);
        $salleRepository=$manager->getRepository(Salle::class);
        $salles = $salleRepository->findAll();
        $courss = $coursRepository->findAll();

       for ($i = 1; $i <=8; $i++) {
            $session = new Session();
            $session->setCours($courss[rand(0, count($courss) -1)]);
            $session->setDate(new \DateTimeImmutable("2023-11-1".$i+8));
            $session->setHeureD(new \DateTime("08:00"));
            $session->setHeureF(new \DateTime("12:00"));
            if($i%2==0){
                $session->setSalle($salles[rand(0, count($salles) -1)]);
            }else{
                $session->setCodeSession("DC25".$i);
            }
            $manager->persist($session);
            $this->addReference("Session".$i, $session);
        }
        $manager->flush();
    }

    public function getDependencies() {
        return array(
            CoursFixtures::class,
            SalleFixtures::class,
        );
    }
}
