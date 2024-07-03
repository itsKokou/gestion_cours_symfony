<?php

namespace App\DataFixtures;

use App\Entity\Classe;
use App\Entity\Module;
use App\Entity\Professeur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
;

class ProfesseurFixtures extends Fixture implements DependentFixtureInterface {
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder){
        $this->encoder=$encoder;
    }

    public function load(ObjectManager $manager): void
    {
        
        $grade=["MASTER","INGENIEUR","DOCTEUR"];
        $plainPassword = 'passer';
        $classeRepository=$manager->getRepository(Classe::class);
        $moduleRepository=$manager->getRepository(Module::class);
        $classes = $classeRepository->findAll();
        $modules = $moduleRepository->findAll();
        for ($i = 1; $i <=6; $i++) {
            $user = new Professeur();
            $pos = rand(0,2);
            $user->setNomComplet('Boss Kokou '.$i);
            $user->setEmail("professeur".$i."@gmail.com");
            $user->setSpecialite("specialite".$i);
            $encoded = $this->encoder->hashPassword($user,
            $plainPassword);
            $user->setPassword($encoded); 
            $user->setGrade($grade[$pos]);
            for ($p=1; $p <=2 ; $p++) { 
                $posM = rand(0,count($modules) -1);
                $posC = rand(0,count($classes) -1);
                $user->addClass($classes[$posC]);
                $user->addModule($modules[$posM]);
            }
            $manager->persist($user);
            $this->addReference("Professeur".$i, $user);
        }
        $manager->flush();
    }

    public function getDependencies() {
        return array(
            ClasseFixtures::class,
            ModuleFixtures::class,
        );
    }
    
}
