<?php

namespace App\DataFixtures;

use App\Entity\Semestre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class SemestreFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $semestres=["S1","S2","S3","S4","S5","S6"];
        for ($i=0; $i < count($semestres) ; $i++) { 
            $data=new Semestre();
            $data->setLibelle($semestres[$i]);
            $this->addReference("semestre".$i,$data);//la ref pour pouvoir le retrouver pour les relations dans bd
            $manager->persist($data);
        }
        $manager->flush();// la transaction de JPA
    }
}
