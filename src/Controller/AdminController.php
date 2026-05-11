<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends AbstractController
{
    // Page d'accueil du back office.
    // Protégée par access_control dans security.yaml : seul ROLE_ADMIN peut y accéder.
    // Symfony redirige vers /admin/login si non authentifié.
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    // Affiche le formulaire de connexion (GET) et reçoit sa soumission (POST).
    // Le traitement POST est entièrement géré par Symfony via form_login dans security.yaml :
    // Symfony intercepte le POST avant que cette méthode soit appelée.
    // AuthenticationUtils fournit l'erreur éventuelle et le dernier login saisi.
    #[Route('/admin/login', name: 'admin.login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur de connexion si elle existe (mauvais mot de passe, etc.)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Pré-remplit le champ login avec le dernier identifiant saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    // Méthode jamais exécutée car interceptée mias necessaire pour la route.
    #[Route('/admin/logout', name: 'admin.logout', methods: ['GET'])]
    public function logout(): Response
    {
        throw new \LogicException('Cette méthode ne doit jamais être appelée. Voir security.yaml.');
    }
}