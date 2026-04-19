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
 * Controleur des formations
 *
 * @author emds
 */
class FormationsController extends AbstractController
{
    private const PAGEFORMATIONS = 'pages/formations.html.twig';
    /**
     *
     * @var FormationRepository
     */

    private FormationRepository $formationRepository;
    private CategorieRepository $categorieRepository;

    public function __construct(
        FormationRepository $formationRepository,
        CategorieRepository $categorieRepository
    ) {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
    }

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

    #[Route('/formations/formation/{id}', name: 'formations.showone')]
    public function showOne(int $id): Response
    {
        $formation = $this->formationRepository->find($id);
        return $this->render("pages/formation.html.twig", [
            'formation' => $formation
        ]);
    }

    /**
     * Affiche le formulaire d'ajout.
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
     * Affiche le formulaire de modification prérempli.
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
     * Suppression avec vérification du token CSRF.
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