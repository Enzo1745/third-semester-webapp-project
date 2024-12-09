<?php

namespace App\Tests;

use App\Entity\Sa;
use App\Entity\Room;
use App\Repository\Model\SaState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class AssociateSaTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        // Créer le client de test
        $this->client = static::createClient();

        // Récupérer l'EntityManager
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        // Purger la base de données avant chaque test
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function testPageIsAccessibleAndFormIsPresent(): void
    {
        // Créer une SA disponible
        $sa = new Sa();
        $sa->setState(SaState::Available);
        $this->entityManager->persist($sa);

        // Créer une salle sans SA
        $room = new Room();
        $room->setRoomName('Salle Test');
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $this->entityManager->persist($room);

        $this->entityManager->flush();

        // Demander la page
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifier la présence du formulaire
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('button:contains("Associer")');
    }

    public function testPageWithoutAvailableRoom(): void
    {
        // S'assurer qu'il n'y a aucune salle disponible dans la base de données de test
        $rooms = $this->entityManager->getRepository(Room::class)->findAll();
        foreach ($rooms as $room) {
            $this->entityManager->remove($room);
        }
        $this->entityManager->flush();

        // Demander la page
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifier le message approprié
        // Mettre à jour le texte pour correspondre à "Nombre de SA disponibles : 0"
        $this->assertSelectorExists('.alert.alert-info');
        $this->assertSelectorTextContains('.alert.alert-info', 'Nombre de SA disponibles : 0');
    }

    public function testPageWithoutAvailableSa(): void
    {
        // Créer une salle disponible
        $room = new Room();
        $room->setRoomName('Salle Disponible');
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $this->entityManager->persist($room);

        // Créer une SA non disponible (état 'Functional')
        $saNotAvailable = new Sa();
        $saNotAvailable->setState(SaState::Functional);
        $this->entityManager->persist($saNotAvailable);

        $this->entityManager->flush();

        // Demander la page
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifier le message approprié
        // Mettre à jour le texte pour correspondre à "Nombre de SA disponibles : 0"
        $this->assertSelectorExists('.alert.alert-info');
        $this->assertSelectorTextContains('.alert.alert-info', 'Nombre de SA disponibles : 0');
    }


    public function testSaAssociationAndAvailableSaCount(): void
    {
        // Créer une SA disponible
        $sa = new Sa();
        $sa->setState(SaState::Available);
        $this->entityManager->persist($sa);

        // Créer une salle disponible
        $room = new Room();
        $room->setRoomName('Salle Test');
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $this->entityManager->persist($room);

        $this->entityManager->flush();

        // Demander la page pour afficher le formulaire
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        // Sélectionner le formulaire et le soumettre
        $form = $crawler->selectButton('Associer')->form([
            'sa_management[room]' => $room->getId(),
        ]);

        // Soumettre le formulaire
        $this->client->submit($form);

        // Réinitialiser l'EntityManager pour recharger les entités
        $this->entityManager->clear();

        // Récupérer la SA et vérifier que l'association avec la salle a bien eu lieu
        $sa = $this->entityManager->getRepository(Sa::class)->find($sa->getId());
        $this->assertSame($room->getId(), $sa->getRoom()->getId());

        // Vérifier que l'état de la SA a été mis à jour en 'Functional'
        $this->assertSame(SaState::Functional, $sa->getState());

        // Vérifier que le nombre de SA disponibles a diminué
        $nbSaAvailable = $this->entityManager->getRepository(Sa::class)->count(['state' => SaState::Available]);
        $this->assertSame(0, $nbSaAvailable);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Fermer l'EntityManager après chaque test
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
