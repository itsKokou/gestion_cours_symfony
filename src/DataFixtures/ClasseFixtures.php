<?php

namespace App\DataFixtures;

use App\Entity\Classe;
use App\Entity\Filiere;
use App\Entity\Niveau;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


class ClasseFixtures extends Fixture implements DependentFixtureInterface {
    public function load(ObjectManager $manager): void{

        $filiereRepository=$manager->getRepository(Filiere::class);
        $niveauRepository=$manager->getRepository(Niveau::class);
        $filieres = $filiereRepository->findAll();
        $niveaux = $niveauRepository->findAll();
        for ($i=1; $i <=7 ; $i++) {
            $posN = rand(0,count($niveaux) -1);
            $posF = rand(0,count($filieres) -1);
            $data=new Classe();
            $data->setFiliere($filieres[$posF]);
            $data->setNiveau($niveaux[$posN]);
            $data->setLibelle($niveaux[$posN]->getLibelle()." ".$filieres[$posF]->getLibelle());
            //$data->setIsArchived(false);
            $this->addReference("Classe".$i,$data);
           $manager->persist($data);
        }
        $manager->flush();

        // for ($i=1; $i <= 10; $i++) { 
        //     $posN= rand(0,4);
        //     $posF= rand(0,6);
        //     $filiere = $this->getReference("Filiere".$posF);
        //     $niveau = $this->getReference("Niveau".$posN);

        //     $code = substr(str_shuffle("ABCD"),0,1);

        //     $data=new Classe();
        //     $data->setFiliere($filiere);
        //     $data->setNiveau($niveau);
        //     $data->setLibelle($niveau->getLibelle()." ".$filieres.$code);
        //     $data->setIsArchived(false);
        //     $this->addReference("Classe".$i,$data);
        //    $manager->persist($data);
        // }
        // $manager->flush();

    }

    public function getDependencies() {
        return array(
            NiveauFixtures::class,
            FiliereFixtures::class,
        );
    }
}
