<?php

namespace App\DataFixtures;

use App\Entity\AnneeScolaire;
use App\Entity\Classe;
use App\Entity\Etudiant;
use App\Entity\Inscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InscriptionFixtures extends Fixture implements DependentFixtureInterface{

    public function load(ObjectManager $manager): void{

        $classeRepository=$manager->getRepository(Classe::class);
        $etudiantRepository=$manager->getRepository(Etudiant::class);
        $anneeScolaireRepository=$manager->getRepository(AnneeScolaire::class);
        $etudiants = $etudiantRepository->findAll();
        $classes = $classeRepository->findAll();
        $anneeScolaires = $anneeScolaireRepository->findAll();

        for ($i = 1; $i <=10; $i++) {
            $inscription = new Inscription();
            $inscription->setCreateAt(new \DateTimeImmutable());
            $inscription->setAnneeScolaire($anneeScolaires[rand(0, count($anneeScolaires) -1)]);
            $inscription->setClasse($classes[rand(0, count($classes) -1)]);
            $inscription->setEtudiant($etudiants[rand(0, count($etudiants) -1)]);
            $manager->persist($inscription);
            $this->addReference("Inscription".$i, $inscription);
        }

        $manager->flush();
    }

    public function getDependencies() {
        return array(
            ClasseFixtures::class,
            EtudiantFixtures::class,
            AnneeScolaireFixtures::class
        );
    }
}
