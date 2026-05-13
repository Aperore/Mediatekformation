<?php
namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur de gestion des formations (front et back office).
 * Gère l'affichage, l'ajout, la modification et la suppression des formations.
 *
 * @author emds
 */
class FormationsController extends AbstractController
{
    /**
     * Chemin du template de la liste des formations.
     */
    private const PAGEFORMATIONS = 'pages/formations.html.twig';

    /**
     * Repository des formations.
     *
     * @var FormationRepository
     */
    private FormationRepository $formationRepository;

    /**
     * Repository des catégories.
     *
     * @var CategorieRepository
     */
    private CategorieRepository $categorieRepository;

    /**
     * Injection des repositories via le constructeur.
     *
     * @param FormationRepository $formationRepository Repository des formations
     * @param CategorieRepository $categorieRepository Repository des catégories
     */
    public function __construct(
        FormationRepository $formationRepository,
        CategorieRepository $categorieRepository
    ) {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
    }

    /**
     * Affiche la liste complète des formations.
     *
     * @return Response
     */
    #[Route('/formations', name: 'formations')]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGEFORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * Affiche la liste des formations triées sur un champ.
     *
     * @param string $champ  Nom du champ sur lequel trier
     * @param string $ordre  Ordre du tri : 'ASC' ou 'DESC'
     * @param string $table  Table liée si le champ appartient à une relation (ex: 'playlist')
     * @return Response
     */
    #[Route('/formations/tri/{champ}/{ordre}/{table}', name: 'formations.sort')]
    public function sort(string $champ, string $ordre, string $table = ""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGEFORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * Affiche les formations dont un champ contient la valeur recherchée.
     *
     * @param string  $champ   Nom du champ dans lequel chercher
     * @param Request $request Requête HTTP contenant le paramètre 'recherche'
     * @param string  $table   Table liée si le champ appartient à une relation
     * @return Response
     */
    #[Route('/formations/recherche/{champ}/{table}', name: 'formations.findallcontain')]
    public function findAllContain(string $champ, Request $request, string $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGEFORMATIONS, [
            'formations' => $formations,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    /**
     * Affiche le détail d'une formation.
     *
     * @param int $id Identifiant de la formation
     * @return Response
     */
    #[Route('/formations/formation/{id}', name: 'formations.showone')]
    public function showOne(int $id): Response
    {
        $formation = $this->formationRepository->find($id);
        return $this->render("pages/formation.html.twig", [
            'formation' => $formation
        ]);
    }

    /**
     * Affiche le formulaire d'ajout d'une formation et traite sa soumission.
     * Placée avant formations.retirer pour éviter que "new" soit capturé comme un {id}.
     *
     * @param Request             $request Requête HTTP
     * @param FormationRepository $repo    Repository des formations
     * @return Response
     */
    #[Route('/formations/new', name: 'formations.ajouter')]
    public function new(Request $request, FormationRepository $repo): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->add($formation);
            $this->addFlash('success', 'Formation ajoutée.');
            return $this->redirectToRoute('formations');
        }

        return $this->render('pages/formationmodifier.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation
        ]);
    }

    /**
     * Affiche le formulaire de modification d'une formation prérempli et traite sa soumission.
     * Symfony injecte automatiquement la Formation via son id (ParamConverter).
     *
     * @param Formation           $formation Formation à modifier
     * @param Request             $request   Requête HTTP
     * @param FormationRepository $repo      Repository des formations
     * @return Response
     */
    #[Route('/formations/{id}/edit', name: 'formations.modifier')]
    public function modifier(Formation $formation, Request $request, FormationRepository $repo): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->add($formation);
            $this->addFlash('success', 'Formation modifiée.');
            return $this->redirectToRoute('formations');
        }

        return $this->render('pages/formationmodifier.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation
        ]);
    }

    /**
     * Supprime une formation après vérification du token CSRF.
     * La suppression retire aussi automatiquement les lignes de la table de jointure
     * formation_categorie (relation ManyToMany gérée par Doctrine).
     *
     * @param Formation           $formation Formation à supprimer
     * @param Request             $request   Requête HTTP
     * @param FormationRepository $repo      Repository des formations
     * @return Response
     */
    #[Route('/formations/{id}', name: 'formations.retirer', methods: ['POST'])]
    public function retirer(Formation $formation, Request $request, FormationRepository $repo): Response
    {
        if ($this->isCsrfTokenValid(
            'retirer_formation_' . $formation->getId(),
            $request->request->get('_token')
        )) {
            $repo->remove($formation);
            $this->addFlash('success', 'Formation supprimée.');
        }

        return $this->redirectToRoute('formations');
    }
}