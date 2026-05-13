<?php
namespace App\Tests\Integration;

use App\Entity\Formation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormationValidationTest extends IntegrationTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        // Appelle le setUp() parent qui recharge la BDD
        parent::setUp();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testDatePasseeEstValide(): void
    {
        $formation = new Formation();
        $formation->setTitle('Test');
        $formation->setVideoId('abc123');
        // Date dans le passé : doit être valide
        $formation->setPublishedAt(new \DateTime('2020-01-01'));

        $erreurs = $this->validator->validate($formation);
        $erreursDate = array_filter(
            iterator_to_array($erreurs),
            fn($e) => $e->getPropertyPath() === 'publishedAt'
        );

        $this->assertCount(0, $erreursDate, 'Une date passée doit être valide.');
    }

    public function testDateAujourduiEstValide(): void
    {
        $formation = new Formation();
        $formation->setTitle('Test');
        $formation->setVideoId('abc123');
        // Date du jour : doit être valide (LessThanOrEqual today)
        $formation->setPublishedAt(new \DateTime('today'));

        $erreurs = $this->validator->validate($formation);
        $erreursDate = array_filter(
            iterator_to_array($erreurs),
            fn($e) => $e->getPropertyPath() === 'publishedAt'
        );

        $this->assertCount(0, $erreursDate, 'La date du jour doit être valide.');
    }

    public function testDateFutureEstInvalide(): void
    {
        $formation = new Formation();
        $formation->setTitle('Test');
        $formation->setVideoId('abc123');
        // Date dans le futur : doit déclencher une erreur de validation
        $formation->setPublishedAt(new \DateTime('+1 day'));

        $erreurs = $this->validator->validate($formation);
        $erreursDate = array_filter(
            iterator_to_array($erreurs),
            fn($e) => $e->getPropertyPath() === 'publishedAt'
        );

        $this->assertCount(1, $erreursDate, 'Une date future doit être invalide.');
    }
}
