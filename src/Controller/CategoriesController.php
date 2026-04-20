<?php
/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoriesController extends AbstractController
{
    private const PAGECATEGORIES = 'pages/categories.html.twig';

    private CategorieRepository $categorieRepository;

    public function __construct(CategorieRepository $categorieRepository)
    {
        $this->categorieRepository = $categorieRepository;
    }

    #[Route('/categories', name: 'categories')]
    public function index(): Response
    {
        $categories = $this->categorieRepository->findAllOrderByName();
        return $this->render(self::PAGECATEGORIES, [
            'categories' => $categories
        ]);
    }

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
