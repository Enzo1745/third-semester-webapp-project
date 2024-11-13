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

        // Vérifier si la page de connexion est récupérée
        $this->assertResponseIsSuccessful();

        // Récupérer le formulaire
        $form = $crawler->selectButton('Se connecter')->form();

        // Soumettre un formulaire avec des champs vides
        $form['connection[identifiant]'] = '';
        $form['connection[motdepasse]'] = '';

        // Soumettre le formulaire
        $client->submit($form);

        // Vérifier que le message d'erreur est bien pour le champ identifiant
        $this->assertSelectorTextContains('.alert-danger-ID', 'Le champ Identifiant est obligatoire');

        // Vérifier que le message d'erreur pour motdepasse n'est pas affiché pour identifiant
        $this->assertSelectorTextContains('.alert-danger-MDP', 'Le champ Mot de passe est obligatoire');
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
        // Vérifier que l'URL actuelle est bien /connexion/done
        $this->assertSame('/connexion/done', $client->getRequest()->getRequestUri());
    }
}
