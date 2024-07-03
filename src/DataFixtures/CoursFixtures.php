<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Cours;
use App\Entity\Classe;
use App\Entity\Module;
use App\Entity\Semestre;
use App\Entity\Professeur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
;

class CoursFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $classeRepository=$manager->getRepository(Classe::class);
        $moduleRepository=$manager->getRepository(Module::class);
        $professeurRepository=$manager->getRepository(Professeur::class);
        $semestreRepository=$manager->getRepository(Semestre::class);
        $classes = $classeRepository->findAll();
        $modules = $moduleRepository->findAll();
        $professeurs = $professeurRepository->findAll();
        $semestres = $semestreRepository->findAll();
        for ($i = 1; $i <=6; $i++) {
            $cours = new Cours();
            $cours->setSemestre($semestres[rand(0, count($semestres) -1)]);
            $cours->setModule($modules[rand(0, count($modules) -1)]);
            $cours->setProfesseur($professeurs[rand(0, count($professeurs) -1)]);
            $cours->setCreateAt(new \DateTimeImmutable());
            $cours->setNbreHeure(40);
            $cours->setNbreHeureRestant(36);
            if($i%2==0){
                for ($i=1; $i <= 2; $i++) { 
                    $cours->addClass($classes[rand(0, count($classes) -1)]);
                }
            }else{
                $cours->addClass($classes[rand(0, count($classes) -1)]);
            }
            $manager->persist($cours);
            $this->setReference("Cours".$i, $cours);
        }
        $manager->flush();
    }

    public function getDependencies() {
        return array(
            ClasseFixtures::class,
            ModuleFixtures::class,
            ProfesseurFixtures::class,
            SemestreFixtures::class,
        );
    }
} 
