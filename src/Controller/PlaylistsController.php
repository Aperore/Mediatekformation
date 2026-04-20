<?php
namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use App\Entity\Playlist;
use App\Form\PlaylistType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of PlaylistsController
 *
 * @author emds
 */
class PlaylistsController extends AbstractController
{
    private const PLAYLISTS   = "pages/playlists.html.twig";
    private const PLAYLIST    = "pages/playlist.html.twig";
    private const PLAYLISTFORM = "pages/playlistmodifier.html.twig";


    public function __construct(
        PlaylistRepository  $playlistRepository,
        CategorieRepository $categorieRepository,
        FormationRepository $formationRepository
    ) {
        $this->playlistRepository  = $playlistRepository;
        $this->categorieRepository = $categorieRepository;
        $this->formationRepository = $formationRepository;
    }

    /**
     * @Route("/playlists", name="playlists")
     * @return Response
     */
    #[Route('/playlists', name: 'playlists')]
    public function index(): Response
    {
        $playlists  = $this->playlistRepository->findAllOrderByName('ASC');
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PLAYLISTS, [
            'playlists'  => $playlists,
            'categories' => $categories
        ]);
    }

    #[Route('/playlists/tri/{champ}/{ordre}', name: 'playlists.sort')]
    public function sort($champ, $ordre): Response
    {
        if ($champ === "name") {
            $playlists = $this->playlistRepository->findAllOrderByName($ordre);
        } elseif ($champ === "NombreFormations") {
            $playlists = $this->playlistRepository->findAllOrderByNombreFormations($ordre);
        }
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PLAYLISTS, [
            'playlists'  => $playlists,
            'categories' => $categories
        ]);
    }

    #[Route('/playlists/recherche/{champ}/{table}', name: 'playlists.findallcontain')]
    public function findAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur     = $request->get("recherche");
        $playlists  = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PLAYLISTS, [
            'playlists'  => $playlists,
            'categories' => $categories,
            'valeur'     => $valeur,
            'table'      => $table
        ]);
    }

    #[Route('/playlists/playlist/{id}', name: 'playlists.showone')]
    public function showOne($id): Response
    {
        $playlist           = $this->playlistRepository->find($id);
        $playlistCategories = $this->categorieRepository->findAllForOnePlaylist($id);
        $playlistFormations = $this->formationRepository->findAllForOnePlaylist($id);
        return $this->render(self::PLAYLIST, [
            'playlist'           => $playlist,
            'playlistcategories' => $playlistCategories,
            'playlistformations' => $playlistFormations
        ]);
    }

    #[Route('/playlists/new', name: 'playlists.ajouter')]
    public function new(Request $request): Response
    {
        $playlist = new Playlist();
        $form     = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playlistRepository->add($playlist);
            $this->addFlash('success', 'Playlist ajoutée.');
            return $this->redirectToRoute('playlists');
        }

        return $this->render(self::PLAYLISTFORM, [
            'form'     => $form->createView(),
            'playlist' => $playlist
        ]);
    }

    #[Route('/playlists/{id}/edit', name: 'playlists.modifier')]
    public function modifier(Playlist $playlist, Request $request): Response
    {
        $form = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->playlistRepository->add($playlist);
            $this->addFlash('success', 'Playlist modifiée.');
            return $this->redirectToRoute('playlists');
        }

        $formations = $this->formationRepository->findAllForOnePlaylist($playlist->getId());

        return $this->render(self::PLAYLISTFORM, [
            'form'       => $form->createView(),
            'playlist'   => $playlist,
            'formations' => $formations
        ]);
    }

    #[Route('/playlists/{id}', name: 'playlists.retirer', methods: ['POST'])]
    public function retirer(Playlist $playlist, Request $request): Response
    {
        $formations = $this->formationRepository->findAllForOnePlaylist($playlist->getId());

        if (count($formations) > 0) {
            $this->addFlash(
                'error',
                'Impossible de supprimer "' . $playlist->getName() . '" : '
                . count($formations) . ' formation(s) y sont rattachées.'
            );
            return $this->redirectToRoute('playlists');
        }

        if ($this->isCsrfTokenValid(
            'retirer_playlist_' . $playlist->getId(),
            $request->request->get('_token')
        )) {
            $this->playlistRepository->remove($playlist);
            $this->addFlash('success', 'Playlist "' . $playlist->getName() . '" supprimée.');
        }

        return $this->redirectToRoute('playlists');
    }
}