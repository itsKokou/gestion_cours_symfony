<?php

namespace App\Controller;

use App\Entity\Enseignement;
use App\Entity\Professeur;
use App\Form\ProfesseurType;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ClasseRepository;
use App\Repository\EnseignementRepository;
use App\Repository\ModuleRepository;
use App\Repository\ProfesseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProfesseurController extends AbstractController{

    #[Route('/professeur', name: 'app_professeur',methods:["POST","GET"])]
    public function index(SessionInterface $session,PaginatorInterface $paginator,Request $request ,ProfesseurRepository $professeurRepository,
                    ClasseRepository $classeRepository,ModuleRepository $moduleRepository): Response {
        $htmlValueClasse = $session->get('htmlClasse');
        $session->remove('htmlClasse');
        $htmlValueModule = $session->get('htmlModule');
        $session->remove('htmlModule');
        $module = null;
        $classe = null;
        $grade = null;
       
        if($session->has('moduleSelected')){
            $module= $session->get('moduleSelected');
        }
        if($session->has('classeSelected')){
            $classe= $session->get('classeSelected');
        }
        if($session->has('gradeSelected')){
            $grade= $session->get('gradeSelected');
        }
        
        $selected=[
            'classe'=>$classe,
            'module'=>$module,
            'grade'=>$grade,
        ];

        $query = $professeurRepository->prepareQueryForpagination($selected);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );
        
        return $this->render('professeur/index.html.twig', [
            'selectedValue' => $selected,
            'pagination' => $pagination,
            'grades'=> $professeurRepository->getGrades(),
            'classes'=> $classeRepository->findBy(["isArchived"=>false]),
            'modules'=> $moduleRepository->findBy(["isArchived"=>false]),
            'htmlValueClasse'=> $htmlValueClasse,
            'htmlValueModule'=> $htmlValueModule,
        ] );
    }

    #[Route('/professeur/filtre/classe/{idC?}', name: 'professeur_filtre_classe')]
    public function showProfesseurByClasse($idC,SessionInterface $session,Request $request ): Response {
        if($idC!=0) {
            $session->set("classeSelected",(int)$idC);
        }else{
            $session->remove("classeSelected");
        }
        return new JsonResponse($this->generateUrl('app_professeur'));  
    }

    #[Route('/professeur/filtre/module/{idM?}', name: 'professeur_filtre_module')]
    public function showProfesseurByModule($idM,SessionInterface $session,Request $request ): Response {
        if($idM!=0) {
            $session->set("moduleSelected",(int)$idM);
        }else{
            $session->remove("moduleSelected");
        }
        return new JsonResponse($this->generateUrl('app_professeur'));  
    }

    #[Route('/professeur/filtre/grade/{grade?}', name: 'professeur_filtre_grade')]
    public function showProfesseurByGrade($grade,SessionInterface $session,Request $request ): Response {
        if($grade!=0) {
            $session->set("gradeSelected",$grade);
        }else{
            $session->remove("gradeSelected");
        }
        return new JsonResponse($this->generateUrl('app_professeur'));  
    }

    #[Route('/professeur/details/{id?}', name: 'app_professeur_details')]
    public function showProfesseurDetails($id,SessionInterface $session,Request $request,ProfesseurRepository $professeurRepository,EnseignementRepository $enseignementRepository,AnneeScolaireRepository $anneeScolaireRepository ): Response {
        $htmlClasse=null;
        $htmlModule=null;
        if($id!=0) {
            $prof= $professeurRepository->find($id);
            $enseignement = $enseignementRepository->findOneBy([
                'professeur'=>$prof,
                'anneeScolaire'=> $anneeScolaireRepository->findOneBy(['isActive'=>true]),
            ]);
            $htmlClasse= $this->renderView('professeur/detailClasse.html.twig',['professeur'=>$prof, 'enseignement'=>$enseignement]);
            $htmlModule= $this->renderView('professeur/detailModule.html.twig',['professeur'=>$prof, 'enseignement'=>$enseignement]);
        }
        $session->set('htmlClasse',$htmlClasse);
        $session->set('htmlModule',$htmlModule);
        return $this->redirectToRoute('app_professeur');  
    }
    

    //Ajout Et La modification
    //'/professeur/save/{id?} = ce path contient un parametre qui s'appelle id; ?= parametre facultatif
    #[Route('/professeur/save/{id?}', name: 'app_professeur_save',methods:["POST","GET"])]
    public function save($id,Request $request ,ProfesseurRepository $professeurRepository, EntityManagerInterface $manager,UserPasswordHasherInterface $encoder,
    AnneeScolaireRepository $anneeScolaireRepository): Response {
        if ($id==null) {
            $prof = new Professeur();
        }else{
            $prof = $professeurRepository->find($id);
        }
        //$prof->setNomComplet("Kokou Ok");
        //Liaison de donnée entre formulaire et objet de type professeur
        //Mapping ou un Binding
        $form = $this->createForm(ProfesseurType::class,$prof);
        if ($id != null) {
            $form->add('isArchived', null, [
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ]);
        }
        $form->handleRequest($request);//Prends les valeur du forme et le met dans prof
        if ($form->isSubmitted() && $form->isValid()) {
            $enseignement = new Enseignement();
            $enseignement->setProfesseur($prof);
            $enseignement->setAnneeScolaire($anneeScolaireRepository->findOneBy(["isActive"=>true]));
            $modules = $form->get("modules")->getData()->getValues();
            $classes = $form->get("classes")->getData()->getValues();
            foreach ($modules as $module) {
                $enseignement->addModule($module);
            }
            foreach ($classes as $classe) {
                $enseignement->addClass($classe);
            }
            $prof->addEnseignement($enseignement);
            $prof->setPassword($encoder->hashPassword($prof, $prof->getPassword()));
            $manager->persist($prof);
            $manager->flush();
        }
        return $this->render('professeur/form.html.twig', [
            'form'=> $form->createView(),//form est un objet, createView genere son code html
        ]);
    }

    #[Route('/professeur/change', name: 'app_professeur_change')]
    public function changeClasseEncours(Request $request, ProfesseurRepository $professeurRepository, SessionInterface $session): Response
    {
        $session->remove('sessionId'); //Au chargement, effacer les operations en cours du prof
        $session->remove("voir");
        if ($request->isXmlHttpRequest() || $request->query->get('idProf') != 0) { //requete asynchrone
            $id = (int) $request->query->get('idProf');
            $session->set('profEncoursID', $id);
        } else if ($request->query->get('idProf') == 0) {
            $session->remove('profEncoursID');
        }
        return new JsonResponse($this->generateUrl('app_session'));
    }
}