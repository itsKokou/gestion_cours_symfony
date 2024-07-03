<?php

namespace App\Controller;

use App\Repository\AbsenceRepository;
use App\Repository\EtudiantRepository;
use App\Repository\SemestreRepository;
use App\Repository\InscriptionRepository;
use App\Repository\AnneeScolaireRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AbsenceController extends AbstractController
{
    #[Route('/absence', name: 'app_absence')]
    public function index(SessionInterface $session, PaginatorInterface $paginator, Request $request,AbsenceRepository $absenceRepository, 
    EtudiantRepository $etudiantRepository, SemestreRepository $semestreRepository,AnneeScolaireRepository $anneeScolaireRepository,InscriptionRepository $inscriptionRepository ): Response
    {
        $semestre = null;
        $etudiant = null;

        if ($session->has('semestreSelected')) {
            $semestre = $session->get('semestreSelected');
        }

        if(in_array('ROLE_ETUDIANT', $this->getUser()->getRoles())){
            $etudiant = $this->getUser();
            $inscription = $inscriptionRepository->findOneBy([
                'etudiant'=>$etudiant,
                'anneeScolaire'=>$anneeScolaireRepository->find($session->get('anneeEncours')),
            ]);
            $semestres = $inscription->getClasse()->getNiveau()->getSemestres()->getValues();
        }else{
            $semestres = $semestreRepository->findBy(['isArchived' => false]);
            if ($session->has('etudiantSelected')) {
                $etudiant = $session->get('etudiantSelected');
            }
        }

        $selected = [
            'anneeScolaire' => $session->get('anneeEncours'),
            'etudiant' => $etudiant,
            'semestre' => $semestre,
        ];

        $query = $absenceRepository->prepareQueryForPagination($selected);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $this->getParameter('PAGE_LIMIT') /*limit per page*/
        );

        return $this->render('absence/index.html.twig', [
            'pagination' => $pagination,
            'selectedValue' => $selected,
            'etudiants' => $etudiantRepository->findBy(['isArchived'=>false]),
            'semestres'=>$semestres,
            'anneeEncours'=>$anneeScolaireRepository->find($session->get('anneeEncours')),
        ]);
    }

    #[Route('/absence/filtre/semestre/{id?}', name: 'absence_filtre_semestre')]
    public function showAbsenceBySemestre($id, SessionInterface $session, Request $request): Response
    {
        if ($id != 0) {
            $session->set("semestreSelected", (int) $id);
        } else {
            $session->remove("semestreSelected");
        }
        return new JsonResponse($this->generateUrl('app_absence'));
    }

    #[Route('/absence/filtre/etudiant/{id?}', name: 'absence_filtre_etudiant')]
    public function showAbsenceByEtudiant($id, SessionInterface $session, Request $request): Response
    {
        if ($id != 0) {
            $session->set("etudiantSelected", (int)$id);
        } else {
            $session->remove("etudiantSelected");
        }
        return new JsonResponse($this->generateUrl('app_absence'));
    }
}
