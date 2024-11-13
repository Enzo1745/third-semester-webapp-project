<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GestionSaTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/charge/gestion_sa');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Gestion des systèmes d'acquisition");
    }
}

