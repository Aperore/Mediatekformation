<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlaylistsTest extends WebTestCase
{
    public function testPagePlaylistsEstAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/playlists');
        $this->assertResponseIsSuccessful();
    }

    public function testTriNomAsc(): void
    {
        $client = static::createClient();
        $client->request('GET', '/playlists/tri/name/ASC');
        $this->assertResponseIsSuccessful();
        // "Bases PHP" < "Symfony" alphabétiquement
        $this->assertSelectorTextContains('tbody tr:first-child h5', 'Bases PHP');
    }

    public function testTriNomDesc(): void
    {
        $client = static::createClient();
        $client->request('GET', '/playlists/tri/name/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('tbody tr:first-child h5', 'Symfony');
    }

    public function testFiltreParNomNombreResultats(): void
    {
        $client = static::createClient();
        $client->request('POST', '/playlists/recherche/name', [
            'recherche' => 'Symfony'
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $client->getCrawler()->filter('tbody tr'));
    }

    public function testFiltreParNomPremierResultat(): void
    {
        $client = static::createClient();
        $client->request('POST', '/playlists/recherche/name', [
            'recherche' => 'Symfony'
        ]);
        $this->assertSelectorTextContains('tbody tr:first-child h5', 'Symfony');
    }

    public function testClicVoirDetailOuvreLaBonnePage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/playlists/tri/name/ASC');
        $client->click(
            $client->getCrawler()->filter('tbody tr:first-child td a')->first()->link()
        );
        $this->assertResponseIsSuccessful();
        // Vérifie que le nom de la playlist "Bases PHP" apparaît dans le détail
        $this->assertSelectorTextContains('h4.text-info', 'Bases PHP');
    }
}
