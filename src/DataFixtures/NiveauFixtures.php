<?php

namespace App\DataFixtures;

use App\Entity\Niveau;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class NiveauFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $niveaux=["L1","L2","L3","M1","M2"];
        for ($i=0; $i < count($niveaux) ; $i++) { 
            $data=new Niveau();
            $data->setLibelle($niveaux[$i]);
            $data->setIsArchived(false);
            $this->addReference("Niveau".$i,$data);//la ref pour pouvoir le retrouver pour les relations dans bd
            $manager->persist($data);
        }
        $manager->flush();// la transaction de JPA
    }
}
