<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Classe;
use App\Form\CoursType;
use App\Entity\Professeur;
use App\Repository\AnneeScolaireRepository;
use App\Repository\CoursRepository;
use App\Repository\ClasseRepository;
use App\Repository\ModuleRepository;
use App\Repository\NiveauRepository;
use App\Repository\SemestreRepository;
use App\Repository\ProfesseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Constraint\IsEmpty;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CoursController extends AbstractController
{
    #[Route('/cours', name: 'app_cours')]
    public function index(
        SessionInterface $session,
        PaginatorInterface $paginator,
        Request $request,
        CoursRepository $coursRepository,
        ClasseRepository $classeRepository,
        NiveauRepository $niveauRepository,
        SemestreRepository $semestreRepository
    ): Response {
        $session->set("path", "app_cours"); //for annee change
        /** For save */
        $session->remove('semestreChosen');
        $session->remove('moduleChosen');
        $session->remove('profChosen');
        //Vider session pour save seance cours
        $session->remove("coursID");
        $session->remove("dateChosen");
        $session->remove("heureDChosen");
        $session->remove("heureFChosen");
        $session->remove("isProf");
        $session->remove("profChosen");
        $session->remove('lieu');
        $session->remove('salleChosen');
        $session->remove('codeChosen');
        /** */

        $classe = null;
        $niveau = null;
        $semestre = null;
        if ($session->has('classeSelected')) {
            $classe = $session->get('classeSelected');
        }
        if ($session->has('niveauSelected')) {
            $niveau = $session->get('niveauSelected');
        }
        if ($session->has('semestreSelected')) {
            $semestre = $session->get('semestreSelected');
        }

        $selected = [
            'anneeScolaire' => $session->get("anneeEncours"),
            'classe' => array_map('intval', explode(',', $classe)),
            'niveau' => $niveau,
            'semestre' => $semestre,
        ];
        // $ok = $coursRepository->findBy(["classes"=>[1,2]]);

        $query = $coursRepository->prepareQueryForPagination($selected);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );

        return $this->render('cours/index.html.twig', [
            'selectedValue' => $selected,
            'pagination' => $pagination,
            'classes' => $classeRepository->findBy(["isArchived" => false]),
            'niveaux' => $niveauRepository->findBy(["isArchived" => false]),
            'semestres' => $semestreRepository->findBy(["isArchived" => false]),
        ]);
    }

    #[Route('/cours/filtre/classe/{id?}', name: 'cours_filtre_classe')]
    public function showCoursByClasse($id, SessionInterface $session, Request $request): Response
    {
        if (!empty($id)) {
            $session->set("classeSelected", $id);
        } else {
            $session->remove("classeSelected");
        }
        return new JsonResponse($this->generateUrl('app_cours'));
    }

    #[Route('/cours/filtre/niveau/{id?}', name: 'cours_filtre_niveau')]
    public function showCoursByNiveau($id, SessionInterface $session, Request $request): Response
    {
        if ($id != 0) {
            $session->set("niveauSelected", (int) $id);
        } else {
            $session->remove("niveauSelected");
        }
        return new JsonResponse($this->generateUrl('app_cours'));
    }

    #[Route('/cours/filtre/semestre/{id?}', name: 'cours_filtre_semestre')]
    public function showClasseBySemestre($id, SessionInterface $session, Request $request): Response
    {
        if ($id != 0) {
            $session->set("semestreSelected", (int) $id);
        } else {
            $session->remove("semestreSelected");
        }
        return new JsonResponse($this->generateUrl('app_cours'));
    }

    #[Route('/cours/plan/module/{id?}', name: 'cours_plan_module')]
    public function ChooseModuleCours($id, SessionInterface $session, Request $request): Response
    {
        if ($id != null) {
            $tab = array_map('intval', explode(',', $id));
            $session->set("semestreChosen", (int) $tab[0]);
            $session->set("moduleChosen", (int) $tab[1]);
        }
        return new JsonResponse($this->generateUrl('app_cours_save'));
    }

    #[Route('/cours/plan/professeur/{id?}', name: 'cours_plan_professeur')]
    public function ChooseProfesseurCours($id, SessionInterface $session, Request $request): Response
    {
        if ($id != 0) {
            $session->set("profChosen", (int) $id);
        }
        return new JsonResponse($this->generateUrl('app_cours_save'));
    }

    #[Route('/cours/save', name: 'app_cours_save', methods: ["POST", "GET"])]
    public function save(
        Request $request,
        SessionInterface $session,
        CoursRepository $coursRepository,
        EntityManagerInterface $manager,
        AnneeScolaireRepository $anneeScolaireRepository,
        ProfesseurRepository $profRepository,
        ModuleRepository $moduleRepository,
        SemestreRepository $semestreRepository,
        ClasseRepository $classeRepository
    ): Response {
        $cours = new Cours();

        if ($session->has('moduleChosen')) {
            $cours->setSemestre($semestreRepository->find($session->get('semestreChosen')));
            $cours->setModule($moduleRepository->find($session->get('moduleChosen')));
        }

        if ($session->has('profChosen')) {
            $cours->setProfesseur($profRepository->find($session->get('profChosen')));
        }
        $cours->setAnneeScolaire($anneeScolaireRepository->findOneBy(['isActive' => true]));
        $form = $this->createForm(CoursType::class, $cours);

        if ($session->has('moduleChosen')) {
            $professeurs = $profRepository->findProfesseurByModule($session->get('moduleChosen'));
            $form->add('professeur', EntityType::class, [
                "required" => false,
                'class' => Professeur::class,
                'choices' => $professeurs,
                'choice_label' => 'nomComplet',
                'multiple' => false,
                'expanded' => false,
                "attr" => [
                    "class" => "select2 select-prof inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style" => "justify-content: space-between;",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ]);
        }

        if ($session->has('profChosen')) {
            $classes = $classeRepository->findClasseByProfAndSemestre([
                'semestre' => $session->get('semestreChosen'),
                'prof' => $session->get('profChosen'),
            ]);
            $form->add('nbreHeureTotal', TextType::class, [
                "required" => false,
                "attr" => [
                    "class" => "inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ])
            ->add('classes', EntityType::class, [
                "required" => true,
                'class' => Classe::class,
                'choices' => $classes,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => false,
                "attr" => [
                    "class" => "select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style" => "justify-content: space-between;"
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                "attr" => [
                    "class" => "px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                    "style" => "margin-left:90%",
                ]
            ]);
        }
        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {

            if (!$this->checkCours($cours->getAnneeScolaire(), $cours->getModule(), $cours->getSemestre(), $cours->getClasses()->getValues(), $coursRepository)) {
                $cours->setNbreHeureRestantPlan($cours->getNbreHeureTotal());
                $session->remove('semestreChosen');
                $session->remove('moduleChosen');
                $session->remove('profChosen');
                $manager->persist($cours);
                $manager->flush();
            } else {
                $session->remove('semestreChosen');
                $session->remove('moduleChosen');
                $session->remove('profChosen');
                $errors['unique'] = 'Ce cours est déjà enregistré pour une de ces classes';
            }
        }
        return $this->render('cours/form.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    private function checkCours($annee, $module, $semestre, $classes, $coursRepository)
    {
        $cours = $coursRepository->findBy([
            "anneeScolaire" => $annee,
            "module" => $module,
            "semestre" => $semestre,
        ]);
        for ($i = 0; $i < sizeof($cours); $i++) {
            for ($j = 0; $j < sizeof($classes); $j++) {
                if (in_array($classes[$j], $cours[$i]->getClasses()->getValues())) {
                    return true;
                }
            }
        }
        return false;
    }

    #[Route('/cours/edit/{id?}', name: 'app_cours_edit', methods: ["POST", "GET"])]
    public function edit($id, Request $request, SessionInterface $session, CoursRepository $coursRepository, EntityManagerInterface $manager, AnneeScolaireRepository $anneeScolaireRepository)
    {
        $cours = $coursRepository->find($id);
        $form = $this->createForm(CoursType::class, $cours);
        $form
            ->add('professeur', EntityType::class, [
                "required" => false,
                'class' => Professeur::class,
                'choice_label' => 'nomComplet',
                'multiple' => false,
                'expanded' => false,
                "attr" => [
                    "class" => "select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style" => "justify-content: space-between;",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ])
            ->add('nbreHeureTotal', TextType::class, [
                "required" => false,
                "attr" => [
                    "class" => "inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ])
            ->add('nbreHeureRestantPlan', TextType::class, [
                "attr" => [
                    "class" => "inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ])
            ->add('classes', EntityType::class, [
                "required" => true,
                'class' => Classe::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => false,
                "attr" => [
                    "class" => "select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style" => "justify-content: space-between;"
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('isArchived', null, [
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                "attr" => [
                    "class" => "px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                    "style" => "margin-left:90%",
                ]
            ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($cours);
            $manager->flush();
        }
        return $this->render('cours/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
