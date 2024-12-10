<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sa;
use App\Repository\SaRepository;

class SAControllerTest extends WebTestCase
{
    public function testAddSa()
    {
        // Démarre le client HTTP Symfony
        $client = static::createClient();

        // Récupère l'EntityManager
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Compte le nombre d'entités Sa avant l'action
        $initialCount = $entityManager->getRepository(Sa::class)->count([]);

        // Ouvre la page contenant le bouton
        $crawler = $client->request('GET', '/page/avec/le/bouton');  // Remplace par la route de ta page

        // Clique sur le bouton "Ajouter un Système d'Acquisition"
        $button = $crawler->selectButton('Ajouter un Système d\'Acquisition')->form();
        $client->submit($button);

        // Vérifie que la réponse est correcte
        $this->assertResponseIsSuccessful();

        // Compte le nombre d'entités Sa après l'action
        $finalCount = $entityManager->getRepository(Sa::class)->count([]);

        // Vérifie que le nombre d'entités Sa a augmenté de 1
        $this->assertEquals($initialCount + 1, $finalCount, 'Le nombre de systèmes d\'acquisition n\'a pas augmenté comme prévu');
    }
}
