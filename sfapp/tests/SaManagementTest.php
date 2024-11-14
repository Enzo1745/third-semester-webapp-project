<?php

namespace App\Tests;

use App\Entity\Sa;
use App\Entity\Salle;
use App\Repository\Model\SaState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class SaManagementTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {

        // Créer le client
        $this->client = static::createClient();

        // Obtenir l'EntityManager
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        // **Purger la base de données avant chaque test**
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function testPageIsAccessibleAndFormIsPresent(): void
    {
        // **Créer les données de test nécessaires**

        // Créer un SA disponible
        $sa = new Sa();
        $sa->setEtat(SaState::Available);
        $this->entityManager->persist($sa);

        // Créer une salle sans SA associé
        $salle = new Salle();
        $salle->setNomSalle('Salle Test');
        $this->entityManager->persist($salle);

        $this->entityManager->flush();

        // **Effectuer le test**

        // Faire la requête GET
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifier que le formulaire existe
        $this->assertSelectorExists('form[name="form_name"]');

        // Vérifier la présence du bouton 'Associer'
        $this->assertSelectorExists('button:contains("Associer")');
    }

    public function testPageWithoutAvailableSalle(): void
    {
        // **Créer les données de test nécessaires**

        // Créer un SA disponible
        $saAvailable = new Sa();
        $saAvailable->setEtat(SaState::Available);
        $this->entityManager->persist($saAvailable);

        // Créer une salle et l'associer à un SA fonctionnel (salle indisponible)
        $salle = new Salle();
        $salle->setNomSalle('Salle D204');
        $this->entityManager->persist($salle);

        $saFunctional = new Sa();
        $saFunctional->setEtat(SaState::Functional);
        $saFunctional->setSalle($salle);
        $this->entityManager->persist($saFunctional);

        $this->entityManager->flush();

        // **Effectuer le test**

        // Faire la requête GET
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifier que le formulaire n'existe pas
        $this->assertSelectorNotExists('form[name="form_name"]');

        // Vérifier que le message "Aucune salle disponible." est affiché
        $this->assertSelectorTextContains('.addForm', 'Aucune salle disponible.');
    }

    public function testPageWithoutAvailableSa(): void
    {
        // **Créer les données de test nécessaires**

        // Créer une salle disponible
        $salle = new Salle();
        $salle->setNomSalle('Salle Disponible');
        $this->entityManager->persist($salle);

        // Créer un SA non disponible (état 'Functional')
        $saNotAvailable = new Sa();
        $saNotAvailable->setEtat(SaState::Functional);
        $this->entityManager->persist($saNotAvailable);

        $this->entityManager->flush();

        // **Effectuer le test**

        // Faire la requête GET
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifier que le formulaire n'existe pas
        $this->assertSelectorNotExists('form[name="form_name"]');

        // Vérifier que le message "Aucun SA disponible." est affiché
        $this->assertSelectorTextContains('.addForm', 'Aucun SA disponible.');
    }

    public function testSaAssociationAndAvailableSaCount(): void
    {
        $client = $this->client;
        $entityManager = $this->entityManager;

        // Créer les données de test nécessaires
        $sa = new Sa();
        $sa->setEtat(SaState::Available);
        $entityManager->persist($sa);

        $salle = new Salle();
        $salle->setNomSalle('Salle Test');
        $entityManager->persist($salle);

        $entityManager->flush();

        // Afficher le formulaire et soumettre les données
        $crawler = $client->request('GET', '/charge/gestion_sa/associer');
        $form = $crawler->selectButton('Associer')->form([
            'sa_management[salle]' => $salle->getId(),
        ]);

        $client->submit($form);

        $entityManager->clear();

        // Recharger les entités
        $sa = $entityManager->getRepository(Sa::class)->find($sa->getId());

        // Vérifier que le SA est correctement associé à la salle
        $this->assertSame($salle->getId(), $sa->getSalle()->getId());

        // Vérifier que l'état du SA est passé à 'Functional'
        $this->assertSame(SaState::Functional, $sa->getEtat());

        // Vérifier que le nombre de SA disponibles est décrémenté
        $nbSaAvailable = $entityManager->getRepository(Sa::class)->count(['etat' => SaState::Available]);
        $this->assertSame(0, $nbSaAvailable);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Fermer l'EntityManager
        $this->entityManager->close();
        $this->entityManager = null;
    }

}