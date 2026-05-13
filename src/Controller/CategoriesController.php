<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur de gestion des catégories (back office).
 * Gère l'affichage, l'ajout et la suppression des catégories.
 *
 * @author emds
 */
class CategoriesController extends AbstractController
{
    /**
     * Chemin du template de la liste des catégories.
     */
    private const PAGECATEGORIES = 'pages/categories.html.twig';

    /**
     * Repository des catégories.
     *
     * @var CategorieRepository
     */
    private CategorieRepository $categorieRepository;

    /**
     * Injection du repository via le constructeur.
     *
     * @param CategorieRepository $categorieRepository Repository des catégories
     */
    public function __construct(CategorieRepository $categorieRepository)
    {
        $this->categorieRepository = $categorieRepository;
    }

    /**
     * Affiche la liste complète des catégories triées par nom.
     *
     * @return Response
     */
    #[Route('/categories', name: 'categories')]
    public function index(): Response
    {
        $categories = $this->categorieRepository->findAllOrderByName();
        return $this->render(self::PAGECATEGORIES, [
            'categories' => $categories
        ]);
    }

    /**
     * Traite l'ajout d'une nouvelle catégorie via le mini formulaire de la page de liste.
     * Vérifie le token CSRF, que le nom n'est pas vide, et qu'il n'existe pas déjà en BDD.
     *
     * @param Request $request Requête HTTP contenant le nom de la catégorie
     * @return Response
     */
    #[Route('/categories/ajouter', name: 'categories.ajouter', methods: ['POST'])]
    public function ajouter(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('ajouter_categorie', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('categories');
        }

        $nomSaisi = trim($request->request->get('name', ''));

        if ($nomSaisi === '') {
            $this->addFlash('error', 'Le nom de la catégorie est obligatoire.');
            return $this->redirectToRoute('categories');
        }

        $existe = $this->categorieRepository->findOneBy(['name' => $nomSaisi]);

        if ($existe) {
            $this->addFlash('error', 'La catégorie "' . $nomSaisi . '" existe déjà.');
            return $this->redirectToRoute('categories');
        }

        $categorie = new Categorie();
        $categorie->setName($nomSaisi);
        $this->categorieRepository->add($categorie);
        $this->addFlash('success', 'Catégorie "' . $nomSaisi . '" ajoutée avec succès.');

        return $this->redirectToRoute('categories');
    }

    /**
     * Supprime une catégorie après vérification du token CSRF.
     * La suppression est bloquée si des formations sont rattachées à la catégorie.
     *
     * @param Categorie $categorie Catégorie à supprimer (injectée via ParamConverter)
     * @param Request   $request   Requête HTTP
     * @return Response
     */
    #[Route('/categories/{id}', name: 'categories.retirer', methods: ['POST'])]
    public function retirer(Categorie $categorie, Request $request): Response
    {
        if (count($categorie->getFormations()) > 0) {
            $this->addFlash(
                'error',
                'Impossible de supprimer "' . $categorie->getName() . '" : '
                . count($categorie->getFormations()) . ' formation(s) y sont rattachées.'
            );
            return $this->redirectToRoute('categories');
        }

        if ($this->isCsrfTokenValid(
            'retirer_categorie_' . $categorie->getId(),
            $request->request->get('_token')
        )) {
            $this->categorieRepository->remove($categorie);
            $this->addFlash('success', 'Catégorie "' . $categorie->getName() . '" supprimée.');
        }

        return $this->redirectToRoute('categories');
    }
}