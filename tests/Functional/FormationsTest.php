<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormationsTest extends WebTestCase
{
    public function testPageFormationsEstAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/formations');
        $this->assertResponseIsSuccessful();
    }

    public function testTriTitreAsc(): void
    {
        $client = static::createClient();
        $client->request('GET', '/formations/tri/title/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('tbody tr:first-child h5', 'Apprendre PHP');
    }

    public function testTriTitreDesc(): void
    {
        $client = static::createClient();
        $client->request('GET', '/formations/tri/title/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('tbody tr:first-child h5', 'Zend et PHP');
    }

    public function testFiltreParTitreNombreResultats(): void
    {
        $client = static::createClient();
        $client->request('POST', '/formations/recherche/title', [
            'recherche' => 'PHP'
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $client->getCrawler()->filter('tbody tr'));
    }

    public function testFiltreParTitrePremierResultat(): void
    {
        $client = static::createClient();
        $client->request('POST', '/formations/recherche/title', [
            'recherche' => 'PHP'
        ]);
        // Triées par date DESC : "Zend et PHP" en premier
        $this->assertSelectorTextContains('tbody tr:first-child h5', 'Zend et PHP');
    }

    public function testClicSurFormationOuvreLaBonnePage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/formations/tri/title/ASC');
        // Clique sur le lien de la miniature de la première formation
        $client->click(
            $client->getCrawler()->filter('tbody tr:first-child td a')->first()->link()
        );
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4', 'Apprendre PHP');
    }
}