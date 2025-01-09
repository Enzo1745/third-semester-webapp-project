<?php

namespace App\Controller;

use App\Entity\Measure;
use App\Entity\Room;
use App\Repository\LastUpdateRepository;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Sa;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\LastUpdate;
use function Symfony\Component\Clock\now;

class ApiController extends AbstractController
{
    private $client;
    private $logger;
    private $entityManager;
    private $dbList;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->entityManager = $entityManager;

        // List of all the database name in the API (commented if there is a problem)
        $this->dbList = [
            /*"sae34bdk1eq1", "sae34bdk1eq2", */"sae34bdk1eq3",
            /*"sae34bdk2eq1", "sae34bdk2eq2", */"sae34bdk2eq3",
            /*"sae34bdl1eq1", */"sae34bdl1eq2",/* "sae34bdl1eq3",*/
            /*"sae34bdl2eq1", "sae34bdl2eq2", */"sae34bdl2eq3",
            /*"sae34bdm1eq1", "sae34bdm1eq2",*/ "sae34bdm1eq3",
        ];
    }

    /**
     * @brief Function to get all data from all the databases since 2025-01-01.
     *
     * @param LastUpdateRepository $lastUpdateRepository
     * @return Response
     */
    #[Route('/api/obtenir_donnees', name: 'api_obtenir_donnees')]
    public function getDataFromApiIn2025(LastUpdateRepository $lastUpdateRepository): Response
    {

        // Get the last update date in the local database
        $lastUpdate = $lastUpdateRepository->findLastUpdate();

        // If there is no last update, meaning it's the first time that the database is getting data
        if (!$lastUpdate) {
            $lastUpdate = new LastUpdate();
            $this->entityManager->persist($lastUpdate);
            $this->entityManager->flush();
        }

        // Get the actual date and the last update date in Y-m-d format (ex : 2025-06-24)
        $actualDate = new \DateTime();
        $actualDate = $actualDate->format('Y-m-d');
        $lastUpdateDate = $lastUpdate->getDate()->format("Y-m-d");

        //
        $url = "https://sae34.k8s.iut-larochelle.fr/api/captures/interval?date1={$lastUpdateDate}&date2={$actualDate}&page=1";


        // Get the login informations from the environnement file
        $username = $_ENV['API_USERNAME'];
        $userpass = $_ENV['API_USERPASS'];

        foreach ($this->dbList as $dbname) {
            try {
                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'accept' => 'application/json',
                        'dbname' => $dbname,
                        'username' => $username,
                        'userpass' => $userpass,
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                $this->logger->info('API Response Status Code: ' . $statusCode);

                $data = $response->toArray();
                $this->logger->info('API Response Data: ' . json_encode($data));

                // Store the returned data in the database
                $this->storeDataInDatabase($data);

                // Update the last update date in the database
                $lastUpdate->setDate(new \DateTime('yesterday'));
                $this->entityManager->persist($lastUpdate);
                $this->entityManager->flush();

                return new Response('Données récupérées et stockées avec succès');
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la récupération des données: ' . $e->getMessage());
                return new Response('Erreur lors de la récupération des données: ' . $e->getMessage(), 500);
            } catch (TransportExceptionInterface $e) {
            } catch (ClientExceptionInterface $e) {
            } catch (DecodingExceptionInterface $e) {
            } catch (RedirectionExceptionInterface $e) {
            } catch (ServerExceptionInterface $e) {
            }
        }
        return new Response('Données non récupérées.');
    }

    /**
     * @brief Function to update the database with new data
     * @param array $data
     * @return void
     * @throws \Exception
     */
    private function storeDataInDatabase(array $data)
    {
        // Group data by 'nomsa' to ensure we process all measures for the same SA together
        $groupedData = [];
        foreach ($data as $item) {
            $saName = $item['nomsa'];
            if (!isset($groupedData[$saName])) {
                $groupedData[$saName] = [];
            }
            $groupedData[$saName][] = $item;
        }

        foreach ($groupedData as $saName => $items) {
            // Sort items by capture date in descending order
            usort($items, function($a, $b) {
                $dateA = new \DateTime($a['dateCapture'], new \DateTimeZone('UTC'));
                $dateB = new \DateTime($b['dateCapture'], new \DateTimeZone('UTC'));

                return $dateB->getTimestamp() - $dateA->getTimestamp();
            });

            $sa = $this->entityManager->getRepository(Sa::class)->findOneBy(['name' => $saName]);

            if (!$sa) {
                $sa = new Sa();
                $sa->setName($saName);
                $sa->setState(SAState::Installed);
                $this->entityManager->persist($sa);
            }

            // Track the latest measure for each type
            $latestMeasures = [];

            foreach ($items as $item) {
                $measure = $this->updateOrCreateMeasure($item, $sa);
                if ($measure) {
                    $this->logger->info('Création ou mise à jour de la mesure avec la valeur : ' . $measure->getValue());
                    $this->entityManager->persist($measure);
                    $sa->addMeasure($measure);

                    // Update the latest measure for this type
                    if (!isset($latestMeasures[$item['nom']]) || strtotime($item['dateCapture']) > strtotime($latestMeasures[$item['nom']]['dateCapture'])) {
                        $latestMeasures[$item['nom']] = $item;
                        $latestMeasures[$item['localisation']] = $item;
                    }
                }
            }

            // Update the SA with the latest measure values
            foreach ($latestMeasures as $item) {
                if ($item === 'temp') {
                    $sa->setTemperature(floatval($item['valeur']));
                } elseif ($item === 'hum') {
                    $sa->setHumidity(floatval($item['valeur']));
                } elseif ($item === 'co2') {
                    $sa->setCO2(floatval($item['valeur']));
                } elseif ($item === 'lum') {
                    $sa->setLum(floatval($item['valeur']));
                } elseif ($item === 'pres') {
                    $sa->setPres(boolval($item['valeur']));
                }
                $roomRepository = $this->entityManager->getRepository(Room::class);
                $room = $roomRepository->findOneBy(['roomName' => $item['localisation']]);

                $sa->setRoom($room);
            }

            $this->entityManager->persist($sa);
        }

        $this->entityManager->flush();
    }

    private function updateOrCreateMeasure(array $item, Sa $sa)
    {
        // Check if a measure with the same type and capture date already exists
        $existingMeasure = $sa->getMeasures()->filter(function(Measure $measure) use ($item) {
            return $measure->getType() === $item['nom'] && $measure->getCaptureDate()->format('Y-m-d H:i:s') === date('Y-m-d H:i:s', strtotime($item['dateCapture']));
        })->first();

        if (!$existingMeasure) {
            $dateTime = new \DateTime($item['dateCapture']);
            $measure = new Measure($item['id'], floatval($item['valeur']), $item['nom'], $dateTime, $item['description'], $sa);
            return $measure;
        }
        else{
            return null;
        }
    }
}
