<?php
namespace App\Tests\Integration;

use App\Entity\Categorie;
use App\Entity\Formation;
use App\Entity\Playlist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

// Classe parente commune à tous les tests d'intégration.
// Elle regroupe la logique de remise à zéro de la BDD pour éviter
// de la répéter dans chaque classe de test.
abstract class IntegrationTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        // Vide toutes les tables et recharge les fixtures avant chaque test.
        // Sans le bundle Doctrine, c'est la seule façon de garantir
        // que chaque test repart d'un état connu et identique.
        $this->resetDatabase();
    }

    private function resetDatabase(): void
    {
        $connection = $this->em->getConnection();

        // Désactive les vérifications de clés étrangères le temps du nettoyage,
        // pour pouvoir vider les tables sans erreur d'intégrité.
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('TRUNCATE TABLE formation_categorie');
        $connection->executeStatement('TRUNCATE TABLE formation');
        $connection->executeStatement('TRUNCATE TABLE playlist');
        $connection->executeStatement('TRUNCATE TABLE categorie');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        // Playlist 1
        $playlist1 = new Playlist();
        $playlist1->setName('Bases PHP');
        $playlist1->setDescription('Les bases du langage PHP');
        $this->em->persist($playlist1);

        // Playlist 2
        $playlist2 = new Playlist();
        $playlist2->setName('Symfony');
        $playlist2->setDescription('Le framework Symfony');
        $this->em->persist($playlist2);

        // Catégorie 1
        $cat1 = new Categorie();
        $cat1->setName('PHP');
        $this->em->persist($cat1);

        // Catégorie 2
        $cat2 = new Categorie();
        $cat2->setName('Symfony');
        $this->em->persist($cat2);

        // Formation 1 : titre commençant par "A", date ancienne
        $f1 = new Formation();
        $f1->setTitle('Apprendre PHP');
        $f1->setVideoId('abc123');
        $f1->setPublishedAt(new \DateTime('2021-01-15'));
        $f1->setPlaylist($playlist1);
        $f1->addCategory($cat1);
        $this->em->persist($f1);

        // Formation 2 : titre commençant par "Z", date récente
        $f2 = new Formation();
        $f2->setTitle('Zend et PHP');
        $f2->setVideoId('def456');
        $f2->setPublishedAt(new \DateTime('2023-06-20'));
        $f2->setPlaylist($playlist1);
        $f2->addCategory($cat1);
        $this->em->persist($f2);

        // Formation 3 : playlist Symfony, catégorie Symfony
        $f3 = new Formation();
        $f3->setTitle('Introduction Symfony');
        $f3->setVideoId('ghi789');
        $f3->setPublishedAt(new \DateTime('2022-03-10'));
        $f3->setPlaylist($playlist2);
        $f3->addCategory($cat2);
        $this->em->persist($f3);

        $this->em->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Ferme l'EntityManager après chaque test pour libérer la connexion
        // et éviter les fuites mémoire sur de longues suites de tests.
        $this->em->close();
    }
}