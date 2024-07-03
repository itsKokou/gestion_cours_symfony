<?php

namespace App\Controller;

use App\Entity\AnneeScolaire;
use App\Entity\Professeur;
use App\Repository\ClasseRepository;
use App\Repository\EnseignementRepository;
use App\Repository\ModuleRepository;
use App\Repository\ProfesseurRepository;
use DateTime;
use App\Entity\Etudiant;
use App\Repository\AbsenceRepository;
use App\Repository\SessionRepository;
use App\Repository\EtudiantRepository;
use App\Repository\DeclarationRepository;
use App\Repository\InscriptionRepository;
use App\Repository\AnneeScolaireRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    #[Route('/home', name: 'app_dashboard')]
    public function index(EtudiantRepository $etudiantRepository, AnneeScolaireRepository $anneeScolaireRepository, InscriptionRepository $inscriptionRepository,
        SessionRepository $sessionRepository, AbsenceRepository $absenceRepository, DeclarationRepository $declarationRepository, EnseignementRepository $enseignementRepository,
        ClasseRepository $classeRepository, ModuleRepository $moduleRepository, ProfesseurRepository $professeurRepository)
    {
        if (in_array('ROLE_ETUDIANT', $this->getUser()->getRoles())) {
            return $this->showEtudiantDashboard($etudiantRepository, $anneeScolaireRepository, $inscriptionRepository, $sessionRepository, $absenceRepository, $declarationRepository);
        } else if (in_array('ROLE_PROFESSEUR', $this->getUser()->getRoles())) {
            return $this->showProfesseurDashboard($sessionRepository, $anneeScolaireRepository, $declarationRepository, $enseignementRepository);
        } else {
            return $this->showAdminDashboard($anneeScolaireRepository, $inscriptionRepository, $classeRepository, $moduleRepository, $professeurRepository,$etudiantRepository,$absenceRepository);
        }
    }

    private function showEtudiantDashboard(EtudiantRepository $etudiantRepository, AnneeScolaireRepository $anneeScolaireRepository, InscriptionRepository $inscriptionRepository,
        SessionRepository $sessionRepository, AbsenceRepository $absenceRepository, DeclarationRepository $declarationRepository)
    {
        $etudiant = $this->getUser();
        $anneeActuelle = $anneeScolaireRepository->findOneBy(["isActive" => true]);
        $inscription = $inscriptionRepository->findOneBy([
            'etudiant' => $etudiant,
            'anneeScolaire' => $anneeActuelle,
        ]);
        $nbreAbsence = count($absenceRepository->findAbsencesByEtudiantAndAnneeScolaire(["etudiant" => $etudiant->getId(), "anneeEncours" => $anneeActuelle->getId()]));
        $nbreDeclaration = count($declarationRepository->findDeclarationByUserAndAnneeScolaire(["user" => $etudiant->getId(), "anneeEncours" => $anneeActuelle->getId()]));
        $lundi = date("Y-m-d", strtotime('monday this week'));
        $samedi = date("Y-m-d", strtotime('saturday this week'));
        $nbreCours = count($sessionRepository->findSessionByClasseAndWeek($inscription->getClasse()->getId(), $anneeActuelle->getId(), $lundi, $samedi));
        return $this->render('dashboard/indexEtu.html.twig', [
            "etudiant" => $etudiant,
            "inscription" => $inscription,
            "nbreCours" => $nbreCours,
            "nbreAbsence" => $nbreAbsence,
            "nbreDeclaration" => $nbreDeclaration,
        ]);
    }

    private function showProfesseurDashboard(SessionRepository $sessionRepository, AnneeScolaireRepository $anneeScolaireRepository, DeclarationRepository $declarationRepository,
        EnseignementRepository $enseignementRepository)
    {
        $prof = $this->getUser();
        $anneeActuelle = $anneeScolaireRepository->findOneBy(["isActive" => true]);
        $nbreDeclaration = count($declarationRepository->findDeclarationByUserAndAnneeScolaire(["user" => $prof->getId(), "anneeEncours" => $anneeActuelle->getId()]));
        $nbreCours = count($this->findSessionByProfesseur($sessionRepository, $prof->getId(), $anneeActuelle));
        $ClasseModule = $this->getClassesAndModulesByProfesseur($enseignementRepository, $prof, $anneeActuelle);
        $classes = $ClasseModule["classes"];
        $modules = $ClasseModule["modules"];
        return $this->render('dashboard/indexProf.html.twig', [
            "prof" => $prof,
            "classes" => $classes,
            "nbreCours" => $nbreCours,
            "nbreClasse" => count($classes),
            "nbreModule" => count($modules),
            "nbreDeclaration" => $nbreDeclaration,
        ]);
    }

    private function findSessionByProfesseur(SessionRepository $sessionRepository, $idProf, $anneeActuelle)
    {
        $lundi = date("Y-m-d", strtotime('monday this week'));
        $samedi = date("Y-m-d", strtotime('saturday this week'));
        $sessions = $sessionRepository->findSessionByWeek($anneeActuelle, $lundi, $samedi);
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

    private function getClassesAndModulesByProfesseur(EnseignementRepository $enseignementRepository, $prof, $anneeActuelle)
    {
        $enseignements = $enseignementRepository->findBy([
            "professeur" => $prof,
            "anneeScolaire" => $anneeActuelle
        ]);
        $classes = new ArrayCollection();
        $modules = new ArrayCollection();
        foreach ($enseignements as $ens) {
            foreach ($ens->getClasses() as $cl) {
                if (!(in_array($cl, $classes->getValues()))) {
                    $classes->add($cl);
                }
            }
            foreach ($ens->getModules() as $mod) {
                if (!(in_array($mod, $modules->getValues()))) {
                    $modules->add($mod);
                }
            }
        }
        return ["classes" => $classes->getValues(), "modules" => $modules->getValues()];
    }

    private function showAdminDashboard(AnneeScolaireRepository $anneeScolaireRepository, InscriptionRepository $inscriptionRepository, ClasseRepository $classeRepository,
        ModuleRepository $moduleRepository, ProfesseurRepository $professeurRepository, EtudiantRepository $etudiantRepository, AbsenceRepository $absenceRepository)
    {
        $admin = $this->getUser();
        $anneeActuelle = $anneeScolaireRepository->findOneBy(["isActive" => true]);
        
        return $this->render('dashboard/indexAdmin.html.twig', [
            "admin" => $admin,
            "nbreInscription" => count($inscriptionRepository->findBy(["isArchived" => false, "anneeScolaire" => $anneeActuelle])),
            "nbreClasse" => count($classeRepository->findBy(["isArchived" => false])),
            "nbreModule" => count($moduleRepository->findBy(["isArchived" => false])),
            "nbreProfesseur" => count($professeurRepository->findBy(["isArchived" => false])),
            "donnees" => $this->getFiveBestAbsents($anneeScolaireRepository,$etudiantRepository,$absenceRepository,$inscriptionRepository)
        ]);
    }

    private function getFiveBestAbsents(AnneeScolaireRepository $anneeScolaireRepository, EtudiantRepository $etudiantRepository, AbsenceRepository $absenceRepository,
    InscriptionRepository $inscriptionRepository)
    {
        $anneeActuelle = $anneeScolaireRepository->findOneBy(["isActive" => true]);
        $donnees = [];
        $etudiants = $etudiantRepository->findEtudiantWithAbsence($anneeActuelle->getId());
        foreach ($etudiants as $etu) {
           $donnees[$etu->getId()] = count($absenceRepository->findAbsencesByEtudiantAndAnneeScolaire(["etudiant"=>$etu->getId(),"anneeEncours"=>$anneeActuelle->getId()]));  
        }
        arsort($donnees);
        // dd($donnees);
        $data = [];
        $i = 0;
        foreach ($donnees as $key => $value) {
            if($i==5){
                break;
            }
            $etudiant = $etudiantRepository->find($key);
            $inscription = $inscriptionRepository->findOneBy(['etudiant' => $etudiant,'anneeScolaire' => $anneeActuelle,]);
            $data[]=[
                'id' => $etudiant->getId(),
                'matricule' => $etudiant->getMatricule(),
                'nomComplet' =>$etudiant->getNomComplet(),
                'email' =>$etudiant->getEmail(),
                'classe' =>$inscription->getClasse()->getLibelle(),
                'nbreAbsence' =>$value,
            ];
            $i++;
        }
        return $data;
    }

}
