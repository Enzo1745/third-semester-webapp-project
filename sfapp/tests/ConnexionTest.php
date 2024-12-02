<?php

namespace App\Tests;

use App\Entity\User;
use http\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ConnexionTest extends WebTestCase
{
    private $client;
    private $entityManager;


    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->clearDatabase(); //We clear here because we flush in our test and we flush because if we dont it does not work
    }

    private function clearDatabase()
    {
        // Fetch the repository for User entity and delete all users
        $userRepository = $this->entityManager->getRepository(User::class);

        // You can adjust the query to only clear specific users if needed
        $this->entityManager->createQueryBuilder()
            ->delete()
            ->from(User::class, 'u')
            ->getQuery()
            ->execute();

        // Optionally clear other entities (e.g., orders, sessions) if needed
        // $this->entityManager->createQueryBuilder()
        //     ->delete()
        //     ->from(Order::class, 'o')
        //     ->getQuery()
        //     ->execute();

        // Flush and clear the EntityManager to ensure no leftover objects in memory
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function testConnexionRoute():void
    {
        $this->client->request('GET', '/connexion');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testConnexionCharge():void
    {
        $user = new User();
        $user->setUsername('charge');
        $user->setPassword('1234');
        $user->setRole('charge');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('connection_button')->form([
            'connection[username]' => 'charge',
            'connection[password]' => '1234',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/charge/salles');
    }

    public function testConnexionTechnicien():void
    {
        $user = new User();
        $user->setUsername('tech');
        $user->setPassword('5678');
        $user->setRole('technicien');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('connection_button')->form([
            'connection[username]' => 'tech',
            'connection[password]' => '5678',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/charge/salles/ajouter'); //a modifier apres le merge
    }

    public function testInvalidIdentifiers(): void
    {
        // Create a user with the username 'charge' and password '1234'
        $user = new User();
        $user->setUsername('charge');
        $user->setPassword('1234');  // Assuming plain text for simplicity
        $user->setRole('charge');

        // Persist the user to the test database
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Test for invalid username (non-existent user)
        $crawler = $this->client->request('GET', '/connexion');

        // Fill in the form with a non-existent username and any password
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => 'nonexistentuser', // Non-existent username
            'connection[password]' => '1234',  // Random password
        ]);

        // Submit the form
        $this->client->submit($form);

        // Assert that the login failed and the user stays on the same page
        $this->assertResponseStatusCodeSame(Response::HTTP_OK); // Should stay on the same page
        $this->assertSelectorTextContains('.alert-danger', 'mot de passe ou identifiant invalide'); // Error message

        // Test for incorrect password (with existing user)
        $crawler = $this->client->request('GET', '/connexion');

        // Fill in the form with the correct username but incorrect password
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => 'charge', // Correct username
            'connection[password]' => 'wrongpassword',  // Incorrect password
        ]);

        // Submit the form
        $this->client->submit($form);

        // Assert that the login failed and the user stays on the same page
        $this->assertResponseStatusCodeSame(Response::HTTP_OK); // Should stay on the same page
        $this->assertSelectorTextContains('.alert-danger', 'mot de passe ou identifiant invalide'); // Error message
    }


    public function testEmptyIdentifiers(): void
    {
        // Request the connexion page
        $crawler = $this->client->request('GET', '/connexion');

        // Fill in the form with empty fields
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => '',  // Empty username
            'connection[password]' => '',  // Empty password
        ]);

        // Submit the form
        $this->client->submit($form);

        // Assert the user stays on the same page and sees error messages
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.alert-danger', 'Le champ Identifiant et Mot de passe est obligatoire');
    }

    public function testMissingUsername(): void
    {
        // Request the connexion page
        $crawler = $this->client->request('GET', '/connexion');

        // Fill in the form with missing username
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => '',  // Empty username
            'connection[password]' => '1234',  // Correct password
        ]);

        // Submit the form
        $this->client->submit($form);

        // Assert the user stays on the same page and sees the missing username error
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.alert-danger', 'Le champ Identifiant est obligatoire');
    }

    public function testMissingPassword(): void
    {
        // Request the connexion page
        $crawler = $this->client->request('GET', '/connexion');

        // Fill in the form with missing password
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => 'charge',  // Correct username
            'connection[password]' => '',  // Empty password
        ]);

        // Submit the form
        $this->client->submit($form);

        // Assert the user stays on the same page and sees the missing password error
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.alert-danger', 'Le champ Mot de passe est obligatoire');
    }

}
