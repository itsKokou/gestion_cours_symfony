<?php

namespace App\DataFixtures;

use App\Entity\Salle;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
;

class SalleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       $salles=["Incub","101","402","302","204","202","401"];
       $places= [58,48,60,40,30,56,60];
        for ($i=0; $i < count($salles) ; $i++) { 
            $data=new Salle();
            $data->setLibelle($salles[$i]);
            $data->setNbrePlace($places[$i]);
            $this->addReference("Salle".$i,$data);//la ref pour pouvoir le retrouver pour les relations dans bd
            $manager->persist($data);
        }
        $manager->flush();// la transaction de JPA
    }
}
