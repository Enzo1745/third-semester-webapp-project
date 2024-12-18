<?php

namespace App\Tests;

use App\Entity\Norm;
use App\Entity\Room;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Sa;
use App\Repository\SaRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Model\SAState;

class DeleteSaTest extends WebTestCase
{
    /**
     * Test deleting a SA with no room associated.
     *
     * This test checks that when a SA that is not associated with any Room is deleted,
     * it is correctly removed from the database, and a success flash message is displayed.
     *
    public function testDeleteSa(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        $saRepository = $entityManager->getRepository(Sa::class);

        $purger = new ORMPurger($entityManager);
        $purger->purge();

        $summerNorm = new Norm();
        $summerNorm->setNormSeason(NormSeason::Summer)
            ->setNormType(NormType::Comfort)
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $entityManager->persist($summerNorm);

        $winterNorm = new Norm();
        $winterNorm->setNormSeason(NormSeason::Winter)
            ->setNormType(NormType::Comfort)
            ->setHumidityMinNorm(40)
            ->setHumidityMaxNorm(80)
            ->setTemperatureMinNorm(15)
            ->setTemperatureMaxNorm(22)
            ->setCo2MinNorm(300)
            ->setCo2MaxNorm(900);
        $entityManager->persist($winterNorm);

        $sa = new Sa();
        $sa->setId(1);
        $sa->setState(SAState::Available);
        $sa->setRoom(null);
        $entityManager->persist($sa);
        $entityManager->flush();

        $saId = $sa->getId();


        $client->request('POST', "/technicien/sa/delete/$saId");


        $this->assertResponseRedirects('/technicien/sa', 302);


        $deletedSa = $saRepository->find($saId);
        $this->assertNull($deletedSa, 'La SA a été supprimée de la base de données');


        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success', 'Le message flash de succès est affiché');
        $this->assertSelectorTextContains('.alert.alert-success', 'Système d\'acquisition supprimé avec succès');
    }

    /**
     * Test deleting a SA that is associated with a Room.
     *
     * This test checks that when a SA that is associated with a Room is deleted,
     * the relationship between the Room and the SA is correctly dissociated.
     *
    public function testDeleteSaWithRoom(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);


        $room = new Room();
        $room->setRoomName('Room Test');
        $room->setNbRadiator(4);
        $room->setNbWindows(15);


        $sa = new Sa();
        $sa->setId(2);
        $sa->setState(SAState::Available);
        $sa->setRoom($room);
        $room->setSa($sa);


        $entityManager->persist($room);
        $entityManager->persist($sa);
        $entityManager->flush();


        $saId = $sa->getId();
        $roomId = $room->getId();


        $client->request('POST', "/technicien/sa/delete/$saId");


        $this->assertResponseRedirects('/technicien/sa', 302);


        $entityManager->clear();


        $room = $entityManager->getRepository(Room::class)->find($roomId);
        $this->assertNotNull($room, 'La salle existe toujours');
        $this->assertNull($room->getSa(), 'La salle n\'est plus associée à la SA');


        $deletedSa = $entityManager->getRepository(Sa::class)->find($saId);
        $this->assertNull($deletedSa, 'La SA a été supprimée de la base de données');


        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success', 'Le message flash de succès est affiché');
        $this->assertSelectorTextContains('.alert.alert-success', 'Système d\'acquisition supprimé avec succès');
    }
    */

}
