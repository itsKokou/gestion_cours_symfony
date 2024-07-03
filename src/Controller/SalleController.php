<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Form\SalleType;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle')]
    public function index(PaginatorInterface $paginator, SalleRepository $salleRepository, Request $request): Response
    {
        $query = $salleRepository->prepareQueryForPagination();
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );

        return $this->render('salle/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/salle/save/{id?}', name: 'app_salle_save', methods: ["POST", "GET"])]
    public function save($id, Request $request, SalleRepository $salleRepository, EntityManagerInterface $manager): Response
    {
        if ($id == null) {
            $salle = new Salle();
        } else {
            $salle = $salleRepository->find($id);
        }
        //$classe->setLibelle("Kokou");
        //Liaison de donnée entre formulaire et objet de type professeur
        //Mapping ou un Binding
        $form = $this->createForm(SalleType::class, $salle);
        if ($id != null) {
            $form->add('isArchived', null, [
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ]);
        }
        $form->handleRequest($request); //Prends les valeur du forme et le met dans classe
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($salle);
            $manager->flush();
        }
        return $this->render('salle/form.html.twig', [
            'form' => $form->createView(), //form est un objet, createView genere son code html
        ]);
    }
}
