<?php

namespace App\Controller;

use TCPDF;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PdfController extends AbstractController
{
    #[Route('/pdf', name: 'app_pdf')]
    public function index(): Response
    {
        return $this->render('pdf/index.html.twig', [
            'controller_name' => 'PdfController',
        ]);
    }

    
    #[Route("/imprimer/pdf", name:"generate_pdf")]
    public function ImprimerPdf(SessionInterface $session):Response
    {
        // Récupération des données de la table depuis un repository ou un service
        $donnees = $session->get("donneesPdf");

        // Création d'un nouvel objet TCPDF
        $pdf = new TCPDF();

        // Ajout d'une page
        $pdf->AddPage();

        // Définition de la police et de la taille
        $pdf->SetFont('helvetica', '', 10);
        // dd($donnees);
        // Création de la table
        $html = '<table border="1">
            <tr>
                <th> N°</th>
                <th> Etudiant</th>
                <th> Matricule</th>
                <th> Email</th>
                <th> Classe</th>
            </tr>';
        foreach ($donnees as $entite) {
            $html .= '<tr>
                <td> ' . $entite->getId() . '</td>
                <td> ' . $entite->getEtudiant()->getNomComplet() . '</td>
                <td> ' . $entite->getEtudiant()->getMatricule() . '</td>
                <td> ' . $entite->getEtudiant()->getEmail() . '</td>
                <td> ' . $entite->getClasse()->getLibelle() . '</td>
            </tr>';
        }
        $html .= '</table>';
       
        

        // Écriture du contenu HTML dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Nom du fichier PDF
        //$nom_fichier = 'C:/Users/winny/Documents/Java/table.pdf';
        $repo = 'C:/Users/winny/Documents/Java/Inscriptions'.date("YmdHis").'.pdf';

        // Sortie du PDF vers le navigateur
        $pdf->Output($repo,'F');

        return new JsonResponse($this->generateUrl('app_inscription'));
    }
}
