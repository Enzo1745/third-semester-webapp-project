<?php

namespace App\Controller;

use App\Entity\Measure;
use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    #[Route('/api/obtenir_donnees', name: 'api_obtenir_donnees')]
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

        if (!$existingMeasure) {
            $measure = new Measure($item['id'], $item['valeur'], $item['nom'], new \DateTime($item['dateCapture']), $item['description'], $sa);
            return $measure;
        }
        else{
            return null;
        }
    }


    #[Route('/api/dernieres_donnees/{name}', name: 'api_get_last_measures')]
    public function getLastMeasures(string $name, SaRepository $saRepository): Response
    {
        // Rechercher le SA dans la base de données
        $sa = $saRepository->findOneBy(['name' => $name]);

        if (!$sa) {
            return new Response("Aucun SA trouvé pour le nom : {$name}", 404);
        }

        try {
            // Construire l'URL de l'API pour récupérer les dernières données
            $url = "https://sae34.k8s.iut-larochelle.fr/api/captures/last?nomsa={$name}&limit=5&page=1";

            // Faire la requête API
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'accept' => 'application/json',
                    'dbname' => "sae34bdm1eq3",
                    'username' => "m1eq3",
                    'userpass' => "wewnUw-zetwo7-mognov",
                ],
            ]);

            // Vérifier le statut de la réponse
            $statusCode = $response->getStatusCode();
            $this->logger->info('API Response Status Code: ' . $statusCode);

            // Traiter les données de la réponse
            $data = $response->toArray();
            $this->logger->info('API Response Data: ' . json_encode($data));

            $this->storeDataInDatabase($data);


            return $this->json($data, 200);
        } catch (TransportExceptionInterface | ClientExceptionInterface | DecodingExceptionInterface |
        RedirectionExceptionInterface | ServerExceptionInterface $e) {
            $this->logger->error('Erreur lors de la récupération des données: ' . $e->getMessage());
            return new Response('Erreur lors de la récupération des données: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/api/donnees_actuelles', name: 'api_refresh_data')]
    public function refreshData(SaRepository $saRepository): Response
    {
        // Récupérer tous les SA avec l'état "Installed"
        $installedSA = $saRepository->findBy(['state' => SAState::Installed]);

        if (empty($installedSA)) {
            return new Response('Aucun SA installé trouvé.', 404);
        }

        $results = [];

        foreach ($installedSA as $sa) {
            $name = $sa->getName();

            try {
                // Appeler la route pour récupérer les dernières données pour ce SA
                $url = $this->generateUrl('api_get_last_measures', ['name' => $name], UrlGeneratorInterface::ABSOLUTE_URL);

                $response = $this->client->request('GET', $url);
                $statusCode = $response->getStatusCode();

                if ($statusCode === 200) {
                    $data = $response->toArray();
                    $results[$name] = $data;

                    $this->logger->info("Données mises à jour pour le SA : {$name}");
                } else {
                    $this->logger->warning("Impossible de récupérer les données pour le SA : {$name}. Code HTTP : {$statusCode}");
                }
            } catch (TransportExceptionInterface | ClientExceptionInterface | DecodingExceptionInterface |
            RedirectionExceptionInterface | ServerExceptionInterface $e) {
                $this->logger->error("Erreur lors de la récupération des données pour le SA : {$name}. Message : " . $e->getMessage());
            }
        }

        return $this->json(['message' => "Données mises à jour pour les SAs installés.", 'results' => $results], 200);
    }
}
