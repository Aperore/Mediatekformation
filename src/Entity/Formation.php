<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{

    /**
     * Début de chemin vers les images
     */
    private const CHEMINIMAGE = "https://i.ytimg.com/vi/";
        
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    // AJOUT : contrainte directement sur l'entité.
    // Dans FormationType, la contrainte était sur le formulaire uniquement —
    // le validateur Symfony ne la trouve pas quand on valide l'entité directement
    // (comme dans les tests). En la mettant sur l'entité, elle est toujours active.
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'La date ne peut pas être postérieure à aujourd\'hui.'
    )]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $videoId = null;

    #[ORM\ManyToOne(inversedBy: 'formations')]
    private ?Playlist $playlist = null;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'formations')]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant de la formation.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne la date de publication.
     *
     * @return \DateTimeInterface|null
     */
    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    /**
     * Définit la date de publication.
     *
     * @param \DateTimeInterface|null $publishedAt Date de publication
     * @return static
     */
    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Retourne la date de publication formatée en dd/mm/YYYY.
     * Retourne une chaîne vide si la date n'est pas définie.
     *
     * @return string
     */
    public function getPublishedAtString(): string
    {
        if ($this->publishedAt === \null) {
            return "";
        }
        return $this->publishedAt->format('d/m/Y');
    }
    
    /**
     * Retourne le titre de la formation.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Definit le titre de la formation.
     *
     * @param string|null $title Titre de la formation
     * @return static
     */
    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Retourne la description de la formation.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Definit l'identifiant de la formation.
     *
     * @param string|null $description Description de la formation
     * @return static
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Retourne l'identifiant de la vidéo de la formation.
     *
     * @return string|null
     */
    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    /**
     * Definit l'identifiant de la vidéo de la formation.
     *
     * @param string|null $videoId Identifiant de la vidéo de la formation
     * @return static
     */
    public function setVideoId(?string $videoId): static
    {
        $this->videoId = $videoId;

        return $this;
    }

    /**
     * Retourne l'URL de la miniature YouTube de la formation.
     *
     * @return string|null
     */
    public function getMiniature(): ?string
    {
        return self::CHEMINIMAGE.$this->videoId."/default.jpg";
    }

    /**
     * Retourne l'URL de l'image haute qualité YouTube de la formation.
     *
     * @return string|null
     */
    public function getPicture(): ?string
    {
        return self::CHEMINIMAGE.$this->videoId."/hqdefault.jpg";
    }
    
    /**
     * Retourne la playlist dans laquelle est la formation.
     *
     * @return playlist|null
     */
    public function getPlaylist(): ?playlist
    {
        return $this->playlist;
    }

     /**
     * Définit la playlist dans laquelle est la formation.
     *
     * @param playlist|null $Playlist Playlist dans laquelle est la formation
     * @return static
     */
    public function setPlaylist(?Playlist $playlist): static
    {
        $this->playlist = $playlist;

        return $this;
    }

    /**
     * Retourne la liste des categories
     * 
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * Ajoute une categorie 
     * 
     * @param Categorie $category La nouvelle categorie
     * @return static
     */
    public function addCategory(Categorie $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * Retire une categorie 
     * 
     * @param Categorie $category La categorie retirée
     * @return static
     */
    public function removeCategory(Categorie $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
