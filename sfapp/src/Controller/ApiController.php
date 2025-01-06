<?php

namespace App\Controller;

use App\Entity\Measure;
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

class ApiController extends AbstractController
{
    private $client;
    private $logger;
    private $entityManager;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/obtenir-donnees', name: 'api_obtenir_donnees')]
    public function getDatasFromApi(): Response
    {
        $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures?page=1';

        $dbname = getenv('API_DBNAME');
        $username = getenv('API_USERNAME');
        $userpass = getenv('API_USERPASS');

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'accept' => 'application/json',
                    'dbname' => "sae34bdm1eq3",
                    'username' => "m1eq3",
                    'userpass' => "wewnUw-zetwo7-mognov",
                ],

            ]);

            $statusCode = $response->getStatusCode();
            $this->logger->info('API Response Status Code: ' . $statusCode);

            $data = $response->toArray();
            $this->logger->info('API Response Data: ' . json_encode($data));

            $this->storeDataInDatabase($data);

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

        return new Response('Données non récupérées.');
    }

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
                return strtotime($b['dateCapture']) - strtotime($a['dateCapture']);
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
                    }
                }
            }

            // Update the SA with the latest measure values
            foreach ($latestMeasures as $item) {
                if ($item['nom'] === 'temp') {
                    $sa->setTemperature($item['valeur']);
                } elseif ($item['nom'] === 'hum') {
                    $sa->setHumidity($item['valeur']);
                } elseif ($item['nom'] === 'co2') {
                    $sa->setCO2($item['valeur']);
                } elseif ($item['nom'] === 'lum') {
                    $sa->setLum($item['valeur']);
                } elseif ($item['nom'] === 'pres') {
                    $sa->setPres($item['valeur']);
                }
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

        if ($existingMeasure) {
            return $existingMeasure;
        }

        $measure = new Measure($item['id'], $item['valeur'], $item['nom'], new \DateTime($item['dateCapture']), $item['description'], $sa);
        return $measure;
    }
}
