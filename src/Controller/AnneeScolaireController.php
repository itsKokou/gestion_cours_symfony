<?php

namespace App\Controller;

use App\Entity\AnneeScolaire;
use App\Form\AnneeScolaireType;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AnneeScolaireRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AnneeScolaireController extends AbstractController
{
    #[Route('/annee/scolaire', name: 'app_annee_scolaire')]
    public function index(AnneeScolaireRepository $anneeRepository,PaginatorInterface $paginator,Request $request,SessionInterface $session): Response
    {
        $session->remove("AnneeLibelle");
        $query = $anneeRepository->prepareQueryForPagination();
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );
  
        return $this->render('annee_scolaire/index.html.twig', [
            'pagination' =>$pagination
        ]);
    }

    #[Route('/annee/scolaire/save/{id?}', name: 'app_annee_scolaire_save',methods:["POST","GET"])]
    public function save($id,AnneeScolaireRepository $anneeRepository,EntityManagerInterface $manager,Request $request,SessionInterface $session): Response
    {
        if ($id==null) {
            $annee = new AnneeScolaire();
        }else{
            $annee = $anneeRepository->find($id);
        }
        if($session->has("AnneeLibelle")){
            $anneeDebut = (int)$session->get("AnneeLibelle");
            $x = $anneeDebut + 1;
            $libelle = "{$anneeDebut}-{$x}";
            $annee->setLibelle($libelle);
        }
        $form = $this->createForm(AnneeScolaireType::class,$annee);
        if ($id != null) {
            $form->add('isArchived', null, [
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ]);
        }
        if($session->has("AnneeLibelle") || $id != null){
            $form->add('libelle',TextType::class,[
                "required"=>false,
                "attr"=>[
                    "class"=>"inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    'readonly' => true,
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
            ]);
        }else{
            $form->add('date',IntegerType::class,[
                "required"=>false,
                "mapped"=>false,
                "attr"=>[
                    "class"=>"anneeDebut inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
                "constraints"=>[
                    new NotBlank(message:"Veuillez renseigner l'année"), 
                    new GreaterThanOrEqual(value:2024, message: "L'année n'est pas valide")
                ]
            ]);
        }
        $success = null;
        $form->handleRequest($request);//Prends les valeur du forme et le met dans classe
        if ($form->isSubmitted() && $form->isValid()) {
            $success = "Année scolaire enregistré avec succès";
            if($annee->isIsActive()==true){
                $anScolaire = $anneeRepository->findOneBy(["isActive"=>true]);
                $anScolaire->setIsActive(false);
                $manager->persist($anScolaire);
                $manager->flush();
            }
            $manager->persist($annee);
            $manager->flush();
            $session->remove("AnneeLibelle");
        }
        return $this->render('annee_scolaire/form.html.twig', [
            'form'=> $form->createView(),//form est un objet, createView genere son code html
            'succes'=>$success,
        ]);
    }

    #[Route('/annee/show/libelle/{an?}', name: 'app_annee_scolaire_show_libelle')]
    public function showLibelleAnnee($an,Request $request,SessionInterface $session): Response {
        $session->set("AnneeLibelle",$an);
        return new JsonResponse($this->generateUrl("app_annee_scolaire_save"));
    }

    private function changeAnneeInSession(array $anneesInSession,int $id):array{
        foreach ($anneesInSession as $key=> $annee) {
            $anneesInSession[$key]->setIsActive($annee->getId()==$id);
            // if($annee->getId()==$id){
            //     $anneesInSession[$key]->setIsActive(true);
            // }else{
            //     $anneesInSession[$key]->setIsActive(false);
            // }
        }
        return $anneesInSession;
    }

    #[Route('/annee/change', name: 'app_annee_scolaire_change')]
    public function changeAnneeEncours(Request $request, AnneeScolaireRepository $repo,SessionInterface $session): Response {
        if($request->isXmlHttpRequest() || $request->query->get('id')!=0) {//requete asynchrone
            $id =(int) $request->query->get('id');
            $anneesInSession = $session->get("annees");
            $anneeEncours = $repo->find($id );//on
            $session->set('anneeEncours', $anneeEncours);
            $session->set('annees', $this->changeAnneeInSession($anneesInSession, $id));
            $session->remove('classeSelected');
        }
        
        return new JsonResponse($this->generateUrl($session->get("path")));
    }
}