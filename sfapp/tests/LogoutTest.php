<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class LogoutTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();


        $user = new User();
        $user->setUsername('charge')
            ->setPassword(password_hash('1234', PASSWORD_BCRYPT)) // Hashed password
            ->setRoles(['ROLE_CHARGE']);

        $user2 = new User();
        $user2->setUsername('tech')
            ->setPassword(password_hash('5678', PASSWORD_BCRYPT)) // Hashed password
            ->setRoles(['ROLE_TECH']);

        $this->entityManager->persist($user);
        $this->entityManager->persist($user2);
        $this->entityManager->flush();
    }

    public function testLogoutAndAccessRestrictionCharge(): void
    {
        // Retrieve the test user
        $userRepository = $this->client->getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['username' => 'charge']);

        // Log in as the test user
        $this->client->loginUser($testUser);

        // Access a protected page
        $this->client->request('GET', '/charge/salles');
        $this->assertResponseIsSuccessful();

        // Log out
        $this->client->request('GET', '/deconnexion');
        $this->assertResponseRedirects('/');

        // Verify that access to the protected page is now restricted
        $this->client->request('GET', '/charge/salles');
        $this->assertResponseRedirects('/connexion');

        // verify that charge that try to go on the technician page is no accepted
        $this->client->request('GET', '/technicien/sa');
        $this->assertResponseRedirects('/connexion');
    }

    public function testLogoutAndAccessRestrictionTech(): void
    {
        // Retrieve the test user
        $userRepository = $this->client->getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['username' => 'tech']);

        // Log in as the test user
        $this->client->loginUser($testUser);

        // Access a protected page
        $this->client->request('GET', '/technicien/sa');
        $this->assertResponseIsSuccessful();

        // Log out
        $this->client->request('GET', '/deconnexion');
        $this->assertResponseRedirects('/');

        // Verify that access to the protected page is now restricted
        $this->client->request('GET', '/technicien/sa');
        $this->assertResponseRedirects('/connexion');

        // verify that tech that try to go on the charge page is no accepted
        $this->client->request('GET', '/charge/salles');
        $this->assertResponseRedirects('/connexion');
    }

    protected function tearDown(): void
    {
        // Remove the test users
        if ($this->entityManager) {
            $this->entityManager->createQuery('DELETE FROM App\\Entity\\User')->execute();
            $this->entityManager->close();
            $this->entityManager = null;
        }

        parent::tearDown();
    }
}
