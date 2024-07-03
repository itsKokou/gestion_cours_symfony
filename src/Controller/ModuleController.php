<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModuleController extends AbstractController
{
    #[Route('/module/{id?}', name: 'app_module')]
    public function index($id, PaginatorInterface $paginator,ModuleRepository $moduleRepository,Request $request, EntityManagerInterface $manager): Response
    {
        if ($id == null) {
            $module = new Module();
        } else {
            $module = $moduleRepository->find($id);
        }
        
        $form = $this->createForm(ModuleType::class, $module);
        if ($id != null) {
            $form->add('isArchived', null, [
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ]);
        }
        $form->handleRequest($request); //Prends les valeur du forme et le met dans module
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($module);
            $manager->flush();
        }
       
        $query = $moduleRepository->prepareQueryForpagination();
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );

        return $this->render('module/index.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }
}
