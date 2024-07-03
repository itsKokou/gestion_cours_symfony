<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Form\ClasseType;
use App\Repository\ClasseRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClasseController extends AbstractController{

    
    #[Route('/classe', name: 'app_classe',methods:["POST","GET"])]
    public function index(SessionInterface $session,PaginatorInterface $paginator,Request $request ,ClasseRepository $classeRepository,
                    NiveauRepository $niveauRepository,FiliereRepository $filiereRepository): Response {
        $filiere=null;
        $niveau=null;
        if($session->has('niveauSelected')){
            $niveau= $session->get('niveauSelected');
        }
        if($session->has('filiereSelected')){
            $filiere= $session->get('filiereSelected');
        }

        $selected=[
            'filiere'=>$filiere,
            'niveau'=>$niveau
        ];

        $query = $classeRepository->prepareQueryForpagination($selected);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );
  
        return $this->render('classe/index.html.twig', [
            'selectedValue' => $selected,
            'filieres' => $filiereRepository->findBy(['isArchived'=> false]),
            'niveaux' =>$niveauRepository->findBy(['isArchived'=> false]),
            'pagination' =>$pagination
        ] );
    }

    #[Route('/classe/filtre/filiere/{idF?}', name: 'classe_filtre_filiere')]
    public function showClasseByFiliere($idF,SessionInterface $session,Request $request ): Response {
        if($idF!=0) {
            $session->set("filiereSelected",(int)$idF);
        }else{
            $session->remove("filiereSelected");
        }
        return new JsonResponse($this->generateUrl('app_classe'));  
    }

    #[Route('/classe/filtre/niveau/{idN?}', name: 'classe_filtre_niveau')]
    public function showClasseByNiveau($idN,SessionInterface $session,Request $request ): Response {
        if($idN!=0) {
            $session->set("niveauSelected",(int)$idN);
        }else{
            $session->remove("niveauSelected");
        }
        return new JsonResponse($this->generateUrl('app_classe'));  
    }

    #[Route('/classe/save/{id?}', name: 'app_classe_save',methods:["POST","GET"])]
    public function save($id,Request $request ,ClasseRepository $classeRepository, EntityManagerInterface $manager): Response {
        if ($id==null) {
            $classe = new Classe();
        }else{
            $classe = $classeRepository->find($id);
        }
        //$classe->setLibelle("Kokou");
        //Liaison de donnée entre formulaire et objet de type professeur
        //Mapping ou un Binding
        $form = $this->createForm(ClasseType::class,$classe);
        if($id!=null){
            $form->add('isArchived',null,[
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400']
            ]);
        }
        $form->handleRequest($request);//Prends les valeur du forme et le met dans classe
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($classe);
            $manager->flush();
        }
        return $this->render('classe/form.html.twig', [
            'form'=> $form->createView(),//form est un objet, createView genere son code html
        ]);
    }

    #[Route('/classe/change', name: 'app_classe_change')]
    public function changeClasseEncours(Request $request, ClasseRepository $classeRepository, SessionInterface $session): Response
    {
        $session->remove('sessionId');//Au chargement, effacer la déclaration en cours du prof
        if ($request->isXmlHttpRequest() || $request->query->get('id') != 0) { //requete asynchrone
            $id = (int) $request->query->get('id');
            $classeEncours = $classeRepository->find($id); //on
            $session->set('classeEncoursID', $classeEncours->getId());
        }else if ($request->query->get('id') == 0){
            $session->remove('classeEncoursID');
        }

        return new JsonResponse($this->generateUrl('app_session'));
    }
}
