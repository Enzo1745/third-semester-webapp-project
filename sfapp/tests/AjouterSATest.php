<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sa;
use App\Repository\SaRepository;

class AjouterSATest extends WebTestCase
{
    public function testAddSa()
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Compte le nombre de SA avant l'ajout
        $initialCount = $entityManager->getRepository(Sa::class)->count([]);

        // Accède à la page contenant le formulaire d'ajout
        $crawler = $client->request('GET', '/technicien/sa'); // Remplace par la bonne route

        // Vérifie que la page s'affiche correctement
        $this->assertResponseIsSuccessful();
        /*
                // Soumet le formulaire du bouton
                $button = $crawler->selectButton('Ajouter un SA')->form(); // Assurez-vous que le texte est exact
                $client->submit($button);

                       // Vérifie que la redirection a réussi
                       $this->assertResponseRedirects('/technicien/sa'); // Remplace par la bonne route
                      $client->followRedirect();

                       // Vérifie que le SA a été ajouté
                       $finalCount = $entityManager->getRepository(Sa::class)->count([]);
                       $this->assertEquals($initialCount + 1, $finalCount, 'Le SA n\'a pas été ajouté correctement.');
                */  }

}
