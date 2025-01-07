<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SAState;

class DisplayRoomsUserTest extends WebTestCase
{
    public function testHomePageAccessibility(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Page d\'accueil');
    }

    public function testRoomSelectionDisplaysData(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');


        $room = new Room();
        $room->setRoomName('D206');
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $entityManager->persist($room);

        $sa = new Sa();
        $sa->setId(time());
        $sa->setState(SAState::Installed);
        $sa->setTemperature(22);
        $sa->setHumidity(50);
        $sa->setCO2(800);
        $sa->setRoom($room); // Liaison avec la salle
        $entityManager->persist($sa);

        $room->setIdSa($sa->getId());


        $entityManager->flush();


        $crawler = $client->request('GET', '/');


        $this->assertSelectorExists('form[name="search_rooms"]', 'Le formulaire de sélection des salles n\'est pas affiché.');
        $this->assertGreaterThan(0, $crawler->filter('select[name="search_rooms[salle]"] option')->count(), 'Le champ salle ne contient aucune option.');


        $form = $crawler->filter('form[name="search_rooms"]')->form([
            'search_rooms[salle]' => $room->getId(),
        ]);
        $client->submit($form);




        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.value', 'D206', 'Le numéro de salle est incorrect.');
        $this->assertSelectorTextContains('.temp', '22°', 'La température est incorrecte.');
        $this->assertSelectorTextContains('.humidity', '50%', 'L\'humidité est incorrecte.');
        $this->assertSelectorTextContains('.co2', '800', 'Le niveau de CO2 est incorrect.');


        $entityManager->clear();
    }

}

