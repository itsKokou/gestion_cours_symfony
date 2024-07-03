<?php

namespace App\DataFixtures;

use App\Entity\Module;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class ModuleFixtures extends Fixture{

    public function load(ObjectManager $manager): void{

        $modules=["JAVA","ALGO","MATH","UML","PYTHON","C++","ANGLAIS"];
        for ($i=0; $i < count($modules) ; $i++) { 
            $data=new Module();
            $data->setLibelle($modules[$i]);
            $this->addReference("Module".$i,$data);//la ref pour pouvoir le retrouver pour les relations dans bd
            $manager->persist($data);
        }
        $manager->flush();// la transaction de JPA
    }
}
