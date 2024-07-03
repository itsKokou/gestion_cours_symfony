<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixtures extends Fixture{
    private $encoder;
    
    public function __construct(UserPasswordHasherInterface $encoder){
        $this->encoder=$encoder;
    }
    
    public function load(ObjectManager $manager): void{

        $roles=["ROLE_AC","ROLE_RP","ROLE_ADMIN"];
        $plainPassword = 'passer';//Le password saisi par user
        for ($i = 1; $i <=5; $i++) {
            $user = new User();
            $pos= rand(0,2);
            $user->setNomComplet('User Kokou '.$i);
            $user->setEmail("user".$i."@gmail.com");
            $encoded = $this->encoder->hashPassword($user,
            $plainPassword);
            $user->setPassword($encoded);
            $user->setRoles([$roles[$pos]]);
            $manager->persist($user);
            $this->addReference("User".$i, $user);
        }

        $manager->flush();
    }
}
