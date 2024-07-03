<?php

namespace App\Controller;

use App\Form\EtudiantType;
use App\Repository\ClasseRepository;
use App\Repository\AbsenceRepository;
use App\Repository\EtudiantRepository;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AnneeScolaireRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EtudiantController extends AbstractController
{
    #[Route('/etudiant', name: 'app_etudiant')]
    public function index(SessionInterface $session,PaginatorInterface $paginator,Request $request ,EtudiantRepository $etudiantRepository,
                    ClasseRepository $classeRepository,): Response {

        $htmlAbsence = $session->get("htmlAbsence");
        $session->remove("htmlAbsence");
        $htmlDossier = $session->get("htmlDossier");
        $session->remove("htmlDossier");

        $session->set("path","app_etudiant");//Annnee scolaire pour redirect apres change select
        $classe = null;
        if($session->has('classeEtudiant')){
            $classe= $session->get('classeEtudiant');
        }
        
        $selected=[
            'anneeScolaire'=>$session->get('anneeEncours')->getId(),
            'classe'=>$classe,
        ];

        $query = $etudiantRepository->prepareQueryForPagination($selected);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );
        
        return $this->render('etudiant/index.html.twig', [
            'selectedValue' => $selected,
            'pagination' => $pagination,
            'classes'=> $classeRepository->findBy(["isArchived"=>false]),
            'htmlAbsence' => $htmlAbsence,
            'htmlDossier' => $htmlDossier,
        ] );
    }

    #[Route('/etudiant/filtre/classe/{idC?}', name: 'etudiant_filtre_classe')]
    public function showEtudiantByClasse($idC,SessionInterface $session,Request $request ): Response {
        if($idC!=0) {
            $session->set("classeEtudiant",(int)$idC);
        }else{
            $session->remove("classeEtudiant");
        }
        return new JsonResponse($this->generateUrl('app_etudiant'));  
    }

    #[Route('/etudiant/absences/{id?}', name: 'app_etudiant_absences')]
    public function showEtudiantAbsences($id, SessionInterface $session, Request $request, EtudiantRepository $etudiantRepository, AbsenceRepository $absenceRepository, AnneeScolaireRepository $anneeScolaireRepository): Response
    {
        $htmlAbsence = null;
        if ($id != 0) {
            $etudiant = $etudiantRepository->find($id);
            $absences = $absenceRepository->findAbsencesByEtudiantAndAnneeScolaire([
                "etudiant"=>$etudiant->getId(),
                "anneeEncours"=>$session->get("anneeEncours"),
            ]);
            $htmlAbsence = $this->renderView('etudiant/absence.html.twig', ['etudiant' => $etudiant, 'absences' => $absences, 'anneeEncours'=> $anneeScolaireRepository->find($session->get("anneeEncours"))]);
        }
        $session->set('htmlAbsence', $htmlAbsence);
        return $this->redirectToRoute('app_etudiant');
    }

    #[Route('/etudiant/dossier/{id?}', name: 'app_etudiant_dossier')]
    public function showEtudiantDossier($id, SessionInterface $session, Request $request, EtudiantRepository $etudiantRepository,AnneeScolaireRepository $anneeScolaireRepository, InscriptionRepository $inscriptionRepository): Response
    {
        $htmlDossier = null;
        if ($id != 0) {
            $etudiant = $etudiantRepository->find($id);
            $inscription = $inscriptionRepository->findOneBy([
                'etudiant'=>$etudiant,
                'anneeScolaire'=>$anneeScolaireRepository->findOneBy(["isActive"=>true]),
            ]);
            $htmlDossier = $this->renderView('etudiant/dossier.html.twig', ['etudiant' => $etudiant,'inscription'=>$inscription ]);
        }
        $session->set('htmlDossier', $htmlDossier);
        return $this->redirectToRoute('app_etudiant');
    }


    #[Route('/etudiant/save/{id?}', name: 'app_etudiant_save')]
    public function save($id,Request $request, EtudiantRepository $etudiantRepo, SessionInterface $session, EntityManagerInterface $manager, UserPasswordHasherInterface $encoder)
    {   
        if($id!=null){
            $etudiant = $etudiantRepo->find($id);
            $form = $this->createForm(EtudiantType::class, $etudiant);
            $form->add('isArchived', null, [
                    'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Enregistrer',
                    "attr" => [
                        "class" => "px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                        "style" => "margin-left:90%",
                    ]
                    ]);
            $form->handleRequest($request); //Prends les valeur du forme et le met dans prof
            if ($form->isSubmitted() && $form->isValid()) {
                $etudiant->setPassword($encoder->hashPassword($etudiant, $etudiant->getPassword()));
                $manager->persist($etudiant);
                $manager->flush();
            }
            return $this->render('etudiant/form.html.twig', [
                'form' => $form->createView(), //form est un objet, createView genere son code html
            ]);
        }else{
            return $this->redirectToRoute("app_etudiant");
        }
        
    }

}
