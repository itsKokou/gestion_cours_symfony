<?php

namespace App\DataFixtures;

use App\Entity\AnneeScolaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class AnneeScolaireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void{

        for($i = 17; $i < 24; $i++){
            $anneeScolaire = new AnneeScolaire();
            $anneeScolaire->setLibelle("20".$i."-20".$i+1);
            $this->addReference("AnneeScolaire".$i,$anneeScolaire);
            $manager->persist($anneeScolaire);
        }    
        $manager->flush();
    }
}
