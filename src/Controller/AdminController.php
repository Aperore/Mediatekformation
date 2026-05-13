<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur de gestion de l'authentification et de l'accès au back office.
 *
 * @author emds
 */
class AdminController extends AbstractController
{
    /**
     * Affiche la page d'accueil du back office.
     * Protégée par access_control dans security.yaml : seul ROLE_ADMIN peut y accéder.
     *
     * @return Response
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * Affiche le formulaire de connexion et fournit les données nécessaires au template.
     * Le traitement POST est entièrement géré par Symfony via form_login dans security.yaml.
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaire Symfony pour récupérer
     *                                                 l'erreur et le dernier login saisi
     * @return Response
     */
    #[Route('/admin/login', name: 'admin.login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * Route de déconnexion interceptée par Symfony avant l'exécution de cette méthode.
     * La méthode ne sera jamais appelée : Symfony invalide la session et redirige
     * vers la cible définie dans security.yaml (logout.target).
     *
     * @return Response
     * @throws \LogicException Toujours levée si la méthode est appelée par erreur
     */
    #[Route('/admin/logout', name: 'admin.logout', methods: ['GET'])]
    public function logout(): Response
    {
        throw new \LogicException('Cette méthode ne doit jamais être appelée. Voir security.yaml.');
    }
}