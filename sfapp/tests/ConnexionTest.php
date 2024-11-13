<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ConnexionTest extends WebTestCase
{
    // check if an errors appears to the user if there is no Username or password in the connection form
    public function testIdentifiantFieldIsRequired(): void
    {
        $client = static::createClient();

        // Access the connection page
        $crawler = $client->request('GET', '/connexion');

        // check if we are into the connection page
        $this->assertResponseIsSuccessful();

        // get the connection form
        $form = $crawler->selectButton('Se connecter')->form();

        // submit an empty form
        $form['connection[username]'] = '';
        $form['connection[password]'] = '';

        // submit the empty form
        $client->submit($form);

        // check if the error message apears for the empty ID
        $this->assertSelectorTextContains('.alert-danger-User', 'Le champ Identifiant est obligatoire');

        // check if the error message apears for the empty Password
        $this->assertSelectorTextContains('.alert-danger-PWD', 'Le champ Mot de passe est obligatoire');
    }

    // Check if the form redirects to the succes page if the form is correctly submitted
    public function testFormSubmittedWithValidData(): void
    {
        $client = static::createClient();

        // Access the connection page
        $crawler = $client->request('GET', '/connexion');

        // Submit the form with valid data
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => 'usernameexample',
            'connection[password]' => 'password123',
        ]);

        $client->submit($form);

        // check if the form redirecs to the succes page
        $this->assertResponseRedirects('/connexion/succes');

        // Follows the redirection and check of the page apears
        $client->followRedirect();
        // check if the URL is /connexion/done
        $this->assertSame('/connexion/succes', $client->getRequest()->getRequestUri());
    }
}
