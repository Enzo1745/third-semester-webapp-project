<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConnexionTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Create test users
        $userCharge = new User();
        $userCharge->setUsername('charge_user')
            ->setPassword(password_hash('password123', PASSWORD_BCRYPT))
            ->setRoles(['ROLE_CHARGE']);
        $this->entityManager->persist($userCharge);

        $userTech = new User();
        $userTech->setUsername('tech_user')
            ->setPassword(password_hash('password123', PASSWORD_BCRYPT))
            ->setRoles(['ROLE_TECH']);
        $this->entityManager->persist($userTech);

        $this->entityManager->flush();
    }

    public function testSuccessfulLoginAsCharge(): void
    {
        // Access login page
        $crawler = $this->client->request('GET', '/connexion');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');


        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = 'charge_user';
        $form['password'] = 'password123';
        $this->client->submit($form);

        // Assert redirection to the room list page
        $this->assertResponseRedirects('/charge/salles');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Liste salles');
    }

    public function testFailedLogin(): void
    {

        $crawler = $this->client->request('GET', '/connexion');
        $this->assertResponseIsSuccessful();


        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = 'invalid_user';
        $form['password'] = 'wrong_password';
        $this->client->submit($form);

        // Assert login error
        $this->assertResponseRedirects('/connexion');

    }


    protected function tearDown(): void
    {
        if ($this->entityManager) {
            $this->entityManager->createQuery('DELETE FROM App\\Entity\\User')->execute();
            $this->entityManager->close();
            $this->entityManager = null;
        }

        parent::tearDown();
    }
}
