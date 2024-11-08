<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ConnexionTest extends WebTestCase
{
    public function testIdentifiantFieldIsRequired(): void
    {
        $client = static::createClient();

        // Accéder à la page de connexion
        $crawler = $client->request('GET', '/connexion');

        // Soumettre le formulaire avec l'identifiant vide et un mot de passe valide
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[identifiant]' => '',  // Identifiant vide
            'connection[motdepasse]' => 'password123',  // Mot de passe valide
        ]);

        $client->submit($form);

        // Vérifier que la page de connexion est affichée
        $this->assertResponseIsSuccessful();

        // Vérifier qu'un message d'erreur est affiché pour le champ identifiant
        $this->assertSelectorTextContains('.form-error', 'L\'identifiant ne peut pas être vide.');
    }

    public function testMotdepasseFieldIsRequired(): void
    {
        $client = static::createClient();

        // Accéder à la page de connexion
        $crawler = $client->request('GET', '/connexion');

        // Soumettre le formulaire avec un identifiant valide et un mot de passe vide
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[identifiant]' => 'user@example.com',  // Identifiant valide
            'connection[motdepasse]' => '',  // Mot de passe vide
        ]);

        $client->submit($form);

        // Vérifier que la page de connexion est affichée
        $this->assertResponseIsSuccessful();

        // Vérifier qu'un message d'erreur est affiché pour le champ mot de passe
        $this->assertSelectorTextContains('.form-error', 'Le mot de passe ne peut pas être vide.');
    }

    public function testFormSubmittedWithValidData(): void
    {
        $client = static::createClient();

        // Accéder à la page de connexion
        $crawler = $client->request('GET', '/connexion');

        // Soumettre le formulaire avec des données valides
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[identifiant]' => 'user@example.com',
            'connection[motdepasse]' => 'password123',
        ]);

        $client->submit($form);

        // Vérifier que le formulaire redirige vers la page de succès
        $this->assertResponseRedirects('/connexion/done');

        // Suivre la redirection et vérifier que la page de succès s'affiche
        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Connexion réussie');
    }
}
