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
 * Contrôleur de gestion des playlists (front et back office).
 * Gère l'affichage, l'ajout, la modification et la suppression des playlists.
 *
 * @author emds
 */
class PlaylistsController extends AbstractController
{
    /**
     * Chemin du template de la liste des playlists.
     */
    private const PLAYLISTS = "pages/playlists.html.twig";

    /**
     * Chemin du template du détail d'une playlist.
     */
    private const PLAYLIST = "pages/playlist.html.twig";

    /**
     * Chemin du template du formulaire d'ajout/modification.
     */
    private const PLAYLISTFORM = "pages/playlistmodifier.html.twig";

    /**
     * Repository des playlists.
     *
     * @var PlaylistRepository
     */
    private PlaylistRepository $playlistRepository;

    /**
     * Repository des catégories.
     *
     * @var CategorieRepository
     */
    private CategorieRepository $categorieRepository;

    /**
     * Repository des formations.
     *
     * @var FormationRepository
     */
    private FormationRepository $formationRepository;

    /**
     * Injection des repositories via le constructeur.
     *
     * @param PlaylistRepository  $playlistRepository  Repository des playlists
     * @param CategorieRepository $categorieRepository Repository des catégories
     * @param FormationRepository $formationRepository Repository des formations
     */
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
     * Affiche la liste complète des playlists triées par nom.
     *
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

    /**
     * Affiche la liste des playlists triées sur un champ.
     *
     * @param string $champ Nom du champ : 'name' ou 'NombreFormations'
     * @param string $ordre Ordre du tri : 'ASC' ou 'DESC'
     * @return Response
     */
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

    /**
     * Affiche les playlists dont un champ contient la valeur recherchée.
     *
     * @param string  $champ   Nom du champ dans lequel chercher
     * @param Request $request Requête HTTP contenant le paramètre 'recherche'
     * @param string  $table   Table liée si le champ appartient à une relation
     * @return Response
     */
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

    /**
     * Affiche le détail d'une playlist avec ses formations et catégories.
     *
     * @param int $id Identifiant de la playlist
     * @return Response
     */
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

    /**
     * Affiche le formulaire d'ajout d'une playlist et traite sa soumission.
     * Placée avant playlists.retirer pour éviter que "new" soit capturé comme un {id}.
     *
     * @param Request $request Requête HTTP
     * @return Response
     */
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

    /**
     * Affiche le formulaire de modification d'une playlist prérempli et traite sa soumission.
     * Passe également les formations de la playlist au template (lecture seule).
     *
     * @param Playlist $playlist Playlist à modifier (injectée via ParamConverter)
     * @param Request  $request  Requête HTTP
     * @return Response
     */
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

    /**
     * Supprime une playlist après vérification du token CSRF.
     * La suppression est bloquée si des formations sont rattachées à la playlist.
     *
     * @param Playlist $playlist Playlist à supprimer (injectée via ParamConverter)
     * @param Request  $request  Requête HTTP
     * @return Response
     */
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