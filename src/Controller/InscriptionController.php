<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\Inscription;
use App\Form\InscriptionType;
use App\Service\FileUploader;
use App\Repository\ClasseRepository;
use App\Repository\EtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InscriptionRepository;
use App\Repository\AnneeScolaireRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InscriptionController extends AbstractController
{

    #[Route('/inscription', name: 'app_inscription')]
    public function index(SessionInterface $session, PaginatorInterface $paginator, Request $request, InscriptionRepository $inscriptionRepository, AnneeScolaireRepository $anneeScolaireRepository, ClasseRepository $classeRepository): Response
    {
        $session->set("path", "app_inscription");
        $classe = null;
        $date = null;
        // dd($session->get("donneesPdf"));
        if ($session->has('classeSelected')) {
            $classe = $session->get('classeSelected');
        }
        if ($session->has('dateSelected')) {
            $date = $session->get('dateSelected');
        }
        $selected = [
            'anneeScolaire' => $session->get('anneeEncours')->getId(),
            'classe' => $classe,
            'date' => $date,
        ];
        $queryInscriptions = $inscriptionRepository->prepareQueryForpagination($selected);
        //FOR PDF
        $donnees = $queryInscriptions->getQuery()->getResult();
        $session->set("donneesPdf",$donnees);
        //FOR PDF
        $pagination = $paginator->paginate(
            $queryInscriptions, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );

        return $this->render('inscription/index.html.twig', [
            'selectedValue' => $selected,
            "pagination" => $pagination,
            'classes' => $classeRepository->findBy(["isArchived" => false]),
            "htmlSwal" => $session->get("ArchiverSwal") ?: null,
        ]);
    }

    #[Route('/inscription/classe/{id?}', name: 'inscription_filtre_classe')]
    public function showInscriptionByClasse($id, ClasseRepository $repoClasse, SessionInterface $session, Request $request): Response
    {
        if ($id != 0) {
            $session->set("classeSelected", (int) $id);
        } else {
            $session->remove("classeSelected");
        }
        return new JsonResponse($this->generateUrl('app_inscription'));
    }

    #[Route('/inscription/date/{date?}', name: 'inscription_filtre_date')]
    public function showInscriptionByDate($date, SessionInterface $session, Request $request): Response
    {
        // $date = $request->getUri();
        if ($date != null) {
            $session->set("dateSelected", $date);
        } else {
            $session->remove("dateSelected");
        }
        return new JsonResponse($this->generateUrl('app_inscription'));
    }

    #[Route('/inscription/archiver/vider', name: 'inscription_archiver_vider')]
    public function viderarchiverInscription(SessionInterface $session, Request $request): Response
    {
        $session->remove("ArchiverSwal");
        $session->remove("inscriptionArchiver");
        return new JsonResponse($this->generateUrl('app_inscription'));
    }

    #[Route('/inscription/archiver/{id?}', name: 'inscription_archiver')]
    public function archiverInscription($id, SessionInterface $session, Request $request, InscriptionRepository $inscriptionRepository, EntityManagerInterface $manager)
    {

        if ($session->has("inscriptionArchiver")) {
            $ins = $inscriptionRepository->find((int) $session->get("inscriptionArchiver"));
            $ins->setIsArchived(true);
            $manager->persist($ins);
            $manager->flush();
            $session->remove("ArchiverSwal");
            $session->remove("inscriptionArchiver");
        } else {
            $ins = $inscriptionRepository->find((int) $id);
            $swal = [
                "etudiant" => $ins->getEtudiant()->getNomComplet(),
                "matricule" => $ins->getEtudiant()->getMatricule(),
                "classe" => $ins->getClasse()->getLibelle()
            ];
            $session->set("inscriptionArchiver", $id);
            $session->set("ArchiverSwal", $swal);
        }

        return new JsonResponse($this->generateUrl('app_inscription'));
    }

    #[Route('/inscription/save', name: 'app_inscription_save')]
    public function save(Request $request, InscriptionRepository $inscriptionRepository, EtudiantRepository $etudiantRepo,
        AnneeScolaireRepository $anneeScolaireRepository, SessionInterface $session, EntityManagerInterface $manager,
        UserPasswordHasherInterface $encoder, FileUploader $fileUploader)
    {

        $inscription = new Inscription();

        $form = $this->createForm(InscriptionType::class, $inscription);
        $form->add('photo', FileType::class, [
            "mapped" => false,
            "required" => false,
            "label" => "Photo",
            "attr" => [
                "class" => "inline-flex w-full h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
            ],
            'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
            "constraints" => [
                new File([
                    'mimeTypes' => [
                        'image/*',
                    ],
                    'mimeTypesMessage' => "Veuillez choisir une photo ",
                ])
            ]
        ]);
        $form->handleRequest($request); //Prends les valeur du forme et le met dans prof
        $errorInscription = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->getEtudiant()->setMatricule("MAT0" . count($etudiantRepo->findAll()) + 1);
            $inscription->getEtudiant()->setPassword($encoder->hashPassword($inscription->getEtudiant(), $inscription->getEtudiant()->getPassword()));
            $anneeEncours = $anneeScolaireRepository->findOneBy(["isActive" => true]);
            $inscription->setAnneeScolaire($anneeEncours);

            //Update effectif de la classe 
            $inscription->getClasse()->setEffectif($inscription->getClasse()->getEffectif() + 1);

            // recuperer la photo de l'etudiant
            $photo = $form->get('photo')->getData();
            if ($photo) {
                $photoNom = $fileUploader->upload($photo, strtolower($inscription->getEtudiant()->getMatricule()));
                $inscription->getEtudiant()->setPhoto($photoNom);
            }

            $manager->persist($inscription);
            $manager->flush();
        }
        return $this->render('inscription/form.html.twig', [
            'form' => $form->createView(), //form est un objet, createView genere son code html
            'entete' => "Inscription",
        ]);
    }

    #[Route('/reinscription/matricule/{mat?}', name: 'app_inscription_matricule')]
    public function EnterMatriculeForReinscription($mat, SessionInterface $session, Request $request): Response
    {
        $session->remove("matChosen");
        if ($mat != null) {
            $session->set("matChosen", $mat);
        }
        return new JsonResponse($this->generateUrl('app_inscription_save_reinscription'));
    }

    #[Route('/reinscription', name: 'app_inscription_save_reinscription')]
    public function reinscription(SessionInterface $session, AnneeScolaireRepository $anneeScolaireRepository, Request $request, InscriptionRepository $inscriptionRepository, EtudiantRepository $etudiantRepo, EntityManagerInterface $manager, UserPasswordHasherInterface $encoder)
    {
        $inscription = new Inscription();
        $etudiant = null;
        $errorInscription = null;
        if ($session->has('matChosen')) {
            $etudiant = $etudiantRepo->findOneBy(["matricule" => $session->get('matChosen')]);
            $inscription->setEtudiant($etudiant);
        }

        $form = $this->createForm(InscriptionType::class, $inscription);
        $form->get('etudiant')->add('matricule', TextType::class, [
            "attr" => [
                "class" => "matricule inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
            ],
            'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
            'constraints' => [
                new NotBlank(), new Length(min: 5)
            ]
        ]);
        //dd($form);
        $form->handleRequest($request); //Prends les valeur du forme et le met dans prof
        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->getEtudiant()->setPassword($encoder->hashPassword($inscription->getEtudiant(), $inscription->getEtudiant()->getPassword()));
            $anneeEncours = $anneeScolaireRepository->findOneBy(["isActive" => true]);
            $inscription->setAnneeScolaire($anneeEncours);

            //Update effectif de la classe 
            $inscription->getClasse()->setEffectif($inscription->getClasse()->getEffectif() + 1);

            //verifier s'il a une inscription en cours
            $inscriptionExiste = $inscriptionRepository->findOneBy([
                "anneeScolaire" => $anneeEncours,
                "etudiant" => $inscription->getEtudiant(),
            ]);

            if ($inscriptionExiste == null) {
                $manager->persist($inscription);
                $manager->flush();
            } else {
                $errorInscription = "Cet étudiant a déjà une inscription en cours";
            }
            $session->remove('matChosen');
        }
        return $this->render('inscription/form.html.twig', [
            'form' => $form->createView(), //form est un objet, createView genere son code html
            'entete' => "Réinscription",
            'errorInscription' => $errorInscription,
        ]);
    }
}
