<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\Model\UserRoles;
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
        $this->clearDatabase();
    }

    private function clearDatabase()
    {
        $this->entityManager->createQueryBuilder()
            ->delete()
            ->from(User::class, 'u')
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function testConnexionRoute(): void
    {
        print_r("Test de la connexion a la Route /connexion\n");
        $this->client->request('GET', '/connexion');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testConnexionCharge(): void
    {
        print_r("Test de la connexion en tant que Chargé de mission\n");
        $user = new User();
        $user->setUsername('charge');
        $user->setPassword('1234');
        $user->setRole(UserRoles::Charge);
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

    public function testConnexionTechnicien(): void
    {
        print_r("Test de la connexion en tant que technicien\n");
        $user = new User();
        $user->setUsername('tech');
        $user->setPassword('5678');
        $user->setRole(UserRoles::Technicien);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('connection_button')->form([
            'connection[username]' => 'tech',
            'connection[password]' => '5678',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/charge/salles/ajouter');
    }

    public function testInvalidIdentifiers(): void
    {
        print_r("Test de la connexion en cas d'identifiants invalides\n");
        $user = new User();
        $user->setUsername('charge');
        $user->setPassword('1234');
        $user->setRole(UserRoles::Charge);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/connexion');

        print_r(" -Test du cas ou l'identifiant est invalide\n");
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => 'nonexistentuser',
            'connection[password]' => '1234',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.alert-danger', 'mot de passe ou identifiant invalide');

        $crawler = $this->client->request('GET', '/connexion');

        print_r(" -Test du cas ou le mot de passe est invalide\n");
        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => 'charge',
            'connection[password]' => 'wrongpassword',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.alert-danger', 'mot de passe ou identifiant invalide');
    }

    public function testEmptyIdentifiers(): void
    {
        print_r("Test de la connexion en cas d'identifiants vides\n");
        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => '',
            'connection[password]' => '',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.alert-danger', 'Le champ Identifiant et Mot de passe est obligatoire');
    }

    public function testMissingUsername(): void
    {
        print_r("Test de la connexion en cas d'ID maquant\n");
        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => '',
            'connection[password]' => '1234',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.alert-danger', 'Le champ Identifiant est obligatoire');
    }

    public function testMissingPassword(): void
    {
        print_r("Test de la connexion en cas de Mot De Passe manquant\n");
        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('Se connecter')->form([
            'connection[username]' => 'charge',
            'connection[password]' => '',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.alert-danger', 'Le champ Mot de passe est obligatoire');
    }
}
