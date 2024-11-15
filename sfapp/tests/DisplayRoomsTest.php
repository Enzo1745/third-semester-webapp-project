<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DisplayRoomsTest extends WebTestCase
{
    public function test(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'charge/salles/liste');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');
    }


}
