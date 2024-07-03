<?php

namespace App\Controller;

use App\Entity\Declaration;
use App\Form\DeclarationType;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeclarationController extends AbstractController
{
    #[Route('/declaration', name: 'app_declaration')]
    public function index(): Response
    {
        return $this->render('declaration/index.html.twig', [
            'controller_name' => 'DeclarationController',
        ]);
    }

    #[Route('/declaration/save', name: 'app_declaration_save', methods: ["POST", "GET"])]
    public function save(SessionInterface $session, Request $request,SessionRepository $sessionRepository ,EntityManagerInterface $manager): Response
    {
        $declaration = new Declaration();
        if($session->has('sessionId')){
            $declaration->setSession($sessionRepository->find($session->get('sessionId')));
            $declaration->setUser($this->getUser());
        }
        $form = $this->createForm(DeclarationType::class, $declaration);

        $form->handleRequest($request); //Prends les valeur du forme et le met dans classe
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($declaration);
            $manager->flush();
        }
        $htmlDeclaration = $this->renderView('declaration/form.html.twig', ['form' => $form->createView()]);
        $session->set("htmlDeclaration",$htmlDeclaration);
        return $this->redirectToRoute("app_session");
    }
}
