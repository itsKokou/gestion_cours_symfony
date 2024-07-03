<?php

namespace App\Controller;

use App\Repository\AnneeScolaireRepository;
use App\Repository\ClasseRepository;
use App\Repository\ProfesseurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, AnneeScolaireRepository $anneeScolaireRepository, SessionInterface $session, ClasseRepository $classeRepository, ProfesseurRepository $professeurRepository): Response
    {
        if ($this->getUser()) {
            $annee = $anneeScolaireRepository->findAll();
            $session->set("annees", $annee);
            $session->set('anneeEncours', $anneeScolaireRepository->findOneBy(['isActive' => 1]));
            $classes = $classeRepository->findBy(["isArchived" => false]);
            if( in_array("ROLE_PROFESSEUR",$this->getUser()->getRoles())){
                $classes = $classeRepository->findClasseByProf($this->getUser()->getId());
            }
            $session->set("classes", $classes);
            // $session->set('classeEncours', $classes[0]);
            $session->set("professeurs",$professeurRepository->findBy(["isArchived" => false]));
            return $this->redirectToRoute('app_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        //$this->redirectToRoute("app_login");
    }
}
