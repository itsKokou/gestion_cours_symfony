<?php

namespace App\Controller;

use App\Entity\Absence;
use App\Repository\DeclarationRepository;
use App\Repository\EtudiantRepository;
use DateTime;
use App\Entity\Salle;
use App\Entity\Module;
use App\Entity\Session;
use App\Form\SessionType;
use App\Entity\Professeur;
use App\Entity\Declaration;
use App\Form\DeclarationType;
use Symfony\Component\Form\Button;
use App\Repository\CoursRepository;
use App\Repository\SalleRepository;
use App\Repository\SessionRepository;
use App\Repository\SemestreRepository;
use App\Repository\ProfesseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InscriptionRepository;
use App\Repository\AnneeScolaireRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{

    private function findSessionByProfesseurAndClasse(SessionRepository $sessionRepository, $idProf, $idClasse, $anneeEnCours)
    {
        $sessions = $sessionRepository->findByClasse($idClasse, $anneeEnCours);
        $sessionsProf = new ArrayCollection();
        foreach ($sessions as $value) {
            if ($value->getProfesseur() == null) {
                if ($value->getCours()->getProfesseur()->getId() == $idProf) {
                    $sessionsProf->add($value);
                }
            } else {
                if ($value->getProfesseur()->getId() == $idProf) {
                    $sessionsProf->add($value);
                }
            }
        }
        return $sessionsProf->getValues();
    }

    private function prepareEvents(array $sessions)
    {
        $colors = ['da974b', '#E8B01F', '#DBCB89', '#71BCF3', '#E0474C', '#7FB8B4', '#B2B1B1', '#9F4C9D', '#0073BC', '#94918d', '#5dffe3', '#1d96d5', '#FFFA00', '#e40090', '#21FF19', '#f20089', '#41dfa8', '#FF1493', '#6A5ACD', '#FFDEAD', '#7FFF00', '#00FA9A', '#00FFFF', '#00CED1', '#1E90FF', '#FF6347', '#DA70D6'];
        $events = [];
        foreach ($sessions as $value) {
            $prof = $value->getProfesseur() != null ? $value->getProfesseur() : $value->getCours()->getProfesseur();
            $li = $value->getSalle() != null ? "SALLE " . $value->getSalle()->getLibelle() : $value->getCodeSession();
            $lieu = "Lieu : " . $li;
            $desc = $prof->getNomComplet();
            $color = $colors[rand(0, count($colors) - 1)];
            $date = $value->getDate()->format('Y-m-d') . ' ' . $value->getHeureD()->format('H:i:s');
            $newdate = $value->getDate()->format('Y-m-d') . ' ' . $value->getHeureF()->format('H:i:s'); //date("Y-m-d H:i:s", strtotime('+1 day', strtotime($value->getDate()->format('y-m-d H:i:s'))));

            $event = [
                'id' => $value->getId(),
                'start' => $date,
                'end' => $newdate,
                'title' => $value->getCours()->getModule()->getLibelle(),
                'description' => $desc,
                'location' => $lieu,
                'color' => $color,
                'textColor' => "#000000",
                'allDay' => false,
            ];
            if (!in_array('ROLE_ETUDIANT', $this->getUser()->getRoles())) {
                $event['url'] = "/session/click/" . $value->getId();
            } else {
                $dateToday = new DateTime("now", new \DateTimeZone("GMT"));
                $d = new DateTime($date, new \DateTimeZone("GMT"));
                $ds = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime($d->format('y-m-d H:i:s'))));
                $dEvent = new DateTime($ds, new \DateTimeZone("GMT"));
                if ($dateToday < $dEvent) {
                    $event['url'] = "/session/click/" . $value->getId();
                }
            }
            $events[] = $event;
        }
        return $events;
    }

    #[Route('/session', name: 'app_session')]
    public function index(SessionInterface $session, EntityManagerInterface $manager, SessionRepository $sessionRepository, AnneeScolaireRepository $anneeScolaireRepository,
        InscriptionRepository $inscriptionRepository, Request $request, DeclarationRepository $declarationRepository): Response
    {
        //----------------Index Affichage 
        $sessions = $sessionRepository->findBy(["isArchived" => false]);
        $anneeActuelle = $anneeScolaireRepository->findOneBy(["isActive" => true]);
        // Recuperer session specifique à prof ou etudiant
        if (in_array('ROLE_ETUDIANT', $this->getUser()->getRoles())) {
            $inscriptionActuelle = $inscriptionRepository->findOneBy([
                'etudiant' => $this->getUser(),
                'anneeScolaire' => $anneeActuelle,
            ]);
            $sessions = $sessionRepository->findByClasse($inscriptionActuelle->getClasse()->getId(), $anneeActuelle->getId());
        } else if (in_array('ROLE_PROFESSEUR', $this->getUser()->getRoles())) {
            if ($session->has('classeEncoursID')) {
                $idClasse = (int) $session->get('classeEncoursID');
            } else {
                $idClasse = 0;
            }
            $sessions = $this->findSessionByProfesseurAndClasse($sessionRepository, $this->getUser()->getId(), $idClasse, $anneeActuelle->getId());
        }
        // Recuperer session specifique prof ou classe pour admin
        if (!(in_array('ROLE_ETUDIANT', $this->getUser()->getRoles()) || in_array('ROLE_PROFESSEUR', $this->getUser()->getRoles()))) {
            if ($session->has('classeEncoursID')) {
                $idClasse = (int) $session->get('classeEncoursID');
            } else {
                $idClasse = 0;
            }
            if ($session->has('profEncoursID')) {
                $sessions = $this->findSessionByProfesseurAndClasse($sessionRepository, (int) $session->get('profEncoursID'), $idClasse, $anneeActuelle->getId());
            } else {
                $sessions = $sessionRepository->findByClasse($idClasse, $anneeActuelle->getId());
            }
        }

        $events = $this->prepareEvents($sessions);
        $data = json_encode($events);
        //--------------------------------------------------
        //----------------Faire declaration-----------------
        $htmlDeclaration = null;
        $htmlDeclarationError = null;
        $succes = null;
        $htmlEtudiant = null;
        $htmlAbsence = null;
        $finSession = null;
        $debutSession = null;
        if ($session->has('sessionId') && (in_array('ROLE_ETUDIANT', $this->getUser()->getRoles()) || in_array('ROLE_PROFESSEUR', $this->getUser()->getRoles()))) {
            $declarationExist = $declarationRepository->findOneBy([
                "session" => $sessionRepository->find($session->get('sessionId')),
                "user" => $this->getUser(),
            ]);
            if ($declarationExist != null) {
                $session->remove('sessionId');
                $htmlDeclarationError = "Vous avez déjà effectué une déclaration pour cette session de cours";
            } else {
                $declaration = new Declaration();

                $declaration->setSession($sessionRepository->find($session->get('sessionId')));
                $declaration->setUser($this->getUser());

                $form = $this->createForm(DeclarationType::class, $declaration);

                $form->handleRequest($request); //Prends les valeur du forme et le met dans classe
                if ($form->isSubmitted() && $form->isValid()) {
                    $manager->persist($declaration);
                    $manager->flush();
                    $succes = "Déclaration enrégistrée avec succès.";

                    //Si déclaration vient du prof alors, on annule la session si declaration avant fin session
                    if (in_array('ROLE_PROFESSEUR', $this->getUser()->getRoles())) {
                        $seance = $sessionRepository->find($session->get('sessionId'));
                        //checker si session pas commencer pour annuler 
                        $dateD = $seance->getDate()->format('Y-m-d') . ' ' . $seance->getHeureD()->format('H:i:s');
                        $dateToday = new DateTime("now", new \DateTimeZone("GMT"));
                        $dateDebutSession = new DateTime($dateD, new \DateTimeZone("GMT"));
                        $debutSession = $dateDebutSession > $dateToday;
                        if ($debutSession == true) {
                            $this->ArchiverSession($session, $manager, $sessionRepository);
                        }
                    }

                    $session->remove('sessionId');
                }
                $htmlDeclaration = $this->renderView('declaration/form.html.twig', ['form' => $form->createView(), "succes" => $succes]);
            }

        } else {
            //demander faire absence ou voir liste étudiants
            if ($session->has('sessionId') && !(in_array('ROLE_ETUDIANT', $this->getUser()->getRoles()) || in_array('ROLE_PROFESSEUR', $this->getUser()->getRoles()))) {
                $seance = $sessionRepository->find($session->get('sessionId'));
                $isAbsence = $seance->isIsAbsence();

                //checker si session est fini avant marquer absence ou si session pas fini pour annuler 
                //$dateD = $seance->getDate()->format('Y-m-d') . ' ' . $seance->getHeureD()->format('H:i:s');
                $dateF = $seance->getDate()->format('Y-m-d') . ' ' . $seance->getHeureF()->format('H:i:s');
                $dateToday = new DateTime("now", new \DateTimeZone("GMT"));
                // $dateDebutSession = new DateTime($dateD, new \DateTimeZone("GMT"));
                $dateFinSession = new DateTime($dateF, new \DateTimeZone("GMT"));

                $finSession = $dateFinSession <= $dateToday;

                //montrer marquer absence ou liste etudiants
                $htmlEtudiant = $this->renderView('session/etudiant.html.twig', [
                    'classes' => $seance->getCours()->getClasses()->getValues(),
                    "isAbsence" => $isAbsence,
                    "finSession" => $finSession,
                ]);

                if ($session->has("voir") && ($session->get("voir") == "absences")) {
                    //Voir absences
                    $absences = $seance->getAbsences()->getValues();
                    if (!empty($absences)) {
                        $absencesOkay = [];
                        foreach ($absences as $value) {
                            $ins = $inscriptionRepository->findOneBy([
                                "etudiant" => $value->getEtudiant(),
                                "anneeScolaire" => $anneeScolaireRepository->findOneBy(["isActive" => true])
                            ]);

                            $absencesOkay[] = [
                                "id" => $value->getEtudiant()->getId(),
                                "matricule" => $value->getEtudiant()->getMatricule(),
                                "nomComplet" => $value->getEtudiant()->getNomComplet(),
                                "classe" => $ins->getClasse()->getLibelle(),
                            ];

                        }
                        $htmlAbsence = $this->renderView('session/absence.html.twig', [
                            'absences' => $absencesOkay,
                        ]);
                        $session->remove("sessionId");
                        $session->remove("voir");
                    } else {
                        $htmlAbsence = 0;
                        $session->remove("sessionId");
                        $session->remove("voir");
                    }

                }
            }
        }
        // dump($deuxBoutons);
        // dump($htmlEtudiant);
        // dd($htmlAbsence);
        return $this->render('session/index.html.twig', [
            'data' => $data,
            'htmlDeclaration' => $htmlDeclaration,
            'htmlDeclarationError' => $htmlDeclarationError,
            'htmlEtudiant' => $htmlEtudiant,
            'htmlAbsence' => $htmlAbsence,
        ]);
    }

    #[Route('/session/vider/session', name: 'session_vider_session')]
    public function ViderSession(SessionInterface $session): Response
    {
        $session->remove("sessionId");
        $session->remove("voir");
        return new JsonResponse($this->generateUrl('app_session'));
    }

    #[Route('/session/click/{id?}', name: 'session_click')]
    public function clickSession($id, SessionInterface $session)
    {
        $session->set('sessionId', $id);
        return $this->redirectToRoute("app_session");
    }

    #[Route('/session/voir/{element?}', name: 'session_voir')]
    public function VoirSessionElements($element, SessionInterface $session)
    {
        $session->set('voir', $element);
        return $this->redirectToRoute("app_session");
    }

    #[Route('/session/absences/save/{presences?}', name: 'session_absence_save')]
    public function SaveSessionAbsences($presences, Request $request, SessionInterface $session, EntityManagerInterface $manager, SessionRepository $sessionRepository,
        EtudiantRepository $etudiantRepository, AnneeScolaireRepository $anneeScolaireRepository, DeclarationRepository $declarationRepository): Response
    {
        $anneeEnCours = $anneeScolaireRepository->findOneBy(["isActive" => true]);
        $seance = $sessionRepository->find($session->get("sessionId"));
        //Mis à jour des nbreHeures de cours
        $cours = $seance->getCours();
        $nbreHeure = (int) $seance->getHeureF()->diff($seance->getHeureD())->format("%h");
        $cours->setNbreHeureRealise($cours->getNbreHeureRealise() + $nbreHeure);

        //Absences
        $seance->setIsAbsence(true);
        $tabPresences = array_map('intval', explode(',', $presences));
        $classes = $seance->getCours()->getClasses();
        foreach ($classes as $cl) {
            foreach ($cl->getInscriptions() as $ins) {
                if (!in_array($ins->getEtudiant()->getId(), $tabPresences) && $ins->getAnneeScolaire()->getId() == $anneeEnCours->getId()) {
                    $absence = new Absence();
                    $absence->setEtudiant($ins->getEtudiant());
                    $absence->setSession($seance);
                    //si l'étudiant a fait une déclaration, son absence n'est pas enregistrée
                    $declaration = $declarationRepository->findOneBy([
                        "user" => $ins->getEtudiant(),
                        "session" => $seance,
                    ]);
                    if ($declaration == null) {
                        $manager->persist($absence);
                        $manager->flush();
                    }
                }
            }
        }
        $session->remove("sessionId");
        $session->remove("voir");

        return new JsonResponse($this->generateUrl('app_session'));
    }

    #[Route('/session/archiver', name: 'session_archiver')]
    public function ArchiverSession(SessionInterface $session, EntityManagerInterface $manager, SessionRepository $sessionRepository, ): Response
    {
        $seance = $sessionRepository->find($session->get("sessionId"));
        //Mis à jour des nbreHeures de cours
        $cours = $seance->getCours();
        $nbreHeure = (int) $seance->getHeureF()->diff($seance->getHeureD())->format("%h");
        $cours->setNbreHeurePlanifie($cours->getNbreHeurePlanifie() - $nbreHeure);
        $cours->setNbreHeureRestantPlan($cours->getNbreHeureRestantPlan() + $nbreHeure);
        $seance->setIsArchived(true);
        $manager->persist($seance);
        $manager->flush();
        $session->remove("sessionId");
        $session->remove("voir");
        return new JsonResponse($this->generateUrl('app_session'));
    }

    #[Route('/session/plan/date/{date?}', name: 'session_plan_date')]
    public function ChooseDateForSession($date, SessionInterface $session, Request $request): Response
    {
        $session->remove("dateError");
        $session->remove("dateChosen");
        if ($date != 0) {
            $dateChosen = new DateTime($date);
            $now = new DateTime("now");
            if ($dateChosen > $now) {
                $diff = $dateChosen->diff($now);
                if ((int) $diff->format('%a') <= 60) {
                    $jour = $dateChosen->format('D');
                    if ($jour == 'Sun') {
                        $session->set("dateError", "Un cours ne peut se faire un dimanche");
                    } else {
                        $session->set("dateChosen", $date);
                        $session->remove("dateError");
                    }
                } else {
                    $session->set("dateError", "Une session de cours se planifie sur moins de deux mois");
                }
            } else {
                $session->set("dateError", "Impossible de planifier une session pour une date antérieure ou aujourd'hui");
            }
        } else {
            $session->set("dateError", "veuillez choisir une date");
        }
        return new JsonResponse($this->generateUrl('app_session_save'));
    }

    #[Route('/session/plan/heured/{heure?}', name: 'session_plan_heure_d')]
    public function ChooseHeureDForSession($heure, SessionInterface $session, Request $request): Response
    {
        $session->remove("heureDError");
        $session->remove("heureDChosen");
        if ($heure != 0) {
            $heureChosen = new DateTime($heure);
            $heureD = new DateTime("08:00");
            $heureF = new DateTime("17:00");
            if ($heureChosen >= $heureD && $heureChosen <= $heureF) {
                $session->set("heureDChosen", $heure);
                $session->remove("heureDError");
            } else {
                $session->set("heureDError", "Le debut d'une session de cours doit être compris entre 08:00 et 17:00");
            }
        } else {
            $session->set("heureDError", "veuillez choisir une heure de début");
        }
        return new JsonResponse($this->generateUrl('app_session_save'));
    }

    #[Route('/session/plan/heuref/{heure?}', name: 'session_plan_heure_f')]
    public function ChooseHeureFForSession($heure, SessionInterface $session, Request $request): Response
    {
        $session->remove("heureFError");
        $session->remove("heureFChosen");
        if ($heure != 0) {
            $heureChosen = new DateTime($heure);
            $heureD = new DateTime("11:00");
            $heureF = new DateTime("20:00");
            if ($heureChosen >= $heureD && $heureChosen <= $heureF) {
                $heureDChosen = new DateTime($session->get("heureDChosen"));
                if ($heureChosen <= $heureDChosen) {
                    $session->set("heureFError", "La fin d'une session ne peut être ni inférieure ni égale au début");
                } else {
                    $diff = $heureChosen->diff($heureDChosen);
                    if ((int) $diff->format('%h') < 3 || (int) $diff->format('%h') > 4) {
                        $session->set("heureFError", "La durée d'une session varie entre 3h et 4h");
                    } else {
                        $session->set("heureFChosen", $heure);
                        $session->remove("heureFError");
                    }
                }
            } else {
                $session->set("heureFError", "La fin d'une session de cours doit être compris entre 11:00 et 20:00");
            }
        } else {
            $session->set("heureFError", "veuillez choisir une heure de fin");
        }
        return new JsonResponse($this->generateUrl('app_session_save'));
    }

    #[Route('/session/plan/isprof/{isProf?}', name: 'session_plan_is_prof')]
    public function ChooseIfProfForSession($isProf, SessionInterface $session, Request $request): Response
    {
        $session->remove("isProf");
        if ($isProf != null) {
            $session->set("isProf", (boolean) $isProf);
        }
        return new JsonResponse($this->generateUrl('app_session_save'));
    }

    #[Route('/session/plan/professeur/{prof?}', name: 'session_plan_professeur')]
    public function ChooseProfesseurForSession($prof, SessionInterface $session, Request $request): Response
    {
        $session->remove("profChosen");
        if ($prof != 0) {
            $session->set("profChosen", $prof);
        }
        return new JsonResponse($this->generateUrl('app_session_save'));
    }

    #[Route('/session/plan/lieu/{lieu?}', name: 'session_plan_lieu')]
    public function ChoosePlaceForSession($lieu, SessionInterface $session): Response
    {
        $session->remove("lieu");
        if ($lieu != null) {
            $session->set("lieu", $lieu);
        }
        return new JsonResponse($this->generateUrl('app_session_save'));
    }

    #[Route('/session/plan/salle/{salle?}', name: 'session_plan_salle')]
    public function ChooseSalleForSession($salle, SessionInterface $session, Request $request): Response
    {
        $session->remove("salleChosen");
        if ($salle != 0) {
            $session->set("salleChosen", $salle);
        }
        return new JsonResponse($this->generateUrl('app_session_save'));
    }

    #[Route('/session/plan/code/{code?}', name: 'session_plan_code')]
    public function EnterCodeForSession($code, SessionInterface $session, Request $request): Response
    {
        $session->remove("codeChosen");
        if ($code != 0) {
            $session->set("codeChosen", $code);
        }
        return new JsonResponse($this->generateUrl('app_session_save'));
    }

    #[Route('/session/plan/abort', name: 'session_plan_abort')]
    public function abortSessionPlan(SessionInterface $session, Request $request): Response
    {
        $session->remove("coursID");
        $session->remove("dateChosen");
        $session->remove("heureDChosen");
        $session->remove("heureFChosen");
        $session->remove("isProf");
        $session->remove("profChosen");
        $session->remove('lieu');
        $session->remove('salleChosen');
        $session->remove('codeChosen');
        return $this->redirectToRoute('app_cours');
    }

    #[Route('/session/save/{id?}', name: 'app_session_save')]
    public function save($id, Request $request, SessionInterface $session, CoursRepository $coursRepository, SessionRepository $sessionRepository, EntityManagerInterface $manager,
        AnneeScolaireRepository $anneeScolaireRepository, ProfesseurRepository $profRepository, SalleRepository $salleRepository): Response
    {

        $errors = [];
        $seance = new Session();

        if (!$session->has('coursID')) {
            $session->set('coursID', $id); //garder le cours 
        }
        $cours = $coursRepository->find($session->get('coursID'));
        $seance->setCours($cours);

        if ($session->has('dateChosen')) {
            $date = new \DateTimeImmutable($session->get('dateChosen'));
            $seance->setDate($date);
        } else {
            $errors['dateError'] = $session->get("dateError");
        }

        if ($session->has('heureDChosen')) {
            $heureD = new DateTime($session->get('heureDChosen'));
            $seance->setHeureD($heureD);
        } else {
            $errors['heureDError'] = $session->get("heureDError");
        }

        if ($session->has('heureFChosen')) {
            $heureF = new DateTime($session->get('heureFChosen'));
            $seance->setHeureF($heureF);
        } else {
            $errors['heureFError'] = $session->get("heureFError");
        }

        if ($session->has('profChosen')) {
            $prof = $profRepository->find($session->get('profChosen'));
            $seance->setProfesseur($prof);
        }

        if ($session->has('salleChosen')) {
            $salle = $salleRepository->find($session->get('salleChosen'));
            $seance->setSalle($salle);
        }

        if ($session->has('codeChosen')) {
            $seance->setCodeSession($session->get('codeChosen'));
        }

        $form = $this->createForm(SessionType::class, $seance);

        if ($session->has('dateChosen')) {
            $form->add('heureD', TimeType::class, [
                "required" => false,
                "widget" => 'single_text',
                'html5' => true,
                'input' => 'datetime',
                "attr" => [
                    "class" => "heure-d inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ]);
        }

        if ($session->has('heureDChosen')) {
            $form->add('heureF', TimeType::class, [
                "required" => false,
                "widget" => 'single_text',
                'html5' => true,
                'input' => 'datetime',
                "attr" => [
                    "class" => "heure-f inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ]);
        }

        //*! okkkkkk------------------------
        if ($session->has('heureFChosen')) {
            $classes = $this->getDataDisponibles($cours->getClasses()->getValues(), $seance->getDate(), $seance->getHeureD(), $seance->getHeureF());
            if (sizeof($classes) < sizeof($cours->getClasses()->getValues())) {
                $errors['ressource'] = "La classe/une des classes n'est pas disponible pour ce cours !";
                $form->add('quitter', ButtonType::class, [
                    'disabled' => false,
                    'label' => 'Quitter',
                    "attr" => [
                        "class" => "btn-quitter px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                        "style" => "margin-left:90%",
                    ]
                ]);
            }
        }

        if ($session->has('heureFChosen') && !$session->has('isProf') && !isset($errors['ressource'])) {
            $form->add('isProf', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Changer de Prof ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'multiple' => false,
                'expanded' => true,
                "attr" => [
                    "class" => "isProf inline-flex w-full space-x-4 px-4 h-10 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block  dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            ]);
        }

        if ($session->has('isProf') && $session->get('isProf')) {
            // dd($session->get('isProf'));
            $professeurs = $profRepository->findProfesseurByModuleAndClasse($cours->getModule()->getId(), $cours->getClasses()->getValues(), $cours->getProfesseur()->getId());
            $professeursDisponibles = $this->getDataDisponibles($professeurs, $seance->getDate(), $seance->getHeureD(), $seance->getHeureF());

            if (empty($professeursDisponibles)) {
                $errors['ressource'] = "Aucun professeur disponible pour ce cours !";
                $form->add('quitter', ButtonType::class, [
                    'disabled' => false,
                    'label' => 'Quitter',
                    "attr" => [
                        "class" => "btn-quitter px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                        "style" => "margin-left:90%",
                    ]
                ]);
            } else {
                $form->add('professeur', EntityType::class, [
                    'class' => Professeur::class,
                    'choice_label' => 'nomComplet',
                    'choices' => $professeursDisponibles,
                    'required' => false,
                    'multiple' => false,
                    'expanded' => false,
                    "attr" => [
                        "class" => "select-prof select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                        "style" => "justify-content: space-between;",
                    ],
                    'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
                ]);
            }

        }

        if ($session->has('isProf') && !$session->get('isProf')) {
            //Si conserve le prof, verifie disponibilité
            $professeurDisponible = $this->getDataDisponibles([$cours->getProfesseur()], $seance->getDate(), $seance->getHeureD(), $seance->getHeureF());
            if (empty($professeurDisponible)) {
                $errors['ressource'] = "Le professeur " . $cours->getProfesseur()->getNomComplet() . " n'est pas disponible pour ce cours!";
                $form->add('quitter', ButtonType::class, [
                    'disabled' => false,
                    'label' => 'Quitter',
                    "attr" => [
                        "class" => "btn-quitter px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                        "style" => "margin-left:90%",
                    ]
                ]);
            }
        }

        if (!$session->has('lieu') && $seance->getHeureF() <= new DateTime("17:00") && empty($errors['ressource'])) {
            if ($session->has('profChosen') || ($session->has('isProf') && !$session->get('isProf'))) {
                $form->add('lieu', ChoiceType::class, [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'Où se deroulera la session ?',
                    'choices' => [
                        'En Ligne' => 'ligne',
                        'En Présentiel' => 'presentiel',
                    ],
                    'multiple' => false,
                    'expanded' => true,
                    "attr" => [
                        "class" => "lieu inline-flex w-full space-x-4 px-4 h-10 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block  dark:placeholder-gray-400 dark:text-white",
                    ],
                    'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
                ]);
            }
        }

        if ($session->has('lieu') && $session->get('lieu') == 'presentiel') {
            $nbrePlaceTotal = $this->getPlaceTotale($cours->getClasses()->getValues());
            $salles = $salleRepository->findSalleByCapacity($nbrePlaceTotal);
            $sallesDisponibles = $this->getSallesDisponibles($salles, $seance->getDate(), $seance->getHeureD(), $seance->getHeureF());
            if (empty($sallesDisponibles)) {
                $errors['ressource'] = "Aucune salle disponible pour ce cours !";
                $form->add('quitter', ButtonType::class, [
                    'disabled' => false,
                    'label' => 'Quitter',
                    "attr" => [
                        "class" => "btn-quitter px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                        "style" => "margin-left:90%",
                    ]
                ]);
            } else {
                $form->add('salle', EntityType::class, [
                    'class' => Salle::class,
                    'choice_label' => 'libelle',
                    'choices' => $sallesDisponibles,
                    'required' => false,
                    'multiple' => false,
                    'expanded' => false,
                    "attr" => [
                        "class" => "salle select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                        "style" => "justify-content: space-between;",
                    ],
                    'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
                ]);
            }
        }

        if (($session->has("heureFChosen") && $seance->getHeureF() > new DateTime("17:00")) || ($session->has('lieu') && $session->get('lieu') == 'ligne')) {
            $form->add('codeSession', TextType::class, [
                "required" => false,
                "attr" => [
                    "class" => "code inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
                'constraints' => [
                    new NotBlank(), new Length(min: 4, max: 10)
                ]
            ]);
        }

        if ($session->has('salleChosen') || $session->has('codeChosen')) {
            $form->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                "attr" => [
                    "class" => "px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                    "style" => "margin-left:90%",
                ]
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //vider la session
            $session->remove("coursID");
            $session->remove("dateChosen");
            $session->remove("heureDChosen");
            $session->remove("heureFChosen");
            $session->remove("isProf");
            $session->remove("profChosen");
            $session->remove('lieu');
            $session->remove('salleChosen');
            $session->remove('codeChosen');

            //update heurePlanifier de cours
            $nbreHeure = (int) $seance->getHeureF()->diff($seance->getHeureD())->format("%h");
            $cours->setNbreHeurePlanifie($cours->getNbreHeurePlanifie() + $nbreHeure);
            $cours->setNbreHeureRestantPlan($cours->getNbreHeureRestantPlan() - $nbreHeure);

            //save
            $manager->persist($seance);
            $manager->flush();
        }
        //dd();
        return $this->render('session/form.html.twig', [
            'form' => $form,
            'errors' => $errors,
        ]);
    }

    private function getPlaceTotale(array $classes)
    {
        $place = 0;
        foreach ($classes as $classe) {
            $place += $classe->getEffectif();
        }
        return $place;
    }

    private function getDataDisponibles(array $datas, $date, DateTime $heureD, DateTime $heureF)
    {
        $datasDisponibles = new ArrayCollection();
        for ($i = 0; $i < sizeof($datas); $i++) {
            $isDispo = 1;
            foreach ($datas[$i]->getCours() as $cours) {
                foreach ($cours->getSessions() as $session) {
                    $sessionDate = $session->getDate();
                    $sessionHeureD = new DateTime($session->getHeureD()->format("H:i"));
                    $sessionHeureF = new DateTime($session->getHeureF()->format("H:i"));
                    if ($sessionDate == $date) {
                        if ($sessionHeureD < $heureF && $heureF < $sessionHeureF) {
                            $isDispo = 0;
                        } else {
                            if ($sessionHeureD < $heureD && $heureD < $sessionHeureF) {
                                $isDispo = 0;
                            } else {
                                if ($heureD <= $sessionHeureD && $sessionHeureD <= $sessionHeureF && $sessionHeureF <= $heureF) {
                                    $isDispo = 0;
                                } else {
                                    if ($sessionHeureD <= $heureD && $heureD <= $heureF && $heureF <= $sessionHeureF) {
                                        $isDispo = 0;
                                    }
                                }

                            }

                        }

                    }
                }
            }
            if ($isDispo == 1) {
                $datasDisponibles->add($datas[$i]);
            }
        }
        return $datasDisponibles->getValues();
    }

    private function getSallesDisponibles(array $salles, $date, DateTime $heureD, DateTime $heureF)
    {
        $sallesDisponibles = new ArrayCollection();

        foreach ($salles as $salle) {
            $isDispo = 1;
            foreach ($salle->getSessions() as $session) {
                $sessionDate = $session->getDate();
                $sessionHeureD = new DateTime($session->getHeureD()->format("H:i"));
                $sessionHeureF = new DateTime($session->getHeureF()->format("H:i"));
                if ($sessionDate == $date) {
                    if ($sessionHeureD < $heureF && $heureF < $sessionHeureF) {
                        $isDispo = 0;
                    } else {
                        if ($sessionHeureD < $heureD && $heureD < $sessionHeureF) {
                            $isDispo = 0;
                        } else {
                            if ($heureD <= $sessionHeureD && $sessionHeureD <= $sessionHeureF && $sessionHeureF <= $heureF) {
                                $isDispo = 0;
                            } else {
                                if ($sessionHeureD <= $heureD && $heureD <= $heureF && $heureF <= $sessionHeureF) {
                                    $isDispo = 0;
                                }
                            }
                        }
                    }
                }
            }
            if ($isDispo == 1) {
                $sallesDisponibles->add($salle);
            }
        }
        return $sallesDisponibles->getValues();
    }
}
