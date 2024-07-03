<?php

namespace App\DataFixtures;

use App\Entity\Filiere;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class FiliereFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $filieres=["IAGE","GLRS","MAIE","TTL","CPD","MOSIEF","CDSD"];
        for ($i=0; $i <count($filieres) ; $i++) { 
            $data=new Filiere();
            $data->setLibelle($filieres[$i]);
            $data->setIsArchived(false);
            $this->addReference("Filiere".$i,$data);
            $manager->persist($data);
        }
        $manager->flush();
    }
}
