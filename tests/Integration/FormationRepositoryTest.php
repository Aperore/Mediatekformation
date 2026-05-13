<?php
namespace App\Tests\Integration;

use App\Entity\Playlist;
use App\Repository\FormationRepository;

class FormationRepositoryTest extends IntegrationTestCase
{
    private FormationRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = static::getContainer()->get(FormationRepository::class);
    }

    public function testFindAllOrderByTitleAsc(): void
    {
        $formations = $this->repo->findAllOrderBy('title', 'ASC');
        // Avec les fixtures : "Apprendre PHP" < "Introduction Symfony" < "Zend et PHP"
        $this->assertEquals('Apprendre PHP', $formations[0]->getTitle());
    }

    public function testFindAllOrderByTitleDesc(): void
    {
        $formations = $this->repo->findAllOrderBy('title', 'DESC');
        // Premier en DESC : "Zend et PHP"
        $this->assertEquals('Zend et PHP', $formations[0]->getTitle());
    }

    public function testFindAllOrderByPlaylistAsc(): void
    {
        $formations = $this->repo->findAllOrderBy('name', 'ASC', 'playlist');
        // "Bases PHP" < "Symfony" alphabétiquement
        $this->assertEquals('Bases PHP', $formations[0]->getPlaylist()->getName());
    }

    public function testFindByContainValueTitleNombreResultats(): void
    {
        $formations = $this->repo->findByContainValue('title', 'PHP');
        // "Apprendre PHP" et "Zend et PHP" contiennent "PHP"
        $this->assertCount(2, $formations);
    }

    public function testFindByContainValueTitlePremierResultat(): void
    {
        $formations = $this->repo->findByContainValue('title', 'PHP');
        // Triées par publishedAt DESC : "Zend et PHP" (2023) avant "Apprendre PHP" (2021)
        $this->assertEquals('Zend et PHP', $formations[0]->getTitle());
    }

    public function testFindByContainValueVideRetourneTout(): void
    {
        $formations = $this->repo->findByContainValue('title', '');
        // Valeur vide : retourne toutes les formations (3 dans les fixtures)
        $this->assertCount(3, $formations);
    }

    public function testFindByContainValuePlaylist(): void
    {
        $formations = $this->repo->findByContainValue('name', 'Symfony', 'playlist');
        // Seule la formation de la playlist "Symfony" est retournée
        $this->assertCount(1, $formations);
        $this->assertEquals('Introduction Symfony', $formations[0]->getTitle());
    }

    public function testFindAllLastedNombreCorrect(): void
    {
        $formations = $this->repo->findAllLasted(2);
        $this->assertCount(2, $formations);
    }

    public function testFindAllLastedPremierEstLePlusRecent(): void
    {
        $formations = $this->repo->findAllLasted(3);
        // La plus récente dans les fixtures : "Zend et PHP" (2023-06-20)
        $this->assertEquals('Zend et PHP', $formations[0]->getTitle());
    }

    public function testFindAllForOnePlaylist(): void
    {
        $playlist = $this->em->getRepository(Playlist::class)
                             ->findOneBy(['name' => 'Bases PHP']);
        $formations = $this->repo->findAllForOnePlaylist($playlist->getId());
        // "Bases PHP" contient 2 formations dans les fixtures
        $this->assertCount(2, $formations);
    }
}
